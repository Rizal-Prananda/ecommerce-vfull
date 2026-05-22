<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->whereNull('unit')->update(['unit' => 'Pcs']);
        DB::table('products')->where('unit', '')->update(['unit' => 'Pcs']);
        DB::table('products')->where('unit', 'pieces')->update(['unit' => 'Pcs']);
        DB::table('products')->where('unit', 'unit')->update(['unit' => 'Unit']);
    }

    public function down(): void
    {
        //
    }
};

