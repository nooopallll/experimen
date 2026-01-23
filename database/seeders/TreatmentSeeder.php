<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Treatment; // Pastikan import Model

class TreatmentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nama_treatment' => 'Deep Clean', 'harga' => 50000],
            ['nama_treatment' => 'Fast Clean', 'harga' => 35000],
            ['nama_treatment' => 'Unyellowing', 'harga' => 60000],
            ['nama_treatment' => 'Repaint Full', 'harga' => 150000],
            ['nama_treatment' => 'Repaint Boost', 'harga' => 80000],
            ['nama_treatment' => 'Repaint Midsole', 'harga' => 100000],
            ['nama_treatment' => 'Repair Sole', 'harga' => 45000],
            ['nama_treatment' => 'Reglue', 'harga' => 40000],
            ['nama_treatment' => 'Sewing/Jahit', 'harga' => 30000],
            ['nama_treatment' => 'Cuci Tas Small', 'harga' => 40000],
            ['nama_treatment' => 'Cuci Tas Medium', 'harga' => 55000],
            ['nama_treatment' => 'Cuci Tas Large', 'harga' => 75000],
            ['nama_treatment' => 'Cuci Topi', 'harga' => 30000],
            ['nama_treatment' => 'Leather Care', 'harga' => 65000],
            ['nama_treatment' => 'Suede Treatment', 'harga' => 60000],
            ['nama_treatment' => 'Insole Replacement', 'harga' => 25000],
            ['nama_treatment' => 'Laces Cleaning', 'harga' => 10000],
        ];

        foreach ($data as $item) {
            Treatment::create($item);
        }
    }
}