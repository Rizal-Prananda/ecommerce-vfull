"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { Swiper, SwiperSlide } from "swiper/react";
import { Navigation } from "swiper/modules";
import { ChevronLeft, ChevronRight, Clock, MapPin, Plus, Search, Star, X } from "lucide-react";

const mockProducts = {
  "nasi & ayam": [
    {
      id: "na-1",
      name: "Nasi Ayam Geprek Sambal Matah Pedas Level 3",
      price: 22000,
      rating: 4.8,
      reviews: 1200,
      distance: "1.2 km",
      time: "20-30 mnt",
      image: "https://picsum.photos/seed/na-1/800/800",
      promo: true,
      bestSeller: false,
    },
    {
      id: "na-2",
      name: "Nasi Ayam Bakar Madu + Lalapan",
      price: 26000,
      rating: 4.7,
      reviews: 860,
      distance: "2.1 km",
      time: "25-35 mnt",
      image: "https://picsum.photos/seed/na-2/800/800",
      promo: false,
      bestSeller: true,
    },
    {
      id: "na-3",
      name: "Nasi Ayam Crispy Saus BBQ",
      price: 24000,
      rating: 4.6,
      reviews: 540,
      distance: "0.9 km",
      time: "15-25 mnt",
      image: "https://picsum.photos/seed/na-3/800/800",
      promo: false,
      bestSeller: false,
    },
    {
      id: "na-4",
      name: "Nasi Ayam Katsu Curry",
      price: 29000,
      rating: 4.8,
      reviews: 430,
      distance: "3.4 km",
      time: "30-40 mnt",
      image: "https://picsum.photos/seed/na-4/800/800",
      promo: true,
      bestSeller: false,
    },
    {
      id: "na-5",
      name: "Nasi Ayam Teriyaki + Telur",
      price: 27000,
      rating: 4.7,
      reviews: 710,
      distance: "2.6 km",
      time: "25-35 mnt",
      image: "https://picsum.photos/seed/na-5/800/800",
      promo: false,
      bestSeller: true,
    },
    {
      id: "na-6",
      name: "Nasi Ayam Rica-Rica",
      price: 25000,
      rating: 4.5,
      reviews: 320,
      distance: "1.8 km",
      time: "20-30 mnt",
      image: "https://picsum.photos/seed/na-6/800/800",
      promo: false,
      bestSeller: false,
    },
  ],
  "bakso & mie": [
    {
      id: "bm-1",
      name: "Bakso Urat Komplit + Pangsit",
      price: 23000,
      rating: 4.7,
      reviews: 980,
      distance: "1.5 km",
      time: "20-30 mnt",
      image: "https://picsum.photos/seed/bm-1/800/800",
      promo: true,
      bestSeller: true,
    },
    {
      id: "bm-2",
      name: "Mie Ayam Jamur Spesial",
      price: 18000,
      rating: 4.6,
      reviews: 640,
      distance: "2.9 km",
      time: "25-35 mnt",
      image: "https://picsum.photos/seed/bm-2/800/800",
      promo: false,
      bestSeller: false,
    },
    {
      id: "bm-3",
      name: "Mie Pedas Level 5 + Baso",
      price: 20000,
      rating: 4.5,
      reviews: 410,
      distance: "0.7 km",
      time: "15-25 mnt",
      image: "https://picsum.photos/seed/bm-3/800/800",
      promo: false,
      bestSeller: true,
    },
    {
      id: "bm-4",
      name: "Bakso Kuah Kaldu Sapi",
      price: 21000,
      rating: 4.6,
      reviews: 520,
      distance: "1.1 km",
      time: "20-30 mnt",
      image: "https://picsum.photos/seed/bm-4/800/800",
      promo: true,
      bestSeller: false,
    },
    {
      id: "bm-5",
      name: "Mie Goreng Jawa + Telur",
      price: 24000,
      rating: 4.7,
      reviews: 390,
      distance: "3.2 km",
      time: "30-40 mnt",
      image: "https://picsum.photos/seed/bm-5/800/800",
      promo: false,
      bestSeller: false,
    },
    {
      id: "bm-6",
      name: "Mie Kuah Seafood",
      price: 28000,
      rating: 4.6,
      reviews: 210,
      distance: "4.0 km",
      time: "35-45 mnt",
      image: "https://picsum.photos/seed/bm-6/800/800",
      promo: false,
      bestSeller: false,
    },
  ],
  minuman: [
    {
      id: "mn-1",
      name: "Es Kopi Susu Gula Aren",
      price: 18000,
      rating: 4.8,
      reviews: 2300,
      distance: "1.0 km",
      time: "10-20 mnt",
      image: "https://picsum.photos/seed/mn-1/800/800",
      promo: true,
      bestSeller: true,
    },
    {
      id: "mn-2",
      name: "Matcha Latte Premium",
      price: 24000,
      rating: 4.7,
      reviews: 980,
      distance: "2.2 km",
      time: "15-25 mnt",
      image: "https://picsum.photos/seed/mn-2/800/800",
      promo: false,
      bestSeller: false,
    },
    {
      id: "mn-3",
      name: "Thai Tea Creamy",
      price: 20000,
      rating: 4.6,
      reviews: 670,
      distance: "1.6 km",
      time: "15-25 mnt",
      image: "https://picsum.photos/seed/mn-3/800/800",
      promo: false,
      bestSeller: true,
    },
    {
      id: "mn-4",
      name: "Lemon Tea Segar",
      price: 16000,
      rating: 4.5,
      reviews: 430,
      distance: "0.8 km",
      time: "10-20 mnt",
      image: "https://picsum.photos/seed/mn-4/800/800",
      promo: true,
      bestSeller: false,
    },
    {
      id: "mn-5",
      name: "Chocolate Milkshake",
      price: 26000,
      rating: 4.7,
      reviews: 520,
      distance: "3.0 km",
      time: "20-30 mnt",
      image: "https://picsum.photos/seed/mn-5/800/800",
      promo: false,
      bestSeller: false,
    },
    {
      id: "mn-6",
      name: "Jus Alpukat Cokelat",
      price: 23000,
      rating: 4.6,
      reviews: 310,
      distance: "2.7 km",
      time: "20-30 mnt",
      image: "https://picsum.photos/seed/mn-6/800/800",
      promo: false,
      bestSeller: true,
    },
  ],
  snack: [
    {
      id: "sn-1",
      name: "Kentang Goreng Crispy",
      price: 15000,
      rating: 4.6,
      reviews: 740,
      distance: "1.3 km",
      time: "10-20 mnt",
      image: "https://picsum.photos/seed/sn-1/800/800",
      promo: false,
      bestSeller: true,
    },
    {
      id: "sn-2",
      name: "Dimsum Ayam 6pcs",
      price: 21000,
      rating: 4.7,
      reviews: 560,
      distance: "2.0 km",
      time: "15-25 mnt",
      image: "https://picsum.photos/seed/sn-2/800/800",
      promo: true,
      bestSeller: false,
    },
    {
      id: "sn-3",
      name: "Cireng Isi Ayam Pedas",
      price: 14000,
      rating: 4.5,
      reviews: 330,
      distance: "0.6 km",
      time: "10-20 mnt",
      image: "https://picsum.photos/seed/sn-3/800/800",
      promo: false,
      bestSeller: false,
    },
    {
      id: "sn-4",
      name: "Sosis Bakar Jumbo",
      price: 17000,
      rating: 4.6,
      reviews: 420,
      distance: "1.9 km",
      time: "15-25 mnt",
      image: "https://picsum.photos/seed/sn-4/800/800",
      promo: false,
      bestSeller: true,
    },
    {
      id: "sn-5",
      name: "Tahu Crispy Sambal",
      price: 12000,
      rating: 4.4,
      reviews: 290,
      distance: "2.5 km",
      time: "15-25 mnt",
      image: "https://picsum.photos/seed/sn-5/800/800",
      promo: true,
      bestSeller: false,
    },
    {
      id: "sn-6",
      name: "Pisang Nugget Cokelat Keju",
      price: 19000,
      rating: 4.7,
      reviews: 510,
      distance: "3.6 km",
      time: "25-35 mnt",
      image: "https://picsum.photos/seed/sn-6/800/800",
      promo: false,
      bestSeller: false,
    },
  ],
};

function formatRupiah(value) {
  const n = Number(value ?? 0);
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(n);
}

function parseKm(distance) {
  const n = Number.parseFloat(String(distance ?? "").replace(",", "."));
  return Number.isFinite(n) ? n : Number.POSITIVE_INFINITY;
}

function parseMinutes(time) {
  const s = String(time ?? "");
  const m = s.match(/(\d+)/);
  const n = m ? Number.parseInt(m[1], 10) : Number.POSITIVE_INFINITY;
  return Number.isFinite(n) ? n : Number.POSITIVE_INFINITY;
}

function ProductCard({ product }) {
  return (
    <motion.div
      layout
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.9 }}
      transition={{ duration: 0.25 }}
      className="group h-full rounded-2xl border border-gray-100 bg-white transition duration-300 hover:-translate-y-1 hover:shadow-xl"
    >
      <div className="relative overflow-hidden rounded-t-2xl">
        <div className="aspect-square w-full bg-gray-50">
          <img src={product.image} alt={product.name} className="h-full w-full object-cover transition duration-300 group-hover:scale-110" loading="lazy" />
        </div>

        {product.promo ? (
          <div className="absolute left-3 top-3 rounded-full bg-red-500 px-3 py-1 text-xs font-semibold text-white shadow-sm">Promo</div>
        ) : product.bestSeller ? (
          <div className="absolute left-3 top-3 rounded-full bg-orange-500 px-3 py-1 text-xs font-semibold text-white shadow-sm">Terlaris</div>
        ) : null}
      </div>

      <div className="flex flex-1 flex-col gap-3 p-4">
        <div
          className="min-h-[40px] text-sm font-semibold text-gray-900"
          style={{
            display: "-webkit-box",
            WebkitLineClamp: 2,
            WebkitBoxOrient: "vertical",
            overflow: "hidden",
          }}
        >
          {product.name}
        </div>

        <div className="flex items-center gap-2 text-xs text-gray-600">
          <div className="flex items-center gap-1">
            <Star className="size-4 text-yellow-400" fill="currentColor" />
            <span className="font-semibold text-gray-900">{Number(product.rating).toFixed(1)}</span>
          </div>
          <span className="text-gray-400">•</span>
          <span>({product.reviews})</span>
        </div>

        <div className="flex items-center gap-3 text-xs text-gray-600">
          <div className="flex items-center gap-1">
            <MapPin className="size-4" />
            <span>{product.distance}</span>
          </div>
          <div className="flex items-center gap-1">
            <Clock className="size-4" />
            <span>{product.time}</span>
          </div>
        </div>

        <div className="mt-auto flex items-center justify-between gap-3">
          <div className="text-sm font-bold text-gray-900">{formatRupiah(product.price)}</div>
          <button type="button" className="inline-flex size-10 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm transition duration-300 hover:bg-blue-700" aria-label="Tambah">
            <Plus className="size-5" />
          </button>
        </div>
      </div>
    </motion.div>
  );
}

function CategoryCarousel({ title, products }) {
  const prevRef = useRef(null);
  const nextRef = useRef(null);
  const swiperRef = useRef(null);

  useEffect(() => {
    if (!swiperRef.current || !prevRef.current || !nextRef.current) return;
    const swiper = swiperRef.current;
    swiper.params.navigation.prevEl = prevRef.current;
    swiper.params.navigation.nextEl = nextRef.current;
    swiper.navigation.init();
    swiper.navigation.update();
  }, []);

  return (
    <motion.section initial={{ opacity: 0, y: 8 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true, margin: "-80px" }} transition={{ duration: 0.35 }} className="group">
      <div className="mb-4 flex items-center justify-between gap-4">
        <h2 className="text-lg font-bold text-gray-900">{String(title).replace(/\b\w/g, (c) => c.toUpperCase())}</h2>
        <div className="flex items-center gap-2 opacity-0 pointer-events-none transition duration-300 group-hover:opacity-100 group-hover:pointer-events-auto">
          <button ref={prevRef} type="button" className="inline-flex size-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-900 transition duration-300 hover:bg-gray-50" aria-label="Sebelumnya">
            <ChevronLeft className="size-5" />
          </button>
          <button ref={nextRef} type="button" className="inline-flex size-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-900 transition duration-300 hover:bg-gray-50" aria-label="Berikutnya">
            <ChevronRight className="size-5" />
          </button>
        </div>
      </div>

      <div className="rounded-2xl border border-gray-100 bg-white p-4 transition duration-300">
        <Swiper
          modules={[Navigation]}
          onSwiper={(swiper) => {
            swiperRef.current = swiper;
          }}
          spaceBetween={16}
          slidesPerView={2.3}
          breakpoints={{
            640: { slidesPerView: 3.3 },
            768: { slidesPerView: 4.3 },
            1024: { slidesPerView: 5.3 },
            1280: { slidesPerView: 6.3 },
          }}
          navigation
        >
          {products.map((p) => (
            <SwiperSlide key={p.id} className="h-auto">
              <ProductCard product={p} />
            </SwiperSlide>
          ))}
        </Swiper>
      </div>
    </motion.section>
  );
}

export default function MarketplaceMakananPage() {
  const [searchQuery, setSearchQuery] = useState("");
  const [activeFilters, setActiveFilters] = useState([]);
  const [sortBy, setSortBy] = useState("terlaris");

  const filterOptions = useMemo(
    () => [
      { key: "rating45", label: "Rating 4.5+" },
      { key: "promo", label: "Promo" },
      { key: "lt2km", label: "< 2 km" },
      { key: "lt20rb", label: "< 20rb" },
      { key: "lt20min", label: "< 20 menit" },
    ],
    [],
  );

  const allProducts = useMemo(() => {
    return Object.entries(mockProducts).flatMap(([category, items]) => items.map((p) => ({ ...p, category })));
  }, []);

  const filteredProducts = useMemo(() => {
    const q = searchQuery.trim().toLowerCase();

    const filtered = allProducts.filter((p) => {
      const matchQuery = q
        ? String(p.name ?? "")
            .toLowerCase()
            .includes(q)
        : true;

      const byRating = !activeFilters.includes("rating45") || Number(p.rating ?? 0) >= 4.5;
      const byPromo = !activeFilters.includes("promo") || p.promo === true;
      const byDistance = !activeFilters.includes("lt2km") || parseKm(p.distance) < 2;
      const byPrice = !activeFilters.includes("lt20rb") || Number(p.price ?? 0) < 20000;
      const byTime = !activeFilters.includes("lt20min") || parseMinutes(p.time) < 20;

      return matchQuery && byRating && byPromo && byDistance && byPrice && byTime;
    });

    const sorted = [...filtered].sort((a, b) => {
      if (sortBy === "rating") return Number(b.rating ?? 0) - Number(a.rating ?? 0);
      if (sortBy === "harga") return Number(a.price ?? 0) - Number(b.price ?? 0);
      return Number(b.reviews ?? 0) - Number(a.reviews ?? 0);
    });

    return sorted;
  }, [allProducts, searchQuery, activeFilters, sortBy]);

  const groupedEntries = useMemo(() => {
    const grouped = filteredProducts.reduce((acc, p) => {
      const key = p.category;
      acc[key] = acc[key] ?? [];
      acc[key].push(p);
      return acc;
    }, {});

    const order = Object.keys(mockProducts);
    return order.map((key) => [key, grouped[key]]).filter(([, items]) => Array.isArray(items) && items.length);
  }, [filteredProducts]);

  function toggleFilter(key) {
    setActiveFilters((prev) => (prev.includes(key) ? prev.filter((x) => x !== key) : [...prev, key]));
  }

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-6 py-10">
      <section className="rounded-2xl border border-gray-100 bg-white p-6 transition duration-300">
        <div className="flex flex-col gap-4">
          <div className="inline-flex w-fit items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700">Marketplace Makanan</div>
          <div className="flex flex-col gap-2">
            <h1 className="text-2xl font-bold text-gray-900 md:text-3xl">Mau makan apa hari ini?</h1>
            <p className="text-sm text-gray-600">Cari menu favorit, pakai filter cepat, lalu geser per kategori.</p>
          </div>

          <div className="grid gap-3 md:grid-cols-[1fr_auto] md:items-center">
            <div className="relative">
              <Search className="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-gray-400" />
              <input
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Cari makanan, minuman, snack..."
                className="h-12 w-full rounded-2xl border border-gray-200 bg-white pl-12 pr-12 text-sm text-gray-900 shadow-sm outline-none transition duration-300 focus:border-blue-300 focus:ring-4 focus:ring-blue-100"
              />
              {searchQuery ? (
                <button
                  type="button"
                  onClick={() => setSearchQuery("")}
                  className="absolute right-3 top-1/2 inline-flex size-9 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 transition duration-300 hover:bg-gray-50"
                  aria-label="Clear"
                >
                  <X className="size-4" />
                </button>
              ) : null}
            </div>

            <div className="flex items-center justify-between gap-3 md:justify-end">
              <label className="text-sm font-semibold text-gray-700">Sort</label>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                className="h-12 rounded-2xl border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-900 shadow-sm outline-none transition duration-300 focus:border-blue-300 focus:ring-4 focus:ring-blue-100"
              >
                <option value="terlaris">Terlaris</option>
                <option value="rating">Rating Tertinggi</option>
                <option value="harga">Harga Terendah</option>
              </select>
            </div>
          </div>

          <div className="flex flex-wrap gap-2">
            {filterOptions.map((f) => {
              const active = activeFilters.includes(f.key);
              return (
                <button
                  key={f.key}
                  type="button"
                  onClick={() => toggleFilter(f.key)}
                  className={`rounded-full px-4 py-2 text-xs font-semibold transition duration-300 ${active ? "bg-gray-900 text-white" : "border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"}`}
                >
                  {f.label}
                </button>
              );
            })}
          </div>
        </div>
      </section>

      <div className="mt-8 grid gap-8">
        <AnimatePresence mode="wait">
          {groupedEntries.length ? (
            <motion.div key="list" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} transition={{ duration: 0.2 }} className="grid gap-8">
              {groupedEntries.map(([categoryName, products]) => (
                <CategoryCarousel key={categoryName} title={categoryName} products={products} />
              ))}
            </motion.div>
          ) : (
            <motion.div key="empty" initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: 10 }} transition={{ duration: 0.25 }} className="rounded-2xl border border-gray-100 bg-white p-10 text-center">
              <div className="text-lg font-bold text-gray-900">Makanan nggak ketemu</div>
              <div className="mt-2 text-sm text-gray-600">Coba ganti keyword atau reset filter.</div>
              <button
                type="button"
                onClick={() => {
                  setSearchQuery("");
                  setActiveFilters([]);
                }}
                className="mt-6 inline-flex h-11 items-center justify-center rounded-2xl bg-gray-900 px-6 text-sm font-semibold text-white transition duration-300 hover:bg-gray-800"
              >
                Reset
              </button>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </main>
  );
}
