import React, { useEffect, useMemo, useState } from "react";

import OrderCard from "./OrderCard.jsx";

const TABS = [
  { key: "ALL", label: "Semua" },
  { key: "PAID", label: "Paid" },
  { key: "UNPAID", label: "Unpaid" },
  { key: "SELESAI", label: "Selesai" },
];

export default function PesananList({ orders = [] }) {
  const [activeTab, setActiveTab] = useState("ALL");
  const [search, setSearch] = useState("");
  const [data, setData] = useState(Array.isArray(orders) ? orders : []);

  // Fetch real orders from server (includes thumbnail from OrderItems / products)
  useEffect(() => {
    let cancelled = false;

    const getAuth = () => {
      try {
        const token = String(window.localStorage.getItem("token_pelanggan") || "").trim();
        const pelangganId = String(window.localStorage.getItem("id_pelanggan") || "").trim();
        const idNum = Number(pelangganId || "0");
        const id = Number.isFinite(idNum) && idNum > 0 ? String(Math.trunc(idNum)) : "";
        return { token, id };
      } catch {
        return { token: "", id: "" };
      }
    };

    const load = async () => {
      const auth = getAuth();
      if (!auth.id && !auth.token) return;

      const res = await fetch("/api/orders", {
        method: "GET",
        headers: {
          ...(auth.token ? { Authorization: `Bearer ${auth.token}` } : {}),
          ...(auth.id ? { "X-Pelanggan-Id": auth.id } : {}),
        },
      }).catch(() => null);

      const json = res ? await res.json().catch(() => null) : null;
      if (!res || !res.ok || !json?.ok) return;

      const list = Array.isArray(json?.data) ? json.data : [];
      if (!cancelled) setData(list);
    };

    void load();
    return () => {
      cancelled = true;
    };
  }, []);

  // Filter logic: tab status + search by order id / items
  const filtered = useMemo(() => {
    const q = String(search || "").trim().toLowerCase();
    const list = Array.isArray(data) ? data : [];

    return list.filter((o) => {
      const status = String(o?.status || "").toUpperCase();
      const matchesTab = activeTab === "ALL" ? true : status === activeTab;
      const matchesSearch = q
        ? String(o?.id || "").toLowerCase().includes(q) || String(o?.items || "").toLowerCase().includes(q)
        : true;
      return matchesTab && matchesSearch;
    });
  }, [data, activeTab, search]);

  return (
    <div className="rounded-2xl border border-zinc-100 bg-white p-6 shadow-sm">
      <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div className="flex items-center gap-2 overflow-x-auto border-b border-zinc-100 pb-2 md:border-0 md:pb-0">
          {TABS.map((t) => {
            const active = t.key === activeTab;
            return (
              <button
                key={t.key}
                type="button"
                onClick={() => setActiveTab(t.key)}
                className={[
                  "h-10 whitespace-nowrap px-1 text-sm font-semibold transition active:scale-95",
                  active ? "border-b-2 border-blue-600 text-zinc-900" : "text-zinc-500 hover:text-zinc-900",
                ].join(" ")}
              >
                {t.label}
              </button>
            );
          })}
        </div>

        <div className="w-full md:w-[340px]">
          <div className="relative">
            <input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari No. Order..."
              className="h-11 w-full rounded-xl border border-zinc-200 bg-white pl-11 pr-4 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none"
            />
            <svg
              viewBox="0 0 24 24"
              className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-400"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
            >
              <path d="M21 21l-4.3-4.3" />
              <circle cx="11" cy="11" r="7" />
            </svg>
          </div>
        </div>
      </div>

      <div className="mt-6 space-y-4">
        {filtered.length === 0 ? (
          <div className="rounded-xl border border-zinc-100 bg-zinc-50/40 px-6 py-14 text-center">
            <div className="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-white shadow-sm">
              <svg viewBox="0 0 24 24" className="h-8 w-8 text-zinc-300" fill="none" stroke="currentColor" strokeWidth="1.5">
                <path d="M6 2h12l3 7-4 12H7L3 9l3-7Z" />
                <path d="M3 9h18" />
                <path d="M9 13h6" />
              </svg>
            </div>
            <div className="mt-4 text-base font-semibold text-zinc-900">Belum ada pesanan dengan status ini</div>
            <div className="mt-1 text-sm text-zinc-500">Coba ganti filter atau cari no. order lain.</div>
          </div>
        ) : (
          filtered.map((o) => <OrderCard key={o.id} order={o} />)
        )}
      </div>
    </div>
  );
}
