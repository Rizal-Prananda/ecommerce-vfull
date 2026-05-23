@extends('layouts.app')

@section('title', 'List Produk')

@section('content')
<div class="px-6 py-6">
    <div class="mb-4 text-sm text-slate-500">
        <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Produk</span>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">List Produk</h1>
            <p class="mt-1 text-sm text-slate-600">Kelola produk, harga, gambar, dan status aktif.</p>
        </div>
        <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all">
            Tambah Produk
        </a>
    </div>

    @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
        {{ session('success') }}
    </div>
    @endif

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input
                type="text"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Cari title, kategori, atau slug..."
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" />
            <div class="flex gap-2">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 active:scale-95 transition-all">
                    Cari
                </button>
                <a href="{{ route('products.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Label</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Harga</th>
                        <th class="px-6 py-4">Stok</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($products as $p)
                    @php
                        $stock=(int) ($p->stock ?? 0);
                        $unit = (string) ($p->unit ?? 'Pcs');
                    @endphp
                        <tr class="hover:bg-zinc-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative size-12 overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100">
                                        <img src="{{ $p->image_url ?? '' }}" alt="{{ $p->title }}" class="h-full w-full object-cover" onerror="this.style.display='none'">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-semibold text-zinc-900">{{ $p->title }}</div>
                                        <div class="mt-0.5 truncate text-xs text-zinc-500">{{ $p->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $code = strtolower((string) ($p->mstLabel?->code ?? ''));
                                    $name = (string) ($p->mstLabel?->name ?? '');
                                @endphp
                                @if ($code === 'promo')
                                    <span class="inline-flex items-center rounded-md bg-red-600 px-2.5 py-1 text-xs font-medium text-white">{{ $name ?: 'Promo' }}</span>
                                @elseif ($code === 'best_seller')
                                    <span class="inline-flex items-center rounded-md bg-zinc-900 px-2.5 py-1 text-xs font-medium text-white">{{ $name ?: 'Best Seller' }}</span>
                                @elseif ($code === 'new')
                                    <span class="inline-flex items-center rounded-md bg-blue-600 px-2.5 py-1 text-xs font-medium text-white">{{ $name ?: 'New' }}</span>
                                @elseif ($name !== '')
                                    <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">{{ $name }}</span>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">
                                    {{ $p->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-zinc-900">{{ $p->price_formatted }}</div>
                                <div class="mt-1 text-xs text-zinc-500">★ {{ number_format((float) ($p->rating ?? 0), 1) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="{{ $stock < 10 ? 'font-semibold text-red-600' : 'text-zinc-700' }}">{{ $stock }} {{ $unit }}</div>
                                @if ($stock === 0)
                                    <div class="mt-1 inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">Habis</div>
                                @elseif ($stock < 10)
                                    <div class="mt-1 inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-800">Stok Tipis</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm">
                                    @if ($p->is_active)
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                    <span class="text-zinc-700">Active</span>
                                    @else
                                    <span class="h-2 w-2 rounded-full bg-zinc-400"></span>
                                    <span class="text-zinc-700">Inactive</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('products.edit', $p) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 19.5 7.125m-2.638-2.638L7.5 13.85V16.5h2.65l9.362-9.375ZM6 18h12" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center">
                                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-zinc-100 text-zinc-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 12 3l5.571 6.75M5.25 10.5h13.5v8.25A2.25 2.25 0 0 1 16.5 21h-9A2.25 2.25 0 0 1 5.25 18.75V10.5Z" />
                                        </svg>
                                    </div>
                                    <div class="mt-4 text-sm font-semibold text-zinc-900">Belum ada produk</div>
                                    <div class="mt-1 text-sm text-zinc-500">Klik “Tambah Produk” untuk menambahkan produk pertama.</div>
                                </div>
                            </td>
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
