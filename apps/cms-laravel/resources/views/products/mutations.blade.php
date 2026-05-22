@extends('layouts.app')

@section('title', 'Mutasi Stock')

@section('content')
@php
$unitRaw = (string) ($product->unit ?? 'Pieces');
$unitLabel = $unitRaw === 'Pieces' ? 'Pcs' : $unitRaw;
$tabValue = $tab ?? 'all';
@endphp

<div class="px-6 py-6" x-data="{ open: false }">
    <div class="mb-4 text-sm text-zinc-500">
        <a href="/dashboard" class="hover:text-zinc-900">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.stock') }}" class="hover:text-zinc-900">Stock Produk</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-900">Mutasi</span>
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

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="size-16 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                    <img src="{{ $product->image_url ?? '' }}" alt="{{ $product->title }}" class="h-full w-full object-cover" onerror="this.style.display='none'">
                </div>
                <div class="min-w-0">
                    <div class="truncate text-lg font-semibold tracking-tight text-zinc-900">{{ $product->title }}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                        <span class="text-xs">SKU: {{ $product->slug }}</span>
                        <span class="text-zinc-300">•</span>
                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">{{ $product->category }}</span>
                        <span class="text-zinc-300">•</span>
                        <span class="text-xs">Stok: <span class="font-semibold text-zinc-900">{{ (int) ($product->stock ?? 0) }} {{ $unitLabel }}</span></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('products.stock') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all">
                    Kembali
                </a>
                <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 active:scale-95 transition-all" @click="open = true">
                    Tambah Mutasi
                </button>
            </div>
        </div>
    </div>

    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('products.stock.mutations', ['product' => $product->id, 'tab' => 'all']) }}"
                class="inline-flex items-center rounded-lg border px-3 py-2 text-sm font-semibold shadow-sm transition {{ $tabValue === 'all' ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-50' }}">
                Semua
            </a>
            <a href="{{ route('products.stock.mutations', ['product' => $product->id, 'tab' => 'in']) }}"
                class="inline-flex items-center rounded-lg border px-3 py-2 text-sm font-semibold shadow-sm transition {{ $tabValue === 'in' ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-50' }}">
                Masuk
            </a>
            <a href="{{ route('products.stock.mutations', ['product' => $product->id, 'tab' => 'out']) }}"
                class="inline-flex items-center rounded-lg border px-3 py-2 text-sm font-semibold shadow-sm transition {{ $tabValue === 'out' ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-50' }}">
                Keluar
            </a>
        </div>

        <a href="{{ route('products.stock.mutations.export', ['product' => $product->id, 'tab' => $tabValue]) }}"
            class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all">
            Export
        </a>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Qty</th>
                        <th class="px-6 py-4">Stok</th>
                        <th class="px-6 py-4">Alasan/Ref</th>
                        <th class="px-6 py-4">Pelaku</th>
                        <th class="px-6 py-4">Sumber</th>
                        <th class="px-6 py-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($items as $m)
                    @php
                    $delta = (int) ($m->delta ?? 0);
                    $isIn = $delta > 0;
                    $qty = abs($delta);
                    $actor = $m->actorUser ? ($m->actorUser->name ?? $m->actorUser->email) : null;
                    $before = (int) ($m->stock_before ?? 0);
                    $after = (int) ($m->stock_after ?? 0);
                    $refText = (string) ($m->reference ?? '');
                    $sourceText = (string) ($m->source ?? '-');
                    @endphp
                    <tr class="hover:bg-zinc-50">
                        <td class="px-6 py-4 text-zinc-700 whitespace-nowrap">
                            {{ optional($m->created_at)->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            @if ($isIn)
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Masuk</span>
                            @else
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Keluar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold whitespace-nowrap {{ $isIn ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $isIn ? '+' : '-' }}{{ $qty }} {{ $unitLabel }}
                        </td>
                        <td class="px-6 py-4 text-zinc-700 whitespace-nowrap">
                            <span class="font-medium text-zinc-900">{{ $before }}</span>
                            <span class="text-zinc-300 mx-1">→</span>
                            <span class="font-medium text-zinc-900">{{ $after }}</span>
                        </td>
                        <td class="px-6 py-4 text-zinc-700">
                            {{ $refText !== '' ? $refText : '-' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-700">
                            {{ $actor ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-700">
                            {{ $sourceText }}
                        </td>
                        <td class="px-6 py-4 text-zinc-700">
                            {{ $m->note ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-14 text-center">
                            <div class="mx-auto flex max-w-md flex-col items-center">
                                <div class="flex size-12 items-center justify-center rounded-full bg-zinc-50 text-zinc-500">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-4l-2 3h-4l-2-3H6a2 2 0 0 1-2-2V4z" />
                                        <path d="M4 13h5l2 3h2l2-3h5" />
                                    </svg>
                                </div>
                                <div class="mt-4 text-sm font-semibold text-zinc-900">Belum ada mutasi stok</div>
                                <div class="mt-1 text-sm text-zinc-600">Tambahkan mutasi pertama untuk mulai melacak pergerakan stok.</div>
                                <button type="button" class="mt-5 inline-flex h-10 items-center justify-center rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 active:scale-95 transition-all" @click="open = true">
                                    Tambah Mutasi Pertama
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
        <div class="absolute inset-0 flex items-end justify-center p-4 sm:items-center">
            <div class="w-full max-w-lg overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                    <div class="text-sm font-semibold text-zinc-900">Tambah Mutasi</div>
                    <button type="button" class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-50 hover:text-zinc-900" @click="open = false">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18" />
                            <path d="M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('products.stock.mutations.store', $product) }}" class="space-y-4 px-5 py-5">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-zinc-700" for="type">Tipe</label>
                            <select id="type" name="type" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">
                                <option value="in" {{ old('type', 'in') === 'in' ? 'selected' : '' }}>Masuk</option>
                                <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Keluar</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700" for="qty">Qty</label>
                            <input id="qty" name="qty" type="number" min="1" value="{{ old('qty', 1) }}" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="reference">Referensi</label>
                        <input id="reference" name="reference" type="text" value="{{ old('reference') }}" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" />
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="note">Catatan</label>
                        <textarea id="note" name="note" rows="3" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">{{ old('note') }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-4">
                        <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all" @click="open = false">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-zinc-800 active:scale-95 transition-all">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
