<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('sqlite');

        if ($schema->hasTable('OrderItems')) {
            return;
        }

        $schema->create('OrderItems', function (Blueprint $table) {
            $table->increments('id_item');
            $table->unsignedInteger('id_order')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('size')->nullable();
            $table->text('image')->nullable();
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('line_total')->default(0);
            $table->dateTime('createdAt')->useCurrent()->index();
            $table->dateTime('updatedAt')->useCurrent()->index();

            $table->index(['id_order', 'product_id', 'variant_id']);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('sqlite');
        $schema->dropIfExists('OrderItems');
    }
};

