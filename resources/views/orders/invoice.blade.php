<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $order->no_invoice }}</title>
    <style>
        /* Desain area layar */
        body { font-family: 'Courier New', monospace; font-size: 12px; margin: 0; padding: 10px; display: flex; flex-direction: column; align-items: center; background-color: #f3f4f6;}
        
        /* Ukuran dibatasi 75mm agar ada margin aman di kertas 80mm */
        #invoice-container { 
            box-sizing: border-box; width: 75mm; padding: 2mm; 
            background-color: white; overflow: hidden; 
        }
        
        .center { text-align: center; }
        .bold { font-weight: normal; } /* Mematikan bold pada class bold */
        h2, th { font-weight: normal; } /* Mematikan bold bawaan pada Judul dan Tabel */
        .border-top { border-top: 1px dashed #000; margin-top: 5px; padding-top: 5px; }
        
        /* Anti kepotong */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th { word-wrap: break-word; overflow-wrap: break-word; vertical-align: top; }
        
        /* Pembagian kolom tabel 2 baris (Kiri dan Kanan) */
        th:nth-child(1), td:nth-child(1) { width: 65%; text-align: left; }
        th:nth-child(2), td:nth-child(2) { width: 35%; text-align: right; }
        
        .footer { font-size: 10px; margin-top: 15px; text-align: center; }

        /* Tombol Bantuan (Di layar) */
        .no-print { margin-top: 20px; display: flex; gap: 10px; width: 75mm; }
        .btn { flex: 1; padding: 10px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; text-align: center; }
        .btn-blue { background-color: #3b66ff; color: white; }
        .btn-gray { background-color: #e5e7eb; color: #374151; }

        /* ====== PENGATURAN KHUSUS MESIN PRINTER ====== */
        @media print {
            html, body { 
                background-color: white; 
                margin: 0 !important; 
                padding: 0 !important; 
                height: auto; 
            }
            .no-print { display: none !important; } 
            thead { display: table-row-group; }
            
            /* Memaksa elemen tidak terpotong atau membuat halaman baru */
            table, tr, td, th, p, div {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
        @page {
            size: 80mm auto; /* Membiarkan panjang kertas menyesuaikan isi (roll) */
            margin: 0mm;
        }
    </style>
</head>
<body>
    
    <div id="invoice-container">
        <div class="center">
            <h2 style="margin:0;">LOUWES CARE</h2>
            <p style="margin:0;">SHOE LAUNDRY & CARE</p>
            <p style="margin:0; font-size:10px;">Jl. Ringroad Timur No 9, Banguntapan, Bantul</p>
            <p style="margin:0; font-size:10px;">WA: 081390154885</p>
        </div>

        <div class="border-top">
            <p style="margin:2px 0;">No: <span id="inv-no">{{ $order->no_invoice }}</span></p>
            <p style="margin:2px 0;">Date: {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p style="margin:2px 0;">CS: {{ $order->kasir }}</p>
        </div>

        <div class="border-top">
            <p style="margin:2px 0;" class="bold">CUSTOMER: <span id="inv-cust-name">{{ strtoupper($order->customer->nama) }}</span></p>
            <p style="margin:2px 0;">HP: ****{{ substr($order->customer->no_hp, -4) }}</p>
        </div>

        <table class="border-top">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->details as $item)
                <tr>
                    <td>
                        {{ $item->nama_barang }}
                        
                        {{-- Menampilkan catatan jika ada, dengan font lebih kecil --}}
                        @if(!empty($item->catatan) && $item->catatan !== '-')
                            <br><span style="font-size: 9px;">Catatan: {{ $item->catatan }}</span>
                        @endif
                        
                        <br><small>({{ $item->layanan }})</small>
                    </td>
                    <td align="right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-top">
            <table>
                @php 
                    $originalTotal = $order->details->sum('harga');
                    $discount = $originalTotal - $order->total_harga;
                @endphp
                <tr>
                    <td style="text-align: right; padding-right: 8px;">Subtotal :</td>
                    <td>Rp {{ number_format($originalTotal, 0, ',', '.') }}</td>
                </tr>
                @if($discount > 0)
                <tr>
                    <td style="text-align: right; padding-right: 8px;">Diskon Reward :</td>
                    <td>- Rp {{ number_format($discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="bold">
                    <td style="text-align: right; padding-right: 8px;">TOTAL :</td>
                    <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px;">{{ $order->status_pembayaran }} :</td>
                    <td>via {{ $order->metode_pembayaran ?? '-' }}</td>
                </tr>
            </table>
        </div>

       @if($order->klaim)
        <div style="margin-top: 10px; border: 1px dashed #000; padding: 5px; text-align: center;" class="bold">
            *** REWARD: {{ strtoupper($order->klaim) }} ***
        </div>
        @endif

        <div class="footer">
            @if($discount > 0)
                <p class="bold" style="margin-bottom: 5px;">* Sudah dipotong Diskon Reward Rp {{ number_format(\App\Models\Setting::getDiskonMember(), 0, ',', '.') }}</p>
            @endif
            <p>* Simpan nota ini sebagai bukti pengambilan</p>
            <p>Barang tidak diambil > 30 hari di luar tanggung jawab kami.</p>
            <p class="bold">-- Terima Kasih --</p>
        </div>
    </div>

    <div class="no-print">
        <button class="btn btn-blue" onclick="window.print()">Cetak Struk</button>
        <button class="btn btn-gray" onclick="window.close()">Tutup</button>
    </div>

    <script>
        // Panggil jendela print secara otomatis dengan jeda 500ms
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        };
    </script>
</body>
</html>