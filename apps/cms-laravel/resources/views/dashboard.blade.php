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
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="absolute -right-3 top-6 grid h-6 w-6 place-items-center rounded-full border border-gray-200 bg-white text-slate-900 shadow-sm" aria-label="Toggle sidebar">
                <svg class="size-4 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>

            <div class="cms-sidebar-header flex h-16 items-center px-4">
                <div class="flex w-full items-center gap-3" :class="sidebarOpen ? '' : 'justify-center'">
                    <div class="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-semibold text-white shadow-lg shadow-blue-500/20">R</div>
                    <div x-show="sidebarOpen" x-transition.opacity.duration.300ms class="leading-tight" x-cloak>
                        <div class="text-sm font-semibold text-white">Rizal CMS</div>
                        <div class="text-xs text-slate-400">Dashboard</div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 text-sm font-medium">
                <div class="space-y-1">
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

                    <div class="space-y-1">
                        <button
                            type="button"
                            @click="openSubmenu === 'produk' ? openSubmenu = null : openSubmenu = 'produk'"
                            class="group relative cms-menu-item flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('api/products*') || request()->is('api/categories*') ? 'cms-menu-active' : '' }}">
                            <span class="flex items-center gap-3">
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 8" />
                                    <path d="M21 12a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 12" />
                                </svg>
                                <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Produk</span>
                                <div
                                    x-show="!sidebarOpen"
                                    x-transition
                                    x-cloak
                                    class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                                    Produk
                                </div>
                            </span>
                            <svg x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak class="size-4 transition-transform" :class="openSubmenu === 'produk' && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="openSubmenu === 'produk' && sidebarOpen" x-collapse x-cloak class="pl-3">
                            <div class="mt-1 space-y-1">
                                <a
                                    href="/api/products"
                                    class="cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18" />
                                        <path d="M3 12h18" />
                                        <path d="M3 18h18" />
                                    </svg>
                                    <span>Semua Produk</span>
                                </a>
                                <a
                                    href="/api/categories"
                                    class="cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 3h7v7H3V3Z" />
                                        <path d="M14 3h7v7h-7V3Z" />
                                        <path d="M14 14h7v7h-7v-7Z" />
                                        <path d="M3 14h7v7H3v-7Z" />
                                    </svg>
                                    <span>Kategori</span>
                                </a>
                                <a
                                    href="/api/products"
                                    class="cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 5v14" />
                                        <path d="M5 12h14" />
                                    </svg>
                                    <span>Tambah Produk</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a
                        href="/api/testimonials"
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('api/testimonials*') ? 'cms-menu-active' : '' }}">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Testimoni</span>
                        <div
                            x-show="!sidebarOpen"
                            x-transition
                            x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Testimoni
                        </div>
                    </a>

                    <a
                        href="/api/recommendations"
                        class="group relative cms-menu-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('api/recommendations*') ? 'cms-menu-active' : '' }}">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 17.27 18.18 21 16.54 13.97 22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27Z" />
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms x-cloak>Rekomendasi</span>
                        <div
                            x-show="!sidebarOpen"
                            x-transition
                            x-cloak
                            class="absolute left-full ml-3 rounded-lg bg-slate-800 px-3 py-2 text-sm text-white opacity-0 invisible shadow-lg transition-all whitespace-nowrap group-hover:opacity-100 group-hover:visible">
                            Rekomendasi
                        </div>
                    </a>
                </div>
            </nav>

            <div class="border-t border-[var(--cms-border)] p-4"></div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-gray-200/80 bg-white px-6">
                <div class="relative w-full max-w-md">
                    <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 21l-4.3-4.3" />
                        <circle cx="11" cy="11" r="7" />
                    </svg>
                    <input class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-10 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:bg-white focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="Search..." />
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50" aria-label="Notifications">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    </button>
                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <div class="text-sm font-semibold text-gray-900">Admin</div>
                            <div class="text-xs text-gray-500">Administrator</div>
                        </div>
                        <div class="grid size-9 place-items-center rounded-full bg-slate-900 text-sm font-semibold text-white ring-2 ring-gray-100">AD</div>
                    </div>
                </div>
            </header>

            <main class="min-w-0 flex-1 overflow-y-auto bg-gray-50 p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                        <p class="mt-1 text-sm text-gray-500">ERP overview untuk operasional & konten</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="/api/health" class="inline-flex h-10 items-center justify-center rounded-md border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50">API Health</a>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Logout</button>
                        </form>
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
