import Link from "next/link";

const CMS_URL = process.env.NEXT_PUBLIC_CMS_URL ?? "http://127.0.0.1:8000";

async function getTestimonials() {
  try {
    const res = await fetch(`${CMS_URL.replace(/\/$/, "")}/api/testimonials`, {
      cache: "no-store",
    });
    const json = await res.json();
    return Array.isArray(json?.data) ? json.data : [];
  } catch {
    return [];
  }
}

async function getRecommendations() {
  try {
    const res = await fetch(`${CMS_URL.replace(/\/$/, "")}/api/recommendations`, { cache: "no-store" });
    const json = await res.json();
    return Array.isArray(json?.data) ? json.data : [];
  } catch {
    return [];
  }
}

export default async function HomePage() {
  const testimonials = await getTestimonials();
  const recommendations = await getRecommendations();

  return (
    <main className="mx-auto w-full max-w-6xl flex-1 px-4">
      <section id="home" className="relative overflow-hidden py-14 md:py-20">
        <div className="grid gap-10 md:grid-cols-2 md:items-center">
          <div className="flex flex-col gap-6">
            <div className="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-3 py-1 text-xs font-medium text-slate-700 shadow-sm">
              <span className="inline-block size-2 rounded-full bg-amber-500"></span>
              Modern • Smooth • Responsive
            </div>
            <h1 className="text-4xl font-semibold leading-tight tracking-tight text-slate-900 md:text-5xl">Elevate your experience dengan desain modern yang rapi dan premium</h1>
            <p className="max-w-xl text-base leading-7 text-slate-600">Company profile 1 halaman (Home, About, Testimoni, Rekomendasi) dengan warna putih bersih, card glass, dan micro-interaction yang halus.</p>
            <div className="flex flex-col gap-3 sm:flex-row">
              <a href="#about" className="inline-flex h-12 items-center justify-center rounded-full bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Lihat Profil
              </a>
              <a href={`${CMS_URL}/dashboard`} className="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
                Ke Dashboard CMS
              </a>
            </div>

            <div className="grid grid-cols-3 gap-3 pt-3">
              <div className="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm">
                <div className="text-2xl font-semibold tracking-tight">120+</div>
                <div className="text-xs font-medium text-slate-600">Project selesai</div>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm">
                <div className="text-2xl font-semibold tracking-tight">4.9</div>
                <div className="text-xs font-medium text-slate-600">Rating rata-rata</div>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm">
                <div className="text-2xl font-semibold tracking-tight">24/7</div>
                <div className="text-xs font-medium text-slate-600">Support cepat</div>
              </div>
            </div>
          </div>

          <div className="relative">
            <div className="absolute -inset-2 rounded-[2.5rem] bg-gradient-to-br from-blue-200/70 via-violet-200/50 to-amber-200/50 blur-2xl"></div>
            <div className="relative rounded-[2.5rem] border border-slate-200 bg-white/85 p-5 shadow-sm backdrop-blur">
              <div className="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 p-4">
                <div className="flex items-center gap-3">
                  <div className="size-11 rounded-2xl bg-slate-900"></div>
                  <div className="flex flex-col">
                    <div className="text-sm font-semibold text-slate-900">Discover Unrivaled Excellence</div>
                    <div className="text-xs text-slate-600">Cari layanan & rekomendasi</div>
                  </div>
                </div>
                <div className="hidden items-center gap-2 md:flex">
                  <span className="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm">Fast</span>
                  <span className="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm">Clean</span>
                </div>
              </div>

              <div className="mt-4 grid gap-3">
                <div className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                  <div className="size-11 rounded-2xl bg-blue-100"></div>
                  <div className="flex-1">
                    <div className="text-sm font-semibold text-slate-900">Branding & Identitas</div>
                    <div className="text-xs text-slate-600">Logo, guideline, template sosial media</div>
                  </div>
                  <div className="text-xs font-semibold text-blue-700">Detail</div>
                </div>
                <div className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                  <div className="size-11 rounded-2xl bg-violet-100"></div>
                  <div className="flex-1">
                    <div className="text-sm font-semibold text-slate-900">Company Profile</div>
                    <div className="text-xs text-slate-600">1 page modern + integrasi CMS</div>
                  </div>
                  <div className="text-xs font-semibold text-blue-700">Detail</div>
                </div>
                <div className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                  <div className="size-11 rounded-2xl bg-amber-100"></div>
                  <div className="flex-1">
                    <div className="text-sm font-semibold text-slate-900">Marketplace</div>
                    <div className="text-xs text-slate-600">Catalog, search, kategori, CTA</div>
                  </div>
                  <Link href="/marketplace" className="text-xs font-semibold text-blue-700">
                    Detail
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div className="border-t border-slate-200/70" />

      <section id="about" className="py-12 md:py-16">
        <div className="grid gap-8 md:grid-cols-2 md:items-center">
          <div className="relative">
            <div className="absolute -inset-2 rounded-[2rem] bg-white/70 blur-xl"></div>
            <div className="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white/85 p-6 shadow-sm backdrop-blur">
              <div className="flex items-end justify-between gap-4">
                <div>
                  <div className="text-xs font-semibold text-slate-600">Our Story</div>
                  <div className="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Rizal Studio</div>
                  <div className="mt-2 max-w-sm text-sm leading-6 text-slate-600">Fokus bikin tampilan modern yang enak dilihat, cepat, dan gampang dikembangkan.</div>
                </div>
                <div className="grid size-14 place-items-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-sm">★</div>
              </div>
              <div className="mt-8 grid gap-3 md:grid-cols-2">
                <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                  <div className="text-sm font-semibold text-slate-900">Quality</div>
                  <div className="mt-1 text-xs leading-5 text-slate-600">Tipografi rapi, spacing konsisten, komponen reusable.</div>
                </div>
                <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                  <div className="text-sm font-semibold text-slate-900">Integrity</div>
                  <div className="mt-1 text-xs leading-5 text-slate-600">Proses jelas, struktur proyek bersih, siap produksi.</div>
                </div>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-5">
            <h2 className="text-2xl font-semibold tracking-tight text-slate-900">Tentang Layanan</h2>
            <p className="text-sm leading-7 text-slate-600">Web ini dibuat modern (gradient lembut + glass card), smooth scroll, dan siap terhubung ke CMS. Cocok untuk company profile, agency, atau brand personal.</p>
            <div className="grid gap-3 sm:grid-cols-2">
              <div className="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm">
                <div className="text-sm font-semibold text-slate-900">Desain Modern</div>
                <div className="mt-1 text-xs leading-5 text-slate-600">Card clean, shadow halus, layout rapi.</div>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm">
                <div className="text-sm font-semibold text-slate-900">Performant</div>
                <div className="mt-1 text-xs leading-5 text-slate-600">Cepat dibuka, animasi ringan.</div>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm">
                <div className="text-sm font-semibold text-slate-900">Terhubung CMS</div>
                <div className="mt-1 text-xs leading-5 text-slate-600">Data testimoni & rekomendasi dari API.</div>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-white/85 p-4 shadow-sm">
                <div className="text-sm font-semibold text-slate-900">Siap Marketplace</div>
                <div className="mt-1 text-xs leading-5 text-slate-600">Marketplace dibuat di Next.js.</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div className="border-t border-slate-200/70" />

      <section id="testimoni" className="py-12 md:py-16">
        <div className="flex items-end justify-between gap-6">
          <div>
            <h2 className="text-2xl font-semibold tracking-tight text-slate-900">Voices of Trust</h2>
            <p className="mt-1 text-sm text-slate-600">Testimoni diambil dari CMS.</p>
          </div>
          <Link href="/testimoni" className="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50 md:inline-flex">
            Lihat Semua
          </Link>
        </div>

        <div className="mt-6 grid gap-4 md:grid-cols-3">
          {(testimonials.length ? testimonials : Array.from({ length: 3 })).slice(0, 3).map((t, idx) => {
            const key = t?.id ?? `s-${idx}`;
            const name = t?.name ?? "Client";
            const role = t?.role ?? "Customer";
            const rating = Number(t?.rating ?? 4.9).toFixed(1);
            const message = t?.message ?? "Desainnya modern, rapi, dan cepat. Hasilnya terasa premium.";

            return (
              <div key={key} className="rounded-2xl border border-slate-200 bg-white/85 p-5 shadow-sm backdrop-blur">
                <div className="flex items-center justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <div className="grid size-10 place-items-center rounded-2xl bg-slate-900 text-sm font-semibold text-white">{name.slice(0, 1).toUpperCase()}</div>
                    <div className="leading-tight">
                      <div className="text-sm font-semibold text-slate-900">{name}</div>
                      <div className="text-xs text-slate-600">{role}</div>
                    </div>
                  </div>
                  <div className="text-xs font-semibold text-amber-600">{rating}</div>
                </div>
                <p className="mt-4 text-sm leading-7 text-slate-700">{message}</p>
              </div>
            );
          })}
        </div>
      </section>

      <div className="border-t border-slate-200/70" />

      <section id="rekomendasi" className="py-12 md:py-16">
        <div className="grid gap-6 md:grid-cols-2 md:items-end">
          <div>
            <h2 className="text-2xl font-semibold tracking-tight text-slate-900">Rekomendasi</h2>
            <p className="mt-1 text-sm text-slate-600">Pilihan layanan yang paling sering dipilih.</p>
          </div>
          <div className="flex gap-2 md:justify-end">
            <Link href="/marketplace" className="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50">
              Lihat Marketplace
            </Link>
            <a href={`${CMS_URL}/dashboard`} className="inline-flex h-11 items-center justify-center rounded-full bg-slate-900 px-5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
              Kelola CMS
            </a>
          </div>
        </div>

        <div className="mt-6 grid gap-4 md:grid-cols-3">
          {(recommendations.length ? recommendations : Array.from({ length: 3 })).slice(0, 3).map((x, idx) => {
            const badge = idx === 0 ? "bg-slate-900" : idx === 1 ? "bg-amber-500" : "bg-violet-600";
            const badgeText = idx === 0 ? "Populer" : idx === 1 ? "Best value" : "New";
            const tag = x?.tag ?? "Paket";
            const title = x?.title ?? (idx === 0 ? "Company Profile Modern" : idx === 1 ? "Branding Starter" : "Marketplace Setup");
            const description = x?.description ?? (idx === 0 ? "Landing 1 halaman + integrasi CMS + CTA." : idx === 1 ? "Logo + guideline + template social media." : "Catalog, kategori, search, dan listing produk.");

            return (
              <div key={x?.id ?? idx} className="rounded-2xl border border-slate-200 bg-white/85 p-5 shadow-sm backdrop-blur">
                <div className="text-xs font-semibold text-blue-700">{tag}</div>
                <div className="mt-1 text-lg font-semibold text-slate-900">{title}</div>
                <div className="mt-2 text-sm leading-7 text-slate-600">{description}</div>
                <div className={`mt-4 inline-flex rounded-full ${badge} px-3 py-1 text-xs font-semibold text-white`}>{badgeText}</div>
              </div>
            );
          })}
        </div>
      </section>
    </main>
  );
}
