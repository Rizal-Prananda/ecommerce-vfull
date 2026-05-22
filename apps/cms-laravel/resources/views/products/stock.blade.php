@extends('layouts.app')

@section('title', 'Stock Produk')

@section('content')
<div class="px-6 py-6">
    <div class="mb-4 text-sm text-slate-500">
        <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Stock Produk</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Stock Produk</h1>
        <p class="mt-1 text-sm text-slate-600">Lihat stok dan buka halaman adjustment untuk perubahan.</p>
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

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('products.stock') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input
                type="text"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Cari produk..."
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
            <div class="flex gap-2">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all">
                    Cari
                </button>
                <a href="{{ route('products.stock') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Nama Produk</th>
                        <th class="px-4 py-3">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($products as $p)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="size-10 overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200">
                                    <img src="{{ $p->image_url ?? '' }}" alt="{{ $p->title }}" class="h-full w-full object-cover" onerror="this.style.display='none'">
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-slate-900">{{ $p->title }}</div>
                                    <div class="mt-0.5 truncate text-xs text-slate-500">{{ $p->category }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ (int) ($p->stock ?? 0) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.stock.mutations', $p) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                                    Mutasi
                                </a>
                                <a href="{{ route('products.stock.adjustment', $p) }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all">
                                    Adjustment
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-600">Belum ada produk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 bg-white px-4 py-3">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
