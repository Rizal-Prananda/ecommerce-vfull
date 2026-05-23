<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 50);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['code' => 'S', 'name' => 'S', 'sort_order' => 10, 'is_active' => true],
            ['code' => 'M', 'name' => 'M', 'sort_order' => 20, 'is_active' => true],
            ['code' => 'L', 'name' => 'L', 'sort_order' => 30, 'is_active' => true],
            ['code' => 'XL', 'name' => 'XL', 'sort_order' => 40, 'is_active' => true],
            ['code' => 'XXL', 'name' => 'XXL', 'sort_order' => 50, 'is_active' => true],
            ['code' => '3XL', 'name' => '3XL', 'sort_order' => 60, 'is_active' => true],
        ];

        foreach ($defaults as $row) {
            DB::table('mst_sizes')->insert([
                ...$row,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_sizes');
    }
};

