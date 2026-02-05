<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $order->no_invoice }}</title>
    <style>
        body { font-family: 'Courier New', monospace; width: 300px; font-size: 12px; margin: 0; padding: 10px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; margin-top: 5px; padding-top: 5px; }
        table { width: 100%; border-collapse: collapse; }
        .footer { font-size: 10px; margin-top: 15px; text-align: center; }
    </style>
</head>
<body onload="window.print()">
    <div class="center">
        <h2 style="margin:0;">LOUWES CARE</h2>
        <p style="margin:0;">SHOE LAUNDRY & CARE</p>
        <p style="margin:0; font-size:10px;">Jl. Ringroad Timur No 9, Banguntapan, Bantul</p>
        <p style="margin:0; font-size:10px;">WA: 081390154885</p>
    </div>

    <div class="border-top">
        <p style="margin:2px 0;">No: {{ $order->no_invoice }}</p>
        <p style="margin:2px 0;">Date: {{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p style="margin:2px 0;">CS: {{ $order->kasir }}</p>
    </div>

    <div class="border-top">
        <p style="margin:2px 0;" class="bold">CUSTOMER: {{ strtoupper($order->customer->nama) }}</p>
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
                <td>Diskon (Poin)</td>
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

    @if(str_contains($order->catatan, '[KLAIM FREE PARFUM]'))
    <div style="margin-top: 10px; border: 1px solid #000; padding: 5px; text-align: center;" class="bold">
        *** FREE PARFUM ***
    </div>
    @endif

    <div class="footer">
        <p>* Simpan nota ini sebagai bukti pengambilan</p>
        <p>Barang tidak diambil > 30 hari di luar tanggung jawab kami.</p>
        <p class="bold">-- Terima Kasih --</p>
    </div>
</body>
</html>