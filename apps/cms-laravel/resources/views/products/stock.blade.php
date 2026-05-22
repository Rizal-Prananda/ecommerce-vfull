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
        <p class="mt-1 text-sm text-slate-600">Klik stok untuk edit langsung, lalu tekan Enter untuk menyimpan.</p>
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

    <div class="mb-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('products.stock') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input
                type="text"
                name="q"
                value="{{ $q ?? '' }}"
                placeholder="Cari produk..."
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" />
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}" />
            <div class="flex gap-2">
                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 active:scale-95 transition-all">
                    Cari
                </button>
                <a href="{{ route('products.stock', ['tab' => $tab ?? 'all']) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('products.stock', ['tab' => 'all', 'q' => $q ?? '']) }}"
            class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold shadow-sm transition {{ ($tab ?? 'all') === 'all' ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-50' }}">
            <span>Semua</span>
            <span class="{{ ($tab ?? 'all') === 'all' ? 'bg-white/15 text-white' : 'bg-zinc-100 text-zinc-700' }} rounded-md px-2 py-0.5 text-xs font-semibold">{{ (int) (($counts['all'] ?? 0)) }}</span>
        </a>
        <a href="{{ route('products.stock', ['tab' => 'low', 'q' => $q ?? '']) }}"
            class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold shadow-sm transition {{ ($tab ?? 'all') === 'low' ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-50' }}">
            <span>Stok Menipis</span>
            <span class="{{ ($tab ?? 'all') === 'low' ? 'bg-white/15 text-white' : 'bg-amber-50 text-amber-700' }} rounded-md px-2 py-0.5 text-xs font-semibold">{{ (int) (($counts['low'] ?? 0)) }}</span>
        </a>
        <a href="{{ route('products.stock', ['tab' => 'out', 'q' => $q ?? '']) }}"
            class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold shadow-sm transition {{ ($tab ?? 'all') === 'out' ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-50' }}">
            <span>Habis</span>
            <span class="{{ ($tab ?? 'all') === 'out' ? 'bg-white/15 text-white' : 'bg-red-50 text-red-700' }} rounded-md px-2 py-0.5 text-xs font-semibold">{{ (int) (($counts['out'] ?? 0)) }}</span>
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Stok Saat Ini</th>
                        <th class="px-6 py-4">Status Stok</th>
                        <th class="px-6 py-4">Terakhir Update</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($products as $p)
                    @php
                    $stockVal = (int) ($p->stock ?? 0);
                    $unitRaw = (string) ($p->unit ?? 'Pieces');
                    $unitLabel = $unitRaw === 'Pieces' ? 'Pcs' : $unitRaw;
                    if ($stockVal <= 0) {
                        $statusText='Habis' ;
                        $statusClass='bg-red-50 text-red-700' ;
                        } elseif ($stockVal < 10) {
                        $statusText='Menipis' ;
                        $statusClass='bg-amber-50 text-amber-700' ;
                        } else {
                        $statusText='Aman' ;
                        $statusClass='bg-green-50 text-green-700' ;
                        }
                        @endphp
                        <tr class="hover:bg-zinc-50" x-data="{ editing: false, value: {{ $stockVal }} }">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200">
                                    <img src="{{ $p->image_url ?? '' }}" alt="{{ $p->title }}" class="h-full w-full object-cover" onerror="this.style.display='none'">
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-slate-900">{{ $p->title }}</div>
                                    <div class="mt-0.5 truncate text-xs text-zinc-500">{{ $p->category }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('products.stock.update', $p) }}" x-ref="form">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="stock" :value="value" />
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <button type="button" class="w-full rounded-lg px-2 py-1 text-left font-semibold text-zinc-900 hover:bg-zinc-100"
                                            x-show="!editing"
                                            @click="editing = true; $nextTick(() => $refs.input.focus());">
                                            <span x-text="`${value} {{ $unitLabel }}`"></span>
                                        </button>
                                        <input x-show="editing"
                                            x-ref="input"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900"
                                            x-model.number="value"
                                            @keydown.enter.prevent="$refs.form.submit()"
                                            @keydown.escape.prevent="editing = false; value = {{ $stockVal }}"
                                            @blur="editing = false" />
                                    </div>
                                </div>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-zinc-500">
                            {{ optional($p->updated_at)->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.stock.adjustment', $p) }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 active:scale-95 transition-all">
                                    Adjustment
                                </a>
                                <a href="{{ route('products.stock.mutations', $p) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all">
                                    Riwayat
                                </a>
                            </div>
                        </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center text-sm text-zinc-600">Belum ada produk.</td>
                        </tr>
                        @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 bg-white px-4 py-3">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
