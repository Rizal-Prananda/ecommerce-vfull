<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $withType = function (array $values, string $type) {
            if (Schema::hasColumn('site_settings', 'type')) {
                $values['type'] = $type;
            }
            return $values;
        };

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'hero_headline'],
            $withType([
                'value' => 'Website Fashion yang Bikin Brand Kamu Dilirik',
                'updated_at' => $now,
                'created_at' => $now,
            ], 'text')
        );

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'hero_subheadline'],
            $withType([
                'value' => 'Kami merancang e-commerce & company profile eksklusif untuk brand fashion. Cepat, elegan, dan gampang di-update sendiri—tanpa ribet coding.',
                'updated_at' => $now,
                'created_at' => $now,
            ], 'text')
        );
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', ['hero_headline', 'hero_subheadline'])->delete();
    }
};

