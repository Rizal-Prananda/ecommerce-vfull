<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login dulu.');
        }

        $role = strtoupper((string) ($user->role ?? ''));
        if (!in_array($role, ['ADMIN', 'SUPERADMIN'], true)) {
            abort(403);
        }

        $status = strtoupper((string) ($user->status ?? 'ACTIVE'));
        if ($status !== 'ACTIVE') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Akun kamu nonaktif.');
        }

        return $next($request);
    }
}
