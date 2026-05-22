<!doctype html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'CMS'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener("alpine:init", () => {
            if (window.collapse) Alpine.plugin(window.collapse);
        });
    </script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="h-screen bg-slate-50 text-slate-900 antialiased">
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
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03) !important;
        }

        /* HEADER */
        header {
            background: #2f4156 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
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

    <div id="cms-admin" class="h-screen flex" x-data="{ sidebarOpen: true }">
        <aside
            class="cms-sidebar sticky top-0 z-40 hidden h-screen flex-col relative transition-all duration-300 ease-out md:flex"
            :class="sidebarOpen ? 'w-[300px]' : 'w-[120px]'">
            <button type="button" @click="sidebarOpen = !sidebarOpen"
                class="absolute -right-3 top-6 grid h-6 w-6 place-items-center rounded-full border border-gray-200 bg-white text-slate-900 shadow-sm"
                aria-label="Toggle sidebar">
                <svg class="size-4 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
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

                    <div
                        x-data="{ open: {{ request()->is('admin/products*') || request()->is('admin/stock*') ? 'true' : 'false' }} }"
                        @sidebar-dropdown-opened.window="if ($event.detail !== 'product') open = false">
                        <button
                            type="button"
                            @click="
                                open = !open;
                                if (open) { $dispatch('sidebar-dropdown-opened', 'product'); }
                            "
                            class="group relative flex w-full items-center rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('admin/products*') || request()->is('admin/stock*') ? 'bg-[#1A56DB] text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}"
                            :class="sidebarOpen ? 'gap-3 px-4 py-2.5 justify-start' : 'gap-0 px-2 py-2.5 justify-center'"
                            :aria-expanded="open"
                            aria-haspopup="menu">
                            <svg class="size-5 shrink-0 {{ request()->is('admin/products*') || request()->is('admin/stock*') ? 'text-white' : 'text-gray-300 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                <path d="M3.3 7l8.7 5 8.7-5" />
                                <path d="M12 22V12" />
                            </svg>
                            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Product</span>

                            <div
                                x-show="!sidebarOpen"
                                x-transition
                                x-cloak
                                class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                                Product
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
                                href="{{ route('products.index') }}"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition {{ request()->is('admin/products*') ? 'bg-white/5 text-white font-medium' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73L13 2.27a2 2 0 0 0-2 0L4 6.27A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                    <path d="M3.3 7l8.7 5 8.7-5" />
                                    <path d="M12 22V12" />
                                </svg>
                                <span>List Produk</span>
                            </a>
                            <a
                                href="{{ route('products.stock') }}"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition {{ request()->is('admin/stock*') ? 'bg-white/5 text-white font-medium' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19V5" />
                                    <path d="M4 19h16" />
                                    <path d="M8 17v-5" />
                                    <path d="M12 17V9" />
                                    <path d="M16 17v-3" />
                                </svg>
                                <span>Stock Produk</span>
                            </a>
                        </div>
                    </div>

                </div>
            </nav>

            <div class="border-t border-[var(--cms-border)] p-4"></div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-gray-200/80 bg-[#0E1F4F] px-6">
                <div class="flex shrink-0 items-center gap-3 ml-auto">
                    <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50" aria-label="Notifications">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    </button>

                    @php
                    $me = auth()->user();
                    $avatarPath = (string) ($me?->avatar_path ?? '');
                    $avatarLegacy = (string) ($me?->avatar ?? '');
                    $avatarUrl = '';
                    if ($avatarPath !== '') {
                    if (str_starts_with($avatarPath, 'http')) {
                    $avatarUrl = $avatarPath;
                    } elseif (str_starts_with($avatarPath, '/uploads/') || str_starts_with($avatarPath, 'uploads/')) {
                    $avatarUrl = asset(ltrim($avatarPath, '/'));
                    } elseif (str_starts_with($avatarPath, 'avatars/')) {
                    $avatarUrl = asset('storage/' . $avatarPath);
                    } else {
                    $avatarUrl = asset('storage/' . ltrim($avatarPath, '/'));
                    }
                    } elseif ($avatarLegacy !== '') {
                    $avatarUrl = str_starts_with($avatarLegacy, 'http') ? $avatarLegacy : asset(ltrim($avatarLegacy, '/'));
                    } else {
                    $nameForAvatar = trim((string) ($me?->name ?? 'Admin'));
                    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($nameForAvatar) . '&background=0f172a&color=ffffff&bold=true&size=128';
                    }
                    @endphp

                    <div x-data="{ open: false }" class="relative">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex items-center gap-3 rounded-full bg-white pl-1 pr-3 py-1 shadow-sm ring-1 ring-black/5 hover:bg-slate-50"
                            :aria-expanded="open"
                            aria-haspopup="menu">
                            <img
                                src="{{ (string) ($avatarUrl ?? '') }}"
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
                            <div class="px-4 py-3 border-b border-slate-100">
                                <div class="text-sm font-semibold text-slate-900 truncate">{{ $me?->name ?? 'Admin' }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ $me?->email ?? '' }}</div>
                            </div>

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
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
