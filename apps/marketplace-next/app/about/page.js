import Link from "next/link";

export default function AboutPage() {
  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-6 py-12">
      <section className="grid gap-8 md:grid-cols-2 md:items-center">
        <div className="rounded-[2.5rem] border border-slate-200 bg-white/75 p-8 shadow-sm backdrop-blur">
          <div className="text-xs font-semibold text-slate-600">Our Story</div>
          <h1 className="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
            Tentang Rizal Studio
          </h1>
          <p className="mt-4 text-sm leading-7 text-slate-600">
            Fokus bikin tampilan modern yang enak dilihat, cepat, dan gampang
            dikembangkan. Struktur rapi, komponen reusable, dan siap produksi.
          </p>
          <div className="mt-6 flex flex-col gap-3 sm:flex-row">
            <Link
              href="/marketplace"
              className="inline-flex h-12 items-center justify-center rounded-full bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
            >
              Lihat Marketplace
            </Link>
            <Link
              href="/"
              className="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50"
            >
              Kembali ke Home
            </Link>
          </div>
        </div>

        <div className="grid gap-4">
          <div className="rounded-[2rem] border border-slate-200 bg-slate-900 p-7 text-white shadow-sm">
            <div className="text-xs font-semibold text-white/70">Value</div>
            <div className="mt-2 text-xl font-semibold tracking-tight">
              Quality & Integrity
            </div>
            <div className="mt-3 grid gap-3 text-sm text-white/80">
              <div className="rounded-2xl bg-white/10 p-4">
                <div className="font-semibold text-white">Quality</div>
                <div className="mt-1 text-xs leading-6 text-white/80">
                  Tipografi rapi, spacing konsisten, dan UI terasa premium.
                </div>
              </div>
              <div className="rounded-2xl bg-white/10 p-4">
                <div className="font-semibold text-white">Integrity</div>
                <div className="mt-1 text-xs leading-6 text-white/80">
                  Proses jelas, struktur proyek bersih, dan mudah dirawat.
                </div>
              </div>
            </div>
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <Link
              href="/testimoni"
              className="rounded-[1.75rem] border border-slate-200 bg-white/85 p-6 shadow-sm hover:bg-white"
            >
              <div className="text-sm font-semibold text-slate-900">Testimoni</div>
              <div className="mt-1 text-xs text-slate-600">
                Review pelanggan terbaru
              </div>
            </Link>
            <Link
              href="/rekomendasi"
              className="rounded-[1.75rem] border border-slate-200 bg-white/85 p-6 shadow-sm hover:bg-white"
            >
              <div className="text-sm font-semibold text-slate-900">
                Rekomendasi
              </div>
              <div className="mt-1 text-xs text-slate-600">
                Paket favorit pilihan
              </div>
            </Link>
          </div>
        </div>
      </section>
    </main>
  );
}

