@extends('layouts.app')

@section('title', 'Mutasi Stock')

@section('content')
<div class="px-6 py-6">
    <div class="mb-4 text-sm text-slate-500">
        <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('products.stock') }}" class="hover:text-slate-900">Stock Produk</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900">Mutasi</span>
    </div>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Mutasi Stock</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $product->title }} • {{ $product->category }}</p>
        </div>
        <a href="{{ route('products.stock') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 active:scale-95 transition-all">
            Kembali
        </a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3">Stok Sebelum</th>
                        <th class="px-4 py-3">Stok Sesudah</th>
                        <th class="px-4 py-3">Sumber</th>
                        <th class="px-4 py-3">Pelaku</th>
                        <th class="px-4 py-3">Referensi</th>
                        <th class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($items as $m)
                        @php
                            $delta = (int) ($m->delta ?? 0);
                            $isIn = $delta > 0;
                            $qty = abs($delta);
                            $source = strtoupper((string) ($m->source ?? ''));
                            $actor = $m->actorUser ? ($m->actorUser->name ?? $m->actorUser->email) : null;
                        @endphp
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 text-slate-700">
                                {{ optional($m->created_at)->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($isIn)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Tambah</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">Keluar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $qty }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ (int) ($m->stock_before ?? 0) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ (int) ($m->stock_after ?? 0) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $source }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $actor ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $m->reference ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $m->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-600">Belum ada mutasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 bg-white px-4 py-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection

