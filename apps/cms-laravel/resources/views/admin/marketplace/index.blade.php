@extends('layouts.app')

@section('title', 'Marketplace')

@section('content')
<div
    class="max-w-7xl mx-auto p-6 lg:p-8"
    x-data="{
            desktopPreview: @js($desktopPreviewUrl),
            mobilePreview: @js($mobilePreviewUrl),
            desktopDragging: false,
            mobileDragging: false,
            saving: false,
            updatePreviewFromFile(file, target) {
                if (!file) return;
                this[target] = URL.createObjectURL(file);
            },
            updatePreview(event, target) {
                const file = event.target.files && event.target.files[0];
                if (!file) return;
                this.updatePreviewFromFile(file, target);
            },
            onDrop(e, target, inputRef, draggingKey) {
                this[draggingKey] = false;
                const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                if (!file) return;
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs[inputRef].files = dt.files;
                this.updatePreviewFromFile(file, target);
            },
            remove(target, inputRef) {
                this[target] = '';
                if (this.$refs[inputRef]) this.$refs[inputRef].value = '';
            },
        }">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-zinc-900 mb-1">Marketplace</h1>
        <p class="text-sm text-zinc-500">Atur hero banner untuk halaman marketplace.</p>
    </div>

    @if (session('success'))
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.marketplace.banner') }}"
        enctype="multipart/form-data"
        class="grid gap-8 lg:grid-cols-2"
        @submit="saving = true">
        @csrf

        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900">Hero Banner Desktop</div>
                        <div class="mt-1 text-xs text-zinc-500">Recommended: 1448x1086px • 4:3 • WebP &lt;500KB</div>
                        <div class="mt-1 text-xs text-zinc-500">Accepted: WebP/JPEG • Max 2MB</div>
                    </div>
                    <button
                        type="button"
                        x-show="desktopPreview"
                        x-cloak
                        @click="remove('desktopPreview', 'desktopInput')"
                        class="inline-flex h-9 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-900 hover:bg-zinc-50">
                        Remove
                    </button>
                </div>

                <label
                    class="mt-5 relative block w-full cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition"
                    :class="desktopDragging ? 'border-black bg-zinc-50' : 'border-zinc-300 hover:border-black'"
                    @dragover.prevent="desktopDragging = true"
                    @dragleave.prevent="desktopDragging = false"
                    @drop.prevent="onDrop($event, 'desktopPreview', 'desktopInput', 'desktopDragging')">
                    <input
                        x-ref="desktopInput"
                        type="file"
                        name="marketplace_hero_banner"
                        accept="image/webp,image/jpeg"
                        class="sr-only"
                        @change="updatePreview($event, 'desktopPreview')">
                    <svg class="mx-auto h-10 w-10 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span class="mt-2 block text-sm font-medium text-zinc-900">Click to upload or drag &amp; drop</span>
                    <span class="mt-1 block text-xs text-zinc-500">1448x1086px • WebP/JPEG • max 2MB</span>
                </label>

                @error('marketplace_hero_banner')
                <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-zinc-900">Hero Banner Mobile</div>
                        <div class="mt-1 text-xs text-zinc-500">Recommended: 768x1024px • 3:4 • WebP &lt;300KB</div>
                        <div class="mt-1 text-xs text-zinc-500">Accepted: WebP/JPEG • Max 2MB</div>
                    </div>
                    <button
                        type="button"
                        x-show="mobilePreview"
                        x-cloak
                        @click="remove('mobilePreview', 'mobileInput')"
                        class="inline-flex h-9 items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-900 hover:bg-zinc-50">
                        Remove
                    </button>
                </div>

                <label
                    class="mt-5 relative block w-full cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition"
                    :class="mobileDragging ? 'border-black bg-zinc-50' : 'border-zinc-300 hover:border-black'"
                    @dragover.prevent="mobileDragging = true"
                    @dragleave.prevent="mobileDragging = false"
                    @drop.prevent="onDrop($event, 'mobilePreview', 'mobileInput', 'mobileDragging')">
                    <input
                        x-ref="mobileInput"
                        type="file"
                        name="marketplace_hero_banner_mobile"
                        accept="image/webp,image/jpeg"
                        class="sr-only"
                        @change="updatePreview($event, 'mobilePreview')">
                    <svg class="mx-auto h-10 w-10 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span class="mt-2 block text-sm font-medium text-zinc-900">Click to upload or drag &amp; drop</span>
                    <span class="mt-1 block text-xs text-zinc-500">768x1024px • WebP/JPEG • max 2MB</span>
                </label>

                @error('marketplace_hero_banner_mobile')
                <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-xl bg-zinc-900 py-3 text-sm font-semibold text-white hover:bg-zinc-800 transition disabled:opacity-70 disabled:cursor-not-allowed"
                :disabled="saving">
                <span x-show="!saving">Save Changes</span>
                <span x-show="saving" x-cloak>Saving...</span>
            </button>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="text-sm font-semibold text-zinc-900 mb-3">Live Preview (Desktop)</div>
                <div class="relative aspect-[4/3] w-full overflow-hidden rounded-xl bg-zinc-100">
                    <template x-if="desktopPreview">
                        <img :src="desktopPreview" alt="Marketplace Desktop Preview" class="h-full w-full object-cover" loading="eager">
                    </template>
                    <template x-if="!desktopPreview">
                        <div class="grid h-full w-full place-items-center text-sm text-zinc-500">
                            Upload gambar untuk preview desktop.
                        </div>
                    </template>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/5 to-transparent"></div>
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-center">
                        <p class="text-white text-xl font-light tracking-widest drop-shadow-lg">AVENUE COLLECTIVE</p>
                        <span class="mt-2 inline-block border border-white px-4 py-1.5 text-xs tracking-[0.2em] text-white">SHOP NOW</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="text-sm font-semibold text-zinc-900 mb-3">Live Preview (Mobile)</div>
                <div class="mx-auto w-[280px] rounded-[2.25rem] border-8 border-zinc-900 bg-zinc-900 shadow-2xl">
                    <div class="relative aspect-[3/4] w-full overflow-hidden rounded-[1.75rem] bg-zinc-100">
                        <template x-if="mobilePreview">
                            <img :src="mobilePreview" alt="Marketplace Mobile Preview" class="h-full w-full object-cover" loading="eager">
                        </template>
                        <template x-if="!mobilePreview">
                            <div class="grid h-full w-full place-items-center text-sm text-zinc-500">
                                Upload gambar untuk preview mobile.
                            </div>
                        </template>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/5 to-transparent"></div>
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-center">
                            <p class="text-white text-lg font-light tracking-widest drop-shadow-lg">AVENUE</p>
                            <span class="mt-1.5 inline-block border border-white px-3 py-1 text-[11px] tracking-[0.2em] text-white">SHOP NOW</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
