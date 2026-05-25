<?php

namespace App\Http\Controllers;

use App\Models\MstLabel;
use App\Models\MstSize;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with('mstLabel')
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
        $labels = MstLabel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('products.create', [
            'labels' => $labels,
            'sizes' => MstSize::query()->where('is_active', true)->orderBy('sort_order')->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:Pieces,Unit'],
            'mst_label_id' => ['required', 'integer', 'exists:mst_labels,id'],
            'price' => ['required', 'numeric'],
            'rating' => ['nullable', 'numeric', 'max:5'],
            'stock' => ['required', 'numeric'],
            'image' => ['required', 'file', 'mimes:svg,webp', 'mimetypes:image/svg+xml,image/webp', 'max:2048'],
            'is_active' => ['nullable'],
            'variants_present' => ['nullable', 'in:1'],
            'variants' => ['nullable', 'array'],
            'variants.*.mst_size_id' => ['required_with:variants', 'integer', 'exists:mst_sizes,id', 'distinct'],
            'variants.*.sku' => ['nullable', 'string', 'max:80', 'distinct', 'unique:product_variants,sku'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
        ]);

        $title = trim((string) $validated['title']);
        $category = trim((string) $validated['category']);
        $unit = trim((string) $validated['unit']);
        $label = MstLabel::query()->find((int) $validated['mst_label_id']);
        if (!$label) {
            $label = MstLabel::query()->where('code', 'none')->first();
        }
        $labelCode = strtolower(trim((string) ($label?->code ?? 'none')));

        $price = $this->parseInt((string) $validated['price']);
        $stock = max(0, $this->parseInt((string) $validated['stock']));
        $ratingInput = $validated['rating'] ?? null;
        $rating = $ratingInput !== null ? (float) $ratingInput : 0.0;
        $rating = max(0.0, min(5.0, $rating));

        $slug = $this->makeUniqueSlug($title);

        $path = $request->file('image')->store('products', 'public');

        $product = Product::create([
            'title' => $title,
            'slug' => $slug,
            'category' => $category,
            'mst_label_id' => $label?->id,
            'unit' => $unit,
            'price' => $price,
            'rating' => $rating,
            'image' => $path,
            'stock' => $stock,
            'is_active' => $request->boolean('is_active'),
            'is_new' => $labelCode === 'new',
            'is_sale' => $labelCode === 'promo',
            'is_best_seller' => $labelCode === 'best_seller',
        ]);

        $variantsPresent = (string) ($validated['variants_present'] ?? '') === '1';
        if ($variantsPresent) {
            $variantsInput = $request->input('variants');
            $variantsInput = is_array($variantsInput) ? $variantsInput : [];

            $sizesById = MstSize::query()->select(['id', 'code'])->get()->keyBy('id');

            $rows = [];
            foreach ($variantsInput as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $mstSizeId = (int) ($row['mst_size_id'] ?? 0);
                $sizeCode = strtoupper(trim((string) ($sizesById[$mstSizeId]->code ?? '')));
                if ($mstSizeId <= 0 || $sizeCode === '') continue;
                $sku = trim((string) ($row['sku'] ?? ''));
                $color = trim((string) ($row['color'] ?? ''));
                $vStock = (int) ($row['stock'] ?? 0);
                $priceOverride = $row['price'] ?? null;
                $priceOverride = $priceOverride === null || $priceOverride === '' ? null : (int) $priceOverride;

                $rows[] = [
                    'product_id' => (int) $product->id,
                    'mst_size_id' => $mstSizeId,
                    'sku' => $sku !== '' ? $sku : null,
                    'size' => $sizeCode,
                    'color' => $color !== '' ? $color : null,
                    'stock' => max(0, $vStock),
                    'price' => $priceOverride !== null ? max(0, $priceOverride) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($rows) > 0) {
                ProductVariant::query()->insert($rows);
            }

            $variantTotal = 0;
            foreach ($rows as $r) {
                $variantTotal += (int) ($r['stock'] ?? 0);
            }

            if ($variantTotal !== $stock) {
                $product->update(['stock' => $variantTotal]);
                $stock = $variantTotal;
            }
        }

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
        $product->load(['variants.mstSize']);

        $labels = MstLabel::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('products.edit', [
            'product' => $product,
            'labels' => $labels,
            'variants' => $product->variants,
            'sizes' => MstSize::query()->where('is_active', true)->orderBy('sort_order')->orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:Pieces,Unit'],
            'mst_label_id' => ['required', 'integer', 'exists:mst_labels,id'],
            'price' => ['required', 'numeric'],
            'rating' => ['nullable', 'numeric', 'max:5'],
            'image' => ['nullable', 'file', 'mimes:svg,webp', 'mimetypes:image/svg+xml,image/webp', 'max:2048'],
            'is_active' => ['nullable'],
            'variants_meta' => ['nullable', 'array'],
            'variants_meta.*.id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')->where(fn($q) => $q->where('product_id', (int) $product->id))],
            'variants_meta.*.mst_size_id' => ['required_with:variants_meta', 'integer', 'exists:mst_sizes,id', 'distinct'],
            'variants_meta.*.sku' => ['nullable', 'string', 'max:80'],
            'variants_meta.*.color' => ['nullable', 'string', 'max:50'],
            'variants_meta.*.price' => ['nullable', 'integer', 'min:0'],
        ]);

        $title = trim((string) $validated['title']);
        $category = trim((string) $validated['category']);
        $unit = trim((string) $validated['unit']);
        $label = MstLabel::query()->find((int) $validated['mst_label_id']);
        if (!$label) {
            $label = MstLabel::query()->where('code', 'none')->first();
        }
        $labelCode = strtolower(trim((string) ($label?->code ?? 'none')));

        $price = $this->parseInt((string) $validated['price']);
        $ratingInput = $validated['rating'] ?? null;
        $rating = $ratingInput !== null ? (float) $ratingInput : 0.0;
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
            'mst_label_id' => $label?->id,
            'unit' => $unit,
            'price' => $price,
            'rating' => $rating,
            'is_active' => $request->boolean('is_active'),
            'is_new' => $labelCode === 'new',
            'is_sale' => $labelCode === 'promo',
            'is_best_seller' => $labelCode === 'best_seller',
        ];
        if ($path !== null) {
            $payload['image'] = $path;
        }
        $product->update($payload);

        $variantsMeta = $request->input('variants_meta');
        $variantsMeta = is_array($variantsMeta) ? $variantsMeta : null;
        if ($variantsMeta !== null) {
            $rows = [];
            $seenSku = [];
            $errors = [];
            $sizesById = MstSize::query()->select(['id', 'code'])->get()->keyBy('id');

            foreach ($variantsMeta as $i => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null;
                $mstSizeId = (int) ($row['mst_size_id'] ?? 0);
                $sizeCode = strtoupper(trim((string) ($sizesById[$mstSizeId]->code ?? '')));
                $sku = trim((string) ($row['sku'] ?? ''));
                $sku = $sku !== '' ? $sku : null;
                $color = trim((string) ($row['color'] ?? ''));
                $color = $color !== '' ? $color : null;
                $price = $row['price'] ?? null;
                $price = $price === null || $price === '' ? null : max(0, (int) $price);

                if ($sku !== null) {
                    if (in_array($sku, $seenSku, true)) {
                        $errors["variants_meta.$i.sku"] = 'SKU harus unik.';
                    } else {
                        $seenSku[] = $sku;
                        $exists = ProductVariant::query()
                            ->where('sku', $sku)
                            ->when($id !== null, fn($q) => $q->where('id', '!=', $id))
                            ->exists();
                        if ($exists) {
                            $errors["variants_meta.$i.sku"] = 'SKU sudah digunakan.';
                        }
                    }
                }

                $rows[] = [
                    'id' => $id,
                    'mst_size_id' => $mstSizeId,
                    'size' => $sizeCode,
                    'sku' => $sku,
                    'color' => $color,
                    'price' => $price,
                ];
            }

            if (count($errors) > 0) {
                return back()->withErrors($errors)->withInput();
            }

            DB::transaction(function () use ($product, $rows) {
                $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

                $existing = ProductVariant::query()
                    ->where('product_id', (int) $lockedProduct->id)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $incomingIds = [];
                foreach ($rows as $r) {
                    if ($r['id'] !== null) {
                        $incomingIds[] = (int) $r['id'];
                    }
                }

                $toDelete = $existing->filter(fn($v) => !in_array((int) $v->id, $incomingIds, true));
                foreach ($toDelete as $v) {
                    if ((int) ($v->stock ?? 0) > 0) {
                        $size = strtoupper(trim((string) ($v->size ?? '')));
                        $color = trim((string) ($v->color ?? ''));
                        $label = $size . ($color !== '' ? (' / ' . $color) : '');
                        throw ValidationException::withMessages([
                            'variants_meta' => "Tidak bisa hapus variant ($label) karena stok masih ada. Mutasi dulu sampai 0.",
                        ]);
                    }
                }

                if ($toDelete->count() > 0) {
                    ProductVariant::query()
                        ->where('product_id', (int) $lockedProduct->id)
                        ->whereIn('id', $toDelete->pluck('id')->all())
                        ->delete();
                }

                foreach ($rows as $r) {
                    $id = $r['id'];
                    if ($id !== null && isset($existing[$id])) {
                        $existing[$id]->update([
                            'sku' => $r['sku'],
                            'mst_size_id' => $r['mst_size_id'],
                            'size' => $r['size'],
                            'color' => $r['color'],
                            'price' => $r['price'],
                        ]);
                        continue;
                    }

                    ProductVariant::create([
                        'product_id' => (int) $lockedProduct->id,
                        'sku' => $r['sku'],
                        'mst_size_id' => $r['mst_size_id'],
                        'size' => $r['size'],
                        'color' => $r['color'],
                        'stock' => 0,
                        'price' => $r['price'],
                    ]);
                }

                $beforeTotal = (int) ($lockedProduct->stock ?? 0);
                $afterTotal = (int) ProductVariant::query()->where('product_id', (int) $lockedProduct->id)->sum('stock');
                if ($afterTotal !== $beforeTotal) {
                    $lockedProduct->update(['stock' => $afterTotal]);
                }
            });
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function updateVariants(Request $request, Product $product): RedirectResponse
    {
        $sizeOptions = ['S', 'M', 'L', 'XL', 'XXL', '3XL'];

        $validated = $request->validate([
            'variants' => ['required', 'array'],
            'variants.*.size' => ['required', 'string', Rule::in($sizeOptions), 'distinct'],
            'variants.*.sku' => [
                'nullable',
                'string',
                'max:80',
                'distinct',
                Rule::unique('product_variants', 'sku')->where(fn($q) => $q->where('product_id', '!=', (int) $product->id)),
            ],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
        ]);

        $variantsInput = $validated['variants'];

        $rows = [];
        foreach ($variantsInput as $row) {
            if (!is_array($row)) {
                continue;
            }
            $size = strtoupper(trim((string) ($row['size'] ?? '')));
            if ($size === '') {
                continue;
            }
            $sku = trim((string) ($row['sku'] ?? ''));
            $color = trim((string) ($row['color'] ?? ''));
            $stock = (int) ($row['stock'] ?? 0);
            $priceOverride = $row['price'] ?? null;
            $priceOverride = $priceOverride === null || $priceOverride === '' ? null : (int) $priceOverride;

            $rows[] = [
                'product_id' => (int) $product->id,
                'sku' => $sku !== '' ? $sku : null,
                'size' => $size,
                'color' => $color !== '' ? $color : null,
                'stock' => max(0, $stock),
                'price' => $priceOverride !== null ? max(0, $priceOverride) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($product, $request, $rows) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $before = (int) ($locked->stock ?? 0);

            ProductVariant::query()->where('product_id', (int) $locked->id)->delete();
            if (count($rows) > 0) {
                ProductVariant::query()->insert($rows);
            }

            $after = 0;
            foreach ($rows as $r) {
                $after += (int) ($r['stock'] ?? 0);
            }

            if ($after !== $before) {
                $locked->update(['stock' => $after]);

                ProductStockMovement::create([
                    'product_id' => (int) $locked->id,
                    'delta' => (int) ($after - $before),
                    'stock_before' => $before,
                    'stock_after' => (int) $after,
                    'source' => 'ADMIN_VARIANTS',
                    'actor_user_id' => optional($request->user())->id,
                    'note' => 'Sync variants & stock',
                    'created_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Variants berhasil diperbarui.');
    }

    public function storeVariantMutation(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer'],
            'type' => ['required', 'in:in,out'],
            'qty' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $variantId = (int) $validated['variant_id'];
        $qty = (int) $validated['qty'];
        $delta = $validated['type'] === 'in' ? $qty : -$qty;

        DB::transaction(function () use ($product, $request, $variantId, $delta, $validated) {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            $variant = ProductVariant::query()
                ->where('product_id', (int) $lockedProduct->id)
                ->whereKey($variantId)
                ->lockForUpdate()
                ->firstOrFail();

            $variantBefore = (int) ($variant->stock ?? 0);
            $variantAfter = $variantBefore + (int) $delta;
            if ($variantAfter < 0) {
                abort(422, 'Stok variant tidak mencukupi.');
            }

            $variant->update(['stock' => $variantAfter]);

            $beforeTotal = (int) ($lockedProduct->stock ?? 0);
            $afterTotal = (int) ProductVariant::query()->where('product_id', (int) $lockedProduct->id)->sum('stock');

            if ($afterTotal !== $beforeTotal) {
                $lockedProduct->update(['stock' => $afterTotal]);

                $size = strtoupper(trim((string) ($variant->mstSize?->code ?? $variant->size ?? '')));
                $color = trim((string) ($variant->color ?? ''));
                $variantLabel = $size . ($color !== '' ? (' / ' . $color) : '');

                $ref = trim((string) ($validated['reference'] ?? ''));
                $reference = $variantLabel !== '' && $ref !== '' ? ($variantLabel . ' • ' . $ref) : ($variantLabel !== '' ? $variantLabel : ($ref !== '' ? $ref : null));

                $note = trim((string) ($validated['note'] ?? ''));
                if ($note === '') {
                    $note = 'Mutasi variant';
                }

                ProductStockMovement::create([
                    'product_id' => (int) $lockedProduct->id,
                    'delta' => (int) ($afterTotal - $beforeTotal),
                    'stock_before' => $beforeTotal,
                    'stock_after' => (int) $afterTotal,
                    'source' => 'ADMIN_VARIANT_MUTATION',
                    'actor_user_id' => optional($request->user())->id,
                    'reference' => $reference,
                    'note' => $note,
                    'created_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Mutasi variant berhasil disimpan.');
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
        $product->load('variants.mstSize');

        return view('products.adjustment', [
            'product' => $product,
            'variants' => $product->variants,
        ]);
    }

    public function mutations(Product $product, Request $request): View
    {
        $product->load('variants.mstSize');

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
            'variants' => $product->variants,
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
