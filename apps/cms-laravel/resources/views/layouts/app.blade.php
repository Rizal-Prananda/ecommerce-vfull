<!doctype html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'CMS'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-screen bg-slate-50 text-slate-900 antialiased">
    <style>
        [x-cloak] {
            display: none !important;
        }

        #cms-admin {
            --cms-bg: #0a0a0a;
            --cms-bg-hover: rgba(255, 255, 255, 0.05);
            --cms-border: rgba(255, 255, 255, 0.08);
            --cms-text: #a1a1aa;
            --cms-text-active: #fafafa;
            --cms-primary: #3b82f6;
        }

        #cms-admin .cms-sidebar {
            background: var(--cms-bg) !important;
            border-right: 1px solid var(--cms-border) !important;
        }

        #cms-admin .cms-sidebar-header {
            background: transparent !important;
            border-bottom: 1px solid var(--cms-border) !important;
        }

        #cms-admin .cms-menu-item {
            color: var(--cms-text) !important;
        }

        #cms-admin .cms-menu-item:hover {
            background: var(--cms-bg-hover) !important;
            color: var(--cms-text-active) !important;
        }

        #cms-admin .cms-menu-active {
            background: rgba(59, 130, 246, 0.12) !important;
            color: #60a5fa !important;
            border-left: 2px solid var(--cms-primary) !important;
            box-shadow: none !important;
        }
    </style>

    <div id="cms-admin" class="h-screen flex" x-data="{ sidebarOpen: true, openSubmenu: 'produk' }">
        <aside
            class="cms-sidebar sticky top-0 z-40 hidden h-screen flex-col relative transition-all duration-300 ease-out md:flex"
            :class="sidebarOpen ? 'w-64' : 'w-20'">
            <button type="button" @click="sidebarOpen = !sidebarOpen"
                class="absolute -right-3 top-6 grid h-6 w-6 place-items-center rounded-full border border-gray-200 bg-white text-slate-900 shadow-sm"
                aria-label="Toggle sidebar">
                <svg class="size-4 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>

            <div class="cms-sidebar-header flex h-16 items-center px-4">
                <div class="flex w-full items-center gap-3" :class="sidebarOpen ? '' : 'justify-center'">
                    <div
                        class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-semibold text-white shadow-lg shadow-blue-500/20">
                        R</div>
                    <div x-show="sidebarOpen" x-transition.opacity.duration.300ms class="leading-tight" x-cloak>
                        <div class="text-sm font-semibold text-white">Rizal CMS</div>
                        <div class="text-xs text-slate-400">Dashboard</div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 text-sm font-medium">
                <div class="space-y-1">
                    <a href="/dashboard"
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('dashboard') ? 'cms-menu-active' : '' }}">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M3 13h8V3H3v10Z" />
                            <path d="M13 21h8V11h-8v10Z" />
                            <path d="M13 3h8v6h-8V3Z" />
                            <path d="M3 17h8v4H3v-4Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Dashboard</span>
                        <div x-show="!sidebarOpen" x-transition x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Dashboard
                        </div>
                    </a>

                    <a href="/dashboard/users"
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('dashboard/users*') ? 'cms-menu-active' : '' }}">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Kelola Pengguna</span>
                        <div x-show="!sidebarOpen" x-transition x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Kelola Pengguna
                        </div>
                    </a>

                    <a href="/dashboard/chat"
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('dashboard/chat*') ? 'cms-menu-active' : '' }}">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Chat Pelanggan</span>
                        <div x-show="!sidebarOpen" x-transition x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Chat Pelanggan
                        </div>
                    </a>

                    <div class="space-y-1">
                        <button type="button"
                            @click="openSubmenu === 'produk' ? openSubmenu = null : openSubmenu = 'produk'"
                            class="group relative cms-menu-item flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200">
                            <span class="flex items-center gap-3">
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 8" />
                                    <path d="M21 12a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 12" />
                                </svg>
                                <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Produk</span>
                                <div x-show="!sidebarOpen" x-transition x-cloak
                                    class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                                    Produk
                                </div>
                            </span>
                            <svg x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak
                                class="size-4 transition-transform" :class="openSubmenu === 'produk' && 'rotate-180'"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="openSubmenu === 'produk' && sidebarOpen" x-collapse x-cloak class="pl-3">
                            <div class="mt-1 space-y-1">
                                <a href="/api/products"
                                    class="cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M3 6h18" />
                                        <path d="M3 12h18" />
                                        <path d="M3 18h18" />
                                    </svg>
                                    <span>Semua Produk</span>
                                </a>
                                <a href="/api/categories"
                                    class="cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M3 3h7v7H3V3Z" />
                                        <path d="M14 3h7v7h-7V3Z" />
                                        <path d="M14 14h7v7h-7v-7Z" />
                                        <path d="M3 14h7v7H3v-7Z" />
                                    </svg>
                                    <span>Kategori</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="/api/testimonials"
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Testimoni</span>
                        <div x-show="!sidebarOpen" x-transition x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Testimoni
                        </div>
                    </a>

                    <a href="/api/recommendations"
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 17.27 18.18 21 16.54 13.97 22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Rekomendasi</span>
                        <div x-show="!sidebarOpen" x-transition x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Rekomendasi
                        </div>
                    </a>
                </div>
            </nav>

            <div class="border-t border-[var(--cms-border)] p-4"></div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-6">
                <div class="flex shrink-0 items-center gap-3 ml-auto">
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
                            aria-haspopup="menu"
                        >
                            <img
                                src="{{ $avatarUrl }}"
                                alt="{{ $me?->name ?? 'Admin' }}"
                                class="size-9 rounded-full object-cover bg-slate-900 ring-2 ring-slate-100"
                            />
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
                            role="menu"
                        >
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
                                    @click="open = false"
                                >
                                    Update Profile
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                        role="menuitem"
                                    >
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
