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

export default async function TestimoniPage() {
  const items = await getTestimonials();

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-6 py-12">
      <section className="rounded-[2.5rem] border border-slate-200 bg-white/75 p-8 shadow-sm backdrop-blur">
        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
          Testimoni
        </h1>
        <p className="mt-2 text-sm text-slate-600">
          Data diambil dari Headless CMS.
        </p>

        <div className="mt-8 grid gap-4 md:grid-cols-3">
          {(items.length ? items : Array.from({ length: 3 })).map((t, idx) => {
            const key = t?.id ?? `s-${idx}`;
            const name = t?.name ?? "Client";
            const role = t?.role ?? "Customer";
            const message =
              t?.message ??
              "Pelayanannya cepat, hasilnya rapi, dan tampilan web terasa premium.";
            const rating = Number(t?.rating ?? 4.9).toFixed(1);
            return (
              <div
                key={key}
                className="rounded-[1.75rem] border border-slate-200 bg-white/85 p-6 shadow-sm"
              >
                <div className="flex items-center justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <div className="grid size-10 place-items-center rounded-2xl bg-slate-900 text-sm font-semibold text-white">
                      {name.slice(0, 1).toUpperCase()}
                    </div>
                    <div className="leading-tight">
                      <div className="text-sm font-semibold text-slate-900">
                        {name}
                      </div>
                      <div className="text-xs text-slate-600">{role}</div>
                    </div>
                  </div>
                  <div className="text-xs font-semibold text-amber-600">
                    {rating}
                  </div>
                </div>
                <p className="mt-4 text-sm leading-7 text-slate-700">{message}</p>
              </div>
            );
          })}
        </div>
      </section>
    </main>
  );
}

