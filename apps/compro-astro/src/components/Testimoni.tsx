import { useEffect, useState } from 'react';
import { Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import 'swiper/css/pagination';

type Testimonial = {
  id: string | number;
  name: string;
  avatar: string;
  rating: number;
  comment: string;
  product_name: string;
  is_verified: boolean;
};

const fallbackTestimonials: Testimonial[] = [
  {
    id: '1',
    name: 'Alya Putri',
    avatar: 'https://ui-avatars.com/api/?name=Alya+Putri&background=F59E0B&color=ffffff&size=128',
    rating: 5,
    comment: 'Baju-nya nyaman dipakai seharian, jahitannya rapi dan finish-nya sangat premium. Pastinya saya balik lagi belanja di sini.',
    product_name: 'Midi Dress Satin',
    is_verified: true,
  },
  {
    id: '2',
    name: 'Hendra Wijaya',
    avatar: 'https://ui-avatars.com/api/?name=Hendra+Wijaya&background=F59E0B&color=ffffff&size=128',
    rating: 5,
    comment: 'Detail produknya detail, warna aslinya lebih bagus lagi daripada foto. Pengiriman cepat dan packaging-nya rapi.',
    product_name: 'Oversized Tee Lurus',
    is_verified: true,
  },
  {
    id: '3',
    name: 'Maya Kurnia',
    avatar: 'https://ui-avatars.com/api/?name=Maya+Kurnia&background=F59E0B&color=ffffff&size=128',
    rating: 5,
    comment: 'Model fashion-nya kekinian, cocok untuk acara santai atau hangout. Saya suka banget kualitas kainnya.',
    product_name: 'Blouse Puff Sleeve',
    is_verified: true,
  },
];

const normalizeTestimonial = (item: any): Testimonial => {
  const name = String(item?.name ?? item?.customer_name ?? item?.full_name ?? 'Customer').trim();
  const avatarRaw = String(item?.avatar ?? item?.photo ?? item?.image ?? '').trim();
  const avatar =
    avatarRaw !== ''
      ? avatarRaw
      : `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=F59E0B&color=ffffff&size=128`;
  const rating = Number(item?.rating ?? item?.stars ?? 5) || 5;
  const comment = String(item?.comment ?? item?.message ?? item?.testimonial ?? '').trim() || 'Produk ini sangat memuaskan dan sesuai ekspektasi.';
  const product_name = String(item?.product_name ?? item?.product ?? item?.title ?? 'Produk Fashion').trim();
  const is_verified = item?.is_verified ?? item?.verified ?? item?.isVerified ?? false;

  return {
    id: item?.id ?? item?.uuid ?? `${name}-${product_name}`,
    name,
    avatar,
    rating: Math.min(5, Math.max(0, rating)),
    comment,
    product_name,
    is_verified: Boolean(is_verified),
  };
};

const StarRating = () => (
  <div className="flex items-center gap-1">
    {Array.from({ length: 5 }, (_, index) => (
      <svg
        key={index}
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
        className="h-4 w-4 text-amber-400"
      >
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.958c.3.921-.755 1.688-1.54 1.118L10 13.347l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.286-3.958a1 1 0 00-.364-1.118L2.643 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z" />
      </svg>
    ))}
  </div>
);

export default function Testimoni({ apiUrl = '/cms/api/testimonials' }: { apiUrl?: string }) {
  const [items, setItems] = useState<Testimonial[]>([]);

  useEffect(() => {
    let active = true;

    fetch(apiUrl)
      .then((res) => {
        if (!res.ok) throw new Error('Failed to load testimonials');
        return res.json();
      })
      .then((data) => {
        const rawItems = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
        const mapped = rawItems.map(normalizeTestimonial).slice(0, 12);
        if (active) {
          setItems(mapped.length ? mapped : fallbackTestimonials);
        }
      })
      .catch(() => {
        if (active) setItems(fallbackTestimonials);
      });

    return () => {
      active = false;
    };
  }, [apiUrl]);

  return (
    <section id="testimoni" className="bg-gray-50 py-16">
      <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div className="text-center">
          <p className="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Apa Kata Mereka</p>
          <h2 className="mt-3 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
            Cerita nyata dari pelanggan kami
          </h2>
        </div>

        <div className="mt-12">
          <Swiper
            modules={[Pagination]}
            pagination={{ clickable: true }}
            spaceBetween={24}
            breakpoints={{
              640: { slidesPerView: 1 },
              768: { slidesPerView: 2 },
              1024: { slidesPerView: 3 },
            }}
          >
            {(items.length ? items : fallbackTestimonials).map((item) => (
              <SwiperSlide key={String(item.id)}>
                <div className="h-full rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                  <StarRating />
                  <p className="mt-6 text-base leading-8 text-slate-700">{item.comment}</p>
                  <div className="mt-8 flex items-center gap-4 border-t border-slate-200 pt-6">
                    <img
                      src={item.avatar}
                      alt={item.name}
                      className="h-12 w-12 rounded-full object-cover"
                    />
                    <div>
                      <p className="text-sm font-semibold text-slate-900">{item.name}</p>
                      <p className="mt-1 text-sm text-slate-500">
                        {item.is_verified ? 'Verified Buyer' : 'Buyer'} • {item.product_name}
                      </p>
                    </div>
                  </div>
                </div>
              </SwiperSlide>
            ))}
          </Swiper>
        </div>
      </div>
    </section>
  );
}
