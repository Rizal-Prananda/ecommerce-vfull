@extends('layouts.app')

@section('title', 'Adjustment Stock')

@section('content')
<div class="px-6 py-6">
    <div class="mb-4 text-sm text-slate-500">
        <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.stock') }}" class="hover:text-slate-900">Stock Produk</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Adjustment</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Adjustment Stock</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $product->title }} • {{ $product->category }}</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="max-w-2xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <form method="POST" action="{{ route('products.stock.update', $product) }}">
            @csrf
            @method('PUT')

            <div class="space-y-5 p-6">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <div class="flex items-center justify-between">
                        <div class="text-slate-600">Stok Saat Ini</div>
                        <div class="font-semibold text-slate-900">{{ (int) ($product->stock ?? 0) }}</div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="stock">Stock Baru</label>
                    <input id="stock" name="stock" type="number" value="{{ old('stock', $product->stock) }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                    @error('stock')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700" for="note">Catatan (opsional)</label>
                    <input id="note" name="note" type="text" value="{{ old('note') }}" class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
                    @error('note')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <a href="{{ route('products.stock') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                    Kembali
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('products.stock.mutations', $product) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                        Mutasi
                    </a>
                    <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all">
                        Simpan Adjustment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

