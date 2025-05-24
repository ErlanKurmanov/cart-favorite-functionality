<?php

namespace App\Http\Controllers;

use App\Contracts\Services\FavoriteServiceInterface;
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

    /**
     * Add a product to the user's favorites.
     *
     * @param Product $product
     * @return RedirectResponse
     */
    public function addFavorite(Product $product): RedirectResponse
    {
        try {
            $this->favoriteService->add($product, Auth::user());
            return redirect()->back()->with('success', 'Added to favorites!');
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
            $this->favoriteService->removeFavorite($product, Auth::user());

            // Redirect back with a success message.
            return redirect()->back()->with('success', 'Removed from favorites!');
        } catch (\Exception $e) {
            logger()->error('Failed to remove favorite: ' . $e->getMessage());

            // If an error occurs, redirect back with an error message.
            return redirect()->back()->with('error', 'Failed to remove from favorites. Please try again.');
        }
    }

    /**
     * Display a list of the user's favorite items.
     *
     * @return \Inertia\Response
     */
    public function getUserFavorites()
    {
        $favoriteItems = Auth::user()->favoriteItems()->with('product')->get();

        return Inertia::render('Favorites/Index', [
            'favoriteItems' => $favoriteItems,
        ]);
    }
}
