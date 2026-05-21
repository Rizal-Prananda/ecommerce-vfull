@extends('layouts.app')

@section('title', 'Chat Pelanggan - CMS')

@section('content')
@php
$router = app('router');
$hasShowRoute = $router->has('dashboard.chat.show');
$hasSendRoute = $router->has('dashboard.chat.send');

$chats = $chats ?? ($customers ?? collect());
$activeChat = $activeChat ?? null;

$activeChatId = (int) (($activeChat['id'] ?? null) ?? ($activeChat?->id ?? null) ?? ($selectedId ?? 0));
$activeCustomer = ($activeChat['customer'] ?? null) ?? ($activeChat?->customer ?? null) ?? ($selectedCustomer ?? null);
$activeMessages = ($activeChat['messages'] ?? null) ?? ($activeChat?->messages ?? null) ?? ($messages ?? collect());

if (!($activeMessages instanceof \Illuminate\Support\Collection)) {
$activeMessages = collect($activeMessages);
}

$activeCustomerName = 'Pelanggan';
if (is_object($activeCustomer)) {
$n1 = (string) ($activeCustomer->namalengkap_pelanggan ?? '');
$n2 = (string) ($activeCustomer->name ?? '');
if (trim($n1) !== '') $activeCustomerName = trim($n1);
elseif (trim($n2) !== '') $activeCustomerName = trim($n2);
} elseif (is_array($activeCustomer)) {
$n1 = (string) ($activeCustomer['namalengkap_pelanggan'] ?? '');
$n2 = (string) ($activeCustomer['name'] ?? '');
if (trim($n1) !== '') $activeCustomerName = trim($n1);
elseif (trim($n2) !== '') $activeCustomerName = trim($n2);
}

$lastMessageId = 0;
try {
$lastMessageId = (int) ($activeMessages->max('id_chat') ?? 0);
} catch (\Throwable $e) {
$lastMessageId = 0;
}

$chatsCollection = $chats instanceof \Illuminate\Support\Collection ? $chats : collect($chats);
$sidebarItems = $chatsCollection->map(function ($chat) use ($activeChatId, $hasShowRoute) {
$chatId = (int) ($chat->id_pelanggan ?? $chat->id ?? 0);
$name = (string) ($chat->namalengkap_pelanggan ?? $chat->name ?? 'Pelanggan');
$email = (string) ($chat->email_pelanggan ?? $chat->email ?? '');
$phone = (string) ($chat->notelepon_pelanggan ?? $chat->phone ?? '');
$unread = (int) ($chat->unread_count ?? $chat->unread ?? 0);
$lastAt = $chat->last_message_at ?? $chat->lastMessageAt ?? null;
$lastHuman = '';
if (!empty($lastAt)) {
try {
$lastHuman = \Illuminate\Support\Carbon::parse($lastAt)->diffForHumans();
} catch (\Throwable $e) {
$lastHuman = '';
}
}
$searchText = mb_strtolower(trim($name . ' ' . $email . ' ' . $phone));
$chatUrl = $hasShowRoute ? route('dashboard.chat.show', $chatId) : url("/dashboard/chat/{$chatId}");

return [
'id' => $chatId,
'name' => $name,
'email' => $email,
'phone' => $phone,
'unread' => $unread,
'last_human' => $lastHuman,
'last_message_at' => $lastAt,
'search_text' => $searchText,
'url' => $chatUrl,
'active' => $activeChatId > 0 && $chatId === (int) $activeChatId,
];
})->values()->all();
@endphp

<div class="px-4 py-4">
    <script>
        window.cmsChatSidebar = function() {
            return {
                q: '',
                activeId: @json($activeChatId),
                items: @json($sidebarItems),
                pollUrl: @json(url('/dashboard/chat/poll')),
                timer: null,
                running: false,
                start() {
                    if (this.running || !this.pollUrl) return;
                    this.running = true;

                    const poll = async () => {
                        if (document.hidden) return;
                        try {
                            const res = await fetch(this.pollUrl, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const json = await res.json().catch(() => null);
                            if (Array.isArray(json?.data)) this.items = json.data;
                        } catch (e) {}
                    };

                    poll();
                    this.timer = setInterval(poll, 3000);
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) poll();
                    });
                    window.addEventListener('beforeunload', () => this.stop());
                },
                stop() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                    this.running = false;
                },
            };
        };

        window.cmsChatRoom = function() {
            return {
                searchOpen: false,
                searchQuery: '',
                searchCount: 0,
                searchHits: 0,
                searchIndex: -1,
                matchEls: [],
                live: {
                    activeId: @json($activeChatId),
                    lastId: @json($lastMessageId),
                    pollUrl: @json(url("/dashboard/chat/{$activeChatId}/poll")),
                    timer: null,
                    running: false,
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        const sc = this.$refs.messagesScroll;
                        if (sc) sc.scrollTop = sc.scrollHeight;
                    });
                },
                appendMessage(msg) {
                    const wrap = this.$refs.messagesScroll?.querySelector('.space-y-3');
                    if (!wrap) return;

                    const isAdmin = String(msg?.pengirim || '') === 'admin';
                    const senderLabel = isAdmin ? (String(msg?.pengirim_nama || 'Admin') || 'Admin') : @json($activeCustomerName);
                    const time = msg?.createdAt ? String(msg.createdAt).slice(11, 16) : '';
                    const pesan = String(msg?.pesan || '');

                    const outer = document.createElement('div');
                    outer.className = isAdmin ? 'flex justify-end' : 'flex justify-start';

                    const bubble = document.createElement('div');
                    bubble.className = (isAdmin ? 'bg-black text-white' : 'bg-gray-100 text-gray-900') + ' max-w-[36rem] rounded-2xl px-4 py-3';

                    const sender = document.createElement('div');
                    sender.className = 'mb-1 text-xs font-semibold opacity-80';
                    sender.textContent = senderLabel;

                    const body = document.createElement('div');
                    body.className = 'whitespace-pre-line text-sm leading-relaxed';
                    body.setAttribute('data-message', pesan);
                    body.textContent = pesan;

                    bubble.appendChild(sender);
                    bubble.appendChild(body);

                    const attachmentPath = String(msg?.attachment_path || '');
                    const attachmentMime = String(msg?.attachment_mime || '');
                    const attachmentName = String(msg?.attachment_original_name || 'Lampiran');
                    if (attachmentPath) {
                        const attachmentUrl = @json(asset('storage')) + '/' + attachmentPath.replace(/^\/+/, '');
                        const isImage = attachmentMime ? attachmentMime.startsWith('image/') : false;

                        const attachmentWrap = document.createElement('div');
                        attachmentWrap.className = 'mt-3';
                        if (isImage) {
                            const a = document.createElement('a');
                            a.href = attachmentUrl;
                            a.target = '_blank';
                            a.rel = 'noreferrer';
                            a.className = 'block';
                            const img = document.createElement('img');
                            img.src = attachmentUrl;
                            img.alt = attachmentName;
                            img.className = 'max-h-64 w-full rounded-xl object-cover';
                            a.appendChild(img);
                            attachmentWrap.appendChild(a);
                        } else {
                            const a = document.createElement('a');
                            a.href = attachmentUrl;
                            a.target = '_blank';
                            a.rel = 'noreferrer';
                            a.className = (isAdmin ? 'text-white' : 'text-gray-900') + ' inline-flex items-center gap-2 rounded-xl border border-black/10 bg-white/60 px-3 py-2 text-sm font-semibold';
                            a.textContent = attachmentName;
                            attachmentWrap.appendChild(a);
                        }
                        bubble.appendChild(attachmentWrap);
                    }

                    if (time) {
                        const t = document.createElement('div');
                        t.className = 'mt-2 text-right text-[11px] opacity-75';
                        t.textContent = time;
                        bubble.appendChild(t);
                    }

                    outer.appendChild(bubble);
                    wrap.appendChild(outer);
                },
                startLive() {
                    if (!this.live.activeId || !this.live.pollUrl || this.live.running) return;
                    this.live.running = true;
                    const poll = async () => {
                        try {
                            const url = `${this.live.pollUrl}?after_id=${encodeURIComponent(String(this.live.lastId || 0))}`;
                            const res = await fetch(url, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const json = await res.json().catch(() => null);
                            const items = Array.isArray(json?.data) ? json.data : [];
                            if (!items.length) return;
                            for (const m of items) {
                                const id = Number(m?.id_chat || 0);
                                if (Number.isInteger(id) && id > (this.live.lastId || 0)) this.live.lastId = id;
                                this.appendMessage(m);
                            }
                            this.scrollToBottom();
                        } catch (e) {}
                    };
                    poll();
                    this.live.timer = setInterval(poll, 2500);
                    window.addEventListener('beforeunload', () => this.stopLive());
                },
                stopLive() {
                    if (this.live.timer) {
                        clearInterval(this.live.timer);
                        this.live.timer = null;
                    }
                    this.live.running = false;
                },
                escapeRegExp(s) {
                    return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                },
                escapeHtml(s) {
                    return String(s)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                },
                resetHighlights() {
                    this.searchCount = 0;
                    this.searchHits = 0;
                    this.searchIndex = -1;
                    this.matchEls = [];
                    const nodes = this.$refs.messagesScroll ? this.$refs.messagesScroll.querySelectorAll('[data-message]') : [];
                    nodes.forEach((el) => {
                        const raw = el.getAttribute('data-message') || '';
                        el.textContent = raw;
                    });
                },
                scrollToMatch(i) {
                    if (!this.matchEls || this.matchEls.length === 0) return;
                    const idx = Math.max(0, Math.min(i, this.matchEls.length - 1));
                    const el = this.matchEls[idx];
                    if (!el) return;
                    el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    el.classList.add('ring-2', 'ring-black/20', 'rounded');
                    setTimeout(() => {
                        try {
                            el.classList.remove('ring-2', 'ring-black/20', 'rounded');
                        } catch (e) {}
                    }, 450);
                },
                nextMatch() {
                    if (this.searchHits <= 0) return;
                    this.searchIndex = (this.searchIndex + 1) % this.searchHits;
                    this.scrollToMatch(this.searchIndex);
                },
                prevMatch() {
                    if (this.searchHits <= 0) return;
                    this.searchIndex = (this.searchIndex - 1 + this.searchHits) % this.searchHits;
                    this.scrollToMatch(this.searchIndex);
                },
                applySearch() {
                    const q = String(this.searchQuery || '').trim();
                    if (q.length < 2) {
                        this.resetHighlights();
                        return;
                    }

                    const nodes = this.$refs.messagesScroll ? this.$refs.messagesScroll.querySelectorAll('[data-message]') : [];
                    const rx = new RegExp(this.escapeRegExp(q), 'gi');
                    let total = 0;
                    const matchesEls = [];

                    nodes.forEach((el) => {
                        const raw = el.getAttribute('data-message') || '';
                        rx.lastIndex = 0;
                        const matches = raw.match(rx);
                        if (!matches) {
                            el.textContent = raw;
                            return;
                        }

                        total += matches.length;
                        matchesEls.push(el);
                        el.innerHTML = this.escapeHtml(raw).replace(rx, (m) => `<mark class="bg-yellow-300 text-black rounded px-0.5">${this.escapeHtml(m)}</mark>`);
                    });

                    this.searchCount = total;
                    this.matchEls = matchesEls;
                    this.searchHits = matchesEls.length;
                    this.searchIndex = this.searchHits > 0 ? 0 : -1;

                    if (this.searchHits > 0) {
                        this.$nextTick(() => this.scrollToMatch(0));
                    }
                },
                openSearch() {
                    this.searchOpen = true;
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) this.$refs.searchInput.focus();
                    });
                },
                closeSearch() {
                    this.searchOpen = false;
                    this.searchQuery = '';
                    this.resetHighlights();
                },
                clearSearch() {
                    this.searchQuery = '';
                    this.resetHighlights();
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) this.$refs.searchInput.focus();
                    });
                },
            };
        };
    </script>
    <div class="grid h-[calc(100vh-4rem)] grid-cols-12 gap-3" x-data="window.cmsChatSidebar()" x-init="start()">
        <section class="col-span-12 flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white lg:col-span-3">
            <div class="border-b border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-semibold text-gray-900">Pelanggan</div>
                    <div class="text-xs text-gray-500" x-text="items.length"></div>
                </div>
                <div class="mt-3">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />
                                <path d="M21 21l-4.3-4.3" />
                            </svg>
                        </div>
                        <input
                            x-model="q"
                            type="text"
                            placeholder="Cari pelanggan..."
                            class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-black focus:bg-white focus:outline-none focus:ring-2 focus:ring-black/10"
                            autocomplete="off" />
                    </div>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-2">
                <div class="space-y-1">
                    <template x-for="item in items" :key="item.id">
                        <a
                            :href="item.url"
                            class="block rounded-xl border px-3 py-3 transition-colors"
                            :class="Number(item.id) === Number(activeId) ? 'border-black bg-gray-50' : 'border-transparent hover:bg-gray-50'"
                            x-show="q.trim() === '' || String(item.search_text || '').includes(q.trim().toLowerCase())">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-gray-900" x-text="item.name"></div>
                                    <div class="truncate text-xs text-gray-500" x-text="item.email"></div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div x-show="String(item.last_human || '') !== ''" class="text-[11px] font-medium text-gray-500" x-text="item.last_human"></div>
                                    <div x-show="Number(item.unread || 0) > 0" class="mt-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-black px-2 text-[11px] font-semibold text-white" x-text="item.unread"></div>
                                </div>
                            </div>
                        </a>
                    </template>
                    <div x-show="items.length === 0" class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                        Belum ada pelanggan.
                    </div>
                </div>
            </div>
        </section>

        <section
            class="col-span-12 flex min-h-0 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white lg:col-span-6"
            x-data="window.cmsChatRoom()"
            x-init="scrollToBottom(); startLive()">
            <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-gray-900">
                        {{ (string) ($activeCustomer?->namalengkap_pelanggan ?? $activeCustomer?->name ?? 'Chat Pelanggan') }}
                    </div>
                    <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-500">
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span>Online</span>
                        @if (!empty($activeCustomer?->email_pelanggan ?? $activeCustomer?->email ?? null))
                        <span class="truncate">• {{ (string) ($activeCustomer?->email_pelanggan ?? $activeCustomer?->email ?? '') }}</span>
                        @endif
                    </div>
                </div>
                <div class="shrink-0">
                    <div class="relative flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-900 hover:bg-gray-50"
                            @click="searchOpen ? closeSearch() : openSearch()"
                            :aria-expanded="searchOpen"
                            aria-label="Cari di chat">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7" />
                                <path d="M21 21l-4.3-4.3" />
                            </svg>
                        </button>

                        <div x-show="searchOpen" x-cloak @keydown.escape.window="closeSearch()" class="absolute right-0 top-full z-30 mt-2 w-72 rounded-lg border border-gray-200 bg-white p-3">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <input
                                        x-ref="searchInput"
                                        x-model="searchQuery"
                                        @input="applySearch()"
                                        @keydown.enter.prevent="$event.shiftKey ? prevMatch() : nextMatch()"
                                        type="text"
                                        placeholder="Cari pesan..."
                                        class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-9 pr-9 text-sm text-gray-900 placeholder-gray-400 focus:border-black focus:outline-none focus:ring-2 focus:ring-black/10"
                                        autocomplete="off" />
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="7" />
                                            <path d="M21 21l-4.3-4.3" />
                                        </svg>
                                    </div>
                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 inline-flex w-9 items-center justify-center text-gray-500 hover:text-black"
                                        @click="clearSearch()"
                                        aria-label="Clear">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 6 6 18" />
                                            <path d="M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <div x-show="searchQuery.trim().length >= 2" x-cloak class="flex items-center gap-2">
                                    <span>
                                        Hasil <span class="font-semibold text-black" x-text="searchHits"></span>
                                    </span>
                                    <span class="text-gray-300">•</span>
                                    <span>
                                        <span class="font-semibold text-black" x-text="searchHits > 0 ? (searchIndex + 1) : 0"></span>/<span x-text="searchHits"></span>
                                    </span>
                                    <div class="ml-1 flex items-center gap-1">
                                        <button
                                            type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-900 hover:bg-gray-50 disabled:opacity-40"
                                            @click="prevMatch()"
                                            :disabled="searchHits <= 0"
                                            aria-label="Sebelumnya">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="m18 15-6-6-6 6" />
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-900 hover:bg-gray-50 disabled:opacity-40"
                                            @click="nextMatch()"
                                            :disabled="searchHits <= 0"
                                            aria-label="Berikutnya">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="m6 9 6 6 6-6" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div x-show="searchQuery.trim().length < 2" x-cloak>Ketik minimal 2 huruf</div>
                                <button type="button" class="text-xs font-medium text-black" @click="closeSearch()">Tutup</button>
                            </div>
                        </div>

                        <div class="text-xs font-medium text-gray-500">
                            @if ($activeChatId > 0)
                            #{{ $activeChatId }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @php
            $hasAnyChat = method_exists($chats, 'isNotEmpty') ? $chats->isNotEmpty() : !empty($chats);
            @endphp

            @if (!$hasAnyChat)
            <div class="flex min-h-0 flex-1 items-center justify-center p-6">
                <div class="mx-auto max-w-sm text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" />
                        <path d="M7.5 9.5h9" />
                        <path d="M7.5 13h6" />
                    </svg>
                    <div class="mt-4 text-base font-semibold text-black">Belum ada pelanggan</div>
                    <div class="mt-1 text-sm text-gray-500">Chat akan muncul disini saat ada pelanggan yang menghubungi</div>
                </div>
            </div>
            @else
            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4" x-ref="messagesScroll">
                <div class="space-y-3">
                    @forelse ($activeMessages as $msg)
                    @php
                    $isAdmin = (string) ($msg->pengirim ?? '') === 'admin';
                    $msgTime = '';
                    $senderLabel = '';
                    if ($isAdmin) {
                    $senderLabel = (string) ($msg->pengirim_nama ?? 'Admin');
                    } else {
                    $senderLabel = (string) ($activeCustomer?->namalengkap_pelanggan ?? 'Pelanggan');
                    }
                    if (!empty($msg->createdAt ?? null)) {
                    $msgTime = \Illuminate\Support\Carbon::parse($msg->createdAt)->format('H:i');
                    } elseif (!empty($msg->created_at ?? null)) {
                    $msgTime = \Illuminate\Support\Carbon::parse($msg->created_at)->format('H:i');
                    }
                    @endphp

                    <div class="{{ $isAdmin ? 'flex justify-end' : 'flex justify-start' }}">
                        <div class="{{ $isAdmin ? 'bg-black text-white' : 'bg-gray-100 text-gray-900' }} max-w-[36rem] rounded-2xl px-4 py-3">
                            <div class="mb-1 text-xs font-semibold opacity-80">{{ $senderLabel }}</div>
                            <div class="whitespace-pre-line text-sm leading-relaxed" data-message="{{ (string) ($msg->pesan ?? '') }}">{{ $msg->pesan ?? '' }}</div>
                            @php
                            $attachmentPath = (string) ($msg->attachment_path ?? '');
                            $attachmentMime = (string) ($msg->attachment_mime ?? '');
                            $attachmentName = (string) ($msg->attachment_original_name ?? 'Lampiran');
                            $attachmentUrl = $attachmentPath !== '' ? asset('storage/' . ltrim($attachmentPath, '/')) : '';
                            $isImage = $attachmentUrl !== '' && ($attachmentMime !== '' ? str_starts_with($attachmentMime, 'image/') : false);
                            @endphp
                            @if ($attachmentUrl !== '')
                            <div class="mt-3">
                                @if ($isImage)
                                <a href="{{ $attachmentUrl }}" target="_blank" rel="noreferrer" class="block">
                                    <img src="{{ $attachmentUrl }}" alt="{{ $attachmentName }}" class="max-h-64 w-full rounded-xl object-cover" />
                                </a>
                                @else
                                <a href="{{ $attachmentUrl }}" target="_blank" rel="noreferrer" class="{{ $isAdmin ? 'text-white' : 'text-gray-900' }} inline-flex items-center gap-2 rounded-xl border border-black/10 bg-white/60 px-3 py-2 text-sm font-semibold">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                    </svg>
                                    <span class="truncate max-w-[18rem]">{{ $attachmentName }}</span>
                                </a>
                                @endif
                            </div>
                            @endif
                            @if ($msgTime !== '')
                            <div class="mt-2 text-right text-[11px] opacity-75">{{ $msgTime }}</div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                        Belum ada chat dari pelanggan ini.
                    </div>
                    @endforelse
                </div>
                <div x-ref="bottom"></div>
            </div>

            <div class="border-t border-gray-200 p-3"
                x-data="{
                    fileName: '',
                    fileSize: '',
                    fileUrl: '',
                    isImage: false,
                    uploading: false,
                    onPickFile(e) {
                        const f = e?.target?.files?.[0] ?? null;
                        if (!f) {
                            this.clearFile();
                            return;
                        }
                        this.fileName = String(f.name || 'Lampiran');
                        this.isImage = String(f.type || '').startsWith('image/');
                        this.fileUrl = this.isImage ? URL.createObjectURL(f) : '';
                        const bytes = Number(f.size || 0);
                        const kb = bytes / 1024;
                        const mb = kb / 1024;
                        this.fileSize = mb >= 1 ? `${mb.toFixed(1)} MB` : `${Math.max(1, Math.round(kb))} KB`;
                    },
                    clearFile() {
                        if (this.fileUrl) {
                            try { URL.revokeObjectURL(this.fileUrl); } catch (e) {}
                        }
                        this.fileName = '';
                        this.fileSize = '';
                        this.fileUrl = '';
                        this.isImage = false;
                        if (this.$refs.file) this.$refs.file.value = '';
                    },
                }">
                @if ($errors->any())
                <div class="mb-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @php
                $sendUrl = $hasSendRoute ? route('dashboard.chat.send', $activeChatId) : url("/dashboard/chat/{$activeChatId}/reply");
                @endphp

                <form method="POST" action="{{ $sendUrl }}" class="space-y-2" enctype="multipart/form-data" @submit="uploading = true">
                    @csrf

                    <div
                        x-show="fileName !== ''"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-120"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center overflow-hidden rounded-lg bg-white ring-1 ring-black/5">
                                <template x-if="isImage && fileUrl">
                                    <img :src="fileUrl" alt="" class="h-10 w-10 object-cover" />
                                </template>
                                <template x-if="!(isImage && fileUrl)">
                                    <svg class="h-5 w-5 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                    </svg>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-900" x-text="fileName"></div>
                                <div class="text-xs text-gray-500" x-text="fileSize"></div>
                            </div>
                        </div>
                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-700 ring-1 ring-black/5 hover:bg-gray-50" @click="clearFile()">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18" />
                                <path d="M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                name="pesan"
                                value="{{ old('pesan') }}"
                                placeholder="Tulis pesan..."
                                class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 text-sm text-gray-900 placeholder-gray-400 focus:border-black focus:bg-white focus:outline-none focus:ring-2 focus:ring-black/10"
                                autocomplete="off" />
                        </div>
                        <label class="relative inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-900 hover:bg-gray-50">
                            <input x-ref="file" type="file" name="attachment" class="hidden" accept="image/*,.pdf" @change="onPickFile($event)" />
                            <span class="absolute -right-1 -top-1 h-3 w-3 rounded-full bg-black ring-2 ring-white" x-show="fileName !== ''" x-cloak></span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                            </svg>
                        </label>
                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-black px-4 text-sm font-semibold text-white hover:bg-gray-900 disabled:opacity-50"
                            @disabled($activeChatId <=0)>
                            <svg x-show="uploading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                            </svg>
                            <span x-text="uploading ? 'Mengirim...' : 'Kirim'"></span>
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </section>

        <aside class="col-span-3 hidden min-h-0 overflow-hidden rounded-2xl border border-gray-200 bg-white lg:block">
            <div class="border-b border-gray-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Detail Pelanggan</div>
                <div class="mt-1 text-xs text-gray-500">Info singkat percakapan</div>
            </div>

            <div class="min-h-0 overflow-y-auto p-4">
                @php
                $detailChat = $activeChat ?? null;
                $detailUser = ($detailChat?->user ?? null) ?: ($activeCustomer ?? null);

                $detailName = (string) ($detailUser?->name ?? $detailUser?->namalengkap_pelanggan ?? 'Pelanggan');
                $detailEmail = (string) ($detailUser?->email ?? $detailUser?->email_pelanggan ?? '');
                $detailPhone = (string) ($detailUser?->phone ?? $detailUser?->notelepon_pelanggan ?? '');

                $detailUserId = (int) (($detailChat?->id ?? null) ?? ($detailUser?->id ?? null) ?? ($activeChatId ?? 0));
                $ordersCount = (int) (($detailChat?->orders_count ?? null) ?? ($detailUser?->orders_count ?? null) ?? 0);

                $createdAtValue = $detailUser?->created_at ?? $detailUser?->createdAt ?? null;
                $createdAtHuman = '';
                if (!empty($createdAtValue)) {
                $createdAtHuman = \Illuminate\Support\Carbon::parse($createdAtValue)->locale('id')->translatedFormat('d F Y');
                }

                $lastLoginValue = $detailUser?->last_login_at ?? $detailUser?->lastLoginAt ?? null;
                $lastLoginHuman = '';
                if (!empty($lastLoginValue)) {
                $lastLoginHuman = \Illuminate\Support\Carbon::parse($lastLoginValue)->locale('id')->diffForHumans();
                }

                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($detailName) . '&background=000000&color=ffffff&bold=true&size=160';
                $profileUrl = $router->has('users.edit') && $detailUserId > 0 ? route('users.edit', $detailUserId) : '#';
                @endphp

                @if ($detailUser && $detailUserId > 0)
                <div class="space-y-4" x-data="{ copied: false }">
                    <div class="flex items-center gap-4">
                        <img src="{{ $avatarUrl }}" alt="{{ $detailName }}" class="h-20 w-20 rounded-2xl border border-gray-200 object-cover" />
                        <div class="min-w-0">
                            <div class="truncate text-base font-semibold text-black">{{ $detailName }}</div>
                            <div class="mt-1 inline-flex items-center gap-2 text-xs text-gray-500">
                                <span class="inline-flex h-2 w-2 rounded-full bg-black"></span>
                                Online
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="text-xs text-gray-500">Nama Lengkap</div>
                            <div class="mt-1 text-sm font-medium text-black">{{ $detailName }}</div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-xs text-gray-500">Alamat Email</div>
                                @if ($detailEmail !== '')
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs font-medium text-black hover:bg-gray-50"
                                    @click="
                                                navigator.clipboard.writeText('{{ e($detailEmail) }}').then(() => {
                                                    copied = true;
                                                    setTimeout(() => copied = false, 1200);
                                                })
                                            ">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 9h10v10H9z" />
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                    </svg>
                                    <span x-text="copied ? 'Copied' : 'Copy'"></span>
                                </button>
                                @endif
                            </div>
                            <div class="mt-1 break-all text-sm font-medium text-black">{{ $detailEmail !== '' ? $detailEmail : '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">No Telepon</div>
                            <div class="mt-1 text-sm font-medium text-black">{{ $detailPhone !== '' ? $detailPhone : '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Tanggal Pembuatan Akun</div>
                            <div class="mt-1 text-sm font-medium text-black">{{ $createdAtHuman !== '' ? $createdAtHuman : '-' }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Terakhir Login</div>
                            <div class="mt-1 text-sm font-medium text-black">{{ $lastLoginHuman !== '' ? $lastLoginHuman : '-' }}</div>
                        </div>
                    </div>

                    <div class="grid gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500">Total Order</div>
                            <div class="text-sm font-medium text-black">{{ $ordersCount }}</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500">Customer ID</div>
                            <div class="text-sm font-medium text-black">#{{ $detailUserId }}</div>
                        </div>
                    </div>
                </div>
                @else
                <div class="flex min-h-[18rem] items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 p-6 text-center">
                    <div class="mx-auto max-w-xs">
                        <svg class="mx-auto h-10 w-10 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 21a8 8 0 0 0-16 0" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <div class="mt-3 text-sm font-semibold text-black">Pilih chat untuk lihat detail</div>
                        <div class="mt-1 text-xs text-gray-500">Detail pelanggan akan tampil di panel ini.</div>
                    </div>
                </div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
