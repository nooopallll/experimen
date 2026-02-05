<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('treatments')->insert([
            [
                'kategori' => 'Sepatu',
                'nama_treatment' => 'Deep Clean',
                'harga' => 50000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori' => 'Sepatu',
                'nama_treatment' => 'Unyellowing',
                'harga' => 75000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kategori' => 'Tas',
                'nama_treatment' => 'Leather Care',
                'harga' => 100000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
