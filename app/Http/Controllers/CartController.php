<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // We'll use a more generic Request for some actions
use Illuminate\Http\Response; // Still useful for certain HTTP status codes, though less common with Inertia.

use App\Contracts\Services\CartServiceInterface;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Resources\CartResource; // Still useful for transforming data before passing it to Vue.
use Inertia\Inertia; // The star of the show for Inertia.js

/**
 * Class CartController
 *
 * Handles shopping cart operations, optimized for Inertia.js with Vue.
 */
class CartController extends Controller
{
    protected CartServiceInterface $cartService;

    /**
     * CartController constructor.
     *
     * @param CartServiceInterface $cartService The service responsible for cart logic.
     */
    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Displays the current user's cart using an Inertia page.
     * This is the primary way to "show" data with Inertia.
     *
     * @return \Inertia\Response An Inertia response to render a Vue component.
     */
    public function index(): \Inertia\Response
    {
        try {
            $cart = $this->cartService->show();

            return Inertia::render('Cart/Index', [
                'cart' => new CartResource($cart), // Transform cart data using your resource
                'successMessage' => session('success'), // Example: flash messages for success
                'errorMessage' => session('error'),     // Example: flash messages for errors
            ]);
        } catch (\Exception $e) {
            // For errors that prevent rendering, you might redirect back with an error message
            // or render a generic error page.
            return Inertia::render('Error', [ // Assuming you have an Error.vue component
                'message' => 'Failed to retrieve cart. Please try again.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }

    /**
     * Adds or updates an item in the cart.
     * This action will typically redirect back or to another page after completion.
     *
     * @param AddCartItemRequest $request The request containing item data.
     * @return \Illuminate\Http\RedirectResponse Redirects back or to a specific page.
     */
    public function addItem(AddCartItemRequest $request): \Illuminate\Http\RedirectResponse
    {
        try {
            $itemData = $request->validated();
            $cart = $this->cartService->addItem($itemData);

            return redirect()->route('cart.index')->with('success', 'Item added to cart successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'cart_add_error' => config('app.debug') ? $e->getMessage() : 'Failed to add item to cart. Please try again.',
            ])->withInput(); // Keep old input if desired
        }
    }

    /**
     * Removes an item from the cart.
     * This action will typically redirect back or to another page after completion.
     *
     * @param int $id The ID of the item to remove.
     * @return \Illuminate\Http\RedirectResponse Redirects back or to a specific page.
     */
    public function removeItem(int $id): \Illuminate\Http\RedirectResponse
    {
        try {
            $cart = $this->cartService->removeItem($id);
            return redirect()->route('cart.index')->with('success', 'Item removed from cart successfully!');
        } catch (\Exception $e) {
            // Redirect back with an error message
            return redirect()->back()->withErrors([
                'cart_remove_error' => config('app.debug') ? $e->getMessage() : 'Failed to remove item from cart. Please try again.',
            ]);
        }
    }

    // You might also have a 'updateItem' method for changing quantities, similar to 'addItem'
    // public function updateItem(UpdateCartItemRequest $request, int $id): \Illuminate\Http\RedirectResponse
    // {
    //     try {
    //         $itemData = $request->validated();
    //         $cart = $this->cartService->updateItem($id, $itemData);
    //         return redirect()->route('cart.index')->with('success', 'Cart updated successfully!');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors(['cart_update_error' => 'Failed to update item.']);
    //     }
    // }

    // If you need a full cart synchronization, you might use it like this,
    // though typically `addItem`, `removeItem`, `updateItem` are more common.
    // public function sync(SyncCartRequest $request): \Illuminate\Http\RedirectResponse
    // {
    //     try {
    //         $cartData = $request->validated();
    //         $cart = $this->cartService->syncCart($cartData);
    //         return redirect()->route('cart.index')->with('success', 'Cart synchronized successfully!');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withErrors(['cart_sync_error' => 'Failed to synchronize cart.']);
    //     }
    // }
}
