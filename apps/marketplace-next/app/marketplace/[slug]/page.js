import Link from "next/link";
import { notFound } from "next/navigation";

const CMS_URL = process.env.NEXT_PUBLIC_CMS_URL ?? "http://127.0.0.1:8000";

function parseIdFromSlug(slug) {
  const s = String(slug ?? "");
  const m = s.match(/^(\d+)-/);
  return m ? Number(m[1]) : null;
}

async function getProducts() {
  try {
    const res = await fetch(`${CMS_URL.replace(/\/$/, "")}/api/products`, {
      cache: "no-store",
    });
    const json = await res.json();
    return Array.isArray(json?.data) ? json.data : [];
  } catch {
    return [];
  }
}

function formatRupiah(value) {
  const n = Number(value ?? 0);
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(n);
}

export default async function MarketplaceDetailPage({ params }) {
  const id = parseIdFromSlug(params?.slug);
  const products = await getProducts();
  const product = id ? products.find((p) => Number(p.id) === id) : null;

  if (!product) notFound();

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-6 py-12">
      <div className="flex items-center justify-between gap-4">
        <div>
          <div className="text-xs font-semibold text-slate-600">Marketplace</div>
          <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
            {product.title}
          </h1>
        </div>
        <Link
          href="/marketplace"
          className="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50"
        >
          Kembali
        </Link>
      </div>

      <div className="mt-8 grid gap-8 md:grid-cols-2">
        <div className="overflow-hidden rounded-[2rem] border border-slate-200 bg-white/85 shadow-sm">
          <div className="aspect-[4/3] w-full bg-slate-100">
            <img
              src={product.image}
              alt={product.title}
              className="h-full w-full object-cover"
              loading="lazy"
            />
          </div>
        </div>

        <div className="rounded-[2rem] border border-slate-200 bg-white/85 p-7 shadow-sm">
          <div className="flex items-start justify-between gap-4">
            <div>
              <div className="text-sm font-semibold text-slate-900">
                {formatRupiah(product.price)}
              </div>
              <div className="mt-1 text-xs text-slate-600">
                Lokasi: {product.location ?? "-"} • Rating:{" "}
                {Number(product.rating ?? 0).toFixed(1)}
              </div>
            </div>
            <div className="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
              ID {product.id}
            </div>
          </div>

          <p className="mt-5 text-sm leading-7 text-slate-700">
            Deskripsi produk akan diambil dari CMS. Untuk sekarang, halaman ini
            menampilkan detail dasar (gambar, harga, lokasi, rating).
          </p>

          <div className="mt-6 flex flex-col gap-3 sm:flex-row">
            <Link
              href="/cart"
              className="inline-flex h-12 flex-1 items-center justify-center rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
            >
              Tambah ke Keranjang
            </Link>
            <Link
              href="/checkout"
              className="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50"
            >
              Checkout
            </Link>
          </div>
        </div>
      </div>
    </main>
  );
}

