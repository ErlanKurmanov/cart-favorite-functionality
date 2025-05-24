<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image_url'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function favoritedByUsers():BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite_items');
    }
}
