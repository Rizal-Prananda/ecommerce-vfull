<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('mst_label_id')->nullable()->after('category')->constrained('mst_labels')->nullOnDelete();
            $table->index(['mst_label_id', 'is_active']);
        });

        $labelIds = DB::table('mst_labels')->pluck('id', 'code');
        $noneId = (int) ($labelIds['none'] ?? 0);
        $newId = (int) ($labelIds['new'] ?? 0);
        $promoId = (int) ($labelIds['promo'] ?? 0);
        $bestSellerId = (int) ($labelIds['best_seller'] ?? 0);

        $products = DB::table('products')->select(['id', 'is_sale', 'is_new', 'is_best_seller'])->get();
        foreach ($products as $p) {
            $id = (int) ($p->id ?? 0);
            if ($id <= 0) {
                continue;
            }

            $target = $noneId;
            if (!empty($p->is_sale) && $promoId > 0) {
                $target = $promoId;
            } elseif (!empty($p->is_best_seller) && $bestSellerId > 0) {
                $target = $bestSellerId;
            } elseif (!empty($p->is_new) && $newId > 0) {
                $target = $newId;
            }

            if ($target > 0) {
                DB::table('products')->where('id', $id)->update(['mst_label_id' => $target]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['mst_label_id', 'is_active']);
            $table->dropConstrainedForeignId('mst_label_id');
        });
    }
};

