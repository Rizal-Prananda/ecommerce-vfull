<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('sqlite');

        if ($schema->hasTable('OrderItems') && !$schema->hasColumn('OrderItems', 'status')) {
            $schema->table('OrderItems', function (Blueprint $table) {
                $table->string('status')->default('UNPAID')->index();
                $table->index(['id_order', 'status']);
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('sqlite');

        if ($schema->hasTable('OrderItems') && $schema->hasColumn('OrderItems', 'status')) {
            $schema->table('OrderItems', function (Blueprint $table) {
                $table->dropIndex(['id_order', 'status']);
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });
        }
    }
};

