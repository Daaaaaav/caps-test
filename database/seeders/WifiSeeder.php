<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WifiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data diambil dari krbs_db.sql
        $wifis = [
            [
                'wifi_id' => 1,
                'company_id' => 2, // Kebun Raya Bogor
                'ssid' => 'KRB_GUEST_FREE',
                'password' => 'BogorSejuk2025',
                'location' => 'Lobby Utama & Loket',
                'is_active' => 1,
                'created_at' => '2025-11-22 12:18:23',
                'updated_at' => '2025-11-22 12:18:23',
            ],
            [
                'wifi_id' => 2,
                'company_id' => 2, // Kebun Raya Bogor
                'ssid' => 'KRB_STAFF_ONLY',
                'password' => 'Staff@Bogor123',
                'location' => 'Ruang Office Lt. 2',
                'is_active' => 1,
                'created_at' => '2025-11-22 12:18:23',
                'updated_at' => '2025-11-22 12:18:23',
            ],
            [
                'wifi_id' => 3,
                'company_id' => 3, // Kebun Raya Bali
                'ssid' => 'BALI_VISITOR',
                'password' => 'BaliExotic',
                'location' => 'Area Restaurant',
                'is_active' => 1,
                'created_at' => '2025-11-22 12:18:23',
                'updated_at' => '2025-11-22 12:18:23',
            ],
            [
                'wifi_id' => 4,
                'company_id' => 4, // Kebun Raya Cibodas
                'ssid' => 'CIBODAS_ADMIN',
                'password' => 'Cibodas#99',
                'location' => 'Ruang Server',
                'is_active' => 1,
                'created_at' => '2025-11-22 12:18:23',
                'updated_at' => '2025-11-22 12:18:23',
            ],
        ];

        // Insert data (menggunakan insert agar ID tetap terjaga)
        DB::table('wifis')->insert($wifis);
    }
}