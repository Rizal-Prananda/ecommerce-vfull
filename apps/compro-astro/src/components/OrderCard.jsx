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

function StarRating({ value, onChange, disabled }) {
  const v = Math.max(0, Math.min(5, Math.trunc(Number(value || 0))));
  const [hoverRating, setHoverRating] = useState(0);
  const display = hoverRating > 0 ? hoverRating : v;
  return (
    <div className="flex items-center gap-1">
      {Array.from({ length: 5 }).map((_, i) => {
        const n = i + 1;
        const active = n <= display;
        return (
          <button
            key={n}
            type="button"
            className="group inline-flex h-9 w-9 items-center justify-center rounded-xl transition active:scale-95 sm:h-10 sm:w-10 disabled:cursor-not-allowed disabled:opacity-60"
            onClick={() => onChange(n)}
            onMouseEnter={() => setHoverRating(n)}
            onMouseLeave={() => setHoverRating(0)}
            disabled={disabled}
            aria-label={`${n} bintang`}
          >
            <svg
              viewBox="0 0 24 24"
              className={`h-8 w-8 transition-transform group-hover:scale-125 ${
                active ? "text-yellow-400" : "text-gray-200"
              }`}
              fill="currentColor"
              aria-hidden="true"
            >
              <path d="M12 17.27 18.18 21 16.54 13.97 22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27Z" />
            </svg>
          </button>
        );
      })}
    </div>
  );
}

function ReviewModal({
  open,
  loading,
  onClose,
  reviewKeys,
  reviewStep,
  setReviewStep,
  reviewDrafts,
  updateReview,
  validateReviewStep,
  submitReview,
}) {
  if (!open) return null;

  const keys = Array.isArray(reviewKeys) ? reviewKeys : [];
  const totalSteps = Math.max(0, keys.length);
  const step = Math.max(0, Math.min(totalSteps - 1, Math.trunc(Number(reviewStep || 0))));
  const key = totalSteps ? keys[step] : "";
  const d = key ? reviewDrafts[key] || {} : {};
  const name = String(d?.name ?? "").trim() || "Produk";
  const variant = String(d?.variant ?? "").trim();
  const thumb = String(d?.thumbnail ?? "").trim() || "/img/placeholder.png";
  const overall = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_overall ?? 0))));
  const quality = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_quality ?? 0))));
  const photo = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_photo ?? 0))));
  const comment = String(d?.comment ?? "");
  const anonymous = !!d?.anonymous;
  const progressText = totalSteps ? `${step + 1}/${totalSteps}` : "0/0";
  const progressPct = totalSteps ? Math.round(((step + 1) / totalSteps) * 100) : 0;
  const isLast = totalSteps ? step === totalSteps - 1 : true;
  const canSubmitCurrent = overall > 0 && quality > 0 && photo > 0 && String(comment || "").trim().length >= 10;

  const goPrev = () => setReviewStep((s) => Math.max(0, Math.trunc(Number(s || 0)) - 1));
  const goNext = () => setReviewStep((s) => Math.min(totalSteps - 1, Math.trunc(Number(s || 0)) + 1));

  const skip = () => {
    if (!key) return;
    updateReview(key, { skipped: true });
    if (isLast) {
      void submitReview();
      return;
    }
    goNext();
  };

  const next = () => {
    if (!key) return;
    const cur = reviewDrafts[key] || {};
    if (!validateReviewStep(cur)) return;
    updateReview(key, { skipped: false });
    if (isLast) void submitReview();
    else goNext();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center px-4">
      <button
        type="button"
        className="absolute inset-0 bg-black/40"
        onClick={() => {
          if (!loading) onClose();
        }}
        aria-label="Tutup"
      />
      <div className="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl max-h-[92vh]">
        <div className="flex flex-none items-center justify-between gap-4 border-b border-zinc-100 px-5 py-4 sm:px-8 sm:py-6">
          <div className="min-w-0">
            <div className="text-lg font-semibold text-zinc-900">Beri Ulasan</div>
            <div className="mt-1 text-sm text-zinc-600">Nilai produk satu per satu.</div>
          </div>
          <div className="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-3 py-1.5 text-sm font-semibold text-zinc-700">
            <span>{progressText}</span>
          </div>
        </div>

        <div className="flex-none px-5 pt-0 sm:px-8">
          <div className="h-1.5 w-full bg-zinc-100">
            <div className="h-1.5 bg-zinc-900" style={{ width: `${progressPct}%` }} />
          </div>
        </div>

        <div className="flex-1 overflow-y-auto px-5 py-5 sm:px-8 sm:py-7">
          <div className="flex flex-col gap-5 sm:flex-row sm:items-start">
            <img
              src={thumb}
              alt={name}
              className="h-20 w-20 flex-none rounded-2xl bg-zinc-100 object-cover ring-1 ring-zinc-200 sm:h-24 sm:w-24"
              loading="lazy"
              onError={(e) => {
                const img = e.currentTarget;
                img.onerror = null;
                img.src = "/img/placeholder.png";
              }}
            />
            <div className="min-w-0 flex-1">
              <div className="text-lg font-semibold leading-snug text-zinc-900 break-words whitespace-normal">{name}</div>
              {variant ? <div className="mt-1 text-sm font-semibold text-zinc-500">{variant}</div> : null}
              <div className="mt-5 grid gap-5">
                <div className="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-5">
                  <div className="text-sm font-semibold text-zinc-800">Rating Keseluruhan</div>
                  <div className="mt-2">
                    <StarRating value={overall} disabled={loading} onChange={(n) => updateReview(key, { rating_overall: n, skipped: false })} />
                  </div>
                </div>

                <div className="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-5">
                  <div className="text-sm font-semibold text-zinc-800">Kualitas Produk</div>
                  <div className="mt-2">
                    <StarRating value={quality} disabled={loading} onChange={(n) => updateReview(key, { rating_quality: n, skipped: false })} />
                  </div>
                </div>

                <div className="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-5">
                  <div className="text-sm font-semibold text-zinc-800">Sesuai Foto</div>
                  <div className="mt-2">
                    <StarRating value={photo} disabled={loading} onChange={(n) => updateReview(key, { rating_photo: n, skipped: false })} />
                  </div>
                </div>

                <div className="relative">
                  <label className="text-sm font-semibold text-zinc-800">Ceritakan pengalamanmu</label>
                  <textarea
                    className="mt-2 min-h-[140px] w-full resize-none rounded-2xl border border-zinc-200 bg-white px-4 py-3 pr-20 text-sm text-zinc-900 shadow-sm outline-none focus:border-gray-900 focus:ring-2 focus:ring-gray-900"
                    value={comment}
                    onChange={(e) => updateReview(key, { comment: e.target.value, skipped: false })}
                    placeholder="Minimal 10 karakter..."
                    disabled={loading}
                    maxLength={500}
                  />
                  <div
                    className={`pointer-events-none absolute bottom-3 right-4 text-xs font-semibold ${
                      String(comment || "").trim().length < 10 ? "text-red-600" : "text-zinc-500"
                    }`}
                  >
                    {String(comment || "").length}/500{String(comment || "").trim().length < 10 ? " (min. 10 karakter)" : ""}
                  </div>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                  <button
                    type="button"
                    className="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-dashed border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                    disabled={loading}
                  >
                    <span className="grid h-6 w-6 place-items-center rounded-lg bg-zinc-900 text-white">
                      <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                      </svg>
                    </span>
                    Tambah Foto/Video
                  </button>

                  <label className="inline-flex items-center gap-3 text-sm font-semibold text-zinc-700">
                    <input
                      type="checkbox"
                      className="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/20"
                      checked={anonymous}
                      onChange={(e) => updateReview(key, { anonymous: !!e.target.checked })}
                      disabled={loading}
                    />
                    Sembunyikan nama saya di ulasan
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="flex flex-none flex-col-reverse gap-2 border-t border-zinc-100 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-6">
          <button
            type="button"
            className="inline-flex h-11 w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
            onClick={goPrev}
            disabled={loading || step <= 0}
          >
            Kembali
          </button>

          <div className="flex w-full flex-col-reverse gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end">
            <button
              type="button"
              className="inline-flex h-11 w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
              onClick={skip}
              disabled={loading || !key}
            >
              Lewati
            </button>
            <button
              type="button"
              className={`inline-flex h-11 w-full items-center justify-center rounded-xl px-5 text-sm font-semibold transition active:scale-95 disabled:cursor-not-allowed sm:w-auto ${
                canSubmitCurrent && !loading ? "bg-gray-900 text-white hover:bg-gray-800 shadow-lg shadow-gray-900/20" : "bg-gray-200 text-gray-400"
              }`}
              onClick={next}
              disabled={loading || !key || !canSubmitCurrent}
            >
              {loading ? "Mengirim..." : isLast ? "Kirim Ulasan" : "Lanjut"}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function OrderCard({ order, onOrderUpdate }) {
  const status = useMemo(() => statusLabel(order?.status), [order?.status]);
  const badgeClass = useMemo(() => statusBadgeClass(status), [status]);
  const [expandedOrderId, setExpandedOrderId] = useState("");
  const [showAllItems, setShowAllItems] = useState(false);
  const [cancelOpen, setCancelOpen] = useState(false);
  const [cancelLoading, setCancelLoading] = useState(false);
  const [reviewOpen, setReviewOpen] = useState(false);
  const [reviewLoading, setReviewLoading] = useState(false);
  const [reviewDrafts, setReviewDrafts] = useState({});
  const [reviewKeys, setReviewKeys] = useState([]);
  const [reviewStep, setReviewStep] = useState(0);

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
    setReviewOpen(true);
  };

  useEffect(() => {
    if (!reviewOpen) return;
    const arr = Array.isArray(order?.items) ? order.items : [];
    const next = {};
    const keys = [];
    for (const it of arr) {
      const pid = Number(it?.product_id ?? 0);
      if (!Number.isFinite(pid) || pid <= 0) continue;
      const vidRaw = it?.variant_id;
      const vid = vidRaw === null || vidRaw === undefined ? null : Number(vidRaw);
      const key = `${Math.trunc(pid)}:${Number.isFinite(vid) && vid > 0 ? Math.trunc(vid) : ""}`;
      keys.push(key);
      next[key] = {
        product_id: Math.trunc(pid),
        variant_id: Number.isFinite(vid) && vid > 0 ? Math.trunc(vid) : null,
        name: String(it?.name ?? "").trim(),
        variant: String(it?.variant ?? "").trim(),
        thumbnail: String(it?.thumbnail ?? "").trim() || "/img/placeholder.png",
        rating_overall: 0,
        rating_quality: 0,
        rating_photo: 0,
        comment: "",
        anonymous: false,
        skipped: false,
      };
    }
    setReviewDrafts(next);
    setReviewKeys(keys);
    setReviewStep(0);
  }, [reviewOpen, order?.id]);

  const updateReview = (key, patch) => {
    setReviewDrafts((prev) => ({
      ...(prev || {}),
      [key]: { ...(prev?.[key] || {}), ...(patch || {}) },
    }));
  };

  const validateReviewStep = (d) => {
    const overall = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_overall ?? 0))));
    const quality = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_quality ?? 0))));
    const photo = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_photo ?? 0))));
    const comment = String(d?.comment ?? "").trim();

    if (overall < 1 || quality < 1 || photo < 1) {
      toast.error("Semua rating wajib diisi");
      return false;
    }
    if (comment.length < 10) {
      toast.error("Ceritakan pengalamanmu minimal 10 karakter");
      return false;
    }
    return true;
  };

  const submitReview = async () => {
    const orderId = String(order?.id ?? "").trim();
    if (!orderId || reviewLoading) return;
    const drafts = reviewDrafts && typeof reviewDrafts === "object" ? reviewDrafts : {};
    const keys = (Array.isArray(reviewKeys) && reviewKeys.length ? reviewKeys : Object.keys(drafts)).filter(Boolean);
    if (!keys.length) {
      toast.error("Produk tidak ditemukan");
      return;
    }

    const token = String(window.localStorage.getItem("token_pelanggan") || "").trim();
    const pelangganId = String(window.localStorage.getItem("id_pelanggan") || "").trim();
    if (!token && !pelangganId) {
      toast.error("Kamu belum login");
      return;
    }

    setReviewLoading(true);
    try {
      const toSend = keys
        .map((k) => ({ key: k, d: drafts[k] || null }))
        .filter((x) => x.d && !x.d.skipped);
      if (!toSend.length) {
        toast.error("Minimal 1 produk harus diulas");
        return;
      }

      for (const x of toSend) {
        const key = x.key;
        const d = drafts[key] || null;
        const productId = Number(d?.product_id ?? 0);
        const variantIdRaw = d?.variant_id;
        const variantId = variantIdRaw === null || variantIdRaw === undefined ? null : Number(variantIdRaw);
        const overall = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_overall ?? 0))));
        const quality = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_quality ?? 0))));
        const photo = Math.max(0, Math.min(5, Math.trunc(Number(d?.rating_photo ?? 0))));
        const comment = String(d?.comment ?? "").trim();
        const anonymous = !!d?.anonymous;
        if (!Number.isFinite(productId) || productId <= 0) {
          toast.error("Produk tidak valid");
          return;
        }
        if (!validateReviewStep(d)) return;

        const res = await fetch("/cms/api/reviews", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(pelangganId ? { "X-Pelanggan-Id": pelangganId } : {}),
          },
          body: JSON.stringify({
            order_id: orderId,
            product_id: Math.trunc(productId),
            variant_id: Number.isFinite(variantId) && variantId > 0 ? Math.trunc(variantId) : null,
            rating: overall,
            rating_overall: overall,
            rating_quality: quality,
            rating_sesuai_foto: photo,
            comment,
            anonymous,
          }),
        });
        const json = await res.json().catch(() => null);
        if (!res.ok || !json?.ok) {
          const msg = String(json?.message || "").trim();
          throw new Error(msg || "review_failed");
        }
      }

      setReviewOpen(false);
      toast.success("Ulasan berhasil dikirim");
    } catch {
      toast.error("Gagal kirim ulasan");
    } finally {
      setReviewLoading(false);
    }
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
        primary: { label: "Beri Ulasan", onClick: handleReview, tone: "dark" },
        secondary: [
          { label: "Lihat Invoice", onClick: handleLihatInvoice },
          { label: "Cetak Struk", onClick: handleCetakStruk },
        ],
      };
    }
    return {
      primary: { label: "Beli Lagi", onClick: handleBuyAgain, tone: "dark" },
      secondary: [],
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
        <div className="flex min-w-0 flex-1 gap-4">
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
                <div className="mt-2 overflow-hidden rounded-xl border border-zinc-200 bg-white">
                  {visibleItems.map((it, idx) => {
                    const isLast = idx === visibleItems.length - 1;
                    const rowBorder = isLast ? "" : "border-b border-zinc-200";
                    const name = String(it?.name ?? "").trim();
                    const variant = String(it?.variant ?? "").trim();
                    const qty = Math.max(1, Math.trunc(Number(it?.qty ?? 1)));
                    const subtotal = Number(it?.subtotal ?? 0);
                    const itemThumb = String(it?.thumbnail ?? "").trim() || "/img/placeholder.png";

                    return (
                      <div
                        key={String(it?.id ?? idx)}
                        className={`flex items-center gap-4 px-4 py-4 ${rowBorder}`}
                      >
                        <img
                          src={itemThumb}
                          alt={name || "Item"}
                          className="h-14 w-14 flex-none rounded-xl bg-gray-100 object-cover ring-1 ring-zinc-200"
                          loading="lazy"
                          onError={(e) => {
                            const img = e.currentTarget;
                            img.onerror = null;
                            img.src = "/img/placeholder.png";
                          }}
                        />
                        <div className="min-w-0 flex-1">
                          <div className="text-base font-semibold leading-snug text-zinc-900 break-words whitespace-normal">{name || "Produk"}</div>
                          <div className="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-600">
                            {variant ? (
                              <span className="inline-flex items-center rounded-lg bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-700">
                                {variant}
                              </span>
                            ) : null}
                            <span className="text-xs font-semibold text-zinc-500">Qty</span>
                            <span className="text-sm font-semibold text-zinc-900">×{qty}</span>
                          </div>
                        </div>
                        <div className="shrink-0 text-right">
                          <div className="text-xs font-semibold text-zinc-500">Subtotal</div>
                          <div className="mt-0.5 text-base font-semibold text-zinc-900">{formatRupiah(subtotal)}</div>
                        </div>
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

        <div className="flex w-full flex-col items-start gap-3 sm:w-auto sm:max-w-[280px] sm:items-end">
          <div className="whitespace-nowrap text-base font-semibold text-zinc-900">{total}</div>

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

      {reviewOpen ? (
        <ReviewModal
          open={reviewOpen}
          loading={reviewLoading}
          onClose={() => setReviewOpen(false)}
          reviewKeys={reviewKeys}
          reviewStep={reviewStep}
          setReviewStep={setReviewStep}
          reviewDrafts={reviewDrafts}
          updateReview={updateReview}
          validateReviewStep={validateReviewStep}
          submitReview={submitReview}
        />
      ) : null}
    </div>
  );
}

