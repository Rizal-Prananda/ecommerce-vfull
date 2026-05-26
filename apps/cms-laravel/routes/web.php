<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function ensureBootstrapAdmin(): void
{
    $email = strtolower(trim((string) env('ADMIN_EMAIL', '')));
    $password = (string) env('ADMIN_PASSWORD', '');

    if ($email === '' || $password === '') {
        return;
    }

    $admin = User::firstOrCreate(
        ['email' => $email],
        [
            'name' => 'Admin',
            'password' => Hash::make($password),
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]
    );

    if (empty($admin->status)) {
        $admin->status = 'ACTIVE';
        $admin->save();
    }
}

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/product-media/{path}', function (string $path) {
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, '..') || !(str_starts_with($path, 'products/') || str_starts_with($path, 'banners/') || str_starts_with($path, 'about/'))) {
        abort(404);
    }

    $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['webp', 'svg', 'jpg', 'jpeg', 'png'], true)) {
        abort(404);
    }

    if (Storage::disk('public')->exists($path)) {
        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    // Backward compatibility: some older uploads were stored under storage/app/{path}
    // instead of the public disk, but marketplace URLs still point to /product-media/...
    $legacyPath = storage_path('app/' . $path);
    if (!File::exists($legacyPath)) {
        abort(404);
    }

    return response()->file($legacyPath, [
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->where('path', '.*');

Route::get('/login', function () {
    ensureBootstrapAdmin();

    $user = Auth::user();
    $role = strtoupper((string) ($user->role ?? ''));
    $status = strtoupper((string) ($user->status ?? 'ACTIVE'));
    if ($user && in_array($role, ['ADMIN', 'SUPERADMIN'], true) && $status === 'ACTIVE') {
        return redirect()->intended('/dashboard');
    }

    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    ensureBootstrapAdmin();

    $credentials = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $login = strtolower(trim((string) $credentials['login']));
    $password = (string) $credentials['password'];

    // Try login with email or username
    $attempt = Auth::attempt(['email' => $login, 'password' => $password], true)
        || Auth::attempt(['username' => $login, 'password' => $password], true);

    if (!$attempt) {
        return back()->with('error', 'Login gagal. Email/Username atau password salah.')->withInput();
    }

    $request->session()->regenerate();
    $user = $request->user();
    if ($user) {
        $status = strtoupper((string) ($user->status ?? 'ACTIVE'));
        $role = strtoupper((string) ($user->role ?? ''));
        if (!in_array($role, ['ADMIN', 'SUPERADMIN'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->with('error', 'Login gagal. Akun tidak memiliki akses.')->withInput();
        }
        if ($status !== 'ACTIVE') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->with('error', 'Akun kamu nonaktif.')->withInput();
        }
        if (empty($user->status)) {
            $user->status = 'ACTIVE';
        }
        $user->last_login_at = now();
        $user->save();
    }
    return redirect()->intended('/dashboard');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('admin')->name('logout');

Route::get('/profile', [ProfileController::class, 'edit'])->middleware('admin')->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->middleware('admin')->name('profile.update');

Route::get('/dashboard', function () {
    $metrics = [
        ['label' => 'Order to Pickup', 'value' => 15, 'tone' => 'from-indigo-500 to-violet-500'],
        ['label' => 'Order Shipping', 'value' => 12, 'tone' => 'from-rose-500 to-orange-500'],
        ['label' => 'New Customers', 'value' => 8, 'tone' => 'from-sky-500 to-blue-500'],
        ['label' => 'Revenue', 'value' => 'Rp 24,5jt', 'tone' => 'from-emerald-500 to-teal-500'],
    ];

    $activeOrders = [
        ['orderNo' => 'ROZ-1045', 'customer' => 'Kevin', 'orderDate' => '2026-05-12'],
        ['orderNo' => 'ROZ-1046', 'customer' => 'Salsa', 'orderDate' => '2026-05-13'],
        ['orderNo' => 'ROZ-1047', 'customer' => 'Dina', 'orderDate' => '2026-05-14'],
    ];

    $actionItems = [
        ['title' => 'Update konten hero', 'desc' => 'Sesuaikan teks headline dan CTA agar lebih jelas.', 'cta' => 'Edit Konten'],
        ['title' => 'Tambah produk Featured', 'desc' => 'Isi minimal 6 item agar listing marketplace terlihat penuh.', 'cta' => 'Tambah Produk'],
    ];

    return view('dashboard', compact('metrics', 'activeOrders', 'actionItems'));
})->middleware('admin');

Route::get('/dashboard/users', function () {
    $q = trim((string) request()->query('q', ''));
    $role = strtoupper(trim((string) request()->query('role', '')));

    $users = User::query()
        ->select(['id', 'name', 'email', 'username', 'role', 'status', 'avatar_path', 'last_login_at', 'created_at', 'updated_at'])
        ->when($q !== '', function ($qq) use ($q) {
            $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            });
        })
        ->when(in_array($role, ['ADMIN', 'CS', 'TEKNISI', 'USER'], true), fn($qq) => $qq->where('role', $role))
        ->orderByDesc('created_at')
        ->simplePaginate(25)
        ->withQueryString();
    $roles = ['ADMIN', 'CS', 'TEKNISI', 'USER'];
    return view('users', compact('users', 'roles'));
})->middleware('admin')->name('users.index');

Route::get('/dashboard/users/{user}/edit', function (User $user) {
    return view('users.edit', compact('user'));
})->middleware('admin')->name('users.edit');

Route::post('/dashboard/users', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', 'unique:users,username'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'role' => ['required', 'in:ADMIN,CS,TEKNISI,USER'],
        'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        'avatar' => ['nullable', 'file', 'image', 'max:2048'],
    ]);

    $user = User::create([
        'name' => trim((string) $validated['name']),
        'username' => strtolower(trim((string) $validated['username'])),
        'email' => strtolower(trim((string) $validated['email'])),
        'role' => strtoupper((string) $validated['role']),
        'password' => Hash::make((string) $validated['password']),
        'status' => 'ACTIVE',
        'email_verified_at' => now(),
    ]);

    $avatar = $request->file('avatar');
    if ($avatar) {
        $filename = (string) Str::uuid() . '.' . $avatar->getClientOriginalExtension();
        $uploadsDir = public_path('uploads/avatars');
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $avatar->move($uploadsDir, $filename);
        $user->avatar_path = 'uploads/avatars/' . $filename;
        $user->save();
    }

    return back()->with('success', 'Pengguna berhasil ditambahkan.');
})->middleware('admin');

Route::put('/dashboard/users/{user}', function (Request $request, User $user) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
        'role' => ['required', 'in:USER,ADMIN,SUPERADMIN'],
        'status' => ['nullable', 'in:active,inactive,suspended'],
        'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        'avatar' => ['nullable', 'file', 'image', 'max:2048'],
    ]);

    $user->name = trim((string) $validated['name']);
    $user->username = strtolower(trim((string) $validated['username']));
    $user->role = strtoupper((string) $validated['role']);
    if (array_key_exists('status', $validated) && $validated['status'] !== null) {
        $user->status = strtoupper((string) $validated['status']);
    }
    if (!empty($validated['password'])) {
        $user->password = Hash::make((string) $validated['password']);
    }

    $avatar = $request->file('avatar');
    if ($avatar) {
        $filename = (string) Str::uuid() . '.' . $avatar->getClientOriginalExtension();

        $old = (string) ($user->avatar_path ?? '');
        $uploadsDir = public_path('uploads/avatars');
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $avatar->move($uploadsDir, $filename);
        $user->avatar_path = 'uploads/avatars/' . $filename;

        if ($old !== '') {
            if (str_starts_with($old, 'uploads/avatars/')) {
                $oldPath = public_path($old);
                if (is_file($oldPath)) @unlink($oldPath);
            }
            if (str_starts_with($old, 'avatars/')) {
                Storage::disk('public')->delete($old);
            }
        }
    }
    $user->save();

    return back()->with('success', 'Pengguna berhasil diperbarui.');
})->middleware('admin')->name('users.update');

Route::put('/dashboard/users/{user}/toggle', function (Request $request, User $user) {
    $me = $request->user();
    if ($me && (int) $me->id === (int) $user->id) {
        return back()->with('error', 'Tidak bisa menonaktifkan akun yang sedang login.');
    }

    $current = strtoupper((string) ($user->status ?? 'ACTIVE'));
    $user->status = $current === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    $user->save();

    return back()->with('success', 'Status pengguna berhasil diubah.');
})->middleware('admin');

Route::get('/dashboard/users/export', function () {
    $q = trim((string) request()->query('q', ''));
    $role = strtoupper(trim((string) request()->query('role', '')));

    $users = User::query()
        ->when($q !== '', function ($qq) use ($q) {
            $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            });
        })
        ->when(in_array($role, ['ADMIN', 'CS', 'TEKNISI', 'USER'], true), fn($qq) => $qq->where('role', $role))
        ->orderByDesc('created_at')
        ->get();

    $lines = [];
    $lines[] = 'id,name,email,role,status,last_active';
    foreach ($users as $u) {
        $status = strtoupper((string) ($u->status ?? 'ACTIVE')) === 'ACTIVE' ? 'Active' : 'Inactive';
        $lastAt = $u->last_login_at ?? $u->updated_at;
        $last = $lastAt ? $lastAt->toISOString() : '';
        $lines[] = implode(',', [
            $u->id,
            '"' . str_replace('"', '""', (string) $u->name) . '"',
            '"' . str_replace('"', '""', (string) $u->email) . '"',
            '"' . str_replace('"', '""', (string) $u->role) . '"',
            $status,
            $last,
        ]);
    }

    $csv = implode("\n", $lines) . "\n";
    return response($csv, 200, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="users.csv"',
    ]);
})->middleware('admin');

Route::delete('/dashboard/users/{user}', function (Request $request, User $user) {
    $me = $request->user();
    if ($me && (int) $me->id === (int) $user->id) {
        return back()->with('error', 'Tidak bisa menghapus akun yang sedang login.');
    }

    $user->delete();
    return back()->with('success', 'Pengguna berhasil dihapus.');
})->middleware('admin');

Route::get('/dashboard/chat', [\App\Http\Controllers\ChatAdminController::class, 'listConversations'])->middleware('admin');
Route::get('/dashboard/chat/poll', [\App\Http\Controllers\ChatAdminController::class, 'pollConversations'])->middleware('admin');
Route::get('/dashboard/chat/{conversationId}', [\App\Http\Controllers\ChatAdminController::class, 'viewConversation'])->middleware('admin');
Route::get('/dashboard/chat/{conversationId}/poll', [\App\Http\Controllers\ChatAdminController::class, 'pollConversation'])->middleware('admin');
Route::post('/dashboard/chat/{conversationId}/reply', [\App\Http\Controllers\ChatAdminController::class, 'reply'])->middleware('admin');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('products', ProductController::class)->except(['show', 'destroy']);
    Route::get('stock', [ProductController::class, 'stock'])->name('products.stock');
    Route::put('stock/{product}', [ProductController::class, 'updateStock'])->name('products.stock.update');
    Route::put('stock/{product}/variants', [ProductController::class, 'updateVariants'])->name('products.stock.variants.update');
    Route::post('stock/{product}/variants/mutations', [ProductController::class, 'storeVariantMutation'])->name('products.stock.variants.mutations.store');
    Route::get('stock/{product}/adjustment', [ProductController::class, 'adjustment'])->name('products.stock.adjustment');
    Route::get('stock/{product}/mutations', [ProductController::class, 'mutations'])->name('products.stock.mutations');
    Route::post('stock/{product}/mutations', [ProductController::class, 'storeMutation'])->name('products.stock.mutations.store');
    Route::get('stock/{product}/mutations/export', [ProductController::class, 'exportMutations'])->name('products.stock.mutations.export');

    Route::get('marketplace', [MarketplaceController::class, 'index'])->name('admin.marketplace.index');
    Route::post('marketplace/hero-banner', [MarketplaceController::class, 'updateBanner'])->name('admin.marketplace.banner');

    Route::get('homepage', [HomeController::class, 'index'])->name('admin.homepage.index');
    Route::post('homepage', [HomeController::class, 'update'])->name('admin.homepage.update');
    Route::post('homepage/reorder', [HomeController::class, 'reorder'])->name('admin.homepage.reorder');

    Route::get('about', [\App\Http\Controllers\Admin\AboutController::class, 'index'])->name('admin.about.index');
    Route::post('about', [\App\Http\Controllers\Admin\AboutController::class, 'update'])->name('admin.about.update');
});
