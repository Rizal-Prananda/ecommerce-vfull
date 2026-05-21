import { useEffect, useRef, useState } from "react";
import toast from "react-hot-toast";

type UserData = {
  nama_lengkap: string;
  email: string;
  avatar?: string | null;
};

function initials(name: string) {
  const parts = String(name || "")
    .trim()
    .split(/\s+/)
    .filter(Boolean);
  if (!parts.length) return "U";
  const a = parts[0]?.[0] ?? "U";
  const b = parts.length > 1 ? parts[parts.length - 1]?.[0] ?? "" : "";
  return (a + b).toUpperCase();
}

export default function UserMenu() {
  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState<UserData | null>(null);
  const [open, setOpen] = useState(false);
  const rootRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    const onDown = (e: MouseEvent) => {
      const el = rootRef.current;
      if (!el) return;
      if (e.target instanceof Node && !el.contains(e.target)) setOpen(false);
    };
    document.addEventListener("mousedown", onDown);
    return () => document.removeEventListener("mousedown", onDown);
  }, []);

  useEffect(() => {
    const token = window.localStorage.getItem("token_pelanggan") ?? "";
    if (!token) {
      setUser(null);
      setLoading(false);
      return;
    }

    const fetchUser = async () => {
      setLoading(true);
      try {
        const res = await fetch("/api/user", {
          headers: { Authorization: `Bearer ${token}` },
        });
        const json = await res.json().catch(() => null);
        if (!res.ok) throw new Error(String(json?.message || "Gagal memuat user"));
        const data = json?.data as UserData | undefined;
        if (!data?.email) throw new Error("Data user tidak valid");
        setUser({
          nama_lengkap: String(data.nama_lengkap ?? ""),
          email: String(data.email ?? ""),
          avatar: data.avatar ?? null,
        });
      } catch (e: unknown) {
        const msg = e instanceof Error ? e.message : "Gagal memuat user";
        setUser(null);
        window.localStorage.removeItem("token_pelanggan");
        window.localStorage.removeItem("id_pelanggan");
        toast.error(msg);
      } finally {
        setLoading(false);
      }
    };

    void fetchUser();
  }, []);

  const logout = async () => {
    const token = window.localStorage.getItem("token_pelanggan") ?? "";
    try {
      await fetch("/api/logout", {
        method: "POST",
        headers: { ...(token ? { Authorization: `Bearer ${token}` } : {}) },
      }).catch(() => null);
    } finally {
      window.localStorage.removeItem("token_pelanggan");
      window.localStorage.removeItem("id_pelanggan");
      const target = `${window.location.origin}/#home`;
      if (window.location.pathname === "/" && window.location.hash === "#home") {
        window.location.reload();
        return;
      }
      window.location.replace(target);
    }
  };

  if (loading && !user) {
    return (
      <div className="hidden items-center gap-2 md:flex">
        <div className="h-10 w-24 rounded-2xl border border-gray-200 bg-white" />
        <div className="h-10 w-24 rounded-2xl bg-black" />
      </div>
    );
  }

  if (!user) {
    return (
      <div className="hidden items-center gap-2 md:flex">
        <a
          href="/login"
          className="inline-flex h-10 items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 text-sm font-semibold text-black hover:bg-gray-50"
        >
          Login
        </a>
        <a
          href="/register"
          className="inline-flex h-10 items-center justify-center rounded-2xl bg-black px-4 text-sm font-semibold text-white hover:bg-gray-900"
        >
          Daftar
        </a>
      </div>
    );
  }

  const avatarUrl = user.avatar || "";
  const name = user.nama_lengkap || "Pelanggan";
  const email = user.email || "";

  return (
    <div ref={rootRef} className="relative hidden md:block">
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="inline-flex h-10 items-center gap-3 rounded-2xl border border-gray-200 bg-white pl-1 pr-3 text-sm font-semibold text-black hover:bg-gray-50"
        aria-haspopup="menu"
        aria-expanded={open}
      >
        {avatarUrl ? (
          <img src={avatarUrl} alt={name} className="size-8 rounded-2xl object-cover bg-gray-100" />
        ) : (
          <div className="grid size-8 place-items-center rounded-2xl bg-black text-xs font-bold text-white">
            {initials(name)}
          </div>
        )}
        <span className="max-w-[10rem] truncate">{name}</span>
        <svg className="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
          <path d="M6 9l6 6 6-6" />
        </svg>
      </button>

      {open ? (
        <div
          className="absolute right-0 mt-2 w-64 overflow-hidden rounded-2xl border border-gray-200 bg-white"
          role="menu"
        >
          <div className="border-b border-gray-200 px-4 py-3">
            <div className="text-sm font-semibold text-black">{name}</div>
            <div className="mt-0.5 text-xs text-gray-500">{email}</div>
          </div>
          <div className="p-1">
            <a
              href="/ProfilPelanggan"
              className="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium text-black hover:bg-gray-50"
              role="menuitem"
            >
              Kunjungi Profil
              <svg className="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M9 18l6-6-6-6" />
              </svg>
            </a>
            <button
              type="button"
              onClick={logout}
              className="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium text-black hover:bg-gray-50"
              role="menuitem"
            >
              Logout
              <svg className="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M10 17l5-5-5-5" />
              </svg>
            </button>
          </div>
        </div>
      ) : null}
    </div>
  );
}

