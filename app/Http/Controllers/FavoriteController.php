<?php

namespace App\Http\Controllers;

use App\Contracts\Services\FavoriteServiceInterface;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    /**
     * The favorite service instance.
     *
     * @var FavoriteServiceInterface
     */
    protected FavoriteServiceInterface $favoriteService;

    /**
     * Create a new controller instance.
     *
     * @param FavoriteServiceInterface $favoriteService
     * @return void
     */
    public function __construct(FavoriteServiceInterface $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function index()
    {
        $user = Auth::user(); // Get the currently authenticated user

        $favorites = $user->favoriteItems()->with('category')->get();
        $categories = Category::all();
        $cartItemsCount = optional($user->cart)->items->sum('quantity') ?? 0;

        return Inertia::render('Favorites', [
            'favorites' => $favorites,
            'categories' => $categories,
            'cartItemsCount' => $cartItemsCount
        ]);
    }

    /**
     * Display a list of the user's favorite items.
     *
     * @return \Inertia\Response
     */

    /**
     * Add a product to the user's favorites.
     *
     * @param Product $product
     * @return RedirectResponse
     */
    public function addFavorite(Product $product): RedirectResponse
    {
        try {
            $added = $this->favoriteService->add($product, Auth::user());

            if ($added) {
                return redirect()->back()->with('success', 'Added to favorites!');
            } else {
                return redirect()->back()->with('info', 'Product is already in favorites.');
            }
        } catch (\Exception $e) {
            logger()->error('Failed to add favorite: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add to favorites. Please try again.');
        }
    }

    /**
     * Remove a product from the user's favorites.
     *
     * @param Product $product
     * @return RedirectResponse
     */
    public function removeFavorite(Product $product): RedirectResponse
    {
        try {
            $removed = $this->favoriteService->removeFavorite($product, Auth::user());
            if ($removed) {
                return redirect()->back()->with('success', 'Removed from favorites!');
            } else {
                return redirect()->back()->with('info', 'Product was not in favorites.');
            }
        } catch (\Exception $e) {
            logger()->error('Failed to remove favorite: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove from favorites. Please try again.');
        }
    }

    public function clear(): RedirectResponse
    {
        try {
            $user = Auth::user();
            $user->favoriteItems()->detach();

            return redirect()->back()->with('success', 'All favorites cleared!');
        } catch (\Exception $e) {
            logger()->error('Failed to clear favorites: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to clear favorites. Please try again.');
        }
    }

}
