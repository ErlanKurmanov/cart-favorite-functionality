<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create fixed categories
        $categories = [
            'Pizza',
            'Sushi',
            'Rolls'
        ];

        foreach ($categories as $name) {
            $category = Category::firstOrCreate(['name' => $name]);

            // Create 4–6 products per category
            Product::factory(rand(4, 6))->create([
                'category_id' => $category->id,
            ]);
        }
    }

}
