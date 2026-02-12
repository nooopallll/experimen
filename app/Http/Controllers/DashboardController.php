<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. SECURITY CHECK
        if (auth()->user()->role !== 'owner') {
            return redirect()->route('dashboard');
        }

        // === A. DATA KARTU (SELALU HARI INI) ===
        $pendapatanHariIni = Order::whereDate('created_at', today())->sum('total_harga');
        $customerHariIni = Order::whereDate('created_at', today())->count();
        $barangMasukHariIni = OrderDetail::whereDate('created_at', today())->count();


        // === B. SETUP FILTER ===
        $filterType = $request->input('filter_type', 'harian'); // Default harian
        
        $chartLabels = [];
        $chartValues = [];
        $startDate = null;
        $endDate = null;

        if ($filterType === 'bulanan') {
            // === LOGIKA FILTER BULANAN (TAMPILKAN 4 MINGGU) ===
            $bulanInput = $request->input('bulan', now()->format('Y-m'));
            
            $start = Carbon::parse($bulanInput)->startOfMonth();
            $end = Carbon::parse($bulanInput)->endOfMonth();
            
            // Simpan untuk filter query Order & Table
            $startDate = $start->format('Y-m-d');
            $endDate = $end->format('Y-m-d');

            // Ambil semua data di bulan ini
            $ordersInMonth = Order::whereBetween('created_at', [$start, $end])->get();

            // Siapkan 4 Bucket Minggu
            $weeklyData = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            foreach ($ordersInMonth as $order) {
                $day = $order->created_at->day;
                
                // Kelompokkan tanggal 1-31 ke dalam 4 minggu
                if ($day <= 7) {
                    $week = 1;
                } elseif ($day <= 14) {
                    $week = 2;
                } elseif ($day <= 21) {
                    $week = 3;
                } else {
                    $week = 4; // Tanggal 22 s/d akhir bulan masuk Minggu 4
                }
                
                $weeklyData[$week] += $order->total_harga;
            }

            // Masukkan ke Chart Data
            foreach ($weeklyData as $weekNum => $total) {
                $chartLabels[] = "Minggu $weekNum";
                $chartValues[] = $total;
            }

        } else {
            // === LOGIKA FILTER HARIAN / CUSTOM RANGE (TAMPILKAN PER HARI) ===
            $startDate = $request->input('start_date', now()->subDays(6)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Ambil data harian
            $rawGrafikData = Order::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('SUM(total_harga) as total')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->get();

            // Loop range tanggal agar grafik tidak bolong
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                $displayDate = $date->translatedFormat('d M');
                $found = $rawGrafikData->firstWhere('date', $dateString);
                
                $chartLabels[] = $displayDate;
                $chartValues[] = $found ? $found->total : 0;
            }
        }


        // === C. LIST ORDER (SESUAI RANGE FILTER DIATAS) ===
        $recentOrders = Order::with('customer')
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->latest()
            ->get();

        return view('owner.dashboard', compact(
            'pendapatanHariIni', 
            'customerHariIni', 
            'barangMasukHariIni',
            'chartLabels',
            'chartValues',
            'recentOrders',
            'startDate',
            'endDate',
            'filterType'
        ));
    }
}