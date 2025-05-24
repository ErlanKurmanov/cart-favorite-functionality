<?php

namespace App\Services;

use App\Contracts\Services\FavoriteServiceInterface;
use App\Models\Product;
use App\Models\User;

class FavoriteService implements FavoriteServiceInterface
{
    public function add(Product $product, User $user): bool
    {
        try {
            // Check if the product is already in favorites
            if (!$user->favoriteItems()->where('product_id', $product->id)->exists()) {
                $user->favoriteItems()->attach($product->id);
                return true;
            }
            return false; // Already exists
        } catch (\Exception $e) {
            logger()->error('Failed to add favorite: ' . $e->getMessage());
            throw $e;
        }
    }

    public function removeFavorite(Product $product, User $user): bool
    {
        try {
            if ($user->favoriteItems()->where('product_id', $product->id)->exists()) {
                $user->favoriteItems()->detach($product->id);
                return true;
            }
            return false; // Didn't exist in favorites
        } catch (\Exception $e) {
            logger()->error('Failed to remove favorite: ' . $e->getMessage());
            throw $e;
        }
    }

    public function isFavorite(Product $product, User $user): bool
    {
        return $user->favoriteItems()->where('product_id', $product->id)->exists();
    }

    public function getUserFavorites(User $user)
    {
        return $user->favoriteItems()->with('category')->get();
    }

    public function clearAllFavorites(User $user): bool
    {
        try {
            $user->favoriteItems()->detach();
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to clear all favorites: ' . $e->getMessage());
            throw $e;
        }
    }
}
