<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'unit' => ['required', 'in:Pieces,Unit'],
            'label' => ['required', 'in:none,new,promo,best_seller'],
            'price' => ['required', 'numeric'],
            'rating' => ['nullable', 'numeric', 'max:5'],
            'stock' => ['required', 'numeric'],
            'image' => ['required', 'file', 'mimes:svg,webp', 'mimetypes:image/svg+xml,image/webp', 'max:2048'],
            'is_active' => ['nullable'],
        ]);

        $title = trim((string) $validated['title']);
        $category = trim((string) $validated['category']);
        $unit = trim((string) $validated['unit']);
        $label = (string) ($validated['label'] ?? 'none');

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
            'unit' => $unit,
            'price' => $price,
            'rating' => $rating,
            'image' => $path,
            'stock' => $stock,
            'is_active' => $request->boolean('is_active'),
            'is_new' => $label === 'new',
            'is_sale' => $label === 'promo',
            'is_best_seller' => $label === 'best_seller',
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
            'unit' => ['required', 'in:Pieces,Unit'],
            'label' => ['required', 'in:none,new,promo,best_seller'],
            'price' => ['required', 'numeric'],
            'rating' => ['nullable', 'numeric', 'max:5'],
            'image' => ['nullable', 'file', 'mimes:svg,webp', 'mimetypes:image/svg+xml,image/webp', 'max:2048'],
            'is_active' => ['nullable'],
        ]);

        $title = trim((string) $validated['title']);
        $category = trim((string) $validated['category']);
        $unit = trim((string) $validated['unit']);
        $label = (string) ($validated['label'] ?? 'none');

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
            'unit' => $unit,
            'price' => $price,
            'rating' => $rating,
            'is_active' => $request->boolean('is_active'),
            'is_new' => $label === 'new',
            'is_sale' => $label === 'promo',
            'is_best_seller' => $label === 'best_seller',
        ];
        if ($path !== null) {
            $payload['image'] = $path;
        }
        $product->update($payload);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function stock(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $tab = trim((string) $request->query('tab', 'all'));
        if (!in_array($tab, ['all', 'low', 'out'], true)) {
            $tab = 'all';
        }

        $base = Product::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('category', 'like', '%' . $q . '%');
            });

        $counts = [
            'all' => (clone $base)->count(),
            'low' => (clone $base)->where('stock', '>', 0)->where('stock', '<', 10)->count(),
            'out' => (clone $base)->where('stock', '=', 0)->count(),
        ];

        $products = (clone $base)
            ->when($tab === 'low', fn($query) => $query->where('stock', '>', 0)->where('stock', '<', 10))
            ->when($tab === 'out', fn($query) => $query->where('stock', '=', 0))
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('products.stock', [
            'products' => $products,
            'q' => $q,
            'tab' => $tab,
            'counts' => $counts,
        ]);
    }

    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        $context = trim((string) $request->input('context', ''));

        $validated = $request->validate([
            'context' => ['nullable', 'string', 'max:50'],
            'stock' => ['required', 'numeric'],
            'reason' => $context === 'adjustment'
                ? ['required', 'in:Stok Opname,Barang Rusak,Koreksi Sistem,Retur,Lainnya']
                : ['nullable', 'string', 'max:100'],
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
                'reference' => $validated['reason'] ?? null,
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
        $tab = trim((string) $request->query('tab', 'all'));
        if (!in_array($tab, ['all', 'in', 'out'], true)) {
            $tab = 'all';
        }

        $items = ProductStockMovement::query()
            ->where('product_id', $product->id)
            ->when($tab === 'in', fn($query) => $query->where('delta', '>', 0))
            ->when($tab === 'out', fn($query) => $query->where('delta', '<', 0))
            ->with('actorUser')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('products.mutations', [
            'product' => $product,
            'items' => $items,
            'tab' => $tab,
        ]);
    }

    public function storeMutation(Product $product, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:in,out'],
            'qty' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = (int) $validated['qty'];
        $delta = $validated['type'] === 'in' ? $qty : -$qty;

        DB::transaction(function () use ($product, $request, $validated, $delta) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $before = (int) ($locked->stock ?? 0);
            $rawAfter = $before + (int) $delta;

            if ($rawAfter < 0) {
                abort(422, 'Stok tidak mencukupi.');
            }

            $after = $rawAfter;

            if ($after === $before) {
                return;
            }

            $locked->update(['stock' => $after]);

            ProductStockMovement::create([
                'product_id' => (int) $locked->id,
                'delta' => (int) $delta,
                'stock_before' => $before,
                'stock_after' => $after,
                'source' => 'ADMIN_MANUAL',
                'actor_user_id' => optional($request->user())->id,
                'reference' => $validated['reference'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Mutasi berhasil ditambahkan.');
    }

    public function exportMutations(Product $product, Request $request)
    {
        $tab = trim((string) $request->query('tab', 'all'));
        if (!in_array($tab, ['all', 'in', 'out'], true)) {
            $tab = 'all';
        }

        $rows = ProductStockMovement::query()
            ->where('product_id', $product->id)
            ->when($tab === 'in', fn($query) => $query->where('delta', '>', 0))
            ->when($tab === 'out', fn($query) => $query->where('delta', '<', 0))
            ->with('actorUser')
            ->orderByDesc('id')
            ->get();

        $fileName = 'mutasi-stock-' . (string) ($product->slug ?: $product->id) . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['tanggal', 'tipe', 'qty', 'stok_sebelum', 'stok_sesudah', 'sumber', 'pelaku', 'referensi', 'catatan']);

            foreach ($rows as $m) {
                $delta = (int) ($m->delta ?? 0);
                $type = $delta >= 0 ? 'masuk' : 'keluar';
                $qty = abs($delta);
                $actor = $m->actorUser ? ($m->actorUser->name ?? $m->actorUser->email) : '';
                fputcsv($out, [
                    optional($m->created_at)->format('Y-m-d H:i:s'),
                    $type,
                    $qty,
                    (int) ($m->stock_before ?? 0),
                    (int) ($m->stock_after ?? 0),
                    (string) ($m->source ?? ''),
                    (string) $actor,
                    (string) ($m->reference ?? ''),
                    (string) ($m->note ?? ''),
                ]);
            }

            fclose($out);
        }, $fileName);
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
