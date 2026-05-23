<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SiteSettingsSeeder::class);

        if (config('app.env') === 'local') {
            $email = 'test@example.com';
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password12345'),
                    'role' => 'ADMIN',
                    'status' => 'ACTIVE',
                ]
            );
        }

        $schema = Schema::connection('sqlite');
        if (!$schema->hasTable('Pelanggan') || !$schema->hasTable('Chat')) {
            return;
        }

        $conn = DB::connection('sqlite');
        $faker = fake();

        $now = Carbon::now();
        $customerTarget = 25;
        $existing = (int) $conn->table('Pelanggan')->count();
        if ($existing >= $customerTarget) {
            return;
        }

        for ($i = $existing; $i < $customerTarget; $i++) {
            $name = $faker->name();
            $emailCustomer = 'customer' . ($i + 1) . '.' . $faker->unique()->safeEmail();
            $phone = $faker->optional()->phoneNumber();

            $customerId = (int) $conn->table('Pelanggan')->insertGetId([
                'namalengkap_pelanggan' => $name,
                'email_pelanggan' => $emailCustomer,
                'notelepon_pelanggan' => $phone,
                'password' => Hash::make('customer12345'),
                'createdAt' => $now->copy()->subDays(rand(1, 30))->toDateTimeString(),
                'last_update' => $now->copy()->subDays(rand(0, 10))->toDateTimeString(),
                'last_update_password' => $now->copy()->subDays(rand(0, 20))->toDateTimeString(),
            ]);

            $messageCount = rand(0, 10);
            $start = $now->copy()->subHours(rand(1, 72));
            $lastSender = null;

            for ($m = 0; $m < $messageCount; $m++) {
                $sender = $m % 2 === 0 ? 'pelanggan' : 'admin';
                $lastSender = $sender;
                $createdAt = $start->copy()->addMinutes($m * rand(2, 12));

                $conn->table('Chat')->insert([
                    'id_pelanggan' => $customerId,
                    'pengirim' => $sender,
                    'pesan' => $sender === 'admin'
                        ? $faker->randomElement(['Baik, saya bantu ya.', 'Siap, boleh info detailnya?', 'Oke, saya cek dulu.'])
                        : $faker->randomElement(['Halo admin, saya mau tanya.', 'Bisa bantu saya?', 'Saya sudah order, bagaimana statusnya?']),
                    'dibaca_admin' => $sender === 'pelanggan' ? (rand(0, 1) === 1 ? 1 : 0) : 1,
                    'createdAt' => $createdAt->toDateTimeString(),
                ]);
            }

            if ($lastSender === 'pelanggan') {
                $conn
                    ->table('Chat')
                    ->where('id_pelanggan', $customerId)
                    ->where('pengirim', 'pelanggan')
                    ->orderByDesc('createdAt')
                    ->limit(1)
                    ->update(['dibaca_admin' => 0]);
            }
        }
    }
}
