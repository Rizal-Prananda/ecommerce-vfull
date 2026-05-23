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
                <thead class="sticky top-0 z-10 bg-zinc-50/95 text-xs font-semibold uppercase text-zinc-500 backdrop-blur supports-[backdrop-filter]:bg-zinc-50/80">
                    <tr>
                        <th class="px-4 py-3 sm:px-6">Waktu</th>
                        <th class="px-4 py-3 sm:px-6">Tipe</th>
                        <th class="hidden px-4 py-3 sm:px-6 md:table-cell">Ukuran</th>
                        <th class="px-4 py-3 text-right sm:px-6">Qty</th>
                        <th class="px-4 py-3 sm:px-6">Stok</th>
                        <th class="px-4 py-3 sm:px-6">Referensi</th>
                        <th class="hidden px-4 py-3 sm:px-6 lg:table-cell">Pelaku</th>
                        <th class="px-4 py-3 sm:px-6">Sumber</th>
                        <th class="hidden px-4 py-3 sm:px-6 md:table-cell">Catatan</th>
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
                    $refText = trim((string) ($m->reference ?? ''));
                    $sourceText = (string) ($m->source ?? '-');
                    $variantLabel = null;
                    $refExtra = null;
                    if ($refText !== '' && str_contains($refText, '•')) {
                        [$variantLabel, $refExtra] = array_map('trim', explode('•', $refText, 2));
                    } else {
                        $refExtra = $refText !== '' ? $refText : null;
                    }
                    @endphp
                    <tr class="odd:bg-white even:bg-zinc-50/40 hover:bg-zinc-100/40 transition-colors">
                        <td class="px-4 py-4 sm:px-6 whitespace-nowrap">
                            <div class="text-sm font-semibold text-zinc-900 tabular-nums">
                                {{ optional($m->created_at)->format('d M Y') }}
                            </div>
                            <div class="mt-0.5 text-xs text-zinc-500 tabular-nums">
                                {{ optional($m->created_at)->format('H:i') }}
                                <span class="hidden sm:inline">•</span>
                                <span class="hidden sm:inline lg:hidden">{{ $actor ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 sm:px-6 whitespace-nowrap">
                            @if ($isIn)
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Masuk</span>
                            @else
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">Keluar</span>
                            @endif
                            @if ($variantLabel !== null && $variantLabel !== '')
                                <div class="mt-1 md:hidden">
                                    <span class="inline-flex items-center rounded-md bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-800">{{ $variantLabel }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="hidden px-4 py-4 sm:px-6 md:table-cell">
                            @if ($variantLabel !== null && $variantLabel !== '')
                                <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-800">{{ $variantLabel }}</span>
                            @else
                                <span class="text-sm text-zinc-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right sm:px-6 whitespace-nowrap tabular-nums">
                            <div class="font-semibold {{ $isIn ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $isIn ? '+' : '-' }}{{ $qty }}
                                <span class="text-xs font-medium text-zinc-500">{{ $unitLabel }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 sm:px-6 whitespace-nowrap tabular-nums">
                            <div class="inline-flex items-center gap-2">
                                <span class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-zinc-900 shadow-sm ring-1 ring-zinc-200">{{ $before }}</span>
                                <span class="text-zinc-300">→</span>
                                <span class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-zinc-900 shadow-sm ring-1 ring-zinc-200">{{ $after }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 sm:px-6">
                            <div class="max-w-[18rem] truncate text-sm font-medium text-zinc-900">{{ $refExtra !== null && $refExtra !== '' ? $refExtra : '-' }}</div>
                        </td>
                        <td class="hidden px-4 py-4 sm:px-6 lg:table-cell">
                            <div class="max-w-[16rem] truncate text-sm font-medium text-zinc-900">{{ $actor ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-4 sm:px-6 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">{{ $sourceText }}</span>
                        </td>
                        <td class="hidden px-4 py-4 sm:px-6 md:table-cell">
                            <div class="max-w-[22rem] whitespace-normal text-sm text-zinc-700">{{ $m->note ?? '-' }}</div>
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
                    <div class="text-sm font-semibold text-zinc-900">Tambah Mutasi (Per Ukuran)</div>
                    <button type="button" class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-50 hover:text-zinc-900" @click="open = false">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18" />
                            <path d="M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if (!empty($variants) && count($variants) > 0)
                <form method="POST" action="{{ route('products.stock.variants.mutations.store', $product) }}" class="space-y-4 px-5 py-5">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-zinc-700" for="variant_id">Ukuran</label>
                            <select id="variant_id" name="variant_id" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">
                                @foreach (($variants ?? []) as $v)
                                    @php
                                        $size = strtoupper(trim((string) (optional($v->mstSize)->code ?? $v->size ?? '')));
                                        $color = trim((string) ($v->color ?? ''));
                                        $label = $size . ($color !== '' ? (' / ' . $color) : '');
                                        $stock = (int) ($v->stock ?? 0);
                                    @endphp
                                    <option value="{{ (int) $v->id }}">{{ $label }} — stok {{ $stock }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-zinc-700" for="variant_type">Tipe</label>
                            <select id="variant_type" name="type" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">
                                <option value="in">Masuk</option>
                                <option value="out">Keluar</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700" for="variant_qty">Qty</label>
                            <input id="variant_qty" name="qty" type="number" min="1" value="1" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" />
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="variant_reference">Referensi</label>
                        <input id="variant_reference" name="reference" type="text" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900" />
                    </div>

                    <div>
                        <label class="text-sm font-medium text-zinc-700" for="variant_note">Catatan</label>
                        <textarea id="variant_note" name="note" rows="3" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900"></textarea>
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
                @else
                <div class="px-5 py-8 text-center">
                    <div class="text-sm font-semibold text-zinc-900">Belum ada variant</div>
                    <div class="mt-1 text-sm text-zinc-600">Tambahkan variant di halaman Tambah Produk terlebih dahulu.</div>
                    <button type="button" class="mt-5 inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all" @click="open = false">
                        Tutup
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
