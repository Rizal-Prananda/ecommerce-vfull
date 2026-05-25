@extends('layouts.app')

@section('title', 'Homepage')

@section('content')
<div class="max-w-7xl mx-auto p-6 lg:p-8" x-data="{ saving: false }">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-[#0F172A] mb-1">Homepage</h1>
        <p class="text-sm text-[#64748B]">Upload dan urutkan banner slideshow untuk Hero landing page.</p>
    </div>

    @if (session('success'))
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <div class="font-semibold mb-1">Periksa input:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form
        id="homepage-form"
        method="POST"
        action="{{ route('admin.homepage.update') }}"
        enctype="multipart/form-data"
        class="space-y-8"
        @submit="saving = true">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <div class="text-sm font-semibold text-[#0F172A]">Hero Slideshow (Landing)</div>
                    <div class="mt-1 text-xs text-[#64748B]">Urutan pertama akan dianggap banner aktif.</div>
                </div>
                <div class="text-right">
                    <div class="text-xs font-semibold text-slate-700">Format</div>
                    <div class="mt-1 text-xs text-[#64748B]">WebP/SVG • Max 2MB • Rasio 16:9</div>
                </div>
            </div>

            <div class="mt-5">
                <label
                    for="hero_banners"
                    id="hero-dropzone"
                    class="block rounded-2xl border border-slate-200 p-12 text-center transition-all duration-300 cursor-pointer hover:border-[#3B82F6] hover:bg-slate-50/50">
                    <svg class="mx-auto h-12 w-12 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V8m0 0l-3 3m3-3l3 3M20 16.5a4.5 4.5 0 00-3.1-4.3A6 6 0 006 10.5a4.5 4.5 0 00.5 9h12.9a3.6 3.6 0 00.6-7z" />
                    </svg>
                    <p class="mt-4 text-sm font-semibold text-[#0F172A]">Tarik banner ke sini atau klik untuk upload</p>
                    <p class="mt-1 text-xs text-[#64748B]">WebP/SVG, max 2MB, rasio 16:9 biar bagus</p>
                    <input
                        id="hero_banners"
                        type="file"
                        name="hero_banners[]"
                        multiple
                        accept="image/webp,image/svg+xml"
                        class="hidden">
                </label>

                @error('hero_banners')
                <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
                @error('hero_banners.*')
                <div class="mt-3 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @if (empty($banners) || !is_array($banners) || count($banners) === 0)
        <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
            <p class="text-sm text-[#64748B]">Belum ada banner. Upload pertama kamu!</p>
        </div>
        @else
        <div class="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-6">
                <div>
                    <div class="text-sm font-semibold text-[#0F172A]">Preview</div>
                    <div class="mt-1 text-xs text-[#64748B]">Drag untuk ubah urutan. Klik trash untuk hapus.</div>
                </div>
                <div id="homepage-total" class="text-xs text-[#64748B]">Total: {{ count($banners) }}</div>
            </div>

            <div id="remove-inputs" class="hidden"></div>

            <div id="homepage-banners-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @foreach($banners as $banner)
                <div class="group relative rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl" data-id="{{ $banner['id'] }}">

                    <img src="{{ $banner['url'] }}" class="aspect-video w-full object-cover" alt="Banner {{ $loop->iteration }}">

                    <div class="absolute inset-0 bg-black/25 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                        <button
                            type="button"
                            data-drag-handle
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-white/90 p-3 text-slate-800 hover:bg-white transition cursor-grab active:cursor-grabbing"
                            aria-label="Drag untuk ubah urutan"
                            title="Drag untuk ubah urutan">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <circle cx="9" cy="6" r="1.2" />
                                <circle cx="15" cy="6" r="1.2" />
                                <circle cx="9" cy="12" r="1.2" />
                                <circle cx="15" cy="12" r="1.2" />
                                <circle cx="9" cy="18" r="1.2" />
                                <circle cx="15" cy="18" r="1.2" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            data-remove
                            data-remove-id="{{ $banner['id'] }}"
                            class="inline-flex items-center justify-center rounded-full bg-red-500/90 p-3 text-white hover:bg-red-500 transition"
                            aria-label="Hapus banner"
                            title="Hapus banner">
                            <span class="text-lg font-semibold leading-none">×</span>
                            <span class="text-lg font-semibold leading-none">×</span>
                        </button>
                    </div>

                    <div class="p-4">
                        <p data-banner-label class="text-sm font-medium text-[#0F172A] truncate">Banner {{ $loop->iteration }}</p>
                        <p class="text-xs text-[#64748B] mt-0.5">{{ $banner['date'] ?: '' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="sticky bottom-0 z-30 bg-white/80 backdrop-blur-xl border-t border-slate-200 py-4 mt-12 -mx-6 px-6">
            <div class="max-w-7xl mx-auto flex justify-end">
                <button
                    type="submit"
                    class="rounded-xl bg-[#3B82F6] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:bg-[#2563EB] hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-60"
                    :disabled="saving">
                    <span x-show="!saving">Simpan Perubahan</span>
                    <span x-show="saving" x-cloak>Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    (function() {
        const input = document.getElementById('hero_banners');
        const dropzone = document.getElementById('hero-dropzone');
        const grid = document.getElementById('homepage-banners-grid');
        const removeInputs = document.getElementById('remove-inputs');
        const totalEl = document.getElementById('homepage-total');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const refreshGridUi = () => {
            if (!grid) return;
            const cards = Array.from(grid.children).filter((el) => el && el.nodeType === 1 && el.hasAttribute('data-id'));
            cards.forEach((card, idx) => {
                const label = card.querySelector('[data-banner-label]');
                if (label) label.textContent = `Banner ${idx + 1}`;
            });
            if (totalEl) totalEl.textContent = `Total: ${cards.length}`;
        };

        const uploadFiles = (files) => {
            if (!input) return;
            const dt = new DataTransfer();
            for (const f of Array.from(files || [])) dt.items.add(f);
            input.files = dt.files;
        };

        if (dropzone && input) {
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
                if (e.dataTransfer?.files?.length) uploadFiles(e.dataTransfer.files);
            });
        }

        if (grid && window.Sortable) {
            new Sortable(grid, {
                animation: 150,
                ghostClass: 'opacity-50',
                handle: '[data-drag-handle]',
                onEnd: function() {
                    refreshGridUi();
                    const order = Array.from(grid.children).map((el) => el.getAttribute('data-id')).filter(Boolean);
                    fetch('/admin/homepage/reorder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            order
                        }),
                        credentials: 'same-origin'
                    }).catch(() => {});
                }
            });
        }

        if (grid && removeInputs) {
            grid.addEventListener('click', (e) => {
                const btn = e.target?.closest?.('[data-remove]');
                if (!btn) return;
                const id = btn.getAttribute('data-remove-id') || '';
                if (!id) return;

                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'hero_banners_remove[]';
                inp.value = id;
                removeInputs.appendChild(inp);

                const card = btn.closest('[data-id]');
                if (card) card.remove();

                refreshGridUi();
            });
        }

        refreshGridUi();
    })();
</script>
@endsection
