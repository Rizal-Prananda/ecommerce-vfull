"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useMemo, useState } from "react";
import { motion } from "framer-motion";

import { Button } from "@/components/ui/button";

export function Navbar() {
  const pathname = usePathname();
  const [scrolled, setScrolled] = useState(false);
  const [hash, setHash] = useState("");

  useEffect(() => {
    const update = () => setScrolled(window.scrollY > 20);
    update();
    window.addEventListener("scroll", update, { passive: true });
    return () => window.removeEventListener("scroll", update);
  }, []);

  useEffect(() => {
    const syncHash = () => setHash(window.location.hash || "");
    syncHash();
    window.addEventListener("hashchange", syncHash);
    return () => window.removeEventListener("hashchange", syncHash);
  }, []);

  const items = useMemo(
    () => [
      { label: "Home", href: "/#home", pathname: "/", hash: "#home" },
      { label: "About", href: "/#about", pathname: "/", hash: "#about" },
      { label: "Testimoni", href: "/#testimoni", pathname: "/", hash: "#testimoni" },
      { label: "Rekomendasi", href: "/#rekomendasi", pathname: "/", hash: "#rekomendasi" },
      { label: "Marketplace", href: "/marketplace", pathname: "/marketplace" },
    ],
    [],
  );

  return (
    <motion.header
      initial={{ y: -100, opacity: 0 }}
      animate={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.5 }}
      className={`fixed top-0 z-50 w-full ${scrolled ? "bg-white/70 backdrop-blur-xl border-b border-gray-200/50 shadow-sm" : "bg-transparent"}`}
    >
      <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <Link href="/" className="flex items-center gap-3">
          <motion.div
            whileHover={{ scale: 1.05, rotate: [0, -10, 10, -10, 0] }}
            transition={{ duration: 0.6 }}
            className="grid size-9 place-items-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30"
          >
            R
          </motion.div>
          <span className="font-bold text-lg text-slate-900">Rizal Studio</span>
        </Link>

        <nav className="hidden md:flex items-center gap-10">
          {items.map((item) => {
            const isMarketplace = item.pathname === "/marketplace";
            const isActivePath = isMarketplace ? pathname.startsWith("/marketplace") : pathname === item.pathname;
            const currentHash = hash || "#home";
            const isActiveHash = item.pathname === "/" && item.hash ? currentHash === item.hash : true;
            const active = item.pathname === "/" ? isActivePath && isActiveHash : isActivePath;
            return (
              <div key={item.label} className="relative">
                {active ? <motion.div layoutId="active-pill" className="absolute -inset-x-3 -inset-y-2 rounded-lg bg-blue-50" transition={{ type: "spring", stiffness: 350, damping: 30 }} /> : null}
                <Link href={item.href} className={`group relative font-medium text-gray-700 hover:text-black ${active ? "text-blue-600" : ""}`}>
                  <span className="relative inline-block">
                    {item.label}
                    <span className={`absolute left-0 -bottom-1 h-0.5 bg-blue-600 transition-all duration-300 ${active ? "w-full" : "w-0"} group-hover:w-full`} />
                  </span>
                </Link>
              </div>
            );
          })}
        </nav>

        <div className="flex items-center gap-2">
          <Button asChild variant="ghost" rounded="xl" className="font-semibold">
            <Link href="/login">Login</Link>
          </Button>
          <motion.div whileHover={{ scale: 1.05 }} transition={{ duration: 0.2 }}>
            <Button asChild rounded="xl" className="bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30 hover:shadow-xl">
              <Link href="/register">Daftar</Link>
            </Button>
          </motion.div>
        </div>
      </div>
    </motion.header>
  );
}
