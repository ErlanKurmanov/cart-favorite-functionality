<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use App\Contracts\Services\CartServiceInterface;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\SyncCartRequest;
use App\Http\Resources\CartResource;

/**
 * Class CartController
 *
 * Handles shopping cart operations via API.
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
     * Adds or updates an item in the cart.
     *
     * @param AddCartItemRequest $request The request containing item data.
     * @return JsonResponse Returns a JSON response with the updated cart.
     */
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        try {
            $itemData = $request->validated();
            $cart = $this->cartService->addItem($itemData);

            return (new CartResource($cart))->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add item to cart',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Removes an item from the cart.
     *
     * @param int $id The ID of the item to remove.
     * @return JsonResponse Returns a JSON response with the updated cart.
     */
    public function removeItem(int $id): JsonResponse
    {
        try {
            $cart = $this->cartService->removeItem($id);

            return (new CartResource($cart))->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove item from cart',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Displays the current user's cart.
     *
     * @return JsonResponse Returns a JSON response with the current cart.
     */
    public function show(): JsonResponse
    {
        try {
            $cart = $this->cartService->show();

            return (new CartResource($cart))->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve cart',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
