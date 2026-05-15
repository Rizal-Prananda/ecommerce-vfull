<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

function ensureBootstrapAdmin(): void
{
    $email = strtolower(trim((string) env('ADMIN_EMAIL', '')));
    $password = (string) env('ADMIN_PASSWORD', '');

    if ($email === '' || $password === '') {
        return;
    }

    User::firstOrCreate(
        ['email' => $email],
        [
            'name' => 'Admin',
            'password' => $password,
            'role' => 'ADMIN',
        ]
    );
}

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/login', function () {
    ensureBootstrapAdmin();

    $user = Auth::user();
    if ($user && strtoupper((string) $user->role) === 'ADMIN') {
        return redirect()->intended('/dashboard');
    }

    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {
    ensureBootstrapAdmin();

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $email = strtolower(trim((string) $credentials['email']));
    $password = (string) $credentials['password'];

    if (!Auth::attempt(['email' => $email, 'password' => $password, 'role' => 'ADMIN'], true)) {
        return back()->with('error', 'Login gagal. Pastikan akun ADMIN dan password benar.')->withInput();
    }

    $request->session()->regenerate();
    return redirect()->intended('/dashboard');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('admin');

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
