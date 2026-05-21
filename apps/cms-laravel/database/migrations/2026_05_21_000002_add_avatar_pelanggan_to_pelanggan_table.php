<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('customer_sqlite');

        if ($schema->hasTable('Pelanggan') && !$schema->hasColumn('Pelanggan', 'avatar_pelanggan')) {
            $schema->table('Pelanggan', function (Blueprint $table) {
                $table->string('avatar_pelanggan')->nullable()->after('password');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('customer_sqlite');

        if ($schema->hasTable('Pelanggan') && $schema->hasColumn('Pelanggan', 'avatar_pelanggan')) {
            $schema->table('Pelanggan', function (Blueprint $table) {
                $table->dropColumn('avatar_pelanggan');
            });
        }
    }
};
