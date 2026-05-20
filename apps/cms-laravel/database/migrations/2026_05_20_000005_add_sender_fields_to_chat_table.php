<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('sqlite');

        if (!$schema->hasTable('Chat')) {
            return;
        }

        $schema->table('Chat', function (Blueprint $table) use ($schema) {
            if (!$schema->hasColumn('Chat', 'pengirim_user_id')) {
                $table->unsignedBigInteger('pengirim_user_id')->nullable();
            }
            if (!$schema->hasColumn('Chat', 'pengirim_nama')) {
                $table->string('pengirim_nama')->nullable();
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('sqlite');

        if (!$schema->hasTable('Chat')) {
            return;
        }

        $schema->table('Chat', function (Blueprint $table) use ($schema) {
            $drops = [];
            foreach (['pengirim_user_id', 'pengirim_nama'] as $col) {
                if ($schema->hasColumn('Chat', $col)) {
                    $drops[] = $col;
                }
            }
            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};

