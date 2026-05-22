<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('category', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'rating' => ['nullable', 'numeric', 'max:5'],
            'stock' => ['required', 'numeric'],
            'image' => ['required', 'file', 'mimes:svg,webp', 'mimetypes:image/svg+xml,image/webp', 'max:2048'],
            'is_active' => ['nullable'],
            'is_new' => ['nullable'],
            'is_sale' => ['nullable'],
        ]);

        $title = trim((string) $validated['title']);
        $category = trim((string) $validated['category']);

        $price = $this->parseInt((string) $validated['price']);
        $stock = max(0, $this->parseInt((string) $validated['stock']));
        $rating = $validated['rating'] !== null ? (float) $validated['rating'] : 0.0;
        $rating = max(0.0, min(5.0, $rating));

        $slug = $this->makeUniqueSlug($title);

        $path = $request->file('image')->store('products', 'public');

        $product = Product::create([
            'title' => $title,
            'slug' => $slug,
            'category' => $category,
            'price' => $price,
            'rating' => $rating,
            'image' => $path,
            'stock' => $stock,
            'is_active' => $request->boolean('is_active', true),
            'is_new' => $request->boolean('is_new', false),
            'is_sale' => $request->boolean('is_sale', false),
        ]);

        if ($stock > 0) {
            ProductStockMovement::create([
                'product_id' => (int) $product->id,
                'delta' => (int) $stock,
                'stock_before' => 0,
                'stock_after' => (int) $stock,
                'source' => 'ADMIN_CREATE',
                'actor_user_id' => optional($request->user())->id,
                'note' => 'Stock awal',
                'created_at' => now(),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'rating' => ['nullable', 'numeric', 'max:5'],
            'image' => ['nullable', 'file', 'mimes:svg,webp', 'mimetypes:image/svg+xml,image/webp', 'max:2048'],
            'is_active' => ['nullable'],
            'is_new' => ['nullable'],
            'is_sale' => ['nullable'],
        ]);

        $title = trim((string) $validated['title']);
        $category = trim((string) $validated['category']);

        $price = $this->parseInt((string) $validated['price']);
        $rating = $validated['rating'] !== null ? (float) $validated['rating'] : 0.0;
        $rating = max(0.0, min(5.0, $rating));

        $slug = $this->makeUniqueSlug($title, $product->id);

        $path = null;
        $file = $request->file('image');
        if ($file) {
            $path = $file->store('products', 'public');

            $oldImage = trim((string) ($product->image ?? ''));
            if ($oldImage !== '') {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $payload = [
            'title' => $title,
            'slug' => $slug,
            'category' => $category,
            'price' => $price,
            'rating' => $rating,
            'is_active' => $request->boolean('is_active', true),
            'is_new' => $request->boolean('is_new', false),
            'is_sale' => $request->boolean('is_sale', false),
        ];
        if ($path !== null) {
            $payload['image'] = $path;
        }
        $product->update($payload);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $oldImage = trim((string) ($product->image ?? ''));
        if ($oldImage !== '') {
            Storage::disk('public')->delete($oldImage);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function stock(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('category', 'like', '%' . $q . '%');
            })
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('products.stock', [
            'products' => $products,
            'q' => $q,
        ]);
    }

    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'stock' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $before = (int) ($product->stock ?? 0);
        $after = max(0, $this->parseInt((string) $validated['stock']));

        if ($before !== $after) {
            $product->update(['stock' => $after]);

            ProductStockMovement::create([
                'product_id' => (int) $product->id,
                'delta' => (int) ($after - $before),
                'stock_before' => $before,
                'stock_after' => (int) $after,
                'source' => 'ADMIN_ADJUST',
                'actor_user_id' => optional($request->user())->id,
                'note' => $validated['note'] ?? null,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Stok berhasil diperbarui.');
    }

    public function adjustment(Product $product): View
    {
        return view('products.adjustment', [
            'product' => $product,
        ]);
    }

    public function mutations(Product $product, Request $request): View
    {
        $items = ProductStockMovement::query()
            ->where('product_id', $product->id)
            ->with('actorUser')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('products.mutations', [
            'product' => $product,
            'items' => $items,
        ]);
    }

    private function parseInt(string $value): int
    {
        $digits = preg_replace('/[^\d]/', '', $value);
        $n = (int) ($digits ?: 0);
        return max(0, $n);
    }

    private function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = (string) Str::uuid();
        }

        $slug = $base;
        $i = 2;

        while (
            Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
