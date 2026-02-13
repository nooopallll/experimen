<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // Pastikan library ini di-import

class DashboardController extends Controller
{
    /**
     * DASHBOARD UTAMA (Index)
     * Logika: Tetap menggunakan logika lama Anda (Total Harga & Filter Mingguan/Harian)
     */
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
        
        // [TAMBAHAN] Data Bulan & Tahun Ini (Untuk melengkapi tampilan dashboard jika diperlukan)
        // Saya tetap gunakan 'total_harga' agar konsisten dengan logika Anda
        $incomeMonth = Order::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_harga');
        $incomeYear  = Order::whereYear('created_at', now()->year)->sum('total_harga');

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
            
            $startDate = $start->format('Y-m-d');
            $endDate = $end->format('Y-m-d');

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
                    $week = 4; 
                }
                
                $weeklyData[$week] += $order->total_harga;
            }

            foreach ($weeklyData as $weekNum => $total) {
                $chartLabels[] = "Minggu $weekNum";
                $chartValues[] = $total;
            }

        } else {
            // === LOGIKA FILTER HARIAN / CUSTOM RANGE ===
            $startDate = $request->input('start_date', now()->subDays(6)->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $rawGrafikData = Order::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('SUM(total_harga) as total')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->get();

            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateString = $date->format('Y-m-d');
                $displayDate = $date->translatedFormat('d M');
                $found = $rawGrafikData->firstWhere('date', $dateString);
                
                $chartLabels[] = $displayDate;
                $chartValues[] = $found ? $found->total : 0;
            }
        }

        // === C. LIST ORDER ===
        $recentOrders = Order::with('customer')
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->latest()
            ->get();

        // Mengirim data ke View Dashboard
        return view('owner.dashboard', compact(
            'pendapatanHariIni', 
            'incomeMonth',  // Tambahan
            'incomeYear',   // Tambahan
            'customerHariIni', 
            'barangMasukHariIni',
            'chartLabels', // Variable untuk grafik (Label)
            'chartValues', // Variable untuk grafik (Data)
            'recentOrders',
            'startDate',
            'endDate',
            'filterType'
        ))
        // [OPSIONAL] Mapping agar cocok dengan view 'laporan' jika dipakai di dashboard juga
        ->with('labels', $chartLabels)
        ->with('data', $chartValues)
        ->with('incomeToday', $pendapatanHariIni); 
    }

    /**
     * HALAMAN KHUSUS LAPORAN (Navbar Menu)
     * Method ini menangani halaman terpisah untuk laporan detail
     */
    public function laporan(Request $request)
    {
        // Panggil logika yang sama dengan index, tapi return ke view yang berbeda
        // Kita bisa copy paste logika di atas, atau redirect. 
        // Agar aman dan terpisah, saya tulis ulang logikanya disini menggunakan 'paid_amount'
        // untuk laporan keuangan yang lebih akurat (Uang Masuk), atau 'total_harga' jika ingin konsisten.
        // Di sini saya gunakan 'paid_amount' agar Laporan Keuangan menghitung UANG NYATA (Cashflow).

        if (auth()->user()->role !== 'owner') return redirect()->route('dashboard');

        // 1. Data Ringkasan
        $today = Carbon::today();
        $incomeToday = Order::whereDate('created_at', $today)->sum('paid_amount');
        $incomeMonth = Order::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->sum('paid_amount');
        $incomeYear  = Order::whereYear('created_at', $today->year)->sum('paid_amount');
        
        $customerHariIni = Order::whereDate('created_at', $today)->count();
        $barangMasukHariIni = OrderDetail::whereDate('created_at', $today)->count();

        // 2. BUILD QUERY FILTER
        $query = Order::with(['customer', 'details']);

        // A. Filter Tanggal Masuk (Wajib untuk Grafik, Default 30 Hari Terakhir)
        $startDate = $request->input('tgl_masuk_start', now()->subDays(29)->format('Y-m-d'));
        $endDate   = $request->input('tgl_masuk_end', now()->format('Y-m-d'));
        
        $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(), 
            Carbon::parse($endDate)->endOfDay()
        ]);

        // B. Filter Tanggal Keluar (Estimasi di Order Detail)
        if ($request->filled('tgl_keluar_start') && $request->filled('tgl_keluar_end')) {
            $query->whereHas('details', function($q) use ($request) {
                $q->whereBetween('estimasi_keluar', [$request->tgl_keluar_start, $request->tgl_keluar_end]);
            });
        }

        // C. Kategori Customer
        if ($request->filled('kategori_customer')) {
            $query->where('tipe_customer', $request->kategori_customer);
        }

        // D. Treatment
        if ($request->filled('treatment')) {
            $query->whereHas('details', function($q) use ($request) {
                $q->where('layanan', $request->treatment);
            });
        }

        // E. Range Harga
        if ($request->filled('min_harga')) {
            $query->where('total_harga', '>=', $request->min_harga);
        }
        if ($request->filled('max_harga')) {
            $query->where('total_harga', '<=', $request->max_harga);
        }

        // F. Komplain (Cek catatan tidak kosong)
        if ($request->has('komplain')) {
            $query->where(function($q) {
                $q->whereNotNull('catatan')
                  ->where('catatan', '!=', '-')
                  ->where('catatan', '!=', '');
            });
        }

        // Eksekusi Query
        $recentOrders = $query->latest()->get();

        // Hitung Total Pendapatan sesuai Filter yang dipilih (Uang Masuk / Paid Amount)
        $totalPendapatan = $recentOrders->sum('paid_amount');

        // 3. SIAPKAN DATA GRAFIK (Berdasarkan data yang sudah difilter)
        $labels = [];
        $data = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        
        // Grouping data berdasarkan tanggal untuk grafik
        $groupedOrders = $recentOrders->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        });

        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $labels[] = $date->translatedFormat('d M');
            $data[] = isset($groupedOrders[$d]) ? $groupedOrders[$d]->sum('paid_amount') : 0;
        }

        $treatments = Treatment::all(); // Untuk dropdown filter
        $filterType = 'custom'; 

        return view('owner.laporan', compact(
            'incomeToday', 'incomeMonth', 'incomeYear', 'customerHariIni', 'barangMasukHariIni',
            'labels', 'data', 'startDate', 'endDate', 'filterType', 'recentOrders', 'totalPendapatan', 'treatments'
        ));
    }
}