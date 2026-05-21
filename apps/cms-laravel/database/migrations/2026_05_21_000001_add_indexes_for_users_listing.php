<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS users_created_at_index ON users (created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS users_role_created_at_index ON users (role, created_at)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_created_at_index');
        DB::statement('DROP INDEX IF EXISTS users_role_created_at_index');
    }
};
