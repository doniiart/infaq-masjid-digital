<?php

namespace Database\Seeders;

use App\Models\InfaqCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Takmir Super Admin Awal
        User::updateOrCreate(
            ['email' => 'takmir@masjid.test'],
            [
                'name'              => 'Admin Takmir Masjid',
                'password'          => Hash::make('Password123!'),
                'role'              => User::ROLE_SUPER_ADMIN,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Kategori Infaq Standar (termasuk kategori default untuk transaksi QRIS otomatis)
        $categories = [
            [
                'slug'          => 'belum-dialokasikan',
                'name'          => 'Belum Dialokasikan',
                'description'   => 'Penampungan otomatis transaksi QRIS Statis sebelum dialokasikan oleh Bendahara/Takmir.',
                'target_amount' => null,
                'is_default'    => true,
                'is_active'     => true,
            ],
            [
                'slug'          => 'infaq-pembangunan',
                'name'          => 'Infaq Pembangunan',
                'description'   => 'Dana khusus renovasi dan perluasan bangunan utama masjid.',
                'target_amount' => 500000000.00,
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'slug'          => 'infaq-operasional',
                'name'          => 'Infaq Operasional',
                'description'   => 'Biaya listrik, air, kebersihan, dan pemeliharaan harian masjid.',
                'target_amount' => null,
                'is_default'    => false,
                'is_active'     => true,
            ],
            [
                'slug'          => 'infaq-umum',
                'name'          => 'Infaq Umum',
                'description'   => 'Dana kegiatan dakwah, santunan, dan program sosial keumatan.',
                'target_amount' => null,
                'is_default'    => false,
                'is_active'     => true,
            ],
        ];

        foreach ($categories as $category) {
            InfaqCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}