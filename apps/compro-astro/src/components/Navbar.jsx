"use client";

import { useCallback, useMemo, useState } from "react";

import { Button } from "./ui/button";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "./ui/dialog";
import { Input } from "./ui/input";
import { Label } from "./ui/label";

function RequiredMark() {
  return <span className="text-red-500">*</span>;
}

export default function Navbar() {
  const [open, setOpen] = useState(false);
  const [mode, setMode] = useState("login");

  const title = useMemo(() => (mode === "login" ? "Login" : "Daftar Akun"), [mode]);

  const description = useMemo(
    () =>
      mode === "login"
        ? "Masuk untuk melanjutkan."
        : "Lengkapi data untuk membuat akun baru.",
    [mode]
  );

  const openLogin = useCallback(() => {
    setMode("login");
    setOpen(true);
  }, []);

  const openRegister = useCallback(() => {
    setMode("register");
    setOpen(true);
  }, []);

  const handleLoginSubmit = useCallback((e) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    formData.get("email");
    formData.get("password");
    e.currentTarget.reset();
    setOpen(false);
  }, []);

  const handleRegisterSubmit = useCallback((e) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    formData.get("name");
    formData.get("email");
    formData.get("phone");
    formData.get("address");
    formData.get("password");
    e.currentTarget.reset();
    setOpen(false);
  }, []);

  return (
    <>
      <header className="sticky top-0 z-50 border-b border-slate-200/70 bg-white/75 backdrop-blur">
        <div className="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-3">
          <a href="#home" className="flex items-center gap-2 font-semibold tracking-tight">
            <span className="grid size-9 place-items-center rounded-2xl bg-blue-600 text-white shadow-sm">
              R
            </span>
            <span className="text-slate-900">Rizal Studio</span>
          </a>

          <nav className="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
            <a href="#home" className="hover:text-slate-900">
              Home
            </a>
            <a href="#about" className="hover:text-slate-900">
              About
            </a>
            <a href="#testimoni" className="hover:text-slate-900">
              Testimoni
            </a>
            <a href="#rekomendasi" className="hover:text-slate-900">
              Rekomendasi
            </a>
            <a
              href="http://localhost:3000"
              className="hover:text-slate-900"
            >
              Marketplace
            </a>
          </nav>

          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={openLogin}>
              Login
            </Button>
            <Button variant="default" onClick={openRegister}>
              Daftar
            </Button>
          </div>
        </div>
      </header>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent className="p-0">
          <div className="rounded-2xl bg-[radial-gradient(900px_500px_at_20%_15%,rgba(59,130,246,0.14),transparent_60%),radial-gradient(900px_500px_at_80%_10%,rgba(139,92,246,0.12),transparent_55%),radial-gradient(900px_500px_at_60%_85%,rgba(245,158,11,0.12),transparent_55%)] p-6">
            <div className="flex items-start justify-between gap-4">
              <DialogHeader>
                <DialogTitle>{title}</DialogTitle>
                <DialogDescription>{description}</DialogDescription>
              </DialogHeader>
              <DialogClose asChild>
                <button
                  type="button"
                  className="grid size-10 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50"
                  aria-label="Tutup"
                >
                  ×
                </button>
              </DialogClose>
            </div>

            <div className="mt-6 rounded-2xl border border-slate-200 bg-white/85 p-6 shadow-sm backdrop-blur">
              {mode === "login" ? (
                <form onSubmit={handleLoginSubmit} className="space-y-5">
                  <div className="space-y-2">
                    <Label htmlFor="login-email">
                      Alamat Email <RequiredMark />
                    </Label>
                    <Input
                      id="login-email"
                      name="email"
                      type="email"
                      required
                      placeholder="nama@email.com"
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="login-password">
                      Password <RequiredMark />
                    </Label>
                    <Input
                      id="login-password"
                      name="password"
                      type="password"
                      required
                      minLength={8}
                      placeholder="Minimal 8 karakter"
                    />
                  </div>

                  <Button type="submit" size="lg" rounded="xl" className="w-full bg-blue-600 hover:bg-blue-700">
                    Login
                  </Button>

                  <div className="text-center text-sm text-slate-600">
                    Belum punya akun?{" "}
                    <button
                      type="button"
                      onClick={() => setMode("register")}
                      className="font-semibold text-slate-900 underline underline-offset-4 hover:text-slate-700"
                    >
                      Daftar di sini
                    </button>
                  </div>
                </form>
              ) : (
                <form onSubmit={handleRegisterSubmit} className="space-y-5">
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div className="space-y-2 sm:col-span-2">
                      <Label htmlFor="reg-name">
                        Nama Lengkap <RequiredMark />
                      </Label>
                      <Input id="reg-name" name="name" type="text" required placeholder="Nama lengkap" />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="reg-email">
                        Alamat Email <RequiredMark />
                      </Label>
                      <Input
                        id="reg-email"
                        name="email"
                        type="email"
                        required
                        placeholder="nama@email.com"
                      />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="reg-phone">
                        No Telepon <RequiredMark />
                      </Label>
                      <Input
                        id="reg-phone"
                        name="phone"
                        type="tel"
                        required
                        pattern="[0-9]{10,13}"
                        placeholder="081234567890"
                      />
                    </div>

                    <div className="space-y-2 sm:col-span-2">
                      <Label htmlFor="reg-address">
                        Alamat Lengkap <RequiredMark />
                      </Label>
                      <Input
                        id="reg-address"
                        name="address"
                        type="text"
                        required
                        placeholder="Alamat lengkap"
                      />
                    </div>

                    <div className="space-y-2 sm:col-span-2">
                      <Label htmlFor="reg-password">
                        Password <RequiredMark />
                      </Label>
                      <Input
                        id="reg-password"
                        name="password"
                        type="password"
                        required
                        minLength={8}
                        placeholder="Minimal 8 karakter"
                      />
                    </div>
                  </div>

                  <Button type="submit" size="lg" rounded="xl" className="w-full">
                    Daftar Sekarang
                  </Button>

                  <div className="text-center text-sm text-slate-600">
                    Sudah punya akun?{" "}
                    <button
                      type="button"
                      onClick={() => setMode("login")}
                      className="font-semibold text-slate-900 underline underline-offset-4 hover:text-slate-700"
                    >
                      Login di sini
                    </button>
                  </div>
                </form>
              )}
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </>
  );
}
