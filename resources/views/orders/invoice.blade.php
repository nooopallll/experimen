<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $order->no_invoice }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        /* Desain area yang akan dicetak */
        body { font-family: 'Courier New', monospace; font-size: 12px; margin: 0; padding: 10px; display: flex; flex-direction: column; align-items: center; background-color: #f3f4f6;}
        
        #invoice-container { 
            width: 300px; 
            padding: 15px; 
            background-color: white; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; margin-top: 5px; padding-top: 5px; }
        table { width: 100%; border-collapse: collapse; }
        .footer { font-size: 10px; margin-top: 15px; text-align: center; }

        /* Desain Tombol Aksi (Hanya terlihat di layar) */
        .action-buttons {
            width: 300px;
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
        }
        .btn-blue { background-color: #3b66ff; color: white; }
        .btn-gray { background-color: #e5e7eb; color: #374151; }
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
                <tr align="left">
                    <th>ITEM</th>
                    <th align="right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->details as $item)
                <tr>
                    <td>{{ $item->nama_barang }}<br><small>({{ $item->layanan }})</small></td>
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
                    <td>Subtotal</td>
                    <td align="right">Rp {{ number_format($originalTotal, 0, ',', '.') }}</td>
                </tr>
                @if($discount > 0)
                <tr>
                    <td>* Diskon Reward (Rp {{ number_format(\App\Models\Setting::getDiskonMember(), 0, ',', '.') }})</td>
                    <td align="right">- Rp {{ number_format($discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="bold">
                    <td>TOTAL</td>
                    <td align="right">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>{{ $order->status_pembayaran }}</td>
                    <td align="right">via {{ $order->metode_pembayaran ?? '-' }}</td>
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

    <div class="action-buttons">
        <button class="btn btn-blue" onclick="downloadPDF()">Download Ulang</button>
        <button class="btn btn-gray" onclick="window.close()">Tutup</button>
    </div>

    <script>
        function downloadPDF() {
            // Ambil nomor nota dan nama untuk format nama file
            var invNo = document.getElementById('inv-no').innerText.trim() || 'Invoice';
            var custName = document.getElementById('inv-cust-name').innerText.trim() || 'Customer';
            var fileName = invNo + ' - ' + custName + '.pdf';
            
            // Elemen yang akan dijadikan PDF
            var element = document.getElementById('invoice-container');

            // Pengaturan ukuran kertas (format custom mirip kertas thermal)
            var opt = {
                margin:       0.1,
                filename:     fileName,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: [3.3, 6], orientation: 'portrait' } 
            };

            // Proses generate dan download
            html2pdf().set(opt).from(element).save();
        }

        // Otomatis download ketika halaman selesai dimuat (jeda 500ms agar font ter-load)
        window.onload = function() {
            setTimeout(function() {
                downloadPDF();
            }, 500);
        };
    </script>
</body>
</html>