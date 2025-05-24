<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ProductController::class, 'index']);
//Route::get('/', function () {
//    return Inertia::render('Home');
//});

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'addItem']);
Route::patch('/cart/update/{id}', [CartController::class, 'updateItem']);
Route::delete('/cart/remove/{id}', [CartController::class, 'removeItem']);
Route::delete('/cart/clear', [CartController::class, 'clearAll']);

Route::get('/favorites', [FavoriteController::class, 'getUserFavorites'])->name('favorites.index');
Route::post('/favorites/add/{product}', [FavoriteController::class, 'addFavorite'])->name('favorites.add');
Route::delete('/favorites/remove/{product}', [FavoriteController::class, 'removeFavorite'])->name('favorites.remove');

//Route::get('/', function () {
//    return Inertia::render('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//});

//Route::get('/dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');
//
//Route::middleware('auth')->group(function () {
//    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//});

require __DIR__.'/auth.php';
