<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('mst_size_id')->nullable()->after('product_id')->constrained('mst_sizes');
            $table->index(['product_id', 'mst_size_id']);
        });

        $sizes = DB::table('mst_sizes')->select(['id', 'code'])->get();
        $map = [];
        foreach ($sizes as $s) {
            $map[strtoupper(trim((string) $s->code))] = (int) $s->id;
        }

        $defaultMstSizeId = $map['M'] ?? null;

        $variants = DB::table('product_variants')->select(['id', 'size'])->get();
        foreach ($variants as $v) {
            $raw = strtoupper(trim((string) ($v->size ?? '')));
            $mstSizeId = $map[$raw] ?? $defaultMstSizeId;
            if ($mstSizeId !== null) {
                DB::table('product_variants')->where('id', (int) $v->id)->update(['mst_size_id' => (int) $mstSizeId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mst_size_id');
        });
    }
};

