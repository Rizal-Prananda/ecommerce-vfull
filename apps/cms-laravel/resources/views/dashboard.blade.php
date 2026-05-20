<!doctype html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ERP Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="h-screen bg-gray-50 text-gray-900">
    <style>
        [x-cloak] {
            display: none !important;
        }

        #cms-admin {
            --cms-bg: linear-gradient(135deg, #08163D, #0E1F4F, #10245C) !important;
            --cms-bg-hover: rgba(255, 255, 255, 0.05);
            --cms-border: rgba(255, 255, 255, 0.08);
            --cms-text: #ffffff;
            --cms-text-active: #ffffff;
            --cms-primary: #2275fc;
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
            background: rgb(44, 149, 247) !important;
            color: var(--cms-text-active) !important;
        }

        #cms-admin .cms-menu-active {
            background: rgba(40, 122, 255, 0.12) !important;
            color: #4393f5 !important;
            border-left: 2px solid var(--cms-primary) !important;
            box-shadow: none !important;
        }
    </style>
    <div id="cms-admin" class="h-screen flex" x-data="{ sidebarOpen: true, openSubmenu: 'produk' }">
        <aside
            class="cms-sidebar sticky top-0 z-40 hidden h-screen flex-col relative transition-all duration-300 ease-out md:flex shadow-[8px_0_15px_rgba(0,0,0,0.15)] shadow-black/50"
            :class="sidebarOpen ? 'w-64' : 'w-20'">
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
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('dashboard') ? 'cms-menu-active' : '' }}">
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
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('dashboard/users*') ? 'cms-menu-active' : '' }}">
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
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('dashboard/chat*') ? 'cms-menu-active' : '' }}">
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

                    
            </nav>

            <div class="border-t border-[var(--cms-border)] p-4"></div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-gray-200/80 bg-[#0E1F4F] px-6">
                <div class="flex shrink-0 items-center gap-3 ml-auto">
                    <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50" aria-label="Notifications">
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
                            class="flex items-center gap-3 rounded-full bg-white pl-1 pr-3 py-1 shadow-sm ring-1 ring-black/5 hover:bg-gray-50"
                            :aria-expanded="open"
                            aria-haspopup="menu"
                        >
                            <img
                                src="{{ $avatarUrl }}"
                                alt="{{ $me?->name ?? 'Admin' }}"
                                class="size-9 rounded-full object-cover bg-slate-900 ring-2 ring-gray-100"
                            />
                            <span class="hidden text-sm font-semibold text-gray-900 sm:block">{{ $me?->name ?? 'Admin' }}</span>
                            <svg class="size-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                            <div class="py-1">
                                <a
                                    href="{{ route('profile.edit') }}"
                                    @click.stop
                                    class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
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

            <main class="min-w-0 flex-1 overflow-y-auto bg-gray-50 p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                        <p class="mt-1 text-sm text-gray-500">Pusat Kontrol Operasional & Konten</p>
                    </div>
                    
                </div>

                <section class="mt-6 grid gap-4 md:grid-cols-4">
                    @foreach ($metrics as $m)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm text-gray-500">{{ $m['label'] }}</div>
                                <div class="mt-1 text-2xl font-bold text-gray-900">{{ $m['value'] }}</div>
                            </div>
                            <div class="grid size-10 place-items-center rounded-lg bg-blue-50 text-blue-600">
                                @if ($loop->index === 0)
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 7h18" />
                                    <path d="M3 12h18" />
                                    <path d="M3 17h18" />
                                </svg>
                                @elseif ($loop->index === 1)
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2h12l4 7-10 13L2 9l4-7Z" />
                                    <path d="M10 9h4" />
                                </svg>
                                @elseif ($loop->index === 2)
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 11a4 4 0 1 0-8 0" />
                                    <path d="M12 15v7" />
                                    <path d="M8 22h8" />
                                </svg>
                                @else
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2v20" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </section>

                <section class="mt-6 grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4">
                                <div>
                                    <div class="text-base font-semibold text-gray-900">Active Order</div>
                                    <div class="mt-1 text-sm text-gray-500">Ringkasan order terbaru</div>
                                </div>
                                <a href="/api/products" class="inline-flex h-10 items-center justify-center rounded-md border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50">Lihat Produk API</a>
                            </div>

                            <div class="overflow-auto">
                                <table class="w-full min-w-[520px] text-left text-sm">
                                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500">
                                        <tr>
                                            <th class="px-5 py-3">ORDER NO</th>
                                            <th class="px-5 py-3">CUSTOMER</th>
                                            <th class="px-5 py-3">ORDER DATE</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($activeOrders as $o)
                                        <tr class="text-gray-700 hover:bg-gray-50">
                                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $o['orderNo'] }}</td>
                                            <td class="px-5 py-3">{{ $o['customer'] }}</td>
                                            <td class="px-5 py-3 text-gray-600">{{ $o['orderDate'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4">
                            <div>
                                <div class="text-base font-semibold text-gray-900">Action Items</div>
                                <div class="mt-1 text-sm text-gray-500">Checklist untuk update konten</div>
                            </div>
                            <div class="grid size-10 place-items-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                            </div>
                        </div>

                        <div class="grid gap-3 p-5">
                            @foreach ($actionItems as $a)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="text-sm font-semibold text-gray-900">{{ $a['title'] }}</div>
                                <div class="mt-1 text-sm text-gray-500">{{ $a['desc'] }}</div>
                                <div class="mt-3">
                                    <button class="inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">{{ $a['cta'] }}</button>
                                </div>
                            </div>
                            @endforeach

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <div class="text-sm font-semibold text-gray-900">Endpoint dipakai front-end</div>
                                <div class="mt-2 grid gap-1 text-sm text-gray-600">
                                    <div class="font-mono">/api/testimonials</div>
                                    <div class="font-mono">/api/recommendations</div>
                                    <div class="font-mono">/api/categories</div>
                                    <div class="font-mono">/api/products</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
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
