<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display the catalog homepage with all products.
     * Supports filtering by category via query parameter.
     */
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $categories->each(function ($category) {
            $category->products_count = $category->products()->count();
        });

        $query = Product::with('category')->active();

        // Filter by category if provided
        if ($request->has('category') && $request->category !== 'all') {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        $products = $query->orderBy('name')->get();

        return view('home', compact('products', 'categories'));
    }

    /**
     * Display a single product detail.
     */
    public function show(string $slug)
    {
        $product = Product::with(['category', 'variations'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Get related products from same category (excluding current product)
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
