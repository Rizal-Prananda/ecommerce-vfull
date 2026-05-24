<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(): View
    {
        $desktopSetting = SiteSetting::query()->firstWhere('key', 'marketplace_hero_banner');
        $mobileSetting = SiteSetting::query()->firstWhere('key', 'marketplace_hero_banner_mobile');

        $desktopValue = (string) ($desktopSetting?->value ?? '');
        $mobileValue = (string) ($mobileSetting?->value ?? '');

        $toPreviewUrl = function (string $value): string {
            $v = trim($value);
            if ($v === '') return '';
            if (str_starts_with($v, 'http://') || str_starts_with($v, 'https://')) return $v;
            return $v[0] === '/' ? $v : ('/' . $v);
        };

        return view('admin.marketplace.index', [
            'desktopSetting' => $desktopSetting,
            'mobileSetting' => $mobileSetting,
            'desktopPreviewUrl' => $toPreviewUrl($desktopValue),
            'mobilePreviewUrl' => $toPreviewUrl($mobileValue),
        ]);
    }

    public function updateBanner(Request $request): RedirectResponse
    {
        $request->validate([
            'marketplace_hero_banner' => ['nullable', 'file', 'mimetypes:image/webp,image/jpeg,image/svg+xml', 'max:2048'],
            'marketplace_hero_banner_mobile' => ['nullable', 'file', 'mimetypes:image/webp,image/jpeg,image/svg+xml', 'max:2048'],
        ]);

        $desktop = $request->file('marketplace_hero_banner');
        $mobile = $request->file('marketplace_hero_banner_mobile');

        if (!$desktop && !$mobile) {
            return back()
                ->withErrors(['marketplace_hero_banner' => 'Pilih minimal 1 file untuk diupload.'])
                ->withInput();
        }

        $validateSvgOrRasterDimensions = function (string $field, $file, int $wExpected, int $hExpected) use ($request) {
            if (!$file) return;

            $mime = strtolower((string) $file->getMimeType());
            $isSvg = $mime === 'image/svg+xml' || str_ends_with(strtolower((string) $file->getClientOriginalName()), '.svg');

            if ($isSvg) {
                $raw = (string) file_get_contents($file->getRealPath());
                $w = null;
                $h = null;

                if (preg_match('/\bwidth\s*=\s*"(\d+(?:\.\d+)?)"/i', $raw, $m)) {
                    $w = (int) round((float) $m[1]);
                }
                if (preg_match('/\bheight\s*=\s*"(\d+(?:\.\d+)?)"/i', $raw, $m)) {
                    $h = (int) round((float) $m[1]);
                }

                if (($w === null || $h === null) && preg_match('/\bviewBox\s*=\s*"([\d\.\-]+)\s+([\d\.\-]+)\s+([\d\.\-]+)\s+([\d\.\-]+)"/i', $raw, $m)) {
                    $vw = (int) round((float) $m[3]);
                    $vh = (int) round((float) $m[4]);
                    if ($vw > 0 && $vh > 0) {
                        $w = $w ?? $vw;
                        $h = $h ?? $vh;
                    }
                }

                if ($w !== $wExpected || $h !== $hExpected) {
                    return back()
                        ->withErrors([$field => "Ukuran gambar harus {$wExpected}x{$hExpected}px."])
                        ->withInput();
                }
                return;
            }

            $request->validate([
                $field => ["dimensions:width={$wExpected},height={$hExpected}"],
            ]);
        };

        $resp = $validateSvgOrRasterDimensions('marketplace_hero_banner', $desktop, 1448, 1086);
        if ($resp instanceof RedirectResponse) return $resp;
        $resp = $validateSvgOrRasterDimensions('marketplace_hero_banner_mobile', $mobile, 768, 1024);
        if ($resp instanceof RedirectResponse) return $resp;

        $makeValues = function (string $publicUrl): array {
            $values = ['value' => $publicUrl];
            if (Schema::hasColumn('site_settings', 'type')) {
                $values['type'] = 'image';
            }
            return $values;
        };

        if ($desktop) {
            $path = $desktop->store('banners', 'public');
            $publicUrl = '/product-media/' . ltrim($path, '/');
            SiteSetting::query()->updateOrCreate(['key' => 'marketplace_hero_banner'], $makeValues($publicUrl));
            SiteSetting::query()->updateOrCreate(['key' => 'hero_banner'], $makeValues($publicUrl));
        }

        if ($mobile) {
            $path = $mobile->store('banners', 'public');
            $publicUrl = '/product-media/' . ltrim($path, '/');
            SiteSetting::query()->updateOrCreate(['key' => 'marketplace_hero_banner_mobile'], $makeValues($publicUrl));
        }

        return back()->with('success', 'Hero banner marketplace berhasil diperbarui.');
    }
}
