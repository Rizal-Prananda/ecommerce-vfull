<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'type')) {
                $table->string('type', 50)->default('text')->after('value');
            }
        });

        $now = now();

        DB::table('site_settings')->updateOrInsert(
            ['key' => 'hero_banner'],
            ['value' => '/images/default-hero.webp', 'type' => 'image', 'updated_at' => $now, 'created_at' => $now]
        );
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};

