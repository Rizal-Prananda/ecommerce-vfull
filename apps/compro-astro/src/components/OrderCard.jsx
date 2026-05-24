import React, { useMemo } from "react";

function formatRupiah(n) {
  const v = Number(n ?? 0);
  const safe = Number.isFinite(v) ? Math.max(0, Math.trunc(v)) : 0;
  return `Rp ${safe.toLocaleString("id-ID")}`;
}

function statusBadgeClass(status) {
  const s = String(status || "").toUpperCase();
  if (s === "UNPAID") return "border-amber-200 bg-amber-50 text-amber-700";
  if (s === "PAID") return "border-emerald-200 bg-emerald-50 text-emerald-700";
  return "border-sky-200 bg-sky-50 text-sky-700";
}

function statusLabel(status) {
  const s = String(status || "").toUpperCase();
  if (s === "UNPAID") return "UNPAID";
  if (s === "PAID") return "PAID";
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

export default function OrderCard({ order }) {
  const status = useMemo(() => statusLabel(order?.status), [order?.status]);
  const badgeClass = useMemo(() => statusBadgeClass(status), [status]);

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
    window.location.assign(`/pembayaran/${id}`);
  };

  const handleDetail = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.location.assign(`/PesananSaya?menu=pesanan&order=${encodeURIComponent(id)}`);
  };

  const handleTrack = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.location.assign(`/PesananSaya?menu=pesanan&track=${encodeURIComponent(id)}`);
  };

  const handleCancel = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.alert(`Batalkan pesanan ${id}`);
  };

  const handleBuyAgain = () => {
    window.location.assign("/marketplace");
  };

  const handleReview = () => {
    const id = String(order?.id ?? "").trim();
    if (!id) return;
    window.alert(`Beri ulasan untuk ${id}`);
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
          { label: "Lacak Pesanan", onClick: handleTrack },
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
  const items = String(order?.items ?? "").trim();
  const dueDate = String(order?.dueDate ?? "").trim();
  const orderId = String(order?.id ?? "").trim();
  const thumb = String(order?.thumbnail ?? "").trim();

  return (
    <div className="rounded-xl border border-zinc-100 bg-white p-4 transition-all duration-200 hover:shadow-lg">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="flex min-w-0 gap-4">
          <img
            src={thumb}
            alt={items || orderId || "Order"}
            onError={(e) => {
              e.currentTarget.src = "/img/placeholder.jpg";
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
            {items ? <div className="mt-2 text-sm font-medium text-zinc-900">{items}</div> : null}
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
    </div>
  );
}

