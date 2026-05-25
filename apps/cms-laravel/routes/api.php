<?php

use App\Http\Controllers\Api\ProductApiController;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

Route::match(['GET', 'OPTIONS'], '/site-settings/{key}', function (Request $request, string $key) {
    $origin = (string) $request->header('Origin', '');
    $allowOrigin = '';
    if (app()->environment('local')) {
        if ($origin !== '') {
            $allowOrigin = $origin;
        } else {
            $allowOrigin = '*';
        }
    }

    if ($request->isMethod('OPTIONS')) {
        $resp = response()->json(['ok' => true]);
        if ($allowOrigin !== '') {
            $resp->headers->set('Access-Control-Allow-Origin', $allowOrigin);
            $resp->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $resp->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Pelanggan-Id');
            $resp->headers->set('Vary', 'Origin');
        }
        return $resp;
    }

    $key = trim($key);
    if ($key === '' || str_contains($key, '..')) {
        abort(404);
    }

    $row = SiteSetting::query()->where('key', $key)->first();

    $resp = response()->json([
        'data' => [
            'key' => $key,
            'value' => $row?->value,
        ],
    ]);

    if ($allowOrigin !== '') {
        $resp->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $resp->headers->set('Vary', 'Origin');
    }

    return $resp;
})->where('key', '[A-Za-z0-9_\-]+');

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

    $name = (string) ($row->namalengkap_pelanggan ?? '');
    $emailOut = (string) ($row->email_pelanggan ?? '');
    $avatarRaw = trim((string) ($row->avatar_pelanggan ?? ''));
    if ($avatarRaw !== '') {
        $avatar = str_starts_with($avatarRaw, 'http') ? $avatarRaw : asset(ltrim($avatarRaw, '/'));
    } else {
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name ?: 'User') . '&background=000000&color=ffffff&size=128';
    }

    return response()->json([
        'ok' => true,
        'message' => 'Login berhasil',
        'data' => [
            'id_pelanggan' => (int) $row->id_pelanggan,
            'nama_lengkap' => $name,
            'email' => $emailOut,
            'avatar' => $avatar,
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
    $phone = (string) ($row->notelepon_pelanggan ?? '');
    $alamat = (string) ($row->alamat_pelanggan ?? '');
    $avatarRaw = trim((string) ($row->avatar_pelanggan ?? ''));
    if ($avatarRaw !== '') {
        $avatar = str_starts_with($avatarRaw, 'http') ? $avatarRaw : asset(ltrim($avatarRaw, '/'));
    } else {
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name ?: 'User') . '&background=000000&color=ffffff&size=128';
    }

    return response()->json([
        'ok' => true,
        'data' => [
            'id_pelanggan' => (int) $id,
            'nama_lengkap' => $name,
            'email' => $email,
            'telepon' => $phone,
            'alamat' => $alamat,
            'avatar' => $avatar,
        ],
    ]);
});

Route::post('/user/avatar', function (Request $request) {
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

    $validated = $request->validate([
        'avatar' => ['required', 'file', 'image', 'max:2048'],
    ]);

    $file = $request->file('avatar');
    if (!$file) {
        return response()->json(['ok' => false, 'message' => 'File avatar tidak ditemukan'], 400);
    }

    $uploadsDir = public_path('uploads/pelanggan-avatars');
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $ext = strtolower((string) $file->getClientOriginalExtension());
    if ($ext === '') $ext = 'jpg';
    $filename = (string) \Illuminate\Support\Str::uuid() . '.' . $ext;
    $file->move($uploadsDir, $filename);

    $path = 'uploads/pelanggan-avatars/' . $filename;

    DB::connection('sqlite')->table('Pelanggan')->where('id_pelanggan', $id)->update([
        'avatar_pelanggan' => $path,
        'last_update' => now()->toDateTimeString(),
    ]);

    return response()->json([
        'ok' => true,
        'data' => [
            'avatar' => asset($path),
        ],
    ]);
});

Route::get('/orders', function (Request $request) {
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

    $conn = DB::connection('sqlite');
    try {
        $hasOrders = $conn->getSchemaBuilder()->hasTable('Orders');
    } catch (\Throwable $e) {
        $hasOrders = false;
    }

    if (!$hasOrders) {
        return response()->json(['ok' => true, 'data' => []]);
    }

    $base = $request->getSchemeAndHttpHost();
    $generateOrderNo = function (string $datePart) use ($conn): string {
        $datePart = preg_replace('/[^0-9]/', '', $datePart) ?: now()->format('Ymd');
        $tries = 0;
        while (true) {
            $candidate = 'ORD-' . $datePart . '-' . random_int(100000000, 999999999);
            $exists = $conn->table('Orders')->where('order_no', $candidate)->exists();
            if (!$exists) return $candidate;
            $tries++;
            if ($tries >= 10) return $candidate;
        }
    };

    $orders = $conn->table('Orders')
        ->where('id_pelanggan', $id)
        ->orderByDesc('createdAt')
        ->get([
            'id_order',
            'id_pelanggan',
            'order_no',
            'status',
            'total',
            'createdAt',
        ]);

    $schema = $conn->getSchemaBuilder();
    $hasItems = $schema->hasTable('OrderItems');
    $hasProducts = $schema->hasTable('products');

    if (!$hasItems) {
        return response()->json(['ok' => true, 'data' => $orders]);
    }

    try {
        if (!$schema->hasColumn('OrderItems', 'review_rating')) {
            $schema->table('OrderItems', function ($table) {
                $table->integer('review_rating')->nullable();
            });
        }
        if (!$schema->hasColumn('OrderItems', 'review_comment')) {
            $schema->table('OrderItems', function ($table) {
                $table->text('review_comment')->nullable();
            });
        }
        if (!$schema->hasColumn('OrderItems', 'reviewed_at')) {
            $schema->table('OrderItems', function ($table) {
                $table->dateTime('reviewed_at')->nullable();
            });
        }
    } catch (\Throwable $e) {
    }

    $orderIds = [];
    foreach ($orders as $o) {
        $orderIds[] = (int) ($o->id_order ?? 0);
    }
    $orderIds = array_values(array_filter($orderIds, fn($x) => $x > 0));

    $itemsByOrder = [];
    $productIdsNeedingImage = [];

    if (count($orderIds)) {
        $items = $conn->table('OrderItems')
            ->whereIn('id_order', $orderIds)
            ->orderBy('id_order')
            ->orderBy('id_item')
            ->get([
                'id_order',
                'product_id',
                'variant_id',
                'qty',
                'title',
                'image',
                'unit_price',
                'size',
            ]);

        foreach ($items as $it) {
            $oid = (int) ($it->id_order ?? 0);
            if ($oid <= 0) {
                continue;
            }
            if (!isset($itemsByOrder[$oid])) {
                $itemsByOrder[$oid] = [];
            }
            $itemsByOrder[$oid][] = $it;

            $img = trim((string) ($it->image ?? ''));
            $pid = (int) ($it->product_id ?? 0);
            if ($img === '' && $pid > 0) {
                $productIdsNeedingImage[$pid] = true;
            }
        }
    }

    $productImageMap = [];
    if ($hasProducts && count($productIdsNeedingImage)) {
        $productIds = array_keys($productIdsNeedingImage);
        $products = $conn->table('products')
            ->whereIn('id', $productIds)
            ->get(['id', 'image']);

        foreach ($products as $p) {
            $pid = (int) ($p->id ?? 0);
            if ($pid <= 0) continue;
            $img = trim((string) ($p->image ?? ''));
            if ($img !== '') $productImageMap[$pid] = $img;
        }
    }

    $monthsId = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $resolveThumb = function (string $raw) use ($base): string {
        $v = trim($raw);
        if ($v === '') return '';

        if (str_starts_with($v, 'http://') || str_starts_with($v, 'https://')) {
            return $v;
        }

        if (str_starts_with($v, '/product-media/')) {
            return $base . $v;
        }
        if (str_starts_with($v, 'product-media/')) {
            return $base . '/' . $v;
        }

        if (str_starts_with($v, '/storage/products/')) {
            $v = 'products/' . ltrim(substr($v, strlen('/storage/products/')), '/');
        } elseif (str_starts_with($v, 'storage/products/')) {
            $v = 'products/' . ltrim(substr($v, strlen('storage/products/')), '/');
        } elseif (str_starts_with($v, '/products/')) {
            $v = 'products/' . ltrim(substr($v, strlen('/products/')), '/');
        }

        if (str_starts_with($v, 'products/')) {
            return $base . '/product-media/' . $v;
        }

        if (str_starts_with($v, '/')) {
            return $base . $v;
        }

        return $base . '/' . $v;
    };

    $hasVariants = $schema->hasTable('product_variants');
    $variantSkuMap = [];
    if ($hasVariants && count($orderIds)) {
        $variantIds = [];
        foreach ($itemsByOrder as $arr) {
            foreach ($arr as $it) {
                $variantId = $it->variant_id === null ? null : (int) ($it->variant_id ?? 0);
                if ($variantId !== null && $variantId > 0) {
                    $variantIds[$variantId] = true;
                }
            }
        }
        $variantIds = array_keys($variantIds);
        if (count($variantIds)) {
            $variantSkuMap = $conn->table('product_variants')->whereIn('id', $variantIds)->pluck('sku', 'id')->all();
        }
    }

    $out = [];
    foreach ($orders as $o) {
        $oid = (int) ($o->id_order ?? 0);
        $status = strtoupper(trim((string) ($o->status ?? '')));
        $createdAt = trim((string) ($o->createdAt ?? ''));
        $orderNo = trim((string) ($o->order_no ?? ''));
        if ($orderNo === '' && $oid > 0) {
            $datePart = '';
            try {
                $datePart = \Carbon\Carbon::parse($createdAt)->format('Ymd');
            } catch (\Throwable $e) {
                $datePart = now()->format('Ymd');
            }
            $orderNo = $generateOrderNo($datePart);
            $conn->table('Orders')->where('id_order', $oid)->whereNull('order_no')->update(['order_no' => $orderNo]);
        }
        $orderId = $orderNo !== '' ? $orderNo : ('#' . $oid);

        $itRows = $itemsByOrder[$oid] ?? [];
        $grouped = [];
        $itemsOut = [];
        $thumb = '';
        $firstProductId = 0;

        foreach ($itRows as $idx => $it) {
            $productId = (int) ($it->product_id ?? 0);
            $variantId = $it->variant_id === null ? null : (int) ($it->variant_id ?? 0);
            $qty = max(1, (int) ($it->qty ?? 1));
            $unitPrice = max(0, (int) ($it->unit_price ?? 0));
            $size = trim((string) ($it->size ?? ''));

            $sku = '';
            if ($variantId !== null && $variantId > 0) {
                $sku = trim((string) ($variantSkuMap[$variantId] ?? ''));
            }
            $name = trim((string) ($it->title ?? ''));
            if ($name === '') $name = $productId > 0 ? ('Produk #' . $productId) : 'Produk';
            $variantLabel = $sku !== '' ? $sku : $size;

            $key = ($variantId !== null && $variantId > 0) ? ('v:' . $variantId) : ('p:' . $productId);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'id' => $variantId !== null && $variantId > 0 ? $variantId : $productId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name' => $name,
                    'variant' => $variantLabel,
                    'qty' => 0,
                    'subtotal' => 0,
                    'thumbnail' => '',
                ];
            }
            $grouped[$key]['qty'] += $qty;
            $grouped[$key]['subtotal'] += ($unitPrice * $qty);

            $img = trim((string) ($it->image ?? ''));
            $pid = (int) ($it->product_id ?? 0);
            if ($idx === 0 && $pid > 0) $firstProductId = $pid;
            if ($thumb === '' && $img !== '') {
                $thumb = $img;
            }
            if ($grouped[$key]['thumbnail'] === '' && $img !== '') {
                $grouped[$key]['thumbnail'] = $img;
            }
        }

        if ($thumb === '' && $firstProductId > 0) {
            $thumb = (string) ($productImageMap[$firstProductId] ?? '');
        }

        $thumb = $resolveThumb((string) $thumb);

        foreach ($grouped as $g) {
            $productId = (int) ($g['product_id'] ?? 0);
            $variantId = $g['variant_id'] === null ? null : (int) ($g['variant_id'] ?? 0);
            $img = trim((string) ($g['thumbnail'] ?? ''));
            if ($img === '' && $productId > 0) {
                $img = (string) ($productImageMap[$productId] ?? '');
            }
            $itemsOut[] = [
                'id' => (int) ($g['id'] ?? 0),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => (string) ($g['name'] ?? ''),
                'variant' => (string) ($g['variant'] ?? ''),
                'qty' => (int) max(1, (int) ($g['qty'] ?? 1)),
                'subtotal' => (int) max(0, (int) ($g['subtotal'] ?? 0)),
                'thumbnail' => $resolveThumb((string) $img),
            ];
        }

        $itemsTextParts = [];
        foreach (array_slice($itemsOut, 0, 2) as $it) {
            $name = trim((string) ($it['name'] ?? ''));
            $variant = trim((string) ($it['variant'] ?? ''));
            $qty = (int) ($it['qty'] ?? 1);
            $itemsTextParts[] = trim($name . ($variant !== '' ? (' ' . $variant) : '') . ': ' . $qty . 'x');
        }
        $itemsText = implode(', ', $itemsTextParts);
        if (count($itemsOut) > 2) {
            $itemsText = trim($itemsText . ' +' . (count($itemsOut) - 2) . ' lainnya');
        }

        $dueDate = null;
        if ($status === 'UNPAID' && $createdAt !== '') {
            try {
                $dt = \Carbon\Carbon::parse($createdAt)->addDays(3);
                $m = (int) $dt->month;
                $dueDate = $dt->day . ' ' . ($monthsId[$m] ?? $dt->format('F')) . ' ' . $dt->year;
            } catch (\Throwable $e) {
                $dueDate = null;
            }
        }

        $out[] = [
            'id' => $orderId,
            'date' => $createdAt,
            'items' => $itemsOut,
            'items_text' => $itemsText,
            'total' => (int) ($o->total ?? 0),
            'status' => $status ?: 'UNPAID',
            'dueDate' => $dueDate,
            'thumbnail' => $thumb,
        ];
    }

    return response()->json(['ok' => true, 'data' => $out]);
});

Route::post('/reviews', function (Request $request) {
    $header = $request->header('Authorization', '');
    $idHeader = trim((string) $request->header('X-Pelanggan-Id', ''));
    $idFromHeader = (int) $idHeader;

    $id = 0;
    if ($idFromHeader > 0) {
        $id = $idFromHeader;
    } else {
        if (preg_match('/^Bearer\s+(.+)$/i', (string) $header, $m)) {
            $token = trim((string) ($m[1] ?? ''));
            if (preg_match('/^pelanggan[:\\-](\\d+)$/i', $token, $m2)) {
                $id = (int) ($m2[1] ?? 0);
            } elseif (ctype_digit($token)) {
                $id = (int) $token;
            }
        }
    }

    if ($id <= 0) {
        return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'order_id' => ['required', 'string', 'max:80'],
        'product_id' => ['required', 'integer', 'min:1'],
        'variant_id' => ['nullable', 'integer', 'min:1'],
        'rating' => ['required', 'integer', 'min:1', 'max:5'],
        'comment' => ['nullable', 'string', 'max:2000'],
    ]);

    $orderId = trim((string) $validated['order_id']);
    $productId = (int) $validated['product_id'];
    $variantId = $validated['variant_id'] === null ? null : (int) $validated['variant_id'];
    $rating = (int) $validated['rating'];
    $comment = trim((string) ($validated['comment'] ?? ''));
    if ($comment === '') $comment = null;

    $conn = DB::connection('sqlite');
    $schema = $conn->getSchemaBuilder();

    $hasOrders = false;
    $hasItems = false;
    $hasProducts = false;
    try {
        $hasOrders = $schema->hasTable('Orders');
        $hasItems = $schema->hasTable('OrderItems');
        $hasProducts = $schema->hasTable('products');
    } catch (\Throwable $e) {
        $hasOrders = false;
        $hasItems = false;
        $hasProducts = false;
    }

    if (!$hasOrders || !$hasItems || !$hasProducts) {
        return response()->json(['ok' => false, 'message' => 'Data belum siap'], 409);
    }

    $orderRow = null;
    if (str_starts_with($orderId, 'ORD-')) {
        $orderRow = $conn->table('Orders')->where('order_no', $orderId)->where('id_pelanggan', $id)->first();
    } elseif (preg_match('/^#(\\d+)$/', $orderId, $m)) {
        $oid = (int) ($m[1] ?? 0);
        if ($oid > 0) {
            $orderRow = $conn->table('Orders')->where('id_order', $oid)->where('id_pelanggan', $id)->first();
        }
    } else {
        $orderRow = $conn->table('Orders')->where('order_no', $orderId)->where('id_pelanggan', $id)->first();
    }

    if (!$orderRow) {
        return response()->json(['ok' => false, 'message' => 'Order tidak ditemukan'], 404);
    }

    $status = strtoupper(trim((string) ($orderRow->status ?? '')));
    if ($status !== 'PAID') {
        return response()->json(['ok' => false, 'message' => 'Ulasan hanya bisa untuk pesanan PAID'], 409);
    }

    $hasLine = $conn->table('OrderItems')
        ->where('id_order', (int) ($orderRow->id_order ?? 0))
        ->where('product_id', $productId)
        ->when($variantId !== null, fn($q) => $q->where('variant_id', $variantId))
        ->when($variantId === null, fn($q) => $q->whereNull('variant_id'))
        ->exists();

    if (!$hasLine) {
        return response()->json(['ok' => false, 'message' => 'Item order tidak valid'], 422);
    }

    try {
        if (!$schema->hasColumn('OrderItems', 'review_rating')) {
            $schema->table('OrderItems', function ($table) {
                $table->integer('review_rating')->nullable();
            });
        }
        if (!$schema->hasColumn('OrderItems', 'review_comment')) {
            $schema->table('OrderItems', function ($table) {
                $table->text('review_comment')->nullable();
            });
        }
        if (!$schema->hasColumn('OrderItems', 'reviewed_at')) {
            $schema->table('OrderItems', function ($table) {
                $table->dateTime('reviewed_at')->nullable();
            });
        }
    } catch (\Throwable $e) {
    }

    if (!$schema->hasTable('product_reviews')) {
        $schema->create('product_reviews', function ($table) {
            $table->bigIncrements('id');
            $table->integer('id_pelanggan');
            $table->string('order_id', 80);
            $table->integer('product_id');
            $table->integer('variant_id')->nullable();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
        });
    }

    $now = now()->toDateTimeString();
    $conn->table('OrderItems')
        ->where('id_order', (int) ($orderRow->id_order ?? 0))
        ->where('product_id', $productId)
        ->when($variantId !== null, fn($q) => $q->where('variant_id', $variantId))
        ->when($variantId === null, fn($q) => $q->whereNull('variant_id'))
        ->update([
            'review_rating' => $rating,
            'review_comment' => $comment,
            'reviewed_at' => $now,
        ]);

    $existing = $conn->table('product_reviews')
        ->where('id_pelanggan', $id)
        ->where('order_id', $orderId)
        ->where('product_id', $productId)
        ->when($variantId !== null, fn($q) => $q->where('variant_id', $variantId))
        ->when($variantId === null, fn($q) => $q->whereNull('variant_id'))
        ->first(['id']);

    if ($existing) {
        $conn->table('product_reviews')->where('id', (int) ($existing->id ?? 0))->update([
            'rating' => $rating,
            'comment' => $comment,
            'updated_at' => $now,
        ]);
    } else {
        $conn->table('product_reviews')->insert([
            'id_pelanggan' => $id,
            'order_id' => $orderId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => $now,
            'updated_at' => null,
        ]);
    }

    $avg = $conn->table('product_reviews')->where('product_id', $productId)->avg('rating');
    $avgNum = is_numeric($avg) ? (float) $avg : 0.0;
    $avgRounded = max(0.0, min(5.0, round($avgNum, 1)));
    $conn->table('products')->where('id', $productId)->update(['rating' => $avgRounded]);

    return response()->json([
        'ok' => true,
        'data' => [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'rating' => $rating,
            'avg_rating' => $avgRounded,
        ],
    ]);
});

Route::get('/cart', function (Request $request) {
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

    $conn = DB::connection('sqlite');

    try {
        $schema = $conn->getSchemaBuilder();
        $hasOrders = $schema->hasTable('Orders');
        $hasItems = $schema->hasTable('OrderItems');
    } catch (\Throwable $e) {
        $hasOrders = false;
        $hasItems = false;
    }

    if (!$hasOrders || !$hasItems) {
        return response()->json(['ok' => true, 'data' => null]);
    }

    $order = $conn->table('Orders')
        ->where('id_pelanggan', $id)
        ->where('status', 'UNPAID')
        ->orderByDesc('createdAt')
        ->first();

    if (!$order) {
        return response()->json(['ok' => true, 'data' => null]);
    }

    $rows = $conn->table('OrderItems')
        ->where('id_order', (int) $order->id_order)
        ->where(function ($q) {
            $q->where('status', 'UNPAID')->orWhereNull('status');
        })
        ->orderBy('id_item')
        ->get([
            'product_id',
            'variant_id',
            'qty',
            'title',
            'image',
            'unit_price',
            'size',
        ]);

    $productIds = [];
    $variantIds = [];
    foreach ($rows as $r) {
        $productId = (int) ($r->product_id ?? 0);
        if ($productId > 0) {
            $productIds[] = $productId;
        }
        $variantId = $r->variant_id === null ? null : (int) $r->variant_id;
        if ($variantId !== null && $variantId > 0) {
            $variantIds[] = $variantId;
        }
    }
    $productIds = array_values(array_unique($productIds));
    $variantIds = array_values(array_unique($variantIds));

    $productStockMap = [];
    if (count($productIds)) {
        $productStockMap = $conn->table('products')->whereIn('id', $productIds)->pluck('stock', 'id')->all();
    }

    $variantStockMap = [];
    $variantProductMap = [];
    if (count($variantIds)) {
        $variantStockMap = $conn->table('product_variants')->whereIn('id', $variantIds)->pluck('stock', 'id')->all();
        $variantProductMap = $conn->table('product_variants')->whereIn('id', $variantIds)->pluck('product_id', 'id')->all();
    }

    $items = [];
    $subtotal = 0;
    foreach ($rows as $r) {
        $productId = (int) ($r->product_id ?? 0);
        $variantId = $r->variant_id === null ? null : (int) $r->variant_id;
        $qtyIn = max(1, (int) ($r->qty ?? 1));
        $price = max(0, (int) ($r->unit_price ?? 0));

        $available = 0;
        if ($variantId !== null) {
            $owner = (int) ($variantProductMap[$variantId] ?? 0);
            if ($owner !== $productId) {
                continue;
            }
            $available = (int) ($variantStockMap[$variantId] ?? 0);
        } else {
            $available = (int) ($productStockMap[$productId] ?? 0);
        }

        if ($available <= 0) {
            continue;
        }

        $qty = max(1, min($available, $qtyIn));
        $line = $price * $qty;
        $subtotal += $line;

        $items[] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'qty' => $qty,
            'title' => (string) ($r->title ?? ''),
            'image' => (string) ($r->image ?? ''),
            'price' => $price,
            'size' => (string) ($r->size ?? ''),
            'stock' => (int) max(0, $available),
        ];
    }

    $discount = (int) floor(max(0, $subtotal) * 0.1);
    $deliveryFee = count($items) ? 10000 : 0;
    $total = max(0, (int) $subtotal - $discount + $deliveryFee);

    return response()->json([
        'ok' => true,
        'data' => [
            'id_order' => (int) $order->id_order,
            'id_pelanggan' => (int) $order->id_pelanggan,
            'status' => (string) ($order->status ?? 'UNPAID'),
            'subtotal' => (int) $subtotal,
            'discount' => (int) $discount,
            'delivery_fee' => (int) $deliveryFee,
            'total' => (int) $total,
            'items' => $items,
        ],
    ]);
});

Route::post('/cart/stock', function (Request $request) {
    $validated = $request->validate([
        'items' => ['required', 'array'],
        'items.*.product_id' => ['required', 'integer', 'min:1'],
        'items.*.variant_id' => ['nullable', 'integer', 'min:1'],
    ]);

    $itemsIn = (array) ($validated['items'] ?? []);
    $conn = DB::connection('sqlite');

    $productIds = [];
    $variantIds = [];
    foreach ($itemsIn as $it) {
        $productId = (int) ($it['product_id'] ?? 0);
        if ($productId > 0) {
            $productIds[] = $productId;
        }
        $variantId = $it['variant_id'] ?? null;
        if ($variantId !== null) {
            $variantId = (int) $variantId;
            if ($variantId > 0) {
                $variantIds[] = $variantId;
            }
        }
    }

    $productIds = array_values(array_unique($productIds));
    $variantIds = array_values(array_unique($variantIds));

    $productStockMap = [];
    if (count($productIds)) {
        $productStockMap = $conn->table('products')->whereIn('id', $productIds)->pluck('stock', 'id')->all();
    }

    $variantStockMap = [];
    $variantProductMap = [];
    if (count($variantIds)) {
        $variantStockMap = $conn->table('product_variants')->whereIn('id', $variantIds)->pluck('stock', 'id')->all();
        $variantProductMap = $conn->table('product_variants')->whereIn('id', $variantIds)->pluck('product_id', 'id')->all();
    }

    $out = [];
    foreach ($itemsIn as $it) {
        $productId = (int) ($it['product_id'] ?? 0);
        $variantId = $it['variant_id'] ?? null;
        $variantId = $variantId === null ? null : (int) $variantId;

        $available = 0;
        if ($variantId !== null) {
            $owner = (int) ($variantProductMap[$variantId] ?? 0);
            if ($owner !== $productId) {
                $available = 0;
            } else {
                $available = (int) ($variantStockMap[$variantId] ?? 0);
            }
        } else {
            $available = (int) ($productStockMap[$productId] ?? 0);
        }

        $out[] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'stock' => (int) max(0, $available),
        ];
    }

    return response()->json([
        'ok' => true,
        'data' => [
            'items' => $out,
        ],
    ]);
});

Route::post('/cart/sync', function (Request $request) {
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

    $validated = $request->validate([
        'items' => ['required', 'array'],
        'items.*.product_id' => ['required', 'integer', 'min:1'],
        'items.*.variant_id' => ['nullable', 'integer', 'min:1'],
        'items.*.qty' => ['required', 'integer', 'min:1'],
        'items.*.title' => ['nullable', 'string', 'max:255'],
        'items.*.image' => ['nullable', 'string', 'max:2048'],
        'items.*.size' => ['nullable', 'string', 'max:50'],
        'items.*.price' => ['nullable', 'integer', 'min:0'],
    ]);

    $itemsIn = (array) ($validated['items'] ?? []);
    $now = now()->toDateTimeString();
    $conn = DB::connection('sqlite');

    $schema = $conn->getSchemaBuilder();
    if (!$schema->hasTable('Orders') || !$schema->hasTable('OrderItems')) {
        return response()->json(['ok' => false, 'message' => 'Cart table not ready. Run migration first.'], 500);
    }

    $out = $conn->transaction(function () use ($conn, $id, $itemsIn, $now) {
        $generateOrderNo = function (string $datePart) use ($conn): string {
            $datePart = preg_replace('/[^0-9]/', '', $datePart) ?: now()->format('Ymd');
            $tries = 0;
            while (true) {
                $candidate = 'ORD-' . $datePart . '-' . random_int(100000000, 999999999);
                $exists = $conn->table('Orders')->where('order_no', $candidate)->exists();
                if (!$exists) return $candidate;
                $tries++;
                if ($tries >= 10) return $candidate;
            }
        };

        $order = $conn->table('Orders')
            ->where('id_pelanggan', $id)
            ->where('status', 'UNPAID')
            ->orderByDesc('createdAt')
            ->first();

        if (!$order) {
            $orderNo = $generateOrderNo(now()->format('Ymd'));
            $orderId = (int) $conn->table('Orders')->insertGetId([
                'id_pelanggan' => $id,
                'order_no' => $orderNo,
                'status' => 'UNPAID',
                'total' => 0,
                'createdAt' => $now,
            ], 'id_order');

            $order = (object) ['id_order' => $orderId, 'id_pelanggan' => $id, 'status' => 'UNPAID', 'order_no' => $orderNo];
        }

        $orderId = (int) $order->id_order;
        $existingNo = trim((string) ($order->order_no ?? ''));
        if ($existingNo === '') {
            $newNo = $generateOrderNo(now()->format('Ymd'));
            $conn->table('Orders')->where('id_order', $orderId)->whereNull('order_no')->update(['order_no' => $newNo]);
            $order->order_no = $newNo;
        }

        $conn->table('OrderItems')->where('id_order', $orderId)->delete();
        if (count($itemsIn) === 0) {
            $conn->table('Orders')->where('id_order', $orderId)->where('status', 'UNPAID')->delete();
            return [
                'id_order' => null,
                'order_no' => null,
                'status' => 'UNPAID',
                'subtotal' => 0,
                'discount' => 0,
                'delivery_fee' => 0,
                'total' => 0,
                'items' => [],
            ];
        }

        $productIds = [];
        $variantIds = [];
        foreach ($itemsIn as $it) {
            $productId = (int) ($it['product_id'] ?? 0);
            if ($productId > 0) {
                $productIds[] = $productId;
            }
            $variantId = $it['variant_id'] === null ? null : (int) ($it['variant_id'] ?? 0);
            if ($variantId !== null && $variantId > 0) {
                $variantIds[] = $variantId;
            }
        }
        $productIds = array_values(array_unique($productIds));
        $variantIds = array_values(array_unique($variantIds));

        $productStockMap = [];
        if (count($productIds)) {
            $productStockMap = $conn->table('products')->whereIn('id', $productIds)->pluck('stock', 'id')->all();
        }

        $variantStockMap = [];
        $variantProductMap = [];
        if (count($variantIds)) {
            $variantStockMap = $conn->table('product_variants')->whereIn('id', $variantIds)->pluck('stock', 'id')->all();
            $variantProductMap = $conn->table('product_variants')->whereIn('id', $variantIds)->pluck('product_id', 'id')->all();
        }

        $rows = [];
        $subtotal = 0;
        foreach ($itemsIn as $it) {
            $productId = (int) ($it['product_id'] ?? 0);
            $variantId = $it['variant_id'] === null ? null : (int) ($it['variant_id'] ?? 0);
            $qtyIn = max(1, (int) ($it['qty'] ?? 1));
            $price = max(0, (int) ($it['price'] ?? 0));
            $available = 0;
            if ($variantId !== null) {
                $owner = (int) ($variantProductMap[$variantId] ?? 0);
                if ($owner !== $productId) {
                    continue;
                }
                $available = (int) ($variantStockMap[$variantId] ?? 0);
            } else {
                $available = (int) ($productStockMap[$productId] ?? 0);
            }
            if ($available <= 0) {
                continue;
            }
            $qty = max(1, min($available, $qtyIn));
            $line = $price * $qty;
            $subtotal += $line;

            $rows[] = [
                'id_order' => $orderId,
                'status' => 'UNPAID',
                'product_id' => $productId,
                'variant_id' => $variantId,
                'title' => (string) ($it['title'] ?? ''),
                'size' => (string) ($it['size'] ?? ''),
                'image' => (string) ($it['image'] ?? ''),
                'unit_price' => $price,
                'qty' => $qty,
                'line_total' => $line,
                'createdAt' => $now,
                'updatedAt' => $now,
            ];
        }

        if (count($rows)) {
            $conn->table('OrderItems')->insert($rows);
        }

        $discount = (int) floor(max(0, $subtotal) * 0.1);
        $deliveryFee = count($rows) ? 10000 : 0;
        $total = max(0, (int) $subtotal - $discount + $deliveryFee);

        $conn->table('Orders')->where('id_order', $orderId)->update([
            'total' => $total,
        ]);

        return [
            'id_order' => $orderId,
            'id_pelanggan' => $id,
            'status' => 'UNPAID',
            'subtotal' => (int) $subtotal,
            'discount' => (int) $discount,
            'delivery_fee' => (int) $deliveryFee,
            'total' => (int) $total,
        ];
    });

    return response()->json(['ok' => true, 'data' => $out]);
});

Route::post('/user/update', function (Request $request) {
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

    $validated = $request->validate([
        'nama_lengkap' => ['required', 'string', 'max:255'],
        'telepon' => ['nullable', 'string', 'max:30'],
        'alamat' => ['nullable', 'string', 'max:2000'],
    ]);

    DB::connection('sqlite')->table('Pelanggan')->where('id_pelanggan', $id)->update([
        'namalengkap_pelanggan' => trim((string) $validated['nama_lengkap']),
        'notelepon_pelanggan' => isset($validated['telepon']) ? trim((string) $validated['telepon']) : null,
        'alamat_pelanggan' => isset($validated['alamat']) ? trim((string) $validated['alamat']) : null,
        'last_update' => now()->toDateTimeString(),
    ]);

    $row = DB::connection('sqlite')->table('Pelanggan')->where('id_pelanggan', $id)->first();
    if (!$row) {
        return response()->json(['ok' => false, 'message' => 'User tidak ditemukan'], 404);
    }

    $name = (string) ($row->namalengkap_pelanggan ?? '');
    $email = (string) ($row->email_pelanggan ?? '');
    $phone = (string) ($row->notelepon_pelanggan ?? '');
    $alamat = (string) ($row->alamat_pelanggan ?? '');
    $avatarRaw = trim((string) ($row->avatar_pelanggan ?? ''));
    if ($avatarRaw !== '') {
        $avatar = str_starts_with($avatarRaw, 'http') ? $avatarRaw : asset(ltrim($avatarRaw, '/'));
    } else {
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name ?: 'User') . '&background=000000&color=ffffff&size=128';
    }

    return response()->json([
        'ok' => true,
        'data' => [
            'id_pelanggan' => (int) $id,
            'nama_lengkap' => $name,
            'email' => $email,
            'telepon' => $phone,
            'alamat' => $alamat,
            'avatar' => $avatar,
        ],
    ]);
});

Route::get('/chat/history', function (Request $request) {
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

    $idQuery = (int) $request->query('id_pelanggan', 0);
    if ($idQuery > 0 && $idQuery !== $id) {
        return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
    }

    $afterId = (int) $request->query('after_id', 0);

    $query = DB::connection('sqlite')
        ->table('Chat')
        ->where('id_pelanggan', $id)
        ->orderBy('createdAt', 'asc');

    if ($afterId > 0) {
        $query->where('id_chat', '>', $afterId);
    }

    $rows = $query->get([
        'id_chat',
        'id_pelanggan',
        'pengirim',
        'pesan',
        'dibaca_admin',
        'createdAt',
        'pengirim_user_id',
        'pengirim_nama',
        'attachment_path',
        'attachment_mime',
        'attachment_size',
        'attachment_original_name',
    ]);

    return response()->json(['ok' => true, 'data' => $rows]);
});

Route::post('/chat/send', function (Request $request) {
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

    $validated = $request->validate([
        'id_pelanggan' => ['required', 'integer'],
        'pesan' => ['nullable', 'string', 'max:2000'],
        'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif'],
    ]);

    $idBody = (int) $validated['id_pelanggan'];
    if ($idBody !== $id) {
        return response()->json(['ok' => false, 'message' => 'Forbidden'], 403);
    }

    $pesan = trim((string) ($validated['pesan'] ?? ''));
    $file = $request->file('attachment');

    if ($pesan === '' && !$file) {
        return response()->json(['ok' => false, 'message' => 'Pesan atau foto wajib diisi'], 422);
    }

    $attachmentPath = null;
    $attachmentOriginalName = null;
    $attachmentMime = null;
    $attachmentSize = null;
    if ($file) {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $filename = (string) Str::uuid() . ($ext !== '' ? ".{$ext}" : '');
        $attachmentPath = $file->storeAs('chat-attachments', $filename, 'public');
        $attachmentOriginalName = (string) $file->getClientOriginalName();
        $attachmentMime = (string) ($file->getClientMimeType() ?? '');
        $attachmentSize = (int) ($file->getSize() ?? 0);
    }

    $now = now()->toDateTimeString();
    $newId = DB::connection('sqlite')->table('Chat')->insertGetId([
        'id_pelanggan' => $id,
        'pengirim' => 'pelanggan',
        'pesan' => $pesan,
        'dibaca_admin' => 0,
        'createdAt' => $now,
        'pengirim_user_id' => null,
        'pengirim_nama' => null,
        'attachment_path' => $attachmentPath,
        'attachment_mime' => $attachmentMime,
        'attachment_size' => $attachmentSize,
        'attachment_original_name' => $attachmentOriginalName,
    ]);

    $row = DB::connection('sqlite')->table('Chat')->where('id_chat', $newId)->first([
        'id_chat',
        'id_pelanggan',
        'pengirim',
        'pesan',
        'dibaca_admin',
        'createdAt',
        'pengirim_user_id',
        'pengirim_nama',
        'attachment_path',
        'attachment_mime',
        'attachment_size',
        'attachment_original_name',
    ]);

    return response()->json(['ok' => true, 'data' => $row], 201);
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

Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{slug}', [ProductApiController::class, 'show']);
