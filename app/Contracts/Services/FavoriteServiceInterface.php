<?php

namespace App\Contracts\Services;

use App\Models\Product;
use App\Models\User;

interface FavoriteServiceInterface
{
    public function add(Product $product, User $user): bool;

    public function removeFavorite(Product $product, User $user): bool;

    public function isFavorite(Product $product, User $user): bool;

    public function getUserFavorites(User $user);

    public function clearAllFavorites(User $user): bool;
}
