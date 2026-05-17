<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'avatar' => ['nullable', 'file', 'image', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $user->name = trim((string) $validated['name']);
        $user->email = strtolower(trim((string) $validated['email']));

        if (!empty($validated['password'])) {
            $user->password = Hash::make((string) $validated['password']);
        }

        $avatar = $request->file('avatar');
        if ($avatar) {
            $filename = (string) Str::uuid() . '.' . $avatar->getClientOriginalExtension();

            $old = (string) ($user->avatar_path ?? '');
            $user->avatar_path = $avatar->storeAs('avatars', $filename, 'public');

            if ($old !== '') {
                if (str_starts_with($old, 'avatars/')) {
                    Storage::disk('public')->delete($old);
                }
                if (str_starts_with($old, '/uploads/avatars/')) {
                    $oldPath = public_path(ltrim($old, '/'));
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
