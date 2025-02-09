<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\ProductResource\Api\Transformers\ProductTransformer;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (! $query) {
            return response()->json(['error' => 'Query parameter is required.'], 400);
        }

        // Search across models using Scout
        $products = Product::search($query)->take(10)->get();
        $categories = Category::search($query)->take(10)->get();
        $blogPosts = BlogPost::search($query)->get();

        // Combine results
        $results = [
            'products' => ProductTransformer::collection($products),
            'categories' => $categories,
            'blogPosts' => $blogPosts,
        ];

        return response()->json($results);
    }
}
