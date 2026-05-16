import { useEffect, useMemo, useRef, useState } from 'react';
import toast, { Toaster } from 'react-hot-toast';
import { MessageCircle, Send, X } from 'lucide-react';

type ChatMessage = {
	id_chat: number;
	id_pelanggan: number;
	pengirim: string;
	pesan: string;
	dibaca_admin: boolean;
	createdAt: string;
};

function formatTime(iso: string) {
	const d = new Date(iso);
	if (Number.isNaN(d.getTime())) return '';
	return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

export default function FloatingChat() {
	const [open, setOpen] = useState(false);
	const [loading, setLoading] = useState(false);
	const [sending, setSending] = useState(false);
	const [text, setText] = useState('');
	const [messages, setMessages] = useState<ChatMessage[]>([]);
	const [hideChatUi, setHideChatUi] = useState(false);

	const bottomRef = useRef<HTMLDivElement | null>(null);

	const auth = useMemo(() => {
		if (typeof window === 'undefined') return { token: '', id: 0 };
		const token = window.localStorage.getItem('token_pelanggan') ?? '';
		const id = Number(window.localStorage.getItem('id_pelanggan') ?? '0');
		return { token, id: Number.isInteger(id) ? id : 0 };
	}, []);

	useEffect(() => {
		const path = window.location.pathname;
		setHideChatUi(path === '/login' || path === '/register');
	}, []);

	useEffect(() => {
		if (!open) return;
		setTimeout(() => bottomRef.current?.scrollIntoView({ block: 'end' }), 0);
	}, [open, messages.length]);

	const loadHistory = async () => {
		if (!auth.token || !auth.id) return;
		setLoading(true);
		try {
			const res = await fetch(`/api/chat/history?id_pelanggan=${auth.id}`, {
				headers: { Authorization: `Bearer ${auth.token}` }
			});
			const json = await res.json().catch(() => null);
			if (!res.ok) throw new Error(json?.message ?? 'Gagal memuat chat');
			const items = Array.isArray(json?.data) ? (json.data as ChatMessage[]) : [];
			setMessages(items);
		} catch (e: unknown) {
			const msg = e instanceof Error ? e.message : 'Gagal memuat chat';
			toast.error(msg);
		} finally {
			setLoading(false);
		}
	};

	const openOrWarn = async () => {
		if (!auth.token || !auth.id) {
			toast.error('Silakan login terlebih dahulu untuk chat.');
			return;
		}
		setOpen(true);
		if (!messages.length) await loadHistory();
	};

	const send = async () => {
		const payload = text.trim();
		if (!payload) return;
		if (!auth.token || !auth.id) {
			toast.error('Silakan login terlebih dahulu.');
			return;
		}
		setSending(true);
		try {
			const res = await fetch('/api/chat/send', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					Authorization: `Bearer ${auth.token}`
				},
				body: JSON.stringify({ id_pelanggan: auth.id, pesan: payload })
			});
			const json = await res.json().catch(() => null);
			if (!res.ok) throw new Error(json?.message ?? 'Gagal mengirim pesan');
			const created = json?.data as ChatMessage | undefined;
			if (created) setMessages((prev) => [...prev, created]);
			setText('');
		} catch (e: unknown) {
			const msg = e instanceof Error ? e.message : 'Gagal mengirim pesan';
			toast.error(msg);
		} finally {
			setSending(false);
		}
	};

	return (
		<>
			<Toaster position="top-right" />

			{hideChatUi ? null : (
				<>
					<button
						type="button"
						onClick={openOrWarn}
						className="fixed bottom-5 right-5 z-[9999] inline-flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/30 hover:brightness-95"
						aria-label="Chat"
					>
						<MessageCircle className="h-5 w-5" />
					</button>

					<div
						className={[
							'fixed inset-0 z-[9999] md:hidden',
							open ? 'pointer-events-auto' : 'pointer-events-none'
						].join(' ')}
					>
						<button
							type="button"
							aria-label="Tutup chat"
							className={[
								'absolute inset-0 bg-slate-900/35 transition-opacity duration-200',
								open ? 'opacity-100' : 'opacity-0'
							].join(' ')}
							onClick={() => setOpen(false)}
							tabIndex={open ? 0 : -1}
						/>
					</div>

					<div
						className={[
							'fixed z-[9999] w-full md:w-[320px]',
							'right-0 bottom-0 left-0 top-0 md:left-auto md:top-auto md:right-5 md:bottom-20',
							open ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0',
							'transition-opacity duration-200'
						].join(' ')}
						role="dialog"
						aria-modal="true"
						aria-label="Chat"
					>
						<div className="flex h-full flex-col overflow-hidden border border-slate-200 bg-white/95 shadow-2xl backdrop-blur md:h-[500px] md:rounded-3xl">
							<div className="flex items-center justify-between gap-3 bg-gradient-to-br from-blue-600 to-indigo-600 px-4 py-3 text-white">
								<div className="min-w-0">
									<div className="text-sm font-semibold leading-5">Chat Customer</div>
									<div className="text-xs opacity-90">Kami siap bantu</div>
								</div>
								<button
									type="button"
									onClick={() => setOpen(false)}
									className="grid h-9 w-9 place-items-center rounded-2xl bg-white/10 hover:bg-white/15"
									aria-label="Tutup"
								>
									<X className="h-5 w-5" />
								</button>
							</div>

							<div className="flex-1 overflow-y-auto px-4 py-3">
								{loading ? (
									<div className="text-sm text-slate-600">Memuat...</div>
								) : messages.length ? (
									<div className="flex flex-col gap-2">
										{messages.map((m) => {
											const mine = m.pengirim === 'pelanggan';
											return (
												<div key={m.id_chat} className={mine ? 'flex justify-end' : 'flex justify-start'}>
													<div
														className={[
															'max-w-[85%] rounded-2xl px-3 py-2 text-sm shadow-sm',
															mine ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-900'
														].join(' ')}
													>
														<div className="whitespace-pre-wrap leading-6">{m.pesan}</div>
														<div
															className={
																mine ? 'mt-1 text-[11px] text-white/80' : 'mt-1 text-[11px] text-slate-500'
															}
														>
															{formatTime(m.createdAt)}
														</div>
													</div>
												</div>
											);
										})}
										<div ref={bottomRef} />
									</div>
								) : (
									<div className="text-sm text-slate-600">Mulai chat dengan mengetik pesan.</div>
								)}
							</div>

							<div className="border-t border-slate-200 bg-white/90 px-3 py-3">
								<div className="flex items-center gap-2">
									<input
										value={text}
										onChange={(e) => setText(e.target.value)}
										onKeyDown={(e) => {
											if (e.key === 'Enter' && !e.shiftKey) {
												e.preventDefault();
												if (!sending) void send();
											}
										}}
										placeholder="Tulis pesan..."
										className="h-11 flex-1 rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none focus:border-blue-500"
									/>
									<button
										type="button"
										onClick={() => void send()}
										disabled={sending}
										className="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
										aria-label="Kirim"
									>
										<Send className="h-5 w-5" />
									</button>
								</div>
							</div>
						</div>
					</div>
				</>
			)}
		</>
	);
}
