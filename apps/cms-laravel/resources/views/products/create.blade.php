@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="px-6 py-6">
    <div class="mb-4 text-sm text-slate-500">
        <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-slate-900">Produk</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Tambah</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Tambah Produk</h1>
        <p class="mt-1 text-sm text-slate-600">Isi detail produk dan upload gambar.</p>
    </div>

    <div class="max-w-3xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="variants_present" value="1">

            <div class="space-y-5 p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-slate-700" for="title">Nama Barang</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('title')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="category">Category</label>
                        <input id="category" name="category" type="text" value="{{ old('category') }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('category')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="label">Label</label>
                        <select id="label" name="mst_label_id" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            @foreach (($labels ?? []) as $lbl)
                                <option value="{{ $lbl->id }}" {{ (string) old('mst_label_id') === (string) $lbl->id ? 'selected' : '' }}>
                                    {{ $lbl->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('mst_label_id')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="unit">Satuan</label>
                        <select id="unit" name="unit" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                            <option value="Pieces" {{ old('unit', 'Pieces') === 'Pieces' ? 'selected' : '' }}>Pieces</option>
                            <option value="Unit" {{ old('unit', 'Pieces') === 'Unit' ? 'selected' : '' }}>Unit</option>
                        </select>
                        @error('unit')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="price">Price</label>
                        <input id="price" name="price" type="text" value="{{ old('price') }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('price')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="stock">Stock</label>
                        <input id="stock" name="stock" type="number" value="{{ old('stock', 0) }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('stock')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ old('is_active', '1') ? 'checked' : '' }} />
                            Active
                        </label>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-slate-700" for="image">Image</label>
                        <input id="image" name="image" type="file" accept="image/webp,image/svg+xml" class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-900 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
                        @error('image')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @php
                $variantsInitial = old('variants');
                if (!is_array($variantsInitial)) {
                    $variantsInitial = [];
                }
            @endphp
            <div class="border-t border-slate-200 bg-white px-6 py-6"
                x-data="{
                    sizes: @js(($sizes ?? [])->map(fn($s) => ['id' => (int) $s->id, 'code' => (string) $s->code])),
                    variants: @js($variantsInitial),
                    defaultSizeId() {
                        const m = (this.sizes || []).find((x) => String(x.code || '').toUpperCase() === 'M');
                        return m ? Number(m.id) : (this.sizes?.[0]?.id ?? null);
                    },
                    addRow() { this.variants.push({ mst_size_id: this.defaultSizeId(), sku: '', color: '', stock: 0, price: '' }); },
                    removeRow(i) { this.variants.splice(i, 1); },
                }">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Variants &amp; Stock</div>
                        <div class="mt-1 text-sm text-slate-600">Atur stok per ukuran (opsional).</div>
                    </div>
                    <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all" @click="addRow()">
                        Add Size
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="py-2 pr-4">Size</th>
                                <th class="py-2 pr-4">SKU</th>
                                <th class="py-2 pr-4">Color</th>
                                <th class="py-2 pr-4">Stock</th>
                                <th class="py-2 pr-4">Price Override</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(v, i) in variants" :key="i">
                                <tr>
                                    <td class="py-3 pr-4 align-top">
                                        <select class="h-10 w-24 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900" :name="`variants[${i}][mst_size_id]`" x-model.number="v.mst_size_id">
                                            <template x-for="s in sizes" :key="s.id">
                                                <option :value="s.id" x-text="s.code"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <input type="text" class="h-10 w-44 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900" :name="`variants[${i}][sku]`" x-model="v.sku" placeholder="AV-TSHIRT-M" />
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <input type="text" class="h-10 w-32 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900" :name="`variants[${i}][color]`" x-model="v.color" placeholder="Black" />
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <input type="number" min="0" class="h-10 w-24 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-900" :name="`variants[${i}][stock]`" x-model.number="v.stock" />
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        <div class="flex overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm focus-within:border-slate-900 focus-within:ring-2 focus-within:ring-slate-900">
                                            <div class="flex items-center bg-slate-50 px-3 text-sm font-semibold text-slate-700">Rp</div>
                                            <input type="number" min="0" class="h-10 w-40 border-0 bg-white px-3 text-sm text-slate-900 outline-none" :name="`variants[${i}][price]`" x-model="v.price" placeholder="(optional)" />
                                        </div>
                                    </td>
                                    <td class="py-3 align-top">
                                        <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all" @click="removeRow(i)">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                @error('variants.*.mst_size_id')
                    <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
                @error('variants.*.stock')
                    <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
                @error('variants.*.sku')
                    <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <a href="{{ route('products.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                    Batal
                </a>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
