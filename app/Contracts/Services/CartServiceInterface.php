<?php

namespace App\Contracts\Services;

use App\Models\Product;

interface CartServiceInterface
{
    public function addItem(array $itemData);
    public function removeItem(int $product);
}
