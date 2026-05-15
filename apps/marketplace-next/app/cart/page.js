"use client";

import Link from "next/link";

export default function CartPage() {
  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-6 py-12">
      <section className="rounded-[2.5rem] border border-slate-200 bg-white/75 p-8 shadow-sm backdrop-blur">
        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
          Keranjang
        </h1>
        <p className="mt-2 text-sm text-slate-600">
          Keranjang disiapkan untuk alur checkout dalam satu frontend.
        </p>

        <div className="mt-8 flex flex-col gap-3 sm:flex-row">
          <Link
            href="/marketplace"
            className="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50"
          >
            Lanjut Belanja
          </Link>
          <Link
            href="/checkout"
            className="inline-flex h-12 items-center justify-center rounded-full bg-slate-900 px-6 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
          >
            Checkout
          </Link>
        </div>
      </section>
    </main>
  );
}

