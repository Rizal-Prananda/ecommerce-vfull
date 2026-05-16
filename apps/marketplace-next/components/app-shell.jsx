"use client";

import { usePathname } from "next/navigation";

import { Navbar } from "@/components/navbar";

export function AppShell({ children }) {
  const pathname = usePathname();
  const hideNavbar =
    pathname === "/login" ||
    pathname.startsWith("/dashboard") ||
    pathname.startsWith("/products");

  if (hideNavbar) return children;

  return (
    <>
      <Navbar />
      <div className="pt-24">{children}</div>
    </>
  );
}
