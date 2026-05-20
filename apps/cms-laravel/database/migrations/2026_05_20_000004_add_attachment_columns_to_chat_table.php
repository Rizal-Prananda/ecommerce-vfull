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
            if (!$schema->hasColumn('Chat', 'attachment_path')) {
                $table->string('attachment_path')->nullable();
            }
            if (!$schema->hasColumn('Chat', 'attachment_original_name')) {
                $table->string('attachment_original_name')->nullable();
            }
            if (!$schema->hasColumn('Chat', 'attachment_mime')) {
                $table->string('attachment_mime')->nullable();
            }
            if (!$schema->hasColumn('Chat', 'attachment_size')) {
                $table->unsignedBigInteger('attachment_size')->nullable();
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
            foreach (['attachment_path', 'attachment_original_name', 'attachment_mime', 'attachment_size'] as $col) {
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

