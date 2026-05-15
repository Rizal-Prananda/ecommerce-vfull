"use client";

import Link from "next/link";

export default function CheckoutPage() {
  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-6 py-12">
      <section className="rounded-[2.5rem] border border-slate-200 bg-white/75 p-8 shadow-sm backdrop-blur">
        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
          Checkout
        </h1>
        <p className="mt-2 text-sm text-slate-600">
          Halaman checkout akan menggunakan session user yang sama (login sekali).
        </p>

        <div className="mt-8 flex flex-col gap-3 sm:flex-row">
          <Link
            href="/login"
            className="inline-flex h-12 items-center justify-center rounded-full bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
          >
            Login untuk Checkout
          </Link>
          <Link
            href="/cart"
            className="inline-flex h-12 items-center justify-center rounded-full border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-50"
          >
            Kembali ke Keranjang
          </Link>
        </div>
      </section>
    </main>
  );
}

