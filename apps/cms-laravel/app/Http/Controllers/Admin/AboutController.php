<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $setting = SiteSetting::query()->firstWhere('key', 'about_photos');
        $raw = trim((string) ($setting?->value ?? ''));
        $photos = [];

        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $photos = array_values(array_filter(array_map(function ($v) {
                    $s = trim((string) $v);
                    return $s !== '' ? $s : null;
                }, $decoded)));
            }
        }

        $toPreviewUrl = function (string $value): string {
            $v = trim($value);
            if ($v === '') return '';
            if (str_starts_with($v, 'http://') || str_starts_with($v, 'https://')) return $v;
            return $v[0] === '/' ? $v : ('/' . $v);
        };

        $items = array_map(function (string $url) use ($toPreviewUrl) {
            return [
                'id' => $url,
                'url' => $toPreviewUrl($url),
            ];
        }, $photos);

        return view('admin.about.index', [
            'items' => $items,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'about_photos' => ['nullable'],
            'about_photos.*' => ['file', 'mimetypes:image/webp,image/jpeg,image/png,image/svg+xml', 'max:2048'],
            'about_photos_remove' => ['nullable', 'array'],
            'about_photos_remove.*' => ['string', 'max:2048'],
        ]);

        $setting = SiteSetting::query()->firstWhere('key', 'about_photos');
        $raw = trim((string) ($setting?->value ?? ''));
        $photos = [];
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $photos = array_values(array_filter(array_map(function ($v) {
                    $s = trim((string) $v);
                    return $s !== '' ? $s : null;
                }, $decoded)));
            }
        }

        $remove = $request->input('about_photos_remove', []);
        if (is_array($remove) && count($remove) > 0) {
            $removeSet = array_fill_keys(array_map(fn($v) => (string) $v, $remove), true);
            $photos = array_values(array_filter($photos, fn($u) => !isset($removeSet[$u])));
        }

        $uploads = $request->file('about_photos', []);
        if (is_array($uploads) && count($uploads) > 0) {
            foreach ($uploads as $file) {
                if (!$file) continue;
                $path = $file->store('about', 'public');
                $publicUrl = '/product-media/' . ltrim($path, '/');
                $photos[] = $publicUrl;
            }
        }

        $photos = array_values(array_unique(array_filter(array_map('trim', $photos))));
        if (count($photos) > 20) {
            $photos = array_slice($photos, 0, 20);
        }

        $makeValues = function (array $values): array {
            if (!Schema::hasColumn('site_settings', 'type')) {
                unset($values['type']);
                return $values;
            }
            if (!array_key_exists('type', $values)) {
                $values['type'] = 'text';
            }
            return $values;
        };

        SiteSetting::query()->updateOrCreate(
            ['key' => 'about_photos'],
            $makeValues(['value' => json_encode($photos, JSON_UNESCAPED_SLASHES), 'type' => 'json'])
        );

        return back()->with('success', 'Konten About berhasil diperbarui.');
    }
}

