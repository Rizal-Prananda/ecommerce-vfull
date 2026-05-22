<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('delta');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('source', 50);
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->unsignedInteger('actor_pelanggan_id')->nullable()->index();
            $table->string('reference', 100)->nullable()->index();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_movements');
    }
};
