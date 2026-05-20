@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    @php
        $roleValue = strtoupper((string) old('role', $user->role ?? 'USER'));
        $statusRaw = (string) ($user->status ?? 'active');
        $statusValue = strtolower((string) old('status', $statusRaw));
        if (in_array($statusValue, ['active', 'inactive', 'suspended'], true) === false) {
            $statusValue = strtolower((string) $statusRaw);
        }
        if (in_array($statusValue, ['active', 'inactive', 'suspended'], true) === false) {
            $statusValue = 'active';
        }
        $avatarPath = (string) ($user->avatar_path ?? '');
        $avatarUrl = '';
        if ($avatarPath !== '') {
            if (str_starts_with($avatarPath, 'http')) {
                $avatarUrl = $avatarPath;
            } else {
                $avatarUrl = asset(ltrim($avatarPath, '/'));
            }
        }
        $initial = strtoupper(substr((string) ($user->name ?? 'A'), 0, 1));
    @endphp
    <div class="px-6 py-6">
        <div class="mb-4 text-sm text-slate-500">
            <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('users.index') }}" class="hover:text-slate-900">Users</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900">Edit</span>
        </div>

        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit User</h1>
            <p class="mt-1 text-sm text-slate-600">Update informasi user, role, dan akses</p>
        </div>

        <div class="max-w-2xl rounded-lg border border-slate-200 bg-white shadow-sm">
            <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700" for="avatar">Foto Profil</label>
                        <div class="mt-1.5 flex items-center gap-4">
                            <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-full bg-slate-900 text-sm font-semibold text-white ring-2 ring-slate-100">
                                @if ($avatarUrl !== '')
                                    <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="h-full w-full object-cover" />
                                @else
                                    {{ $initial }}
                                @endif
                            </div>
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
                        <label class="text-sm font-medium text-slate-700" for="name">Nama Lengkap</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        />
                        @error('name')
                            <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="username">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            required
                            value="{{ old('username', $user->username) }}"
                            class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                        />
                        @error('username')
                            <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            class="mt-1.5 w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-500 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            readonly
                            disabled
                        />
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700" for="password">Password</label>
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700" for="role">Role</label>
                            <select
                                id="role"
                                name="role"
                                class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                                @foreach (['USER', 'ADMIN', 'SUPERADMIN'] as $r)
                                    <option value="{{ $r }}" {{ $roleValue === $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700" for="status">Status</label>
                            <select
                                id="status"
                                name="status"
                                class="mt-1.5 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                                @foreach (['active', 'inactive', 'suspended'] as $s)
                                    <option value="{{ $s }}" {{ $statusValue === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="text-xs text-red-600 mt-1.5">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-6 py-4">
                    <a
                        href="{{ route('users.index') }}"
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
