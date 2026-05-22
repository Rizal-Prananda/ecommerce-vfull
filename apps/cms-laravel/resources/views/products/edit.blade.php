@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="px-6 py-6">
    <div class="mb-4 text-sm text-slate-500">
        <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.index') }}" class="hover:text-slate-900">Produk</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Edit</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Produk</h1>
        <p class="mt-1 text-sm text-slate-600">Perbarui detail produk. Upload gambar hanya jika ingin ganti.</p>
    </div>

    <div class="max-w-3xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-5 p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-slate-700" for="title">Nama Barang</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $product->title) }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('title')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="category">Category</label>
                        <input id="category" name="category" type="text" value="{{ old('category', $product->category) }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('category')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="price">Price</label>
                        <input id="price" name="price" type="text" value="{{ old('price', $product->price) }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('price')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="rating">Rating</label>
                        <input id="rating" name="rating" type="text" value="{{ old('rating', $product->rating) }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                        @error('rating')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ old('is_active', $product->is_active ? '1' : '') ? 'checked' : '' }} />
                            Active
                        </label>
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_new" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ old('is_new', $product->is_new ? '1' : '') ? 'checked' : '' }} />
                            New
                        </label>
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_sale" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ old('is_sale', $product->is_sale ? '1' : '') ? 'checked' : '' }} />
                            Sale
                        </label>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-slate-700" for="image">Image</label>
                        <div class="mt-1.5 flex items-center gap-4">
                            <div class="size-16 overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200">
                                <img src="{{ $product->image_url ?? '' }}" alt="{{ $product->title }}" class="h-full w-full object-cover" onerror="this.style.display='none'">
                            </div>
                            <div class="min-w-0 flex-1">
                                <input id="image" name="image" type="file" accept="image/webp,image/svg+xml" class="block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-900 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
                                @error('image')
                                <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-slate-500">Boleh kosong kalau tidak ganti gambar.</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <a href="{{ route('products.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                    Batal
                </a>
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
