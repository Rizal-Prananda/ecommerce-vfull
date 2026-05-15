<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

Route::get('/testimonials', function () {
    return response()->json([
        'data' => [
            [
                'id' => 1,
                'name' => 'Dina',
                'role' => 'Founder',
                'rating' => 4.9,
                'message' => 'Desain modern, responsif, dan hasilnya rapi banget. Proses komunikasi juga enak.',
            ],
            [
                'id' => 2,
                'name' => 'Raka',
                'role' => 'Marketing Lead',
                'rating' => 4.8,
                'message' => 'Loading cepat, layout bersih, dan CTA-nya terasa premium.',
            ],
            [
                'id' => 3,
                'name' => 'Salsa',
                'role' => 'Owner',
                'rating' => 5.0,
                'message' => 'Sesuai referensi warna yang lembut, jadi terlihat mahal.',
            ],
        ],
    ]);
});

Route::get('/recommendations', function () {
    return response()->json([
        'data' => [
            [
                'id' => 1,
                'tag' => 'Paket',
                'title' => 'Company Profile Modern',
                'description' => 'Landing 1 halaman: Home, About, Testimoni, Rekomendasi + integrasi CMS.',
            ],
            [
                'id' => 2,
                'tag' => 'Paket',
                'title' => 'Branding Starter',
                'description' => 'Logo, guideline singkat, template social media, dan asset kit.',
            ],
            [
                'id' => 3,
                'tag' => 'Paket',
                'title' => 'Marketplace Setup',
                'description' => 'Catalog, kategori, pencarian, listing produk, dan CTA.',
            ],
        ],
    ]);
});

Route::get('/categories', function () {
    return response()->json([
        'data' => [
            ['id' => 1, 'name' => 'Featured'],
            ['id' => 2, 'name' => 'Electronics'],
            ['id' => 3, 'name' => 'Property'],
            ['id' => 4, 'name' => 'Services'],
            ['id' => 5, 'name' => 'Furniture'],
        ],
    ]);
});

Route::get('/products', function (Request $request) {
    $categoryId = $request->integer('categoryId');
    $q = trim((string) $request->query('q', ''));
    $items = [
        [
            'id' => 1,
            'title' => 'Office Chair Minimal',
            'categoryId' => 5,
            'price' => 1299000,
            'rating' => 4.7,
            'image' => 'https://picsum.photos/seed/chair/600/450',
            'location' => 'Jakarta',
        ],
        [
            'id' => 2,
            'title' => 'Smart Watch Pro',
            'categoryId' => 2,
            'price' => 899000,
            'rating' => 4.6,
            'image' => 'https://picsum.photos/seed/watch/600/450',
            'location' => 'Bandung',
        ],
        [
            'id' => 3,
            'title' => 'Interior Consultation',
            'categoryId' => 4,
            'price' => 299000,
            'rating' => 4.9,
            'image' => 'https://picsum.photos/seed/consult/600/450',
            'location' => 'Online',
        ],
        [
            'id' => 4,
            'title' => 'Apartment Studio Listing',
            'categoryId' => 3,
            'price' => 15000000,
            'rating' => 4.8,
            'image' => 'https://picsum.photos/seed/apartment/600/450',
            'location' => 'Surabaya',
        ],
        [
            'id' => 5,
            'title' => 'Wireless Earbuds',
            'categoryId' => 2,
            'price' => 399000,
            'rating' => 4.5,
            'image' => 'https://picsum.photos/seed/earbuds/600/450',
            'location' => 'Semarang',
        ],
        [
            'id' => 6,
            'title' => 'Featured Starter Kit',
            'categoryId' => 1,
            'price' => 499000,
            'rating' => 4.8,
            'image' => 'https://picsum.photos/seed/featured/600/450',
            'location' => 'Jakarta',
        ],
    ];

    if ($categoryId) {
        $items = array_values(array_filter($items, fn ($x) => (int) $x['categoryId'] === $categoryId));
    }

    if ($q !== '') {
        $items = array_values(array_filter($items, fn ($x) => str_contains(mb_strtolower($x['title']), mb_strtolower($q))));
    }

    return response()->json(['data' => $items]);
});
