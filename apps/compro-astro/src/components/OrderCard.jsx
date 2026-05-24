import React, { useEffect, useMemo, useState } from "react";
import { toast } from "sonner";

function formatRupiah(n) {
  const v = Number(n ?? 0);
  const safe = Number.isFinite(v) ? Math.max(0, Math.trunc(v)) : 0;
  return `Rp ${safe.toLocaleString("id-ID")}`;
}

function statusBadgeClass(status) {
  const s = String(status || "").toUpperCase();
  if (s === "UNPAID") return "border-amber-200 bg-amber-50 text-amber-700";
  if (s === "PAID") return "border-emerald-200 bg-emerald-50 text-emerald-700";
  if (s === "CANCELLED") return "border-red-200 bg-red-50 text-red-700";
  return "border-sky-200 bg-sky-50 text-sky-700";
}

function statusLabel(status) {
  const s = String(status || "").toUpperCase();
  if (s === "UNPAID") return "UNPAID";
  if (s === "PAID") return "PAID";
  if (s === "CANCELLED") return "CANCELLED";
  return "SELESAI";
}

function SecondaryButton({ children, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 active:scale-95"
    >
      {children}
    </button>
  );
}

function PrimaryBlueButton({ children, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="inline-flex h-10 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700 active:scale-95"
    >
      {children}
    </button>
  );
}

function PrimaryDarkButton({ children, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="inline-flex h-10 items-center justify-center rounded-xl bg-zinc-900 px-4 text-sm font-semibold text-white transition hover:bg-zinc-800 active:scale-95"
    >
      {children}
    </button>
  );
}

export default function OrderCard({ order, onOrderUpdate }) {
  const status = useMemo(() => statusLabel(order?.status), [order?.status]);
  const badgeClass = useMemo(() => statusBadgeClass(status), [status]);
  const [expandedOrderId, setExpandedOrderId] = useState("");
  const [showAllItems, setShowAllItems] = useState(false);
  const [cancelOpen, setCancelOpen] = useState(false);
  const [cancelLoading, setCancelLoading] = useState(false);

  useEffect(() => {
    const read = () => {
      try {
        const url = new URL(window.location.href);
        return String(url.searchParams.get("order") || "").trim();
      } catch {
        return "";
      }
    };

    const onPop = () => setExpandedOrderId(read());
    onPop();
    window.addEventListener("popstate", onPop);
    return () => window.removeEventListener("popstate", onPop);
  }, []);

  const handleLihatInvoice = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.open(`/invoice/${id}`, "_blank");
  };

  const handleCetakStruk = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.open(`/struk/${id}?print=true`, "_blank");
  };

  const handlePay = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.location.assign(`/pembayaran/${encodeURIComponent(id)}`);
  };

  const handleDetail = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    try {
      const url = new URL(window.location.href);
      url.searchParams.set("menu", "pesanan");
      const current = String(url.searchParams.get("order") || "").trim();
      if (current === id) {
        url.searchParams.delete("order");
      } else {
        url.searchParams.set("order", id);
      }
      window.history.pushState({}, "", url.toString());
      window.dispatchEvent(new PopStateEvent("popstate"));
    } catch {
      window.location.assign(`/PesananSaya?menu=pesanan&order=${encodeURIComponent(id)}`);
    }
  };

  const handleCancel = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    setCancelOpen(true);
  };

  const handleBuyAgain = () => {
    window.location.assign("/marketplace");
  };

  const handleReview = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.alert(`Beri ulasan untuk ${id}`);
  };

  const confirmCancel = async () => {
    const id = String(order?.id ?? "").trim();
    if (!id || cancelLoading) return;
    setCancelLoading(true);
    try {
      const res = await fetch(`/api/order/${encodeURIComponent(id)}/cancel`, {
        method: "POST",
        headers: { Accept: "application/json" },
      });
      const json = await res.json().catch(() => null);
      if (!res.ok || !json?.success) {
        throw new Error("cancel_failed");
      }
      setCancelOpen(false);
      toast.success("Pesanan berhasil dibatalkan");
      try {
        window.localStorage.removeItem("cart_items");
        window.dispatchEvent(new CustomEvent("cart:updated", { detail: { count: 0 } }));
      } catch {}
      try {
        const token = String(window.localStorage.getItem("token_pelanggan") || "").trim();
        const pelangganId = String(window.localStorage.getItem("id_pelanggan") || "").trim();
        await fetch("/cms/api/cart/sync", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(pelangganId ? { "X-Pelanggan-Id": pelangganId } : {}),
          },
          body: JSON.stringify({ items: [] }),
        }).catch(() => null);
      } catch {}
      if (typeof onOrderUpdate === "function") onOrderUpdate();
    } catch {
      toast.error("Gagal membatalkan pesanan");
    } finally {
      setCancelLoading(false);
    }
  };

  const actions = useMemo(() => {
    if (status === "UNPAID") {
      return {
        primary: { label: "Bayar Sekarang", onClick: handlePay, tone: "blue" },
        secondary: [
          { label: "Lihat Invoice", onClick: handleLihatInvoice },
          { label: "Batalkan", onClick: handleCancel },
        ],
      };
    }
    if (status === "PAID") {
      return {
        primary: { label: "Lihat Detail", onClick: handleDetail, tone: "secondary" },
        secondary: [
          { label: "Lihat Invoice", onClick: handleLihatInvoice },
          { label: "Cetak Struk", onClick: handleCetakStruk },
        ],
      };
    }
    return {
      primary: { label: "Beri Ulasan", onClick: handleReview, tone: "dark" },
      secondary: [
        { label: "Lihat Detail", onClick: handleDetail },
        { label: "Cetak Struk", onClick: handleCetakStruk },
        { label: "Beli Lagi", onClick: handleBuyAgain },
      ],
    };
  }, [status]);

  const total = formatRupiah(order?.total ?? 0);
  const date = String(order?.date ?? "").trim();
  const items = Array.isArray(order?.items) ? order.items : [];
  const itemsText = !Array.isArray(order?.items) ? String(order?.items_text ?? order?.items ?? "").trim() : String(order?.items_text ?? "").trim();
  const dueDate = String(order?.dueDate ?? "").trim();
  const orderId = String(order?.id ?? "").trim();
  const thumb = String(order?.thumbnail ?? "").trim() || "/img/placeholder.png";
  const expanded = !!expandedOrderId && expandedOrderId === orderId;
  const canToggleItems = Array.isArray(items) && items.length > 2;
  const hiddenCount = canToggleItems ? Math.max(0, items.length - 2) : 0;
  const visibleItems = showAllItems ? items : items.slice(0, 2);

  useEffect(() => {
    if (expanded) {
      setShowAllItems(true);
      return;
    }
    setShowAllItems(false);
  }, [expanded, orderId]);

  return (
    <div className="rounded-xl border border-zinc-100 bg-white p-4 transition-all duration-200 hover:shadow-lg">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="flex min-w-0 gap-4">
          <img
            src={thumb}
            alt={items || orderId || "Order"}
            onError={(e) => {
              const img = e.currentTarget;
              const tried = String(img.dataset.triedAltHost || "");
              if (!tried) {
                try {
                  const u = new URL(img.src);
                  if (u.hostname === "localhost") {
                    u.hostname = "127.0.0.1";
                    img.dataset.triedAltHost = "1";
                    img.src = u.toString();
                    return;
                  }
                  if (u.hostname === "127.0.0.1") {
                    u.hostname = "localhost";
                    img.dataset.triedAltHost = "1";
                    img.src = u.toString();
                    return;
                  }
                } catch {}
              }
              img.onerror = null;
              img.src = "/img/placeholder.png";
            }}
            className="h-16 w-16 flex-none rounded-lg bg-gray-100 object-cover"
            loading="lazy"
          />

          <div className="min-w-0">
            <div className="flex flex-wrap items-center gap-3">
              <div className="truncate text-sm font-semibold text-zinc-900">{orderId}</div>
              <span className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${badgeClass}`}>
                {status}
              </span>
            </div>

            {date ? <div className="mt-1 text-sm text-zinc-500">{date}</div> : null}
            {Array.isArray(items) && items.length ? (
              <div className="mt-3">
                <div className="text-xs font-semibold text-zinc-500">DETAIL PESANAN</div>
                <div className="mt-2 overflow-hidden rounded-xl border border-zinc-100 bg-zinc-50/40">
                  {visibleItems.map((it, idx) => {
                    const isLast = idx === visibleItems.length - 1;
                    const rowBorder = isLast ? "" : "border-b border-zinc-100";
                    const name = String(it?.name ?? "").trim();
                    const variant = String(it?.variant ?? "").trim();
                    const qty = Math.max(1, Math.trunc(Number(it?.qty ?? 1)));
                    const subtotal = Number(it?.subtotal ?? 0);
                    const itemThumb = String(it?.thumbnail ?? "").trim() || "/img/placeholder.png";

                    return (
                      <div key={String(it?.id ?? idx)} className={`flex items-center gap-3 px-4 py-3 ${rowBorder}`}>
                        <img
                          src={itemThumb}
                          alt={name || "Item"}
                          className="h-12 w-12 flex-none rounded-lg bg-gray-100 object-cover"
                          loading="lazy"
                          onError={(e) => {
                            const img = e.currentTarget;
                            img.onerror = null;
                            img.src = "/img/placeholder.png";
                          }}
                        />
                        <div className="min-w-0 flex-1">
                          <div className="truncate text-sm font-semibold text-zinc-900">{name || "Produk"}</div>
                          <div className="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                            {variant ? <span className="truncate">{variant}</span> : null}
                            <span>×{qty}</span>
                          </div>
                        </div>
                        <div className="shrink-0 text-right text-sm font-semibold text-zinc-900">{formatRupiah(subtotal)}</div>
                      </div>
                    );
                  })}
                </div>
                {canToggleItems ? (
                  <button
                    type="button"
                    onClick={() => setShowAllItems((v) => !v)}
                    className="mt-2 text-xs font-semibold text-blue-600 transition hover:text-blue-700 active:scale-95"
                  >
                    {showAllItems ? "Sembunyikan" : `Lihat ${hiddenCount} item lainnya`}
                  </button>
                ) : null}
              </div>
            ) : itemsText ? (
              <div className="mt-2 text-sm font-medium text-zinc-900">{itemsText}</div>
            ) : null}
            {status === "UNPAID" && dueDate ? (
              <div className="mt-2 text-sm font-medium text-amber-700">Bayar sebelum {dueDate}</div>
            ) : null}
          </div>
        </div>

        <div className="flex shrink-0 flex-col items-start gap-3 sm:items-end">
          <div className="whitespace-nowrap text-base font-bold text-zinc-900">{total}</div>

          <div className="flex w-full flex-wrap justify-end gap-2">
            {actions.primary.tone === "blue" ? (
              <PrimaryBlueButton onClick={actions.primary.onClick}>{actions.primary.label}</PrimaryBlueButton>
            ) : actions.primary.tone === "dark" ? (
              <PrimaryDarkButton onClick={actions.primary.onClick}>{actions.primary.label}</PrimaryDarkButton>
            ) : (
              <SecondaryButton onClick={actions.primary.onClick}>{actions.primary.label}</SecondaryButton>
            )}

            {actions.secondary.map((a) => (
              <SecondaryButton key={a.label} onClick={a.onClick}>
                {a.label}
              </SecondaryButton>
            ))}
          </div>
        </div>
      </div>

      {cancelOpen ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4">
          <button
            type="button"
            className="absolute inset-0 bg-black/40"
            onClick={() => {
              if (!cancelLoading) setCancelOpen(false);
            }}
            aria-label="Tutup"
          />
          <div className="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div className="flex items-start gap-4">
              <div className="grid h-11 w-11 flex-none place-items-center rounded-2xl bg-red-50 text-red-600">
                <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 9v4" />
                  <path d="M12 17h.01" />
                  <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                </svg>
              </div>
              <div className="min-w-0">
                <div className="text-base font-semibold text-zinc-900">Batalkan Pesanan?</div>
                <div className="mt-1 text-sm text-zinc-600">
                  Pesanan <span className="font-semibold text-zinc-900">{orderId}</span> akan dibatalkan...
                </div>
              </div>
            </div>

            <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
              <button
                type="button"
                className="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                onClick={() => setCancelOpen(false)}
                disabled={cancelLoading}
              >
                Batal
              </button>
              <button
                type="button"
                className="inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                onClick={() => void confirmCancel()}
                disabled={cancelLoading}
              >
                {cancelLoading ? "Memproses..." : "Ya, Batalkan"}
              </button>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}

