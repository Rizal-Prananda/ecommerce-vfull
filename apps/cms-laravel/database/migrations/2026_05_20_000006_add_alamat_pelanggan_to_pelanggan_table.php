<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('sqlite');

        if ($schema->hasTable('Pelanggan') && !$schema->hasColumn('Pelanggan', 'alamat_pelanggan')) {
            $schema->table('Pelanggan', function (Blueprint $table) {
                $table->text('alamat_pelanggan')->nullable();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('sqlite');

        if ($schema->hasTable('Pelanggan') && $schema->hasColumn('Pelanggan', 'alamat_pelanggan')) {
            $schema->table('Pelanggan', function (Blueprint $table) {
                $table->dropColumn('alamat_pelanggan');
            });
        }
    }
};

