<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = trim((string) $request->query('q', ''));

        $items = Product::query()
            ->with(['mstLabel', 'variants.mstSize'])
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('category', 'like', '%' . $q . '%');
            })
            ->orderByDesc('id')
            ->get();

        return ProductResource::collection($items);
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->with(['mstLabel', 'variants.mstSize'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
