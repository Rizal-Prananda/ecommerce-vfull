@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
@php
$labelId = old('mst_label_id');
if ($labelId === null) {
    $labelId = $product->mst_label_id;
}
@endphp

<div class="px-6 py-6">
    <div class="mb-4 text-sm text-zinc-500">
        <a href="/dashboard" class="hover:text-zinc-900">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-zinc-900">Produk</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-900">Edit</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Edit Produk</h1>
        <p class="mt-1 text-sm text-zinc-600">Perbarui detail produk dan pengaturan tampil di website.</p>
    </div>

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @method('PUT')

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <div class="text-sm font-semibold text-zinc-900">Detail Produk</div>
                    <div class="mt-1 text-sm text-zinc-500">Nama dan kategori.</div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-zinc-700" for="title">Nama Barang</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $product->title) }}" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" />
                        @error('title')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="category">Kategori</label>
                        <input id="category" name="category" type="text" value="{{ old('category', $product->category) }}" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" />
                        @error('category')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="label">Label</label>
                        <select id="label" name="mst_label_id" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">
                            @foreach (($labels ?? []) as $lbl)
                                <option value="{{ $lbl->id }}" {{ (string) $labelId === (string) $lbl->id ? 'selected' : '' }}>
                                    {{ $lbl->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('mst_label_id')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <div class="text-sm font-semibold text-zinc-900">Harga &amp; Inventori</div>
                    <div class="mt-1 text-sm text-zinc-500">Harga, stok, dan satuan.</div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-zinc-700" for="price">Harga</label>
                        <div class="mt-1.5 flex overflow-hidden rounded-lg border border-zinc-300 bg-white shadow-sm focus-within:border-zinc-900 focus-within:ring-2 focus-within:ring-zinc-900">
                            <div class="flex items-center bg-zinc-50 px-3 text-sm font-semibold text-zinc-700">Rp</div>
                            <input id="price" name="price" type="text" value="{{ old('price', $product->price) }}" class="w-full border-0 bg-white px-3 py-2 text-sm text-zinc-900 outline-none" />
                        </div>
                        @error('price')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="stock">Stok</label>
                        <div class="mt-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-900">
                            {{ (int) ($product->stock ?? 0) }} {{ (string) ($product->unit ?? 'Pieces') }}
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="unit">Satuan</label>
                        <select id="unit" name="unit" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">
                            <option value="Pieces" {{ old('unit', $product->unit ?? 'Pieces') === 'Pieces' ? 'selected' : '' }}>Pieces</option>
                            <option value="Unit" {{ old('unit', $product->unit ?? 'Pieces') === 'Unit' ? 'selected' : '' }}>Unit</option>
                        </select>
                        @error('unit')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @php
                $variantsMetaInitial = old('variants_meta');
                if (!is_array($variantsMetaInitial)) {
                    $variantsMetaInitial = [];
                    foreach (($variants ?? []) as $v) {
                        $variantsMetaInitial[] = [
                            'id' => (int) ($v->id ?? 0),
                            'mst_size_id' => (int) ($v->mst_size_id ?? 0),
                            'size' => strtoupper(trim((string) ($v->mstSize?->code ?? $v->size ?? ''))),
                            'sku' => (string) ($v->sku ?? ''),
                            'color' => (string) ($v->color ?? ''),
                            'stock' => (int) ($v->stock ?? 0),
                            'price' => $v->price !== null ? (int) $v->price : null,
                        ];
                    }
                }
            @endphp
            <div
                class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm"
                x-data="{
                    sizes: @js(($sizes ?? [])->map(fn($s) => ['id' => (int) $s->id, 'code' => (string) $s->code])),
                    defaultSizeId() {
                        const m = (this.sizes || []).find((x) => String(x.code || '').toUpperCase() === 'M');
                        return m ? Number(m.id) : (this.sizes?.[0]?.id ?? null);
                    },
                    variants: (@js($variantsMetaInitial) || []).map((row) => ({
                        id: row?.id ?? null,
                        mst_size_id: Number(row?.mst_size_id ?? 0) || null,
                        size: String(row?.size ?? '').toUpperCase().trim() || '',
                        sku: String(row?.sku ?? ''),
                        color: String(row?.color ?? ''),
                        stock: Number(row?.stock ?? 0),
                        price: row?.price ?? '',
                    })),
                    init() {
                        this.variants = (this.variants || []).map((v) => {
                            const code = String(v.size || '').toUpperCase().trim();
                            if (!v.mst_size_id) {
                                const found = (this.sizes || []).find((s) => String(s.code || '').toUpperCase() === code);
                                v.mst_size_id = found ? Number(found.id) : this.defaultSizeId();
                            }
                            return v;
                        });
                    },
                    addRow() { this.variants.push({ id: null, mst_size_id: this.defaultSizeId(), sku: '', color: '', stock: 0, price: '' }); },
                    removeRow(i) { this.variants.splice(i, 1); },
                }"
            >
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900">Variants &amp; Stock</div>
                        <div class="mt-1 text-sm text-zinc-500">Atur ukuran dan SKU. Stok tidak bisa diubah di sini.</div>
                    </div>
                    <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 active:scale-95 transition-all" @click="addRow()">
                        Add Size
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                <th class="py-2 pr-4">Size</th>
                                <th class="py-2 pr-4">SKU</th>
                                <th class="py-2 pr-4">Color</th>
                                <th class="py-2 pr-4">Stock</th>
                                <th class="py-2 pr-4">Price Override</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            <template x-for="(v, i) in variants" :key="i">
                                <tr>
                                    <td class="py-3 pr-4 align-top">
                                        <input type="hidden" :name="`variants_meta[${i}][id]`" :value="v.id ?? ''" />
                                        <select
                                            class="h-10 w-24 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900"
                                            :name="`variants_meta[${i}][mst_size_id]`"
                                            x-init="$nextTick(() => { $el.value = String(v.mst_size_id ?? '') })"
                                            @change="v.mst_size_id = Number($event.target.value)"
                                        >
                                            <template x-for="s in sizes" :key="s.id">
                                                <option :value="s.id" :selected="Number(v.mst_size_id) === Number(s.id)" x-text="s.code"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <input type="text" class="h-10 w-44 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" :name="`variants_meta[${i}][sku]`" x-model="v.sku" placeholder="AV-TSHIRT-M" />
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <input type="text" class="h-10 w-32 rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" :name="`variants_meta[${i}][color]`" x-model="v.color" placeholder="Black" />
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <input type="number" class="h-10 w-24 rounded-lg border border-zinc-200 bg-zinc-50 px-3 text-sm font-semibold text-zinc-900 shadow-sm outline-none" :value="v.stock ?? 0" disabled readonly />
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <div class="flex overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm focus-within:border-zinc-900 focus-within:ring-2 focus-within:ring-zinc-900">
                                            <div class="flex items-center bg-zinc-50 px-3 text-sm font-semibold text-zinc-700">Rp</div>
                                            <input type="number" min="0" class="h-10 w-40 border-0 bg-white px-3 text-sm text-zinc-900 outline-none" :name="`variants_meta[${i}][price]`" x-model="v.price" placeholder="(optional)" />
                                        </div>
                                    </td>
                                    <td class="py-3 align-top">
                                        <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all" @click="removeRow(i)">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                @error('variants_meta')
                    <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
                @error('variants_meta.*.mst_size_id')
                    <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
                @error('variants_meta.*.sku')
                    <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm"
                x-data="{
                    dragging: false,
                    previewUrl: '{{ $product->image_url ?? '' }}',
                    fileName: '',
                    setFile(file) {
                        if (!file) return;
                        this.fileName = file.name || '';
                        const reader = new FileReader();
                        reader.onload = () => { this.previewUrl = String(reader.result || ''); };
                        reader.readAsDataURL(file);
                    },
                    onChange(e) {
                        const file = e?.target?.files?.[0];
                        this.setFile(file);
                    },
                    onDrop(e) {
                        e.preventDefault();
                        this.dragging = false;
                        const file = e?.dataTransfer?.files?.[0];
                        if (!file) return;
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        this.$refs.file.files = dt.files;
                        this.setFile(file);
                    }
                }">
                <div class="mb-5">
                    <div class="text-sm font-semibold text-zinc-900">Gambar Produk</div>
                    <div class="mt-1 text-sm text-zinc-500">Upload hanya jika ingin ganti.</div>
                </div>

                <div class="aspect-video overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Preview" class="h-full w-full object-cover" />
                    </template>
                    <template x-if="!previewUrl">
                        <div class="flex h-full items-center justify-center text-sm text-zinc-500">Belum ada gambar</div>
                    </template>
                </div>

                <div
                    class="mt-4 rounded-lg border-2 border-dashed border-zinc-200 bg-white p-4 text-sm text-zinc-600"
                    :class="dragging ? 'border-zinc-900 bg-zinc-50' : ''"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop="onDrop($event)">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-zinc-900">Drag &amp; drop file</div>
                            <div class="mt-1 text-xs text-zinc-500">SVG atau WEBP, max 2MB</div>
                            <div class="mt-1 text-xs text-zinc-500" x-show="fileName" x-text="fileName"></div>
                        </div>
                        <label class="inline-flex h-9 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50">
                            Pilih File
                            <input x-ref="file" id="image" name="image" type="file" accept="image/webp,image/svg+xml" class="hidden" @change="onChange($event)" />
                        </label>
                    </div>
                </div>

                @error('image')
                <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <div class="text-sm font-semibold text-zinc-900">Pengaturan</div>
                    <div class="mt-1 text-sm text-zinc-500">Tampilkan di website.</div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-medium text-zinc-700">Tampilkan di website</div>
                            <div class="mt-1 text-xs text-zinc-500">Jika nonaktif, produk tidak muncul di marketplace.</div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $product->is_active ? '1' : '') ? 'checked' : '' }}>
                            <div class="peer h-6 w-11 rounded-full bg-zinc-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-zinc-900 peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('products.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all">
                    Batal
                </a>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 active:scale-95 transition-all">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
