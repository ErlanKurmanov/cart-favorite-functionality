<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request; // We'll use a more generic Request for some actions
use Illuminate\Http\Response; // Still useful for certain HTTP status codes, though less common with Inertia.

use App\Contracts\Services\CartServiceInterface;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Resources\CartResource; // Still useful for transforming data before passing it to Vue.
use Illuminate\Support\Facades\Auth;
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

        $cart = $this->cartService->show();
        return Inertia::render('Cart', [
            'cart' => new CartResource($cart), // Transform cart data using your resource
            'successMessage' => session('success'), // Example: flash messages for success
            'errorMessage' => session('error'),     // Example: flash messages for errors
        ]);

    }

    /**
     * Adds or updates an item in the cart.
     * This action will typically redirect back or to another page after completion.
     *
     * @param AddCartItemRequest $request The request containing item data.
     * @return \Illuminate\Http\RedirectResponse Redirects back or to a specific page.
     */
    public function addItem(AddCartItemRequest $request)
    {
        try {
            $itemData = $request->validated();

            $cart = $this->cartService->addItem($itemData);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'cart_add_error' => config('app.debug') ? $e->getMessage() : 'Failed to add item to cart. Please try again.',
            ]);
        }
    }

    /**
     * Removes an item from the cart.
     * This action will typically redirect back or to another page after completion.
     *
     * @param int $id The ID of the item to remove.
     * @return \Illuminate\Http\RedirectResponse Redirects back or to a specific page.
     */
    public function removeItem(int $id)
    {
        try {
            $cart = $this->cartService->removeItem($id);
        } catch (\Exception $e) {
            // Redirect back with an error message
            return redirect()->back()->withErrors([
                'cart_remove_error' => config('app.debug') ? $e->getMessage() : 'Failed to remove item from cart. Please try again.',
            ]);
        }
    }

     public function updateItem(Request $request, int $id)
     {
         try {
             $itemData = $request->validate([
                 'quantity' => 'required'
             ]);

             $cart = Auth::user()->cart()->firstOrCreate();
             $cart->items()->find($id)->update([
                 'quantity' => $itemData['quantity']
             ]);
         } catch (\Exception $e) {
             return redirect()->back()->withErrors(['cart_update_error' => 'Failed to update item.']);
         }
     }

    public function clearAll()
    {
        $cart = Auth::user()->cart()->firstOrCreate();
        $cart->items()->delete();
   }
}
