<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('customer_sqlite');

        if (!$schema->hasTable('Pelanggan')) {
            $schema->create('Pelanggan', function (Blueprint $table) {
                $table->increments('id_pelanggan');
                $table->string('namalengkap_pelanggan');
                $table->string('email_pelanggan')->unique();
                $table->string('notelepon_pelanggan')->nullable();
                $table->string('password');
                $table->dateTime('createdAt')->useCurrent();
                $table->dateTime('last_update')->nullable();
                $table->dateTime('last_update_password')->nullable();
            });
        }

        if (!$schema->hasTable('Chat')) {
            $schema->create('Chat', function (Blueprint $table) {
                $table->increments('id_chat');
                $table->unsignedInteger('id_pelanggan');
                $table->string('pengirim');
                $table->text('pesan');
                $table->boolean('dibaca_admin')->default(false);
                $table->dateTime('createdAt')->useCurrent();

                $table->index(['id_pelanggan', 'createdAt']);
                $table
                    ->foreign('id_pelanggan')
                    ->references('id_pelanggan')
                    ->on('Pelanggan')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('customer_sqlite');

        if ($schema->hasTable('Chat')) {
            $schema->drop('Chat');
        }

        if ($schema->hasTable('Pelanggan')) {
            $schema->drop('Pelanggan');
        }
    }
};
