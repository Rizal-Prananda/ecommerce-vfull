<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Chat - CMS</title>
    <script defer src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/favicon.ico" />
</head>
<body class="bg-[#0a0a0a] text-[#a1a1aa]">
    <header class="fixed top-0 right-0 left-64 z-40 border-b border-gray-800 bg-[#0a0a0a]/80 backdrop-blur">
        <div class="h-16 px-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Chat Pelanggan</h1>
                <p class="text-sm text-gray-500">Kelola percakapan pelanggan</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="/dashboard" class="text-sm text-gray-400 hover:text-white transition-colors">Dashboard</a>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="text-sm text-gray-400 hover:text-white transition-colors">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="flex h-screen">
        <aside class="fixed left-0 top-0 bottom-0 w-64 border-r border-gray-800 bg-[#0a0a0a] overflow-y-auto pt-20">
            <div class="p-4">
                <h2 class="text-sm font-semibold text-white mb-4">Pelanggan</h2>
                <div class="space-y-2">
                    @foreach ($customers as $c)
                        @php
                            $isActive = (int) $selectedId === (int) $c->id_pelanggan;
                            $unread = (int) ($c->unread_count ?? 0);
                        @endphp
                        <a
                            href="/dashboard/chat/{{ $c->id_pelanggan }}"
                            class="{{ $isActive ? 'bg-blue-600 text-white' : 'hover:bg-gray-900 text-gray-300' }} flex w-full items-start justify-between gap-3 p-3 rounded-lg transition-colors"
                        >
                            <div class="min-w-0">
                                <div class="font-medium text-sm truncate">{{ $c->namalengkap_pelanggan }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $c->email_pelanggan }}</div>
                            </div>
                            @if ($unread > 0)
                                <span class="shrink-0 rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-black">{{ $unread }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>

        <main class="flex-1 ml-64 pt-20 pb-32 flex flex-col">
            @if ($customers->isEmpty())
                <div class="flex-1 flex items-center justify-center p-6">
                    <div class="text-center">
                        <p class="text-gray-500">Belum ada pelanggan.</p>
                    </div>
                </div>
            @else
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="max-w-3xl">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-white">{{ $selectedCustomer?->namalengkap_pelanggan ?? 'Pelanggan' }}</h3>
                            <p class="text-sm text-gray-500">{{ $selectedCustomer?->email_pelanggan ?? '' }}</p>
                            @if (!empty($selectedCustomer?->notelepon_pelanggan))
                                <p class="text-sm text-gray-500">{{ $selectedCustomer->notelepon_pelanggan }}</p>
                            @endif
                        </div>

                        <div class="space-y-4">
                            @forelse ($messages as $msg)
                                @php
                                    $isAdmin = ($msg->pengirim ?? '') === 'admin';
                                    $time = !empty($msg->createdAt) ? \Illuminate\Support\Carbon::parse($msg->createdAt)->format('H:i') : '';
                                @endphp
                                <div class="{{ $isAdmin ? 'flex justify-end' : 'flex justify-start' }}">
                                    <div class="{{ $isAdmin ? 'bg-blue-600 text-white' : 'bg-gray-900 text-gray-100' }} rounded-lg p-3 max-w-sm">
                                        <div class="text-xs font-semibold mb-1">{{ $isAdmin ? 'Admin' : ($selectedCustomer?->namalengkap_pelanggan ?? 'Pelanggan') }}</div>
                                        <p class="text-sm whitespace-pre-line">{{ $msg->pesan }}</p>
                                        @if ($time !== '')
                                            <div class="text-xs mt-2 opacity-70">{{ $time }}</div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500">Belum ada chat dari pelanggan ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="fixed bottom-0 right-0 left-64 border-t border-gray-800 bg-[#0a0a0a] p-6">
                    <div class="max-w-3xl mx-auto">
                        @if ($errors->any())
                            <div class="mb-3 rounded-lg border border-rose-900/40 bg-rose-950/30 p-3 text-sm text-rose-200">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="/dashboard/chat/{{ $selectedId }}/reply" class="flex gap-3">
                            @csrf
                            <input
                                type="text"
                                name="pesan"
                                value="{{ old('pesan') }}"
                                placeholder="Tulis balasan..."
                                class="flex-1 bg-gray-900 border border-gray-800 rounded-lg px-4 py-2 text-white placeholder-gray-600 focus:outline-none focus:border-blue-600 transition-colors"
                                autocomplete="off"
                            />
                            <button
                                type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                            >
                                Kirim
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </main>
    </div>
</body>
</html>
