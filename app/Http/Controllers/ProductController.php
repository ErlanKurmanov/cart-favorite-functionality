<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProductServiceInterface;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products.
     *
     * @return Response
     */
    public function index(): Response
    {
        $products = $this->productService->getAllProducts();


        return Inertia::render('Products/Index', [
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * Display products by category.
     *
     * @param int $id The category ID.
     * @return Response|RedirectResponse
     */
    public function byCategory(int $id): Response|RedirectResponse
    {
        try {
            $products = $this->productService->getProductsByCategory($id);

            return Inertia::render('Products/ByCategory', [
                'products' => ProductResource::collection($products),
                'categoryId' => $id, // Passing category ID might be useful for the Vue component
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Category not found.');

        }
    }

    /**
     * Display the specified product.
     *
     * @param int $id The product ID.
     * @return Response|RedirectResponse
     */
    public function show(int $id): Response|RedirectResponse
    {
        try {
            $product = $this->productService->getProductById($id);

            return Inertia::render('Products/Show', [
                'product' => new ProductResource($product), // Single resource
            ]);
        } catch (ModelNotFoundException $e) {
            // If the product is not found, redirect with an error.
            return redirect()->back()->with('error', 'Product not found.');
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @param CreateProductRequest $request
     * @return RedirectResponse
     */
    public function store(CreateProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = $this->productService->createProduct($data);

        return redirect()->route('products.show', $product->id)->with('success', 'Product created successfully!');
        // Or redirect to the product index:
        // return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Update the specified product in storage.
     *
     * @param UpdateProductRequest $request
     * @param int $id The product ID.
     * @return RedirectResponse
     */
    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();
        try {
            $product = $this->productService->updateProduct($id, $data);

            // Redirect back with a success message.
            return redirect()->back()->with('success', 'Product updated successfully!');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Product not found.');
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param int $id The product ID.
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if ($deleted) {
                return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
            }
            return redirect()->back()->with('error', 'Failed to delete product.');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Product not found.');
        }
    }
}
