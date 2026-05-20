<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'namalengkap_pelanggan' => ['required', 'string', 'max:255'],
        'notelepon_pelanggan' => ['required', 'string', 'max:30'],
        'email_pelanggan' => ['required', 'string', 'email', 'max:255', 'unique:Pelanggan,email_pelanggan'],
        'alamat_pelanggan' => ['nullable', 'string', 'max:2000'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $now = now()->toDateTimeString();
    $email = mb_strtolower(trim((string) $validated['email_pelanggan']));

    $id = DB::connection('sqlite')->table('Pelanggan')->insertGetId([
        'namalengkap_pelanggan' => $validated['namalengkap_pelanggan'],
        'email_pelanggan' => $email,
        'notelepon_pelanggan' => $validated['notelepon_pelanggan'],
        'alamat_pelanggan' => $validated['alamat_pelanggan'] ?? null,
        'password' => Hash::make($validated['password']),
        'createdAt' => $now,
        'last_update' => $now,
        'last_update_password' => $now,
    ]);

    return response()->json([
        'ok' => true,
        'message' => 'Daftar berhasil',
        'data' => [
            'id_pelanggan' => (int) $id,
        ],
    ], 201);
});

Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email_pelanggan' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    $email = mb_strtolower(trim((string) $validated['email_pelanggan']));
    $row = DB::connection('sqlite')
        ->table('Pelanggan')
        ->whereRaw('lower(email_pelanggan) = ?', [$email])
        ->first();

    if (!$row || !Hash::check($validated['password'], (string) ($row->password ?? ''))) {
        return response()->json(['ok' => false, 'message' => 'Email atau password salah'], 401);
    }

    return response()->json([
        'ok' => true,
        'message' => 'Login berhasil',
        'data' => [
            'id_pelanggan' => (int) $row->id_pelanggan,
        ],
    ]);
});

Route::get('/user', function (Request $request) {
    $header = $request->header('Authorization', '');
    $idHeader = trim((string) $request->header('X-Pelanggan-Id', ''));
    $idFromHeader = (int) $idHeader;

    $id = 0;
    if ($idFromHeader > 0) {
        $id = $idFromHeader;
    } else {
        if (preg_match('/^Bearer\s+(.+)$/i', (string) $header, $m)) {
            $token = trim((string) ($m[1] ?? ''));
            if (preg_match('/^pelanggan[:\-](\d+)$/i', $token, $m2)) {
                $id = (int) ($m2[1] ?? 0);
            } elseif (ctype_digit($token)) {
                $id = (int) $token;
            }
        }
    }

    if ($id <= 0) {
        return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
    }

    $row = DB::connection('sqlite')->table('Pelanggan')->where('id_pelanggan', $id)->first();
    if (!$row) {
        return response()->json(['ok' => false, 'message' => 'User tidak ditemukan'], 404);
    }

    $name = (string) ($row->namalengkap_pelanggan ?? '');
    $email = (string) ($row->email_pelanggan ?? '');
    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name ?: 'User') . '&background=000000&color=ffffff&size=128';

    return response()->json([
        'ok' => true,
        'data' => [
            'nama_lengkap' => $name,
            'email' => $email,
            'avatar' => $avatar,
        ],
    ]);
});

Route::post('/logout', function () {
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
        $items = array_values(array_filter($items, fn($x) => (int) $x['categoryId'] === $categoryId));
    }

    if ($q !== '') {
        $items = array_values(array_filter($items, fn($x) => str_contains(mb_strtolower($x['title']), mb_strtolower($q))));
    }

    return response()->json(['data' => $items]);
});
