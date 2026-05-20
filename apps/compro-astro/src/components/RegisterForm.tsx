import { useMemo, useState } from "react";

export default function RegisterForm() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [address, setAddress] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const CMS_REGISTER_URL = useMemo(() => "http://localhost:8001/api/register", []);

  const submit = async () => {
    setErrorMessage(null);
    setLoading(true);
    try {
      const res = await fetch(CMS_REGISTER_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          namalengkap_pelanggan: name,
          email_pelanggan: email,
          notelepon_pelanggan: phone,
          alamat_pelanggan: address,
          password,
          password_confirmation: passwordConfirmation,
        }),
      });
      const json = await res.json().catch(() => null);
      if (!res.ok) {
        const fallback = "Gagal daftar";
        const message = String(json?.message || "").trim();
        const errors = json?.errors;
        const firstErrorFromObject = errors && typeof errors === "object" && !Array.isArray(errors) ? String(Object.values(errors)?.flat?.()?.[0] ?? "").trim() : "";
        const firstErrorFromArray = Array.isArray(errors) && errors.length ? String(errors[0] ?? "").trim() : "";
        const msg = message || firstErrorFromObject || firstErrorFromArray || fallback;
        throw new Error(msg);
      }

      window.location.href = "/login";
    } catch (e: unknown) {
      const msg = e instanceof Error ? e.message : "Gagal daftar";
      setErrorMessage(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-[calc(100vh-4rem)]">
      <div className="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl grid-cols-1 md:grid-cols-2">
        <div className="hidden min-h-full flex-col justify-between bg-black px-10 py-12 text-white md:flex">
          <div className="text-sm font-semibold tracking-tight">Rizal Studio</div>

          <div>
            <div className="text-4xl font-bold leading-tight tracking-tight">Mulai Belanja &amp; Jualan di Marketplace Kami</div>
            <div className="mt-3 max-w-md text-sm leading-7 text-gray-300">Bangun toko, kelola produk, dan ngobrol langsung dengan pelanggan dalam satu dashboard yang rapi.</div>

            <div className="mt-8 grid grid-cols-3 gap-3">
              <div className="rounded-2xl bg-gray-900 p-4">
                <div className="h-24 w-full rounded-xl bg-gray-800"></div>
                <div className="mt-3 h-3 w-24 rounded bg-gray-800"></div>
                <div className="mt-2 h-3 w-16 rounded bg-gray-800"></div>
              </div>
              <div className="rounded-2xl bg-gray-900 p-4">
                <div className="h-24 w-full rounded-xl bg-gray-800"></div>
                <div className="mt-3 h-3 w-20 rounded bg-gray-800"></div>
                <div className="mt-2 h-3 w-14 rounded bg-gray-800"></div>
              </div>
              <div className="rounded-2xl bg-gray-900 p-4">
                <div className="h-24 w-full rounded-xl bg-gray-800"></div>
                <div className="mt-3 h-3 w-28 rounded bg-gray-800"></div>
                <div className="mt-2 h-3 w-12 rounded bg-gray-800"></div>
              </div>
            </div>

            <div className="mt-8 flex items-center gap-4">
              <div className="flex -space-x-2">
                <div className="grid size-9 place-items-center rounded-full border border-black bg-gray-700 text-xs font-semibold text-white">A</div>
                <div className="grid size-9 place-items-center rounded-full border border-black bg-gray-600 text-xs font-semibold text-white">R</div>
                <div className="grid size-9 place-items-center rounded-full border border-black bg-gray-500 text-xs font-semibold text-white">S</div>
                <div className="grid size-9 place-items-center rounded-full border border-black bg-gray-400 text-xs font-semibold text-black">+</div>
              </div>
              <div className="text-sm text-gray-300">
                Dipercaya <span className="font-semibold text-white">10.000+</span> seller
              </div>
            </div>
          </div>

          <div className="text-xs text-gray-400">© {new Date().getFullYear()} Rizal Studio</div>
        </div>

        <div className="flex min-h-full items-center justify-center bg-white px-4 py-10 md:px-10 md:py-12">
          <div className="w-full max-w-md">
            <div className="text-2xl font-semibold tracking-tight text-black">Buat Akun Baru</div>
            <div className="mt-2 text-sm text-gray-500">
              Sudah punya akun?{" "}
              <a className="font-semibold text-black underline-offset-2 hover:underline" href="/login">
                Login
              </a>
            </div>

            {errorMessage ? (
              <div className="mt-5 rounded-xl border border-gray-200 bg-white p-4 text-sm text-black">
                <div className="font-semibold">Gagal daftar</div>
                <div className="mt-1 text-gray-600">{errorMessage}</div>
              </div>
            ) : null}

            <form
              className="mt-6 space-y-5"
              onSubmit={(e) => {
                e.preventDefault();
                if (!loading) void submit();
              }}
            >
              <div className="flex flex-col gap-2">
                <label className="block text-sm font-semibold text-black">
                  Nama Lengkap <span className="text-gray-400">*</span>
                </label>
                <input
                  className="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-black outline-none focus:ring-2 focus:ring-black/10"
                  placeholder="Nama lengkap"
                  autoComplete="name"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                />
              </div>

              <div className="flex flex-col gap-2">
                <label className="block text-sm font-semibold text-black">
                  No Telepon <span className="text-gray-400">*</span>
                </label>
                <input
                  type="tel"
                  className="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-black outline-none focus:ring-2 focus:ring-black/10"
                  placeholder="081234567890"
                  autoComplete="tel"
                  required
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                />
              </div>

              <div className="flex flex-col gap-2">
                <label className="block text-sm font-semibold text-black">
                  Email <span className="text-gray-400">*</span>
                </label>
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
                <label className="block text-sm font-semibold text-black">
                  Alamat Lengkap <span className="text-gray-400">*</span>
                </label>
                <textarea
                  rows={3}
                  className="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-black outline-none focus:ring-2 focus:ring-black/10"
                  placeholder="Alamat lengkap"
                  required
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                />
              </div>

              <div className="flex flex-col gap-2">
                <label className="block text-sm font-semibold text-black">
                  Password <span className="text-gray-400">*</span>
                </label>
                <div className="relative">
                  <input
                    type={showPassword ? "text" : "password"}
                    minLength={8}
                    className="h-11 w-full rounded-xl border border-gray-300 bg-white pl-4 pr-11 text-sm text-black outline-none focus:ring-2 focus:ring-black/10"
                    placeholder="Minimal 8 karakter"
                    autoComplete="new-password"
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
              </div>

              <div className="flex flex-col gap-2">
                <label className="block text-sm font-semibold text-black">
                  Konfirmasi Password <span className="text-gray-400">*</span>
                </label>
                <div className="relative">
                  <input
                    type={showPasswordConfirmation ? "text" : "password"}
                    minLength={8}
                    className="h-11 w-full rounded-xl border border-gray-300 bg-white pl-4 pr-11 text-sm text-black outline-none focus:ring-2 focus:ring-black/10"
                    placeholder="Ulangi password"
                    autoComplete="new-password"
                    required
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                  />
                  <button
                    type="button"
                    className="absolute right-2 top-1/2 -translate-y-1/2 inline-flex size-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                    aria-label={showPasswordConfirmation ? "Sembunyikan konfirmasi password" : "Tampilkan konfirmasi password"}
                    onClick={() => setShowPasswordConfirmation((v) => !v)}
                  >
                    <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                      <circle cx="12" cy="12" r="3" />
                    </svg>
                  </button>
                </div>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="inline-flex h-11 w-full items-center justify-center rounded-xl bg-black px-4 text-sm font-semibold text-white hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-60"
              >
                {loading ? "Memproses..." : "Buat Akun"}
              </button>

              <div className="text-xs text-gray-500">Dengan mendaftar, kamu menyetujui kebijakan dan syarat layanan kami.</div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
