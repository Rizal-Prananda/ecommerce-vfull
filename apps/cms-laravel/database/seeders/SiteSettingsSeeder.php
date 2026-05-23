<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $withType = function (array $values) {
            if (Schema::hasColumn('site_settings', 'type')) {
                $values['type'] = 'image';
            }
            return $values;
        };

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'hero_banner'],
            $withType(['value' => '/images/default-hero.webp', 'updated_at' => $now, 'created_at' => $now])
        );

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'marketplace_hero_banner'],
            $withType(['value' => null, 'updated_at' => $now, 'created_at' => $now])
        );

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'marketplace_hero_banner_mobile'],
            $withType(['value' => null, 'updated_at' => $now, 'created_at' => $now])
        );
    }
}
