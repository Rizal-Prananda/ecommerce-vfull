<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $setting = SiteSetting::query()->firstWhere('key', 'hero_banners');
        $raw = (string) ($setting?->value ?? '');

        $toPreviewUrl = function (string $value): string {
            $v = trim($value);
            if ($v === '') return '';
            if (str_starts_with($v, 'http://') || str_starts_with($v, 'https://')) return $v;
            return $v[0] === '/' ? $v : ('/' . $v);
        };

        $decoded = [];
        try {
            $j = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($j)) {
                foreach ($j as $it) {
                    $s = trim((string) $it);
                    if ($s !== '') $decoded[] = $s;
                }
            }
        } catch (\Throwable $e) {
            $decoded = [];
        }

        $decoded = array_values(array_unique($decoded));
        $banners = array_map(function (string $v) use ($toPreviewUrl) {
            $date = '';
            try {
                $value = trim($v);
                if (str_starts_with($value, '/product-media/')) {
                    $rel = ltrim(substr($value, strlen('/product-media/')), '/');
                    if (Storage::disk('public')->exists($rel)) {
                        $ts = (int) Storage::disk('public')->lastModified($rel);
                        if ($ts > 0) {
                            $date = \Carbon\Carbon::createFromTimestamp($ts)->timezone(config('app.timezone'))->format('d M Y');
                        }
                    }
                }
            } catch (\Throwable $e) {
                $date = '';
            }

            return ['id' => $v, 'url' => $toPreviewUrl($v), 'date' => $date];
        }, $decoded);

        return view('admin.home.index', [
            'banners' => $banners,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'hero_banners' => ['nullable', 'array'],
            'hero_banners.*' => ['file', 'mimetypes:image/webp,image/svg+xml', 'max:2048'],
            'hero_banners_remove' => ['nullable', 'array'],
            'hero_banners_remove.*' => ['string', 'max:500'],
        ]);

        $files = $request->file('hero_banners', []);
        $remove = $request->input('hero_banners_remove', []);

        $hasUpload = is_array($files) && count(array_filter($files)) > 0;
        $hasRemove = is_array($remove) && count(array_filter($remove)) > 0;
        if (!$hasUpload && !$hasRemove) {
            return back()
                ->withErrors(['hero_banners' => 'Pilih minimal 1 file untuk diupload atau pilih file untuk dihapus.'])
                ->withInput();
        }

        $setting = SiteSetting::query()->firstWhere('key', 'hero_banners');
        $raw = (string) ($setting?->value ?? '');
        $existing = [];
        try {
            $j = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($j)) {
                foreach ($j as $it) {
                    $s = trim((string) $it);
                    if ($s !== '') $existing[] = $s;
                }
            }
        } catch (\Throwable $e) {
            $existing = [];
        }

        $existing = array_values(array_unique($existing));
        $removeSet = [];
        if (is_array($remove)) {
            foreach ($remove as $v) {
                $s = trim((string) $v);
                if ($s !== '') $removeSet[$s] = true;
            }
        }

        $kept = array_values(array_filter($existing, fn($v) => !isset($removeSet[$v])));
        $uploaded = [];
        if (is_array($files)) {
            foreach ($files as $f) {
                if (!$f) continue;
                $path = $f->store('banners', 'public');
                $uploaded[] = '/product-media/' . ltrim($path, '/');
            }
        }

        $final = array_values(array_unique(array_merge($kept, $uploaded)));
        if (count($final) > 10) {
            $final = array_slice($final, 0, 10);
        }

        $values = ['value' => json_encode($final, JSON_UNESCAPED_SLASHES)];
        if (Schema::hasColumn('site_settings', 'type')) {
            $values['type'] = 'text';
        }
        SiteSetting::query()->updateOrCreate(['key' => 'hero_banners'], $values);

        return back()->with('success', 'Homepage slideshow berhasil diperbarui.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['string', 'max:500'],
        ]);

        $order = array_values(array_filter(array_map(fn($v) => trim((string) $v), $validated['order'])));

        $setting = SiteSetting::query()->firstWhere('key', 'hero_banners');
        $raw = (string) ($setting?->value ?? '');
        $existing = [];
        try {
            $j = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($j)) {
                foreach ($j as $it) {
                    $s = trim((string) $it);
                    if ($s !== '') $existing[] = $s;
                }
            }
        } catch (\Throwable $e) {
            $existing = [];
        }

        $existing = array_values(array_unique($existing));
        $existingSet = array_fill_keys($existing, true);

        $next = [];
        foreach ($order as $id) {
            if (isset($existingSet[$id])) {
                $next[] = $id;
                unset($existingSet[$id]);
            }
        }
        foreach ($existing as $id) {
            if (isset($existingSet[$id])) {
                $next[] = $id;
            }
        }

        $next = array_values(array_unique($next));
        if (count($next) > 10) {
            $next = array_slice($next, 0, 10);
        }

        $values = ['value' => json_encode($next, JSON_UNESCAPED_SLASHES)];
        if (Schema::hasColumn('site_settings', 'type')) {
            $values['type'] = 'text';
        }
        SiteSetting::query()->updateOrCreate(['key' => 'hero_banners'], $values);

        return response()->json(['ok' => true, 'data' => ['count' => count($next)]]);
    }
}
