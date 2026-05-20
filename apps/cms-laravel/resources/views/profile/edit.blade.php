@extends('layouts.app')

@section('title', 'Update Profile')

@section('content')
    @php
        $me = $user ?? auth()->user();
        $avatarPath = (string) ($me?->avatar_path ?? '');
        $avatarLegacy = (string) ($me?->avatar ?? '');
        $avatarUrl = '';
        if ($avatarPath !== '') {
            if (str_starts_with($avatarPath, 'http')) {
                $avatarUrl = $avatarPath;
            } else {
                $avatarUrl = asset(ltrim($avatarPath, '/'));
            }
        } elseif ($avatarLegacy !== '') {
            $avatarUrl = str_starts_with($avatarLegacy, 'http') ? $avatarLegacy : asset(ltrim($avatarLegacy, '/'));
        } else {
            $nameForAvatar = trim((string) ($me?->name ?? 'Admin'));
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($nameForAvatar) . '&background=0f172a&color=ffffff&bold=true&size=128';
        }
    @endphp

    <div class="px-6 py-6">
        <div class="mb-4 text-sm text-slate-500">
            <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900">Profile</span>
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Update Profile</h1>
            <p class="mt-1 text-sm text-slate-600">Perbarui informasi akun, avatar, dan keamanan.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 max-w-2xl rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        <div class="max-w-2xl rounded-lg border border-slate-200 bg-white shadow-sm">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700" for="avatar">Foto Profil</label>
                        <div class="mt-1.5 flex items-center gap-4">
                            <img
                                src="{{ $avatarUrl }}"
                                alt="{{ $me?->name ?? 'Admin' }}"
                                class="size-12 rounded-full object-cover bg-slate-900 ring-2 ring-slate-100"
                            />
                            <div class="min-w-0 flex-1">
                                <input
                                    id="avatar"
                                    name="avatar"
                                    type="file"
                                    accept="image/*"
                                    class="mt-1.5 block w-full rounded-md border border-slate-300 bg-white text-sm text-slate-900 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                />
                                @error('avatar')
                                    <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="name">Nama</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $me?->name) }}"
                            class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        />
                        @error('name')
                            <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $me?->email) }}"
                            class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        />
                        @error('email')
                            <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700" for="password">Password Baru</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Kosongkan jika tidak diubah"
                                class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />
                            @error('password')
                                <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700" for="password_confirmation">Konfirmasi Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                placeholder="Ulangi password baru"
                                class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-6 py-4">
                    <a
                        href="/dashboard"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-md bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
