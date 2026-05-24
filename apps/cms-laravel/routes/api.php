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

Route::get('/site-settings/{key}', function (string $key) {
    $key = trim($key);
    if ($key === '' || str_contains($key, '..')) {
        abort(404);
    }

    $row = SiteSetting::query()->where('key', $key)->first();

    return response()->json([
        'data' => [
            'key' => $key,
            'value' => $row?->value,
        ],
    ]);
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

    $rows = $conn->table('Orders')
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

    return response()->json(['ok' => true, 'data' => $rows]);
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

    $items = [];
    $subtotal = 0;
    foreach ($rows as $r) {
        $productId = (int) ($r->product_id ?? 0);
        $variantId = $r->variant_id === null ? null : (int) $r->variant_id;
        $qty = max(1, (int) ($r->qty ?? 1));
        $price = max(0, (int) ($r->unit_price ?? 0));
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
        $order = $conn->table('Orders')
            ->where('id_pelanggan', $id)
            ->where('status', 'UNPAID')
            ->orderByDesc('createdAt')
            ->first();

        if (!$order) {
            $orderId = (int) $conn->table('Orders')->insertGetId([
                'id_pelanggan' => $id,
                'order_no' => null,
                'status' => 'UNPAID',
                'total' => 0,
                'createdAt' => $now,
            ], 'id_order');

            $order = (object) ['id_order' => $orderId, 'id_pelanggan' => $id, 'status' => 'UNPAID'];
        }

        $orderId = (int) $order->id_order;

        $conn->table('OrderItems')->where('id_order', $orderId)->delete();

        $rows = [];
        $subtotal = 0;
        foreach ($itemsIn as $it) {
            $productId = (int) ($it['product_id'] ?? 0);
            $variantId = $it['variant_id'] === null ? null : (int) ($it['variant_id'] ?? 0);
            $qty = max(1, (int) ($it['qty'] ?? 1));
            $price = max(0, (int) ($it['price'] ?? 0));
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
