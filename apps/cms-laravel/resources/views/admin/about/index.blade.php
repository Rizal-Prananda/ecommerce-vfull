@extends('layouts.app')

@section('title', 'About')

@section('content')
<div class="max-w-7xl mx-auto p-6 lg:p-8" x-data="{ saving: false }">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">About</h1>
        <p class="mt-1 text-sm text-slate-600">Upload foto untuk section Brand Story (About) di landing page.</p>
    </div>

    @if (session('success'))
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.about.update') }}" enctype="multipart/form-data" @submit="saving = true">
        @csrf

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-[#0F172A]">About Photos (Landing)</div>
                    <div class="mt-1 text-xs text-[#64748B]">Bisa upload lebih dari 1 foto. Akan dipakai untuk image di section About.</div>
                    <div class="mt-1 text-xs text-[#64748B]">Accepted: WebP/JPG/PNG/SVG • Max 2MB per file • Max 20 images</div>
                </div>
            </div>

            <label for="about_photos" id="about-dropzone" class="mt-5 block border border-slate-200 rounded-2xl p-12 text-center hover:border-[#3B82F6] hover:bg-slate-50/50 transition-all duration-300 cursor-pointer">
                <svg class="mx-auto h-12 w-12 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V8m0 0l-3 3m3-3l3 3M20 16.5a4.5 4.5 0 00-3.1-4.3A6 6 0 006 10.5a4.5 4.5 0 00.5 9h12.9a3.6 3.6 0 00.6-7z" />
                </svg>
                <p class="mt-4 text-sm font-semibold text-[#0F172A]">Tarik foto ke sini atau klik untuk upload</p>
                <p class="mt-1 text-xs text-[#64748B]">WebP/JPG/PNG/SVG, max 2MB</p>
                <input id="about_photos" type="file" name="about_photos[]" multiple accept="image/webp,image/jpeg,image/png,image/svg+xml" class="hidden">
            </label>

            @error('about_photos.*')
            <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-[#0F172A]">Preview</div>
                    <div class="mt-1 text-xs text-[#64748B]">Centang Remove lalu klik Simpan untuk menghapus.</div>
                </div>
                <div id="about-total" class="text-xs font-semibold text-slate-600">Total: {{ count($items) }}</div>
            </div>

            @if (count($items) === 0)
            <div class="py-20 text-center">
                <p class="text-[#64748B]">Belum ada foto. Upload pertama kamu!</p>
            </div>
            @else
            <div id="about-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @foreach ($items as $idx => $photo)
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" data-id="{{ $photo['id'] }}">
                    <img src="{{ $photo['url'] }}" class="aspect-video w-full object-cover" alt="About photo {{ $idx + 1 }}">

                    <div class="absolute inset-0 bg-black/25 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                        <label class="inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-white transition cursor-pointer" title="Hapus foto">
                            <input type="checkbox" name="about_photos_remove[]" value="{{ $photo['id'] }}" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                            <span>Remove</span>
                        </label>
                    </div>

                    <div class="p-4">
                        <p class="text-sm font-medium text-[#0F172A] truncate" data-photo-label>Foto {{ $idx + 1 }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="sticky bottom-0 z-30 bg-white/80 backdrop-blur-xl border-t border-slate-200 py-4 mt-12 -mx-6 px-6">
            <div class="max-w-7xl mx-auto flex justify-end">
                <button type="submit" class="rounded-xl bg-[#3B82F6] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:bg-[#2563EB] hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed" :disabled="saving">
                    <span x-show="!saving">Simpan Perubahan</span>
                    <span x-show="saving" x-cloak>Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>

    <script is:inline>
        document.addEventListener('DOMContentLoaded', () => {
            const dropzone = document.getElementById('about-dropzone');
            const input = document.getElementById('about_photos');
            if (!dropzone || !input) return;
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('border-[#3B82F6]', 'bg-slate-50/50');
            });
            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('border-[#3B82F6]', 'bg-slate-50/50');
            });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-[#3B82F6]', 'bg-slate-50/50');
                const files = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
                if (!files || files.length === 0) return;
                const dt = new DataTransfer();
                Array.from(files).forEach((f) => dt.items.add(f));
                input.files = dt.files;
            });
        });
    </script>
</div>
@endsection

