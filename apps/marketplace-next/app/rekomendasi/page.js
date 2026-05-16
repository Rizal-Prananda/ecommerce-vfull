const CMS_URL = process.env.NEXT_PUBLIC_CMS_URL ?? "http://127.0.0.1:8000";

async function getRecommendations() {
  try {
    const res = await fetch(
      `${CMS_URL.replace(/\/$/, "")}/api/recommendations`,
      { cache: "no-store" }
    );
    const json = await res.json();
    return Array.isArray(json?.data) ? json.data : [];
  } catch {
    return [];
  }
}

export default async function RekomendasiPage() {
  const items = await getRecommendations();
  const list = items.length
    ? items
    : [
        {
          id: 1,
          tag: "Paket",
          title: "Company Profile Modern",
          description:
            "Landing 1 halaman: Home, About, Testimoni, Rekomendasi + integrasi CMS.",
        },
        {
          id: 2,
          tag: "Paket",
          title: "Branding Starter",
          description: "Logo, guideline singkat, template social media, dan kit.",
        },
        {
          id: 3,
          tag: "Paket",
          title: "Marketplace Setup",
          description: "Catalog, kategori, pencarian, listing produk, dan CTA.",
        },
      ];

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-6 py-12">
      <section className="rounded-[2.5rem] border border-slate-200 bg-white/75 p-8 shadow-sm backdrop-blur">
        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
          Rekomendasi
        </h1>
        <p className="mt-2 text-sm text-slate-600">
          Paket yang paling sering dipilih.
        </p>

        <div className="mt-8 grid gap-4 md:grid-cols-3">
          {list.slice(0, 6).map((x, idx) => {
            const badge =
              idx === 0
                ? "bg-slate-900"
                : idx === 1
                  ? "bg-amber-500"
                  : "bg-violet-600";
            const badgeText = idx === 0 ? "Populer" : idx === 1 ? "Best value" : "New";

            return (
              <div
                key={x.id ?? idx}
                className="rounded-[1.75rem] border border-slate-200 bg-white/85 p-6 shadow-sm"
              >
                <div className="text-xs font-semibold text-blue-700">
                  {x.tag ?? "Paket"}
                </div>
                <div className="mt-2 text-lg font-semibold text-slate-900">
                  {x.title}
                </div>
                <div className="mt-2 text-sm leading-7 text-slate-600">
                  {x.description}
                </div>
                <div className={`mt-5 inline-flex rounded-full ${badge} px-3 py-1 text-xs font-semibold text-white`}>
                  {badgeText}
                </div>
              </div>
            );
          })}
        </div>
      </section>
    </main>
  );
}

