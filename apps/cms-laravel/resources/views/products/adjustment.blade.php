@extends('layouts.app')

@section('title', 'Adjustment Stock')

@section('content')
@php
$currentStock = (int) ($product->stock ?? 0);
$initialStock = (int) old('stock', $currentStock);
$unitRaw = (string) ($product->unit ?? 'Pieces');
$unitLabel = $unitRaw === 'Pieces' ? 'Pcs' : $unitRaw;
@endphp

<div class="px-6 py-6"
    x-data="{
        currentStock: {{ $currentStock }},
        newStock: {{ $initialStock }},
        get difference() { return (this.newStock ?? 0) - (this.currentStock ?? 0); },
        get status() {
            const n = Number(this.newStock ?? 0);
            if (n === 0) return 'out';
            if (n < 10) return 'low';
            return 'ok';
        },
        get statusLabel() { return this.status === 'ok' ? 'Aman' : (this.status === 'low' ? 'Menipis' : 'Habis'); },
        get statusClass() { return this.status === 'ok' ? 'bg-green-50 text-green-700' : (this.status === 'low' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700'); },
        get diffText() { return this.difference > 0 ? `+${this.difference}` : String(this.difference); },
        get diffClass() { return this.difference > 0 ? 'text-emerald-700' : (this.difference < 0 ? 'text-rose-700' : 'text-zinc-500'); },
        inc() { this.newStock = Number(this.newStock ?? 0) + 1; },
        dec() { this.newStock = Math.max(0, Number(this.newStock ?? 0) - 1); },
    }">
    <div class="mb-4 text-sm text-zinc-500">
        <a href="/dashboard" class="hover:text-zinc-900">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.stock') }}" class="hover:text-zinc-900">Stock Produk</a>
        <span class="mx-2">/</span>
        <span class="text-zinc-900">Adjustment</span>
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

    <form method="POST" action="{{ route('products.stock.update', $product) }}" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @method('PUT')
        <input type="hidden" name="context" value="adjustment" />

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="size-16 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                        <img src="{{ $product->image_url ?? '' }}" alt="{{ $product->title }}" class="h-full w-full object-cover" onerror="this.style.display='none'">
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-xl font-semibold tracking-tight text-zinc-900">{{ $product->title }}</div>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                            <span class="text-xs">SKU: {{ $product->slug }}</span>
                            <span class="text-zinc-300">•</span>
                            <span class="inline-flex items-center rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">{{ $product->category }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <div class="text-sm font-medium text-zinc-700">Stok Saat Ini</div>
                        <div class="mt-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-600">Current</span>
                                <span class="font-semibold text-zinc-900">{{ $currentStock }} {{ $unitLabel }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-zinc-700" for="stock">Stok Baru</label>
                        <div class="mt-1.5 flex items-stretch overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm focus-within:border-zinc-900 focus-within:ring-2 focus-within:ring-zinc-900">
                            <button type="button" class="inline-flex w-14 items-center justify-center bg-zinc-50 text-zinc-900 hover:bg-zinc-100" @click="dec()">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14" />
                                </svg>
                            </button>
                            <div class="flex flex-1 items-center gap-3 px-4">
                                <input id="stock" name="stock" type="number" min="0"
                                    class="w-full border-0 bg-white py-4 text-2xl font-semibold text-zinc-900 outline-none"
                                    x-model.number="newStock" />
                                <div class="shrink-0 text-sm font-semibold text-zinc-500">{{ $unitLabel }}</div>
                            </div>
                            <button type="button" class="inline-flex w-14 items-center justify-center bg-zinc-50 text-zinc-900 hover:bg-zinc-100" @click="inc()">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14" />
                                    <path d="M5 12h14" />
                                </svg>
                            </button>
                        </div>
                        @error('stock')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-zinc-700" for="reason">Alasan</label>
                        <select id="reason" name="reason" required class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">
                            <option value="" {{ old('reason') === null || old('reason') === '' ? 'selected' : '' }}>Pilih alasan</option>
                            <option value="Stok Opname" {{ old('reason') === 'Stok Opname' ? 'selected' : '' }}>Stok Opname</option>
                            <option value="Barang Rusak" {{ old('reason') === 'Barang Rusak' ? 'selected' : '' }}>Barang Rusak</option>
                            <option value="Koreksi Sistem" {{ old('reason') === 'Koreksi Sistem' ? 'selected' : '' }}>Koreksi Sistem</option>
                            <option value="Retur" {{ old('reason') === 'Retur' ? 'selected' : '' }}>Retur</option>
                            <option value="Lainnya" {{ old('reason') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('reason')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-zinc-700" for="note">Catatan (opsional)</label>
                        <textarea id="note" name="note" rows="4" class="mt-1.5 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none focus:border-zinc-900 focus:ring-2 focus:ring-zinc-900">{{ old('note') }}</textarea>
                        @error('note')
                        <div class="mt-1.5 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('products.stock') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 shadow-sm hover:bg-zinc-50 active:scale-95 transition-all">
                    Kembali
                </a>
                <button type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-semibold shadow-sm active:scale-95 transition-all"
                    :disabled="newStock === currentStock"
                    :class="newStock === currentStock ? 'bg-zinc-300 text-white cursor-not-allowed' : 'bg-zinc-900 text-white hover:bg-zinc-800'">
                    Simpan Adjustment
                </button>
            </div>
        </div>

        <div class="space-y-6 lg:sticky lg:top-24 h-fit">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="text-sm font-semibold text-zinc-900">Ringkasan</div>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-600">Sebelum</span>
                        <span class="font-semibold text-zinc-900">{{ $currentStock }} {{ $unitLabel }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-600">Sesudah</span>
                        <span class="font-semibold text-zinc-900"><span x-text="newStock"></span> {{ $unitLabel }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-600">Selisih</span>
                        <span class="font-bold" :class="diffClass"><span x-text="diffText"></span> {{ $unitLabel }}</span>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Status Stok Baru</div>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold" :class="statusClass" x-text="statusLabel"></span>
                    </div>
                </div>

                <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-700" x-show="Math.abs(difference) > 10">
                    Perubahan stok cukup besar. Pastikan sudah sesuai sebelum disimpan.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
