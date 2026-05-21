<!doctype html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users - Rizal CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-screen bg-slate-50 text-slate-900">
    <style>
        [x-cloak] {
        display: none !important;
        }

        #cms-admin {
            --cms-bg: linear-gradient(180deg, #2f4156 0%, #243447 100%) !important;
            --cms-bg-hover: rgba(200, 217, 230, 0.12);
            --cms-border: rgba(255, 255, 255, 0.08);
            --cms-text: #f5efeb;
            --cms-text-active: #ffffff;
            --cms-primary: #567c8d;

            --cms-card: #ffffff;
            --cms-soft: #f5efeb;
            --cms-soft-blue: #c8d9e6;
            --cms-accent: #567c8d;
            --cms-title: #2f4156;
        }

        body {
            background: #f5efeb !important;
            color: #2f4156 !important;
        }

        #cms-admin .cms-sidebar {
            background: var(--cms-bg) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
        }

        #cms-admin .cms-sidebar-header {
            background: transparent !important;
            border-bottom: 1px solid var(--cms-border) !important;
        }

        #cms-admin .cms-menu-item {
            color: var(--cms-text) !important;
        }

        #cms-admin .cms-menu-item:hover {
            background: rgba(86, 124, 141, 0.35) !important;
            color: var(--cms-text-active) !important;
        }

        #cms-admin .cms-menu-active {
            background: linear-gradient(90deg, rgba(200, 217, 230, 0.18), rgba(86, 124, 141, 0.22)) !important;
            color: #ffffff !important;
            border-left: 3px solid #c8d9e6 !important;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.03) !important;
        }

        /* HEADER */
        header {
            background: #2f4156 !important;
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
        }

        /* MAIN */
        main {
            background: #f5efeb !important;
        }

        /* CARD */
        .rounded-lg.border,
        .rounded-lg.border.border-gray-200,
        .overflow-hidden.rounded-lg {
            background: #ffffff !important;
            border-color: rgba(47, 65, 86, 0.08) !important;
            box-shadow:
            0 4px 14px rgba(47, 65, 86, 0.05),
            0 1px 2px rgba(47, 65, 86, 0.04) !important;
        }

        /* TITLE */
        h1,
        .text-gray-900,
        .font-semibold,
        .font-bold {
            color: #2f4156 !important;
        }

        /* SUBTITLE */
        .text-gray-500,
        .text-gray-600,
        .text-gray-700 {
            color: #567c8d !important;
        }

        /* TABLE HEADER */
        thead {
          background: #f5efeb !important;
        }

        thead th {
         color: #567c8d !important;
        }

        tbody tr:hover {
         background: rgba(200, 217, 230, 0.18) !important;
        }

        /* ICON BOX */
        .bg-blue-50 {
         background: rgba(200, 217, 230, 0.35) !important;
        }

        .text-blue-600 {
         color: #567c8d !important;
        }

        /* BUTTON PRIMARY */
        .bg-slate-900 {
          background: #567c8d !important;
        }

        .bg-slate-900:hover {
         background: #2f4156 !important;
        }

        /* BUTTON OUTLINE */
        a.inline-flex,
        button.inline-flex {
            transition: all .2s ease;
        }

        a.inline-flex:hover {
            background: #c8d9e6 !important;
            border-color: #567c8d !important;
            color: #2f4156 !important;
        }

        /* PROFILE BUTTON */
        .rounded-full.bg-white {
            background: #ffffff !important;
        }

        /* ENDPOINT BOX */
        .bg-gray-50 {
         background: #f5efeb !important;
        }

        /* INPUT / ACTION CARD */
        .shadow-sm {
            box-shadow:
            0 2px 10px rgba(47, 65, 86, 0.05) !important;
        }
    </style>

    @php
    $roles = $roles ?? ['ADMIN', 'CS', 'TEKNISI', 'USER'];
    $q = trim((string) request()->query('q', ''));
    $roleFilter = strtoupper(trim((string) request()->query('role', '')));
    $exportUrl = '/dashboard/users/export?' . http_build_query(['q' => $q, 'role' => $roleFilter]);

    $roleBadge = function ($role) {
    $r = strtoupper((string) $role);
    return match ($r) {
    'ADMIN' => 'bg-red-100 text-red-800',
    'TEKNISI' => 'bg-blue-100 text-blue-800',
    'CS' => 'bg-green-100 text-green-800',
    default => 'bg-gray-100 text-gray-800',
    };
    };

    $avatarSrc = function ($path) {
    $p = trim((string) $path);
    if ($p === '') return '';
    if (str_starts_with($p, 'http')) return $p;
    if (str_starts_with($p, '/uploads/') || str_starts_with($p, 'uploads/')) return asset(ltrim($p, '/'));
    if (str_starts_with($p, 'avatars/')) return asset('storage/' . $p);
    return asset('storage/' . ltrim($p, '/'));
    };
    @endphp

    <div
        id="cms-admin"
        class="h-screen flex"
        x-data="{
            sidebarOpen: true,
            openSubmenu: 'produk',
            addOpen: false,
            editOpen: false,
            editUserId: null,
            editForm: { name: '', username: '', email: '', role: 'USER', password: '', password_confirmation: '' },
            editingUser: null,
            showEditModal: false,
            openEdit(user) {
                this.editingUser = user;
                this.showEditModal = true;
                this.editUserId = user?.id ?? null;
                this.editForm = {
                    name: user?.name ?? '',
                    username: user?.username ?? '',
                    email: user?.email ?? '',
                    role: user?.role ?? 'USER',
                    password: '',
                    password_confirmation: ''
                };
                this.editOpen = true;
            },
            closeDialogs() {
                this.addOpen = false;
                this.editOpen = false;
                this.showEditModal = false;
                this.editingUser = null;
            }
        }"
        @keydown.escape.window="closeDialogs()"
        @open-edit.window="openEdit($event.detail)">
        <aside
            class="cms-sidebar sticky top-0 z-40 hidden h-screen flex-col relative transition-all duration-300 ease-out md:flex shadow-[8px_0_15px_rgba(0,0,0,0.15)] shadow-black/50"
            :class="sidebarOpen ? 'w-[300px]' : 'w-[120px]'">
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="absolute -right-3 top-6 grid h-6 w-6 place-items-center rounded-full border border-gray-200 bg-white text-slate-900 shadow-sm" aria-label="Toggle sidebar">
                <svg class="size-4 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>

            <div class="cms-sidebar-header flex h-auto flex-col items-center justify-center gap-2 px-4 py-3">
                <img src="{{ asset('logo.png') }}" alt="Avenue Collective" class="size-20 rounded-xl object-cover shadow-[0_0_15px_rgba(0,0,0,0.15)] shadow-black/50" onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">

                <div x-show="sidebarOpen" x-transition.opacity.duration.300ms class="leading-tight text-center" x-cloak>
                    <div class="text-sm font-semibold text-white">Avenue Collective</div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 text-sm font-medium">
                <div class="space-y-1 rounded-lg px-3 py-2.5 shadow-[0_0_15px_rgba(0,0,0,0.15)] shadow-black/30">
                    <a
                        href="/dashboard"
                        class="group relative cms-menu-item flex items-center rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('dashboard') ? 'cms-menu-active' : '' }}"
                        :class="sidebarOpen ? 'gap-3 px-3 py-2.5 justify-start' : 'gap-0 px-2 py-2.5 justify-center'">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 13h8V3H3v10Z" />
                            <path d="M13 21h8V11h-8v10Z" />
                            <path d="M13 3h8v6h-8V3Z" />
                            <path d="M3 17h8v4H3v-4Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Dashboard</span>
                        <div
                            x-show="!sidebarOpen"
                            x-transition
                            x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Dashboard
                        </div>
                    </a>

                    <a
                        href="/dashboard/users"
                        class="group relative cms-menu-item flex items-center rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('dashboard/users*') ? 'cms-menu-active' : '' }}"
                        :class="sidebarOpen ? 'gap-3 px-3 py-2.5 justify-start' : 'gap-0 px-2 py-2.5 justify-center'">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Kelola Pengguna</span>
                        <div
                            x-show="!sidebarOpen"
                            x-transition
                            x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Kelola Pengguna
                        </div>
                    </a>

                    <a
                        href="/dashboard/chat"
                        class="group relative cms-menu-item flex items-center rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('dashboard/chat*') ? 'cms-menu-active' : '' }}"
                        :class="sidebarOpen ? 'gap-3 px-3 py-2.5 justify-start' : 'gap-0 px-2 py-2.5 justify-center'">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Chat Pelanggan</span>
                        <div
                            x-show="!sidebarOpen"
                            x-transition
                            x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Chat Pelanggan
                        </div>
                    </a>

                    <div
                        x-data="{ open: {{ request()->is('report*') ? 'true' : 'false' }} }"
                        @sidebar-dropdown-opened.window="if ($event.detail !== 'report') open = false">
                        <button
                            type="button"
                            @click="
                                open = !open;
                                if (open) { $dispatch('sidebar-dropdown-opened', 'report'); }
                            "
                            class="group relative flex w-full items-center rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('report*') ? 'bg-[#1A56DB] text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}"
                            :class="sidebarOpen ? 'gap-3 px-4 py-2.5 justify-start' : 'gap-0 px-2 py-2.5 justify-center'"
                            :aria-expanded="open"
                            aria-haspopup="menu">
                            <svg class="size-5 shrink-0 {{ request()->is('report*') ? 'text-white' : 'text-gray-300 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19V5" />
                                <path d="M4 19h16" />
                                <path d="M8 17v-5" />
                                <path d="M12 17V9" />
                                <path d="M16 17v-3" />
                            </svg>
                            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Report</span>

                            <div
                                x-show="!sidebarOpen"
                                x-transition
                                x-cloak
                                class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                                Report
                            </div>

                            <svg
                                x-show="sidebarOpen"
                                x-transition.opacity.duration.200ms
                                x-cloak
                                class="ml-auto size-4 text-gray-300 transition-transform"
                                :class="open ? 'rotate-180' : ''"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="sidebarOpen && open" x-collapse x-cloak class="mt-1 ml-4 space-y-1 border-l border-white/10 pl-4">
                            <a
                                href="/report/penjualan"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition {{ request()->is('report/penjualan*') ? 'bg-white/5 text-white font-medium' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19V5" />
                                    <path d="M4 19h16" />
                                    <path d="M8 17v-5" />
                                    <path d="M12 17V9" />
                                    <path d="M16 17v-3" />
                                </svg>
                                <span>Laporan Penjualan</span>
                            </a>

                            <a
                                href="/report/pelanggan"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition {{ request()->is('report/pelanggan*') ? 'bg-white/5 text-white font-medium' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span>Laporan Pelanggan</span>
                            </a>

                            <a
                                href="/report/produk"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition {{ request()->is('report/produk*') ? 'bg-white/5 text-white font-medium' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                    <path d="M3.3 7l8.7 5 8.7-5" />
                                    <path d="M12 22V12" />
                                </svg>
                                <span>Laporan Produk</span>
                            </a>

                            <a
                                href="/report/keuangan"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition {{ request()->is('report/keuangan*') ? 'bg-white/5 text-white font-medium' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 1v22" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                <span>Laporan Keuangan</span>
                            </a>

                            <a
                                href="/report/export"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition {{ request()->is('report/export*') ? 'bg-white/5 text-white font-medium' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <path d="M7 10l5 5 5-5" />
                                    <path d="M12 15V3" />
                                </svg>
                                <span>Export Data</span>
                            </a>
                        </div>
                    </div>


            </nav>

            <div class="border-t border-[var(--cms-border)] p-4"></div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-gray-200/80 bg-[#0E1F4F] px-6">
                @php
                $me = auth()->user();
                $meAvatarPath = (string) ($me?->avatar_path ?? '');
                $meAvatarLegacy = (string) ($me?->avatar ?? '');
                $meAvatarUrl = '';
                if ($meAvatarPath !== '') {
                $meAvatarUrl = $avatarSrc($meAvatarPath);
                } elseif ($meAvatarLegacy !== '') {
                $meAvatarUrl = str_starts_with($meAvatarLegacy, 'http') ? $meAvatarLegacy : asset(ltrim($meAvatarLegacy, '/'));
                } else {
                $nameForAvatar = trim((string) ($me?->name ?? 'Admin'));
                $meAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($nameForAvatar) . '&background=0f172a&color=ffffff&bold=true&size=128';
                }
                @endphp

                <div class="flex shrink-0 items-center gap-3 ml-auto">
                    <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50" aria-label="Notifications">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    </button>

                    <div x-data="{ open: false }" class="relative">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex items-center gap-3 rounded-full bg-white pl-1 pr-3 py-1 shadow-sm ring-1 ring-black/5 hover:bg-slate-50"
                            :aria-expanded="open"
                            aria-haspopup="menu">
                            <img
                                src="{{ $meAvatarUrl }}"
                                alt="{{ $me?->name ?? 'Admin' }}"
                                class="size-9 rounded-full object-cover bg-slate-900 ring-2 ring-slate-100" />
                            <span class="hidden text-sm font-semibold text-slate-900 sm:block">{{ $me?->name ?? 'Admin' }}</span>
                            <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            @click.outside="open = false"
                            @keydown.escape.window="open = false"
                            x-transition:enter="transition ease-out duration-120"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 z-50 mt-2 w-48 rounded-md bg-white shadow-lg ring-1 ring-black/5"
                            role="menu">
                            <div class="py-1">
                                <a
                                    href="{{ route('profile.edit') }}"
                                    @click.stop
                                    class="flex w-full items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                                    role="menuitem"
                                    @click="open = false">
                                    Update Profile
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                        role="menuitem">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="min-w-0 flex-1 overflow-y-auto bg-slate-50">
                <div class="w-full px-4 py-6 sm:px-6">
                    <div class="mb-4 text-sm text-slate-500">
                        <a href="/dashboard" class="hover:text-slate-900">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span class="text-slate-900">Users</span>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Users</h1>
                            <p class="mt-1 text-sm text-slate-600">Manage users, roles, and access.</p>
                        </div>
                        <button
                            type="button"
                            @click="addOpen = true"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-300 px-4 text-sm font-semibold text-slate-900 shadow-m hover:bg-slate-400"
                        >
                            Add User
                        </button>
                    </div>

                    @if (session('success'))
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        {{ session('error') }}
                    </div>
                    @endif
                    @if ($errors->any())
                    <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow isolate">
                        <div class="sticky top-0 z-10 border-b border-slate-200 bg-white/85 backdrop-blur">
                            <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <form method="GET" action="/dashboard/users" class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
                                    <div class="relative w-full sm:max-w-sm">
                                        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 21l-4.3-4.3" />
                                            <circle cx="11" cy="11" r="7" />
                                        </svg>
                                        <input
                                            name="q"
                                            value="{{ $q }}"
                                            placeholder="Search users..."
                                            class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-10 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200" />
                                    </div>

                                    <select
                                        name="role"
                                        class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200 sm:w-48">
                                        <option value="">All roles</option>
                                        @foreach ($roles as $r)
                                        <option value="{{ $r }}" {{ $roleFilter === $r ? 'selected' : '' }}>{{ $r }}</option>
                                        @endforeach
                                    </select>

                                    <div class="flex gap-2">
                                        <button
                                            type="submit"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
                                            Apply
                                        </button>
                                        <a
                                            href="{{ $exportUrl }}"
                                            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
                                            Export
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @if ($users->isEmpty())
                        <div class="grid place-items-center px-6 py-16">
                            <div class="mx-auto max-w-sm text-center">
                                <div class="mx-auto grid size-12 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
                                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                </div>
                                <div class="mt-4 text-base font-semibold text-slate-900">No users yet</div>
                                <div class="mt-1 text-sm text-slate-600">Create your first user to get started.</div>
                                <button
                                    type="button"
                                    @click="addOpen = true"
                                    class="mt-6 inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                                    Add User
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="md:hidden divide-y divide-slate-200">
                            @foreach ($users as $u)
                            @php
                            $isMe = auth()->id() === $u->id;
                            $isActive = strtoupper((string) ($u->status ?? 'ACTIVE')) === 'ACTIVE';
                            $initial = strtoupper(substr((string) $u->name, 0, 1));
                            $avatarPath = (string) ($u->avatar_path ?? '');
                            $avatarUrl = $avatarSrc($avatarPath);
                            @endphp
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 gap-3">
                                        <div class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-full bg-slate-900 text-sm font-semibold text-white">
                                            @if ($avatarPath !== '')
                                            <img src="{{ $avatarUrl }}" alt="{{ $u->name }}" class="h-full w-full object-cover" />
                                            @else
                                            {{ $initial }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-slate-900">{{ $u->name }}</div>
                                            <div class="mt-0.5 text-xs text-slate-500">ID: {{ $u->id }}</div>
                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleBadge($u->role) }}">
                                                    {{ strtoupper((string) $u->role) }}
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                    <span class="inline-block size-2 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                                        <button
                                            @click="open = !open"
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                            :class="{ 'bg-slate-100': open }"
                                            aria-label="Actions">
                                            <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="5" r="1.5" />
                                                <circle cx="12" cy="12" r="1.5" />
                                                <circle cx="12" cy="19" r="1.5" />
                                            </svg>
                                        </button>

                                        <div
                                            x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            @click="open = false"
                                            x-cloak
                                            class="absolute right-0 z-[9999] mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                                            <a
                                                href="{{ route('users.edit', $u->id) }}"
                                                class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                                @click="open = false">
                                                <svg class="mr-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>

                                            @if ($isMe)
                                            <button type="button" disabled class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-400 cursor-not-allowed">
                                                <svg class="mr-3 h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                {{ $isActive ? 'Suspend' : 'Activate' }}
                                            </button>
                                            @else
                                            <form method="POST" action="/dashboard/users/{{ $u->id }}/toggle">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">
                                                    <svg class="mr-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    {{ $isActive ? 'Suspend' : 'Activate' }}
                                                </button>
                                            </form>
                                            @endif

                                            <div class="my-1 h-px bg-slate-100"></div>

                                            @if ($isMe)
                                            <button type="button" disabled class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-400 cursor-not-allowed">
                                                <svg class="mr-3 h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                            @else
                                            <form method="POST" action="/dashboard/users/{{ $u->id }}" onsubmit="return confirm('Hapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="group flex w-full items-center px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                                    <svg class="mr-3 h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="hidden md:block overflow-x-auto md:overflow-visible">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3 text-left font-semibold first:rounded-tl-lg">ID</th>
                                        <th class="px-5 py-3 text-left font-semibold">User</th>
                                        <th class="px-5 py-3 text-left font-semibold">Email</th>
                                        <th class="px-5 py-3 text-left font-semibold">Role</th>
                                        <th class="px-5 py-3 text-left font-semibold">Status</th>
                                        <th class="px-5 py-3 text-left font-semibold">Last Active</th>
                                        <th class="px-5 py-3 text-right font-semibold last:rounded-tr-lg">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach ($users as $u)
                                    @php
                                    $isMe = auth()->id() === $u->id;
                                    $isActive = strtoupper((string) ($u->status ?? 'ACTIVE')) === 'ACTIVE';
                                    $initial = strtoupper(substr((string) $u->name, 0, 1));
                                    $avatarPath = (string) ($u->avatar_path ?? '');
                                    $avatarUrl = $avatarSrc($avatarPath);
                                    $lastAt = $u->last_login_at ?? $u->updated_at;
                                    $lastActive = $lastAt ? $lastAt->format('Y-m-d H:i') : '—';
                                    @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-3 text-slate-600">{{ $u->id }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="grid size-8 shrink-0 place-items-center overflow-hidden rounded-full bg-slate-900 text-xs font-semibold text-white">
                                                    @if ($avatarPath !== '')
                                                    <img src="{{ $avatarUrl }}" alt="{{ $u->name }}" class="h-full w-full object-cover" />
                                                    @else
                                                    {{ $initial }}
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="truncate font-semibold text-slate-900">{{ $u->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-slate-700">{{ $u->email }}</td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleBadge($u->role) }}">
                                                {{ strtoupper((string) $u->role) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                <span class="inline-block size-2 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                {{ $isActive ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-slate-600">{{ $lastActive }}</td>
                                        <td class="px-5 py-3 text-right">
                                            <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                                                <button
                                                    @click="open = !open"
                                                    type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                                    :class="{ 'bg-slate-100': open }"
                                                    aria-label="Actions">
                                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="5" r="1.5" />
                                                        <circle cx="12" cy="12" r="1.5" />
                                                        <circle cx="12" cy="19" r="1.5" />
                                                    </svg>
                                                </button>

                                                <div
                                                    x-show="open"
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="transform opacity-0 scale-95"
                                                    x-transition:enter-end="transform opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="transform opacity-100 scale-100"
                                                    x-transition:leave-end="transform opacity-0 scale-95"
                                                    @click="open = false"
                                                    x-cloak
                                                    class="absolute right-0 z-[9999] mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                                                    <a
                                                        href="{{ route('users.edit', $u->id) }}"
                                                        class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                                        @click="open = false">
                                                        <svg class="mr-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit
                                                    </a>

                                                    @if ($isMe)
                                                    <button type="button" disabled class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-400 cursor-not-allowed">
                                                        <svg class="mr-3 h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                        {{ $isActive ? 'Suspend' : 'Activate' }}
                                                    </button>
                                                    @else
                                                    <form method="POST" action="/dashboard/users/{{ $u->id }}/toggle">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">
                                                            <svg class="mr-3 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                            </svg>
                                                            {{ $isActive ? 'Suspend' : 'Activate' }}
                                                        </button>
                                                    </form>
                                                    @endif

                                                    <div class="my-1 h-px bg-slate-100"></div>

                                                    @if ($isMe)
                                                    <button type="button" disabled class="group flex w-full items-center px-4 py-2 text-left text-sm text-slate-400 cursor-not-allowed">
                                                        <svg class="mr-3 h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                    @else
                                                    <form method="POST" action="/dashboard/users/{{ $u->id }}" onsubmit="return confirm('Hapus user ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="group flex w-full items-center px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                                            <svg class="mr-3 h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Delete
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (method_exists($users, 'currentPage'))
                        <div class="flex flex-col gap-2 border-t border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-sm text-slate-600">
                                Page {{ $users->currentPage() }}
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($users->onFirstPage())
                                <span class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-400">
                                    Prev
                                </span>
                                @else
                                <a href="{{ $users->previousPageUrl() }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                                    Prev
                                </a>
                                @endif

                                @if ($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                                    Next
                                </a>
                                @else
                                <span class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-400">
                                    Next
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @endif
                    </section>
                </div>
            </main>
        </div>

        <div x-show="addOpen" x-transition.opacity x-cloak class="fixed inset-0 z-50">
            <button type="button" class="absolute inset-0 bg-slate-900/40" @click="addOpen = false" aria-label="Close"></button>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Add User</div>
                            <div class="mt-1 text-sm text-slate-600">Create a new user account.</div>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-50" @click="addOpen = false" aria-label="Close">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                    <form method="POST" action="/dashboard/users" class="px-5 py-5" enctype="multipart/form-data">
                        @csrf
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900" for="name">Name</label>
                                <input id="name" name="name" required value="{{ old('name') }}"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200"
                                    placeholder="Full name" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900" for="username">Username</label>
                                <input id="username" name="username" required value="{{ old('username') }}"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200"
                                    placeholder="username" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900" for="email">Email</label>
                                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200"
                                    placeholder="name@company.com" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900" for="role">Role</label>
                                <select id="role" name="role" required
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200">
                                    @foreach ($roles as $r)
                                    <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900" for="password">Password</label>
                                <input id="password" name="password" type="password" required minlength="8"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200"
                                    placeholder="Minimum 8 characters" />
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-sm font-semibold text-slate-900" for="password_confirmation">Confirm Password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200"
                                    placeholder="Repeat password" />
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-sm font-semibold text-slate-900" for="avatar">Profile Photo (optional)</label>
                                <input id="avatar" name="avatar" type="file" accept="image/*"
                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-900 hover:file:bg-slate-200" />
                                <div class="text-xs text-slate-500">PNG/JPG, max 2MB.</div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-2">
                            <button type="button" @click="addOpen = false"
                                class="inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                Cancel
                            </button>
                            <button type="submit"
                                class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                                Add User
                            </button>
                        </div>
                    </form>
                </div>
                    <div class="mt-6 flex items-center justify-end gap-2">
                        <button type="button" @click="addOpen = false"
                            class="inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-300 px-4 text-sm font-semibold text-slate-900 shadow-m hover:bg-slate-400">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="editOpen" x-transition.opacity x-cloak class="fixed inset-0 z-50">
            <button type="button" class="absolute inset-0 bg-slate-900/40" @click="editOpen = false" aria-label="Close"></button>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Edit User</div>
                            <div class="mt-1 text-sm text-slate-600">Update user details.</div>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-50" @click="editOpen = false" aria-label="Close">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" :action="`/dashboard/users/${editUserId}`" class="px-5 py-5" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900">Name</label>
                                <input name="name" x-model="editForm.name" required
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900">Username</label>
                                <input name="username" x-model="editForm.username" required
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900">Email</label>
                                <input name="email" type="email" x-model="editForm.email" required
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900">Role</label>
                                <select name="role" x-model="editForm.role" required
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200">
                                    @foreach ($roles as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-900">Password</label>
                                <input name="password" type="password" x-model="editForm.password"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200"
                                    placeholder="Leave blank to keep" />
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-sm font-semibold text-slate-900">Confirm Password</label>
                                <input name="password_confirmation" type="password" x-model="editForm.password_confirmation"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:bg-white focus:ring-2 focus:ring-slate-200"
                                    placeholder="Repeat password" />
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-sm font-semibold text-slate-900" for="avatar_edit">Profile Photo</label>
                                <input id="avatar_edit" name="avatar" type="file" accept="image/*"
                                    class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-900 hover:file:bg-slate-200" />
                                <div class="text-xs text-slate-500">Upload untuk mengganti foto. PNG/JPG, max 2MB.</div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-2">
                            <button type="button" @click="editOpen = false"
                                class="inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                                Cancel
                            </button>
                            <button type="submit"
                                class="inline-flex h-10 items-center justify-center rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("alpine:init", () => {
            if (window.collapse) Alpine.plugin(window.collapse);
        });
    </script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>
