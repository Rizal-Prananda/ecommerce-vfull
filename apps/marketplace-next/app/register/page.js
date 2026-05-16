"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

function RequiredMark() {
  return <span className="text-red-500">*</span>;
}

export default function RegisterPage() {
  const router = useRouter();
  const [error, setError] = useState("");
  const [pending, setPending] = useState(false);

  async function onSubmit(e) {
    e.preventDefault();
    setError("");
    setPending(true);
    try {
      const formData = new FormData(e.currentTarget);
      const payload = {
        name: String(formData.get("name") ?? ""),
        email: String(formData.get("email") ?? ""),
        phone: String(formData.get("phone") ?? ""),
        address: String(formData.get("address") ?? ""),
        password: String(formData.get("password") ?? ""),
      };
      const res = await fetch("/api/auth/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) {
        setError(String(json?.error ?? "Registrasi gagal."));
        return;
      }
      router.push("/dashboard");
      router.refresh();
    } finally {
      setPending(false);
    }
  }

  return (
    <main className="mx-auto w-full max-w-2xl flex-1 px-6 py-12">
      <section className="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white/75 shadow-sm backdrop-blur">
        <div className="bg-gradient-to-br from-blue-50 via-indigo-50 to-white p-8">
          <h1 className="text-3xl font-bold tracking-tight text-slate-900">
            Register
          </h1>
          <p className="mt-2 text-sm text-slate-600">
            Buat akun untuk akses landing + marketplace (login sekali).
          </p>
        </div>

        <div className="bg-white p-8">
          <form onSubmit={onSubmit} className="space-y-5">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2 md:col-span-2">
                <Label htmlFor="name" className="font-semibold">
                  Nama Lengkap <RequiredMark />
                </Label>
                <Input
                  id="name"
                  name="name"
                  type="text"
                  required
                  className="h-12 rounded-xl focus-visible:ring-4 focus-visible:ring-blue-100"
                  placeholder="Nama lengkap"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="email" className="font-semibold">
                  Email <RequiredMark />
                </Label>
                <Input
                  id="email"
                  name="email"
                  type="email"
                  required
                  className="h-12 rounded-xl focus-visible:ring-4 focus-visible:ring-blue-100"
                  placeholder="nama@email.com"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="phone" className="font-semibold">
                  No Telepon <RequiredMark />
                </Label>
                <Input
                  id="phone"
                  name="phone"
                  type="tel"
                  required
                  pattern="[0-9]{10,13}"
                  className="h-12 rounded-xl focus-visible:ring-4 focus-visible:ring-blue-100"
                  placeholder="081234567890"
                />
              </div>

              <div className="space-y-2 md:col-span-2">
                <Label htmlFor="address" className="font-semibold">
                  Alamat Lengkap <RequiredMark />
                </Label>
                <Input
                  id="address"
                  name="address"
                  type="text"
                  required
                  className="h-12 rounded-xl focus-visible:ring-4 focus-visible:ring-blue-100"
                  placeholder="Alamat lengkap"
                />
              </div>

              <div className="space-y-2 md:col-span-2">
                <Label htmlFor="password" className="font-semibold">
                  Password <RequiredMark />
                </Label>
                <Input
                  id="password"
                  name="password"
                  type="password"
                  required
                  minLength={8}
                  className="h-12 rounded-xl focus-visible:ring-4 focus-visible:ring-blue-100"
                  placeholder="Minimal 8 karakter"
                />
              </div>
            </div>

            {error ? (
              <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {error}
              </div>
            ) : null}

            <Button
              type="submit"
              disabled={pending}
              className="h-12 w-full rounded-xl bg-gray-900 text-white shadow-sm hover:bg-gray-800"
            >
              {pending ? "Memproses..." : "Daftar Sekarang"}
            </Button>

            <div className="text-center text-sm text-slate-600">
              Sudah punya akun?{" "}
              <a href="/login" className="font-semibold text-slate-900 hover:underline">
                Login di sini
              </a>
            </div>
          </form>
        </div>
      </section>
    </main>
  );
}

