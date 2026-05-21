import { useEffect, useRef, useState } from "react";
import toast, { Toaster } from "react-hot-toast";
import { MessageCircle, Paperclip, Send, X } from "lucide-react";

type ChatMessage = {
  id_chat: number;
  id_pelanggan: number;
  pengirim: string;
  pesan: string;
  dibaca_admin: boolean;
  createdAt: string;
  attachment_path?: string | null;
  attachment_mime?: string | null;
  attachment_original_name?: string | null;
};

function formatTime(iso: string) {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return "";
  return d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
}

export default function FloatingChat() {
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [sending, setSending] = useState(false);
  const [text, setText] = useState("");
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [hideChatUi, setHideChatUi] = useState(false);
  const [photo, setPhoto] = useState<File | null>(null);
  const [photoPreviewUrl, setPhotoPreviewUrl] = useState<string>("");

  const CMS_BASE = "http://127.0.0.1:8001";

  const bottomRef = useRef<HTMLDivElement | null>(null);
  const lastIdRef = useRef<number>(0);
  const pollTimerRef = useRef<number | null>(null);
  const fileRef = useRef<HTMLInputElement | null>(null);

  const getAuth = () => {
    if (typeof window === "undefined") return { token: "", id: 0 };
    const token = window.localStorage.getItem("token_pelanggan") ?? "";
    const id = Number(window.localStorage.getItem("id_pelanggan") ?? "0");
    return { token, id: Number.isInteger(id) ? id : 0 };
  };

  useEffect(() => {
    const path = window.location.pathname;
    setHideChatUi(path === "/login" || path === "/register");
  }, []);

  useEffect(() => {
    try {
      const raw = window.sessionStorage.getItem("flash_toast");
      if (!raw) return;
      window.sessionStorage.removeItem("flash_toast");
      const data = JSON.parse(raw) as { type?: string; message?: string };
      const msg = String(data?.message ?? "").trim();
      if (!msg) return;
      if (String(data?.type) === "success") toast.success(msg);
      else toast.error(msg);
    } catch {}
  }, []);

  useEffect(() => {
    if (!open) return;
    setTimeout(() => bottomRef.current?.scrollIntoView({ block: "end" }), 0);
  }, [open, messages.length]);

  useEffect(() => {
    if (!photo) {
      if (photoPreviewUrl) URL.revokeObjectURL(photoPreviewUrl);
      setPhotoPreviewUrl("");
      return;
    }
    const url = URL.createObjectURL(photo);
    setPhotoPreviewUrl(url);
    return () => URL.revokeObjectURL(url);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photo]);

  const loadHistory = async () => {
    const auth = getAuth();
    if (!auth.id) return;
    setLoading(true);
    try {
      const res = await fetch(`${CMS_BASE}/api/chat/history?id_pelanggan=${auth.id}`, {
        headers: {
          ...(auth.token ? { Authorization: `Bearer ${auth.token}` } : {}),
          "X-Pelanggan-Id": String(auth.id),
        },
      });
      const json = await res.json().catch(() => null);
      if (!res.ok) throw new Error(json?.message ?? "Gagal memuat chat");
      const items = Array.isArray(json?.data) ? (json.data as ChatMessage[]) : [];
      setMessages(items);
      lastIdRef.current = items.length ? Number(items[items.length - 1]?.id_chat ?? 0) : 0;
    } catch (e: unknown) {
      const msg = e instanceof Error ? e.message : "Gagal memuat chat";
      toast.error(msg);
    } finally {
      setLoading(false);
    }
  };

  const openOrWarn = async () => {
    const auth = getAuth();
    if (!auth.id) {
      toast.error("Silakan login terlebih dahulu untuk chat.");
      return;
    }
    setOpen(true);
    if (!messages.length) await loadHistory();
  };

  const send = async () => {
    const payload = text.trim();
    if (!payload && !photo) return;
    const auth = getAuth();
    if (!auth.id) {
      toast.error("Silakan login terlebih dahulu.");
      return;
    }
    setSending(true);
    try {
      const res = await (async () => {
        if (photo) {
          const fd = new FormData();
          fd.set("id_pelanggan", String(auth.id));
          fd.set("pesan", payload);
          fd.set("attachment", photo, photo.name);
          return fetch(`${CMS_BASE}/api/chat/send`, {
            method: "POST",
            headers: {
              ...(auth.token ? { Authorization: `Bearer ${auth.token}` } : {}),
              "X-Pelanggan-Id": String(auth.id),
            },
            body: fd,
          });
        }
        return fetch(`${CMS_BASE}/api/chat/send`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            ...(auth.token ? { Authorization: `Bearer ${auth.token}` } : {}),
            "X-Pelanggan-Id": String(auth.id),
          },
          body: JSON.stringify({ id_pelanggan: auth.id, pesan: payload }),
        });
      })();
      const json = await res.json().catch(() => null);
      if (!res.ok) throw new Error(json?.message ?? "Gagal mengirim pesan");
      const created = json?.data as ChatMessage | undefined;
      if (created) {
        lastIdRef.current = Number(created.id_chat ?? lastIdRef.current);
        setMessages((prev) => [...prev, created]);
      }
      setText("");
      setPhoto(null);
      if (fileRef.current) fileRef.current.value = "";
    } catch (e: unknown) {
      const msg = e instanceof Error ? e.message : "Gagal mengirim pesan";
      toast.error(msg);
    } finally {
      setSending(false);
    }
  };

  useEffect(() => {
    if (!open) {
      if (pollTimerRef.current) {
        window.clearInterval(pollTimerRef.current);
        pollTimerRef.current = null;
      }
      return;
    }

    const auth = getAuth();
    if (!auth.id) return;

    const tick = async () => {
      const afterId = Number(lastIdRef.current ?? 0);
      try {
        const res = await fetch(`${CMS_BASE}/api/chat/history?id_pelanggan=${auth.id}&after_id=${afterId}`, {
          headers: {
            ...(auth.token ? { Authorization: `Bearer ${auth.token}` } : {}),
            "X-Pelanggan-Id": String(auth.id),
          },
        });
        const json = await res.json().catch(() => null);
        if (!res.ok) return;
        const items = Array.isArray(json?.data) ? (json.data as ChatMessage[]) : [];
        if (!items.length) return;
        lastIdRef.current = Number(items[items.length - 1]?.id_chat ?? lastIdRef.current);
        setMessages((prev) => [...prev, ...items]);
      } catch {}
    };

    void tick();
    pollTimerRef.current = window.setInterval(() => void tick(), 2500);
    return () => {
      if (pollTimerRef.current) {
        window.clearInterval(pollTimerRef.current);
        pollTimerRef.current = null;
      }
    };
  }, [open]);

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

          <div className={["fixed inset-0 z-[9999] md:hidden", open ? "pointer-events-auto" : "pointer-events-none"].join(" ")}>
            <button
              type="button"
              aria-label="Tutup chat"
              className={["absolute inset-0 bg-slate-900/35 transition-opacity duration-200", open ? "opacity-100" : "opacity-0"].join(" ")}
              onClick={() => setOpen(false)}
              tabIndex={open ? 0 : -1}
            />
          </div>

          <div
            className={[
              "fixed z-[9999] w-full md:w-[320px]",
              "right-0 bottom-0 left-0 top-0 md:left-auto md:top-auto md:right-5 md:bottom-20",
              open ? "pointer-events-auto opacity-100" : "pointer-events-none opacity-0",
              "transition-opacity duration-200",
            ].join(" ")}
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
                <button type="button" onClick={() => setOpen(false)} className="grid h-9 w-9 place-items-center rounded-2xl bg-white/10 hover:bg-white/15" aria-label="Tutup">
                  <X className="h-5 w-5" />
                </button>
              </div>

              <div className="flex-1 overflow-y-auto px-4 py-3">
                {loading ? (
                  <div className="text-sm text-slate-600">Memuat...</div>
                ) : messages.length ? (
                  <div className="flex flex-col gap-2">
                    {messages.map((m) => {
                      const mine = m.pengirim === "pelanggan";
                      const attachmentPath = String(m.attachment_path ?? "");
                      const attachmentMime = String(m.attachment_mime ?? "");
                      const isImage = attachmentPath && attachmentMime.startsWith("image/");
                      const attachmentUrl = attachmentPath
                        ? `${CMS_BASE}/storage/${attachmentPath.replace(/^\/+/, "")}`
                        : "";
                      return (
                        <div key={m.id_chat} className={mine ? "flex justify-end" : "flex justify-start"}>
                          <div className={["max-w-[85%] rounded-2xl px-3 py-2 text-sm shadow-sm", mine ? "bg-blue-600 text-white" : "bg-slate-100 text-slate-900"].join(" ")}>
                            {isImage && attachmentUrl ? (
                              <a href={attachmentUrl} target="_blank" rel="noreferrer" className="block">
                                <img src={attachmentUrl} alt={m.attachment_original_name ?? "Foto"} className="mb-2 max-h-56 w-full rounded-xl object-cover" />
                              </a>
                            ) : null}
                            {m.pesan ? <div className="whitespace-pre-wrap leading-6">{m.pesan}</div> : null}
                            <div className={mine ? "mt-1 text-[11px] text-white/80" : "mt-1 text-[11px] text-slate-500"}>{formatTime(m.createdAt)}</div>
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
                {photo ? (
                  <div className="mb-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2">
                    <div className="flex min-w-0 items-center gap-3">
                      <div className="h-10 w-10 overflow-hidden rounded-xl bg-slate-100">
                        {photoPreviewUrl ? <img src={photoPreviewUrl} alt="Preview" className="h-full w-full object-cover" /> : null}
                      </div>
                      <div className="min-w-0">
                        <div className="truncate text-sm font-semibold text-slate-900">{photo.name}</div>
                        <div className="text-[11px] text-slate-500">Foto</div>
                      </div>
                    </div>
                    <button
                      type="button"
                      onClick={() => {
                        setPhoto(null);
                        if (fileRef.current) fileRef.current.value = "";
                      }}
                      className="grid h-9 w-9 place-items-center rounded-2xl bg-slate-100 text-slate-700 hover:bg-slate-200"
                      aria-label="Hapus foto"
                      disabled={sending}
                    >
                      <X className="h-4 w-4" />
                    </button>
                  </div>
                ) : null}

                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => fileRef.current?.click()}
                    disabled={sending}
                    className="grid h-11 w-11 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                    aria-label="Tambah foto"
                  >
                    <Paperclip className="h-5 w-5" />
                  </button>
                  <input
                    ref={fileRef}
                    type="file"
                    accept="image/*"
                    className="hidden"
                    onChange={(e) => {
                      const f = e.target.files?.[0] ?? null;
                      if (!f) return;
                      if (!String(f.type || "").startsWith("image/")) {
                        toast.error("File harus berupa foto.");
                        e.target.value = "";
                        return;
                      }
                      setPhoto(f);
                    }}
                  />
                  <input
                    value={text}
                    onChange={(e) => setText(e.target.value)}
                    onKeyDown={(e) => {
                      if (e.key === "Enter" && !e.shiftKey) {
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
