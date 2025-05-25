<?php

namespace App\Http\Controllers;

use App\Events\CartCleared;
use App\Events\ItemRemovedFromCart;
use App\Events\ItemUpdatedInCart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Contracts\Services\CartServiceInterface;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Add these imports for Pusher integration
use App\Events\ItemAddedToCart;
use Illuminate\Support\Facades\Event;

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
            'cart' => new CartResource($cart),
            'successMessage' => session('success'),
            'errorMessage' => session('error'),
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

            // Fire the event for Pusher notification
            Event::dispatch(new ItemAddedToCart(
                Auth::user(),
                $itemData,
                $cart
            ));

            return redirect()->back()->with('success', 'Item added to cart successfully!');

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
     * @param int $id The ID of the cart item (not product ID) to remove.
     * @return \Illuminate\Http\RedirectResponse Redirects back or to a specific page.
     */
    public function removeItem(int $id)
    {
        try {
            // Add debugging
            \Log::info('Attempting to remove cart item', [
                'item_id' => $id,
                'user_id' => Auth::id()
            ]);

            // Check if cart item exists and belongs to user
            $user = Auth::user();
            $cart = $user->cart()->first();

            if (!$cart) {
                throw new \Exception('Cart not found for user');
            }

            $cartItem = $cart->items()->find($id);

            if (!$cartItem) {
                throw new \Exception('Cart item not found');
            }

            // Use the service to remove the item
            $cart = $this->cartService->removeItem($id);

            // Fire event for Pusher notification
            Event::dispatch(new ItemRemovedFromCart(
                Auth::user(),
                $cart
            ));

            return redirect()->back()->with('success', 'Item removed from cart successfully!');

        } catch (\Exception $e) {
            // Log the actual error
            \Log::error('Failed to remove cart item', [
                'item_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

            // Fire event for Pusher notification
            Event::dispatch(new ItemUpdatedInCart(
                Auth::user(),
                $itemData,
                $cart
            ));

            return redirect()->back()->with('success', 'Cart updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['cart_update_error' => 'Failed to update item.']);
        }
    }

    public function clearAll()
    {
        try {
            $cart = Auth::user()->cart()->firstOrCreate();
            $cart->items()->delete();

            // Fire event for Pusher notification
            Event::dispatch(new CartCleared(
                Auth::user(),
                $cart
            ));

            return redirect()->back()->with('success', 'Cart cleared successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['cart_clear_error' => 'Failed to clear cart.']);
        }
    }
}
