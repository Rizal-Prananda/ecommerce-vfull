import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import "swiper/css";
import "swiper/css/navigation";
import { AppShell } from "@/components/app-shell";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata = {
  title: "Rizal Studio",
  description: "Company profile + marketplace dalam 1 frontend",
};

export default function RootLayout({ children }) {
  return (
    <html lang="id" className={`${geistSans.variable} ${geistMono.variable} h-full antialiased`}>
      <body className="min-h-full flex flex-col bg-transparent">
        <AppShell>
          <div className="flex-1">{children}</div>
        </AppShell>
      </body>
    </html>
  );
}
