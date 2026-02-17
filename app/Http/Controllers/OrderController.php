<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Karyawan;
use App\Models\Treatment;
use App\Models\PointHistory; 
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * 1. DAFTAR PESANAN
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'details'])->latest();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function($q) use ($keyword) {
                $q->where('no_invoice', 'like', "%{$keyword}%")
                  ->orWhereHas('customer', function($c) use ($keyword) {
                      $c->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('no_hp', 'like', "%{$keyword}%");
                  });
            });
        }

        // Filter Lanjutan
        if ($request->filled('tgl_masuk')) {
            $query->whereDate('created_at', '>=', $request->tgl_masuk);
        }
        if ($request->filled('tgl_keluar')) {
            $query->whereHas('details', function($q) use ($request) {
                $q->whereDate('estimasi_keluar', $request->tgl_keluar);
            });
        }
        if ($request->filled('kategori_customer')) {
            $query->where('tipe_customer', $request->kategori_customer);
        }
        if ($request->filled('treatment')) {
            $query->whereHas('details', function($q) use ($request) {
                $q->where('layanan', $request->treatment);
            });
        }
        if ($request->filled('komplain')) {
            $query->where('catatan', '!=', '-')->whereNotNull('catatan');
        }

        $orders = $query->paginate(10);

        // Ambil data treatments untuk dropdown filter
        $treatments = Treatment::orderBy('nama_treatment', 'asc')->get(); // Pastikan data diambil

        if ($request->ajax()) {
            return view('pesanan.partials.list', compact('orders'))->render();
        }
        return view('pesanan.index', compact('orders', 'treatments')); 
    }

    /**
     * 2. EDIT PESANAN
     */
    public function show($id)
    {
        $order = Order::with(['customer.member', 'details'])->findOrFail($id);
        $treatments = Treatment::orderBy('nama_treatment', 'asc')->get();
        $karyawans = Karyawan::orderBy('nama_karyawan', 'asc')->get();
        $nominalDiskon = Setting::getDiskonMember();
        return view('pesanan.show', compact('order', 'treatments', 'karyawans', 'nominalDiskon'));
    }

    /**
     * 3. UPDATE PESANAN (EDIT)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), ['nama_customer' => 'required|string']);

        if ($validator->fails()) {
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $order = Order::with('customer.member', 'details')->findOrFail($id);

            // --- A. LOGIKA KLAIM POIN ---
            $klaimStatus = $order->klaim; 
            
            if ($request->filled('claim_type') && empty($order->klaim)) {
                $member = $order->customer->member;
                if ($member && $member->poin >= 8) {
                    $member->decrement('poin', 8); 
                    if ($request->claim_type == 'diskon') $klaimStatus = 'Diskon'; 
                    elseif ($request->claim_type == 'parfum') $klaimStatus = 'Parfum'; 

                    PointHistory::create([
                        'member_id'   => $member->id,
                        'order_id'    => $order->id,
                        'amount'      => -8,
                        'type'        => 'redeem',
                        'description' => 'Tukar ' . $klaimStatus . ' (Edit Order)'
                    ]);
                }
            }
            $staticDiscount = ($klaimStatus === 'Diskon') ? Setting::getDiskonMember() : 0;

            // --- B. UPDATE ORDER HEADER ---
            $updateData = [
                'nama_customer'     => $request->nama_customer, 
                'status_order'      => $request->status, 
                'kasir_keluar'      => $request->kasir_keluar, 
                'catatan'           => $request->catatan,
                'klaim'             => $klaimStatus, 
                'metode_pembayaran' => $request->metode_pembayaran ?? $order->metode_pembayaran,
                'status_pembayaran' => $request->status_pembayaran ?? $order->status_pembayaran,
            ];

            if ($request->filled('paid_amount')) {
                $updateData['paid_amount'] = $request->paid_amount;
            }

            $order->update($updateData);

            // [LOGIKA BARU] Jika status diubah jadi Lunas, otomatis set paid_amount = total_harga
            if ($request->status_pembayaran == 'Lunas') {
                $order->paid_amount = $order->total_harga;
                $order->save();
            }

            if ($order->customer) $order->customer->update(['nama' => $request->nama_customer]);

            // --- C. UPDATE ITEM ---
            $subtotalItem = 0; 

            if ($request->has('item')) {
                $order->details()->delete(); 

                foreach ($request->item as $key => $namaBarang) {
                    if (!empty($namaBarang)) {
                        $harga = (int) preg_replace('/[^0-9]/', '', $request->harga[$key] ?? 0);

                        $order->details()->create([
                            'order_id'        => $order->id,
                            'nama_barang'     => $namaBarang,
                            'layanan'         => $request->kategori_treatment[$key] ?? '-',
                            'estimasi_keluar' => $request->tanggal_keluar[$key] ?? null,
                            'status'          => $request->status_detail[$key] ?? 'Proses',
                            'harga'           => $harga,
                            'catatan'         => $request->catatan_detail[$key] ?? null,
                        ]);
                        $subtotalItem += $harga;
                    }
                }
                $order->total_harga = $subtotalItem - $staticDiscount;
                $order->save();
            } else {
                $subtotalItem = $order->details->sum('harga');
                $order->total_harga = $subtotalItem - $staticDiscount;
                $order->save();
            }

            // --- D. LOGIKA OTOMATIS STATUS ORDER ---
            $totalDetails = $order->details()->count();
            $unfinishedItems = $order->details()->whereNotIn('status', ['Selesai', 'Diambil'])->count();
            $pickedUpItems = $order->details()->where('status', 'Diambil')->count();

            if ($totalDetails > 0) {
                if ($unfinishedItems > 0) {
                    $order->status_order = 'Proses';
                } elseif ($pickedUpItems == $totalDetails) {
                    $order->status_order = 'Diambil';
                } else {
                    $order->status_order = 'Selesai';
                }
                $order->save(); 
            }

            DB::commit();

            $freshOrder = Order::with(['customer', 'details'])->findOrFail($id);

            if ($request->ajax()) {
                return response()->json([
                    'status'          => 'success',
                    'message'         => 'Berhasil diperbarui!',
                    'order'           => $freshOrder,
                    'original_total'  => $subtotalItem,
                    'discount_amount' => $staticDiscount,
                    'claim_type'      => $freshOrder->klaim 
                ]);
            }

            return back()->with('success', 'Status pesanan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * 4. SIMPAN ORDER BARU (STORE)
     */
    public function store(Request $request)
    {
        $rawItems = $request->item ?? [];
        $validIndexes = [];
        if(is_array($rawItems)) { 
            foreach($rawItems as $index => $val) { 
                if(!empty($val)) $validIndexes[] = $index; 
            } 
        }

        if (empty($validIndexes)) {
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => 'Harap isi minimal satu item.'], 422);
            return back()->with('error', 'Item kosong.');
        }

        try {
            DB::beginTransaction();

            // 1. CEK STATUS EXISTING (Untuk tabel orders -> Status Transaksi)
            $existingCustomer = Customer::where('no_hp', $request->no_hp)->first();
            $statusTransaksi = 'New Customer';
            
            if ($existingCustomer) {
                $statusTransaksi = $existingCustomer->member ? 'Member' : 'Repeat Order';
            }

            // 2. CREATE/UPDATE CUSTOMER (Data Profil di tabel Customers)
            $customerData = [
                'nama' => $request->nama_customer,
                'sumber_info' => $request->sumber_info
            ];

            // Update tipe profil HANYA jika diisi di form agar tidak menghapus data lama
            if ($request->filled('tipe_customer')) {
                $customerData['tipe'] = $request->tipe_customer;
            }

            $customer = Customer::updateOrCreate(
                ['no_hp' => $request->no_hp],
                $customerData
            );

            // --- LOGIKA DISKON MEMBER ---
            $staticDiscount = 0;
            $klaimColumnValue = null; 
            $isRedeeming = false;

            if ($customer->member && $customer->member->poin >= 8 && $request->filled('claim_type')) {
                if ($request->claim_type === 'diskon') {
                    $staticDiscount = Setting::getDiskonMember();
                    $klaimColumnValue = 'Diskon'; 
                    $isRedeeming = true;
                } elseif ($request->claim_type === 'parfum') {
                    $klaimColumnValue = 'Parfum'; 
                    $isRedeeming = true;
                }
            }

            $count = Order::count() + 1;
            $invoice = 'INV-' . date('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            $subtotalItem = 0;
            foreach ($validIndexes as $i) {
                $subtotalItem += (int) preg_replace('/[^0-9]/', '', $request->harga[$i] ?? 0);
            }
            $finalTotal = $subtotalItem - $staticDiscount;

            // --- PEMBAYARAN ---
            $statusPembayaran = $request->status_pembayaran ?? 'Belum Lunas';
            $metodePembayaran = $request->metode_pembayaran ?? 'Tunai';
            $inputPaidAmount = $request->paid_amount ? (int) preg_replace('/[^0-9]/', '', $request->paid_amount) : 0;
            
            $jumlahBayar = 0;
            if ($statusPembayaran == 'Lunas') {
                $jumlahBayar = ($inputPaidAmount > 0) ? $inputPaidAmount : $finalTotal;
            } elseif ($statusPembayaran == 'DP') {
                $jumlahBayar = $inputPaidAmount;
            } else {
                $jumlahBayar = 0;
                $metodePembayaran = null; 
            }

            // 3. CREATE ORDER
            $order = Order::create([
                'no_invoice'        => $invoice,
                'customer_id'       => $customer->id,
                'tgl_masuk'         => now(),
                'total_harga'       => $finalTotal, 
                'klaim'             => $klaimColumnValue, 
                'paid_amount'       => $jumlahBayar,
                'metode_pembayaran' => $metodePembayaran,
                'status_pembayaran' => $statusPembayaran,
                'status_order'      => 'Proses',
                'tipe_customer'     => $statusTransaksi, // Menggunakan label Member/Repeat/New
                'catatan'           => $request->catatan[$validIndexes[0]] ?? '-', 
                'kasir'             => $request->cs ?? 'Admin',
            ]);

            foreach ($validIndexes as $i) {
                $harga = (int) preg_replace('/[^0-9]/', '', $request->harga[$i]);
                
                OrderDetail::create([
                    'order_id'        => $order->id,
                    'nama_barang'     => $rawItems[$i],
                    'layanan'         => $request->kategori_treatment[$i] ?? 'General',
                    'harga'           => $harga,
                    'estimasi_keluar' => !empty($request->tanggal_keluar[$i]) ? $request->tanggal_keluar[$i] : null,
                    'catatan'         => $request->catatan[$i] ?? null,
                    'status'          => 'Proses',
                ]);
            }

            if ($isRedeeming) {
                $customer->member->decrement('poin', 8);
                PointHistory::create([
                    'member_id'   => $customer->member->id,
                    'order_id'    => $order->id,
                    'amount'      => -8,
                    'type'        => 'redeem',
                    'description' => 'Tukar ' . $klaimColumnValue
                ]);
            }

            if ($customer->member) {
                $customer->member->increment('total_transaksi', $subtotalItem);
                $poinBaru = floor($subtotalItem / 50000);
                if ($poinBaru > 0) {
                    $customer->member->increment('poin', $poinBaru);
                    PointHistory::create([
                        'member_id'   => $customer->member->id,
                        'order_id'    => $order->id,
                        'amount'      => $poinBaru,
                        'type'        => 'earn',
                        'description' => 'Poin Transaksi ' . $invoice
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status'          => 'success',
                    'message'         => 'Order berhasil disimpan!',
                    'order'           => $order->load('customer', 'details'),
                    'discount_amount' => $staticDiscount,
                    'original_total'  => $subtotalItem,
                    'claim_type'      => $klaimColumnValue 
                ]);
            }

            return redirect()->route('orders.invoice', $order->id)->with('success', 'Order berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    // LOGIKA UNTUK TOGGLE STATUS WA SENT (WA SENT 1 = Invoice, WA SENT 2 = Pengambilan)
    public function toggleWa(Request $request, $id, $type)
    {
        $order = Order::with(['customer', 'details'])->findOrFail($id);
        
        $waUrl = null;
        $customer = $order->customer;
        $phone = $customer ? $customer->no_hp : '';
        
        // Format nomor HP (08 -> 628)
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }

        if ($type == '1') {
            // VALIDASI: Hanya kirim jika Status Order adalah 'Proses'
            if ($order->status_order != 'Proses') {
                if ($request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => 'WA Masuk hanya bisa dikirim jika status pesanan PROSES.']);
                }
                return back()->with('error', 'Gagal: Status pesanan bukan Proses.');
            }

            $order->wa_sent_1 = !$order->wa_sent_1;
            
            // Jika status berubah jadi TERKIRIM (True), buat link WA Invoice
            if ($order->wa_sent_1 && $phone) {
                $total = number_format($order->total_harga, 0, ',', '.');
                // Format detail per item (Item, Treatment, Status baris sendiri-sendiri)
                $detailsList = $order->details->map(fn($d) => "Item: *{$d->nama_barang}*\nTreatment: {$d->layanan}\nStatus: {$d->status}" . ($d->catatan ? "\nKondisi: {$d->catatan}" : ""))->join("\n\n");
                // Generate Link Publik
                // $linkInvoice = route('invoice.public', $order->id);
                
                // Logika Status Pembayaran
                $infoBayar = "Status Pembayaran: *{$order->status_pembayaran}*";
                if ($order->status_pembayaran == 'Lunas') {
                    $infoBayar = "Status Pembayaran: *LUNAS*";
                } elseif ($order->status_pembayaran == 'DP') {
                    $dp = number_format($order->paid_amount, 0, ',', '.');
                    $sisa = number_format($order->total_harga - $order->paid_amount, 0, ',', '.');
                    $infoBayar = "Status Pembayaran: *DP (Rp {$dp})*\nSisa Tagihan: *Rp {$sisa}*";
                }

                $msg = "Halo Kak *{$customer->nama}*,\n";
                $msg .= "No Nota: *{$order->no_invoice}*\n";
                $msg .= "\n{$detailsList}\n\n";
                $msg .= "Total Harga: *Rp {$total}*\n";
                $msg .= "{$infoBayar}\n\n";
                // $msg .= "*Link Nota Digital (Download PDF):*\n{$linkInvoice}\n\n";
                $msg .= "Mohon save nomor kami untuk memudahkan komunikasi ya kak. Terima kasih telah mempercayakan sepatu kakak di Louwes Shoes Care.\n";
                $msg .= "Simpan pesan ini sebagai bukti pengambilan ya kak!!";
                $waUrl = "https://wa.me/{$phone}?text=" . urlencode($msg);
            }

        } elseif ($type == '2') {
            // VALIDASI: Hanya kirim jika WA 1 sudah dikirim
            if (!$order->wa_sent_1) {
                if ($request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => 'WA Pengambilan hanya bisa dikirim jika WA Masuk sudah dikirim.']);
                }
                return back()->with('error', 'Gagal: WA Masuk belum dikirim.');
            }

            $order->wa_sent_2 = !$order->wa_sent_2;

            // Jika status berubah jadi TERKIRIM (True), buat link WA Pengambilan
            if ($order->wa_sent_2 && $phone) {
                  $total = number_format($order->total_harga, 0, ',', '.');
                
                // Cek Status Kelengkapan Item
                $finishedCount = $order->details->where('status', 'Selesai')->count();
                $processCount = $order->details->where('status', 'Proses')->count();
                $pickedUpCount = $order->details->where('status', 'Diambil')->count();

                // Format detail per item (Item, Treatment, Status baris sendiri-sendiri)
                $detailsList = $order->details->map(fn($d) => "Item: *{$d->nama_barang}*\nTreatment: {$d->layanan}\nStatus: {$d->status}" . ($d->catatan ? "\nKondisi: {$d->catatan}" : ""))->join("\n\n");
                // Generate Link Publik
                // $linkInvoice = route('invoice.public', $order->id);
                
                // Logika Status Pembayaran
                $infoBayar = "Status Pembayaran: *{$order->status_pembayaran}*";
                if ($order->status_pembayaran == 'Lunas') {
                    $infoBayar = "Status Pembayaran: *LUNAS*";
                } elseif ($order->status_pembayaran == 'DP') {
                    $dp = number_format($order->paid_amount, 0, ',', '.');
                    $sisa = number_format($order->total_harga - $order->paid_amount, 0, ',', '.');
                    $infoBayar = "Status Pembayaran: *DP (Rp {$dp})*\nSisa Tagihan: *Rp {$sisa}*";
                }
                
                $msg = "Halo Kak *{$customer->nama}*,\n";
                
                // LOGIKA PESAN DINAMIS (PARSIAL VS FULL)
                if ($pickedUpCount > 0) {
                    // KASUS: Sudah ada yang diambil sebelumnya (Ambil Sebagian)
                    if ($finishedCount > 0) {
                        $msg .= "Menginfokan sisa pesanan No Nota: *{$order->no_invoice}* sudah selesai dan siap diambil ya kak.\n\n";
                    } else {
                        $msg .= "Update status pesanan No Nota: *{$order->no_invoice}*.\n\n";
                    }
                } else {
                    $msg .= "Pesanan kakak No Nota: *{$order->no_invoice}* sudah selesai dan siap diambil ya kak.\n\n";
                }

                $msg .= "{$detailsList}\n\n";
                $msg .= "Total Harga: *Rp {$total}*\n";
                $msg .= "{$infoBayar}\n\n";
                $msg .= "Mohon bawa nota saat pengambilan ya kak. Terima kasih!\n";

                $waUrl = "https://wa.me/{$phone}?text=" . urlencode($msg);
            }
        }

        $order->save();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'wa_sent_1' => $order->wa_sent_1,
                'wa_sent_2' => $order->wa_sent_2,
                'wa_url' => $waUrl
            ]);
        }

        return back()->with('success', 'Status WA berhasil diubah.');
    }

    /**
     * 5. CEK CUSTOMER (FORM POST)
     * Digunakan saat submit dari halaman Cek Customer (cek-customer.blade.php)
     */
    public function check(Request $request)
    {
        $request->validate([
            'no_hp' => 'required',
        ]);

        $no_hp = $request->no_hp;
        $customer = Customer::with('member')->where('no_hp', $no_hp)->first();
        
        $status = 'New Customer';
        $is_member = false;
        $poin = 0;
        $color = 'text-blue-600 bg-blue-100 border-blue-200';
        $tipe_pilihan = '';

        if ($customer) {
            $tipe_pilihan = $customer->tipe;
            if ($customer->member) {
                $status = 'Member';
                $is_member = true;
                $poin = $customer->member->poin;
                $color = 'text-pink-600 bg-pink-100 border-pink-200';
            } else {
                $status = 'Repeat Order';
                $color = 'text-green-600 bg-green-100 border-green-200';
            }
        }

        // Data pendukung untuk view input-order
        $treatments = Treatment::orderBy('kategori', 'asc')->get();
        $karyawans = Karyawan::orderBy('nama_karyawan', 'asc')->get();
        $nominal_diskon = Setting::getDiskonMember(); 

        return view('input-order', compact('customer', 'no_hp', 'status', 'is_member', 'poin', 'color', 'treatments', 'karyawans', 'nominal_diskon', 'tipe_pilihan'));
    }

    /**
     * 6. CEK CUSTOMER (AJAX)
     * Digunakan untuk live check di halaman Input Order (input-order.blade.php)
     */
    public function checkCustomer(Request $request)
    {
        $hp = $request->no_hp;
        $customer = Customer::with('member')->where('no_hp', $hp)->first();

        if ($customer) {
            $badge = $customer->member ? 'Member' : 'Repeat Order';
            $poin = $customer->member ? $customer->member->poin : 0;
            
            return response()->json([
                'found' => true,
                'nama' => $customer->nama,
                'tipe_form' => $customer->tipe ?? '', 
                'sumber_info' => $customer->sumber_info,
                'badge' => $badge,
                'poin' => $poin,
                'member_id' => $customer->member ? $customer->member->id : null
            ]);
        }

        return response()->json(['found' => false]);
    }

    /**
     * 7. HAPUS ITEM TERPILIH (AJAX)
     */
    public function deleteItems(Request $request)
    {
        $ids = $request->ids;
        
        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada item dipilih'], 400);
        }

        try {
            DB::beginTransaction();

            // Ambil salah satu item untuk mendapatkan Order parent-nya sebelum dihapus
            $firstDetail = OrderDetail::whereIn('id', $ids)->first();
            
            if ($firstDetail) {
                $order = $firstDetail->order;

                // Hapus item yang dipilih
                OrderDetail::whereIn('id', $ids)->delete();

                // Hitung ulang total harga order
                $subtotal = $order->details()->sum('harga');
                $discount = ($order->klaim === 'Diskon') ? Setting::getDiskonMember() : 0;
                
                $order->total_harga = max(0, $subtotal - $discount);
                
                // Jika status Lunas, sesuaikan paid_amount agar sinkron
                if ($order->status_pembayaran == 'Lunas') {
                    $order->paid_amount = $order->total_harga;
                }
                
                $order->save();
            }

            DB::commit();
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}