import { useMemo, useState } from "react";
import toast from "react-hot-toast";

export default function LoginForm() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);

  const CMS_LOGIN_URL = useMemo(() => "http://localhost:8001/api/login", []);

  const submit = async () => {
    setLoading(true);
    try {
      const res = await fetch(CMS_LOGIN_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email_pelanggan: email, password }),
      });
      const json = await res.json().catch(() => null);
      if (!res.ok) {
        const fallback = "Login gagal";
        const message = String(json?.message || "").trim();
        const errors = json?.errors;
        const firstErrorFromObject = errors && typeof errors === "object" && !Array.isArray(errors) ? String(Object.values(errors)?.flat?.()?.[0] ?? "").trim() : "";
        const firstErrorFromArray = Array.isArray(errors) && errors.length ? String(errors[0] ?? "").trim() : "";
        throw new Error(message || firstErrorFromObject || firstErrorFromArray || fallback);
      }

      const id = Number(json?.data?.id_pelanggan ?? 0);
      if (Number.isInteger(id) && id > 0) {
        window.localStorage.setItem("id_pelanggan", String(id));
        window.localStorage.setItem("token_pelanggan", `pelanggan:${id}`);
      }

      try {
        window.sessionStorage.setItem("flash_toast", JSON.stringify({ type: "success", message: "Login berhasil" }));
      } catch {}
      window.location.href = "/#home";
    } catch (e: unknown) {
      const raw = e instanceof Error ? e.message : "Login gagal";
      const msg = /failed to fetch|networkerror|load failed/i.test(raw) ? "Tidak bisa terhubung ke server login." : raw;
      toast.error(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-[calc(100vh-4rem)]">
      <div className="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl grid-cols-1 md:grid-cols-2">
        <div className="hidden min-h-full flex-col justify-between bg-black px-10 py-12 text-white md:flex">
          <div className="flex items-center gap-2 font-semibold tracking-tight">
            <div className="grid size-9 place-items-center rounded-2xl bg-white text-sm font-bold text-black">R</div>
            <div>Rizal Studio</div>
          </div>

          <div>
            <div className="text-4xl font-bold leading-tight tracking-tight">Selamat Datang Kembali</div>
            <div className="mt-3 max-w-md text-sm leading-7 text-gray-300">Masuk untuk melanjutkan aktivitas marketplace dan kelola chat pelanggan dengan cepat.</div>

            <div className="mt-8 space-y-3 text-sm text-gray-200">
              <div className="flex items-start gap-3">
                <div className="mt-0.5 grid size-6 place-items-center rounded-lg bg-gray-900 text-white">
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M20 6 9 17l-5-5" />
                  </svg>
                </div>
                <div>
                  <div className="font-semibold text-white">Checkout lebih cepat</div>
                  <div className="text-gray-300">Simpan data akun untuk transaksi yang lebih praktis.</div>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <div className="mt-0.5 grid size-6 place-items-center rounded-lg bg-gray-900 text-white">
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M20 6 9 17l-5-5" />
                  </svg>
                </div>
                <div>
                  <div className="font-semibold text-white">Chat terintegrasi</div>
                  <div className="text-gray-300">Balas pesan pelanggan tanpa pindah-pindah aplikasi.</div>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <div className="mt-0.5 grid size-6 place-items-center rounded-lg bg-gray-900 text-white">
                  <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M20 6 9 17l-5-5" />
                  </svg>
                </div>
                <div>
                  <div className="font-semibold text-white">Riwayat rapi</div>
                  <div className="text-gray-300">Semua percakapan tersimpan dan mudah dicari.</div>
                </div>
              </div>
            </div>
          </div>

          <div className="text-xs text-gray-400">© {new Date().getFullYear()} Rizal Studio</div>
        </div>

        <div className="flex min-h-full items-center justify-center bg-white px-4 py-10 md:px-10 md:py-12">
          <div className="w-full max-w-md">
            <div className="text-2xl font-bold tracking-tight text-black">Masuk ke Akun</div>
            <div className="mt-2 text-sm text-gray-500">
              Belum punya akun?{" "}
              <a className="font-semibold text-black underline-offset-2 hover:underline" href="/register">
                Daftar gratis
              </a>
            </div>

            <form
              className="mt-6 space-y-5"
              onSubmit={(e) => {
                e.preventDefault();
                if (!loading) void submit();
              }}
            >
              <div className="flex flex-col gap-2">
                <label className="block text-sm font-semibold text-black">Email</label>
                <input
                  type="email"
                  className="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-black outline-none focus:ring-2 focus:ring-black/10"
                  placeholder="nama@email.com"
                  autoComplete="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                />
              </div>

              <div className="flex flex-col gap-2">
                <label className="block text-sm font-semibold text-black">Password</label>
                <div className="relative">
                  <input
                    type={showPassword ? "text" : "password"}
                    className="h-11 w-full rounded-xl border border-gray-300 bg-white pl-4 pr-11 text-sm text-black outline-none focus:ring-2 focus:ring-black/10"
                    placeholder="Password"
                    autoComplete="current-password"
                    required
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                  />
                  <button
                    type="button"
                    className="absolute right-2 top-1/2 -translate-y-1/2 inline-flex size-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                    aria-label={showPassword ? "Sembunyikan password" : "Tampilkan password"}
                    onClick={() => setShowPassword((v) => !v)}
                  >
                    <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                </div>
                <div className="flex items-center justify-end">
                  <a href="#" className="text-sm font-semibold text-black underline-offset-2 hover:underline">
                    Lupa password?
                  </a>
                </div>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="inline-flex h-11 w-full items-center justify-center rounded-xl bg-black px-4 text-sm font-semibold text-white hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {loading ? "Memproses..." : "Masuk"}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
