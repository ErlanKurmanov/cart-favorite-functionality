<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Services\CartServiceInterface;
use App\Models\Cart;

/**
 * Class CartService
 *
 * Service class responsible for handling shopping cart logic, including
 * syncing guest carts, adding/removing items, and retrieving the cart.
 * It interacts with the CartRepository to persist cart data.
 */
class CartService implements CartServiceInterface
{
    /**
     * The cart repository implementation.
     *
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cartRepository;

    /**
     * Create a new CartService instance.
     *
     * @param CartRepositoryInterface $cartRepository The repository to use for cart data access.
     */
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }


    /**
     * Adds a single item to the authenticated user's cart.
     *
     * If the item already exists in the cart, the quantity is updated.
     * Otherwise, a new cart item is created.
     *
     * @param array $itemData An array containing the item data, typically with 'product_id' and 'quantity'.
     * @return Cart The updated shopping cart instance for the authenticated user.
     * @throws \Exception If there is an error during the add operation.
     */
    public function addItem(array $itemData): Cart
    {
        try {
            $cart = $this->cartRepository->getForUser(Auth::user());

            DB::transaction(function () use ($cart, $itemData) {
                $this->mergeCartItem($cart, $itemData);
            });

            return $cart->fresh(['cartItems']);
        } catch (\Exception $e) {
            \Log::error('Error adding item to cart: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Removes an item from the authenticated user's cart based on product ID.
     *
     * @param int $id The product ID of the item to remove.
     * @return Cart The updated shopping cart instance for the authenticated user.
     * @throws \Exception If there is an error during the removal process.
     */
    public function removeItem(int $id): Cart
    {
        try {
            $cart = $this->cartRepository->getForUser(Auth::user());

            // Check if the item exists in the cart
            $itemExists = $cart->cartItems()->where('product_id', $id)->exists();

            if (!$itemExists) {
                throw new CartItemNotFoundException("Cart item with product ID {$id} not found.");
            }

            DB::transaction(function () use ($cart, $id) {
                $cart->cartItems()->where('product_id', $id)->delete();
            });

            return $cart->fresh(['cartItems']);
        } catch (\Exception $e) {
            \Log::error('Error removing item from cart: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieves the shopping cart for the currently authenticated user.
     *
     * @return Cart The shopping cart instance for the authenticated user.
     * @throws \Exception If there is an error retrieving the cart.
     */
    public function show(): Cart
    {
        try {
            return $this->cartRepository->getForUser(Auth::user());
        } catch (\Exception $e) {
            \Log::error('Error retrieving cart: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Merges a single item into the given cart.
     *
     * If the item (product_id) already exists in the cart, its quantity is increased.
     * Otherwise, a new cart item is created for the product.
     *
     * @param Cart $cart The cart instance to merge the item into.
     * @param array $itemData The item data to merge, typically with 'product_id' and 'quantity'.
     * @return void This method does not return a value.
     */
    protected function mergeCartItem(Cart $cart, array $itemData): void
    {
        $existingItem = $cart->cartItems()
            ->where('product_id', $itemData['product_id'])
            ->first();

        if ($existingItem) {
            $existingItem->update(
                [
                    'quantity' => $existingItem->quantity + $itemData['quantity'],
                    'options' => $itemData['options'] ?? $existingItem->options,
                ]
            );
        } else {
            $cart->cartItems()->create($itemData);
        }
    }
}
