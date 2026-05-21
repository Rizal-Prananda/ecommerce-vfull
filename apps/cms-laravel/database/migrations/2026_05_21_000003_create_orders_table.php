<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('sqlite');

        if ($schema->hasTable('Orders')) {
            return;
        }

        $schema->create('Orders', function (Blueprint $table) {
            $table->increments('id_order');
            $table->unsignedInteger('id_pelanggan')->index();
            $table->string('order_no')->nullable()->index();
            $table->string('status')->default('PENDING')->index();
            $table->unsignedBigInteger('total')->default(0);
            $table->dateTime('createdAt')->useCurrent()->index();

            $table->index(['id_pelanggan', 'createdAt']);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('sqlite');
        $schema->dropIfExists('Orders');
    }
};

