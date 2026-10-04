<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penerimaan Konsinyasi - {{ $receipt->receipt_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #6b21a8;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #6b21a8;
        }
        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 11px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 4px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .items-table th {
            background-color: #f3e8ff;
            color: #581c87;
            text-align: left;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            height: 80px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #7e22ce; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Cetak Surat Penerimaan
        </button>
    </div>

    <div class="header">
        <h1>{{ $storeName }}</h1>
        <p>{{ $storeAddress }} | Telp: {{ $storePhone }}</p>
        <h2 style="font-size: 14px; margin-top: 10px; text-transform: uppercase;">BUKTI PENERIMAAN BARANG KONSINYASI (TITIP JUAL)</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>No. Bukti</strong></td>
            <td style="width: 35%;">: {{ $receipt->receipt_number }}</td>
            <td style="width: 15%;"><strong>Supplier Penitip</strong></td>
            <td style="width: 35%;">: {{ $receipt->supplier?->name }} ({{ $receipt->supplier?->phone ?? '-' }})</td>
        </tr>
        <tr>
            <td><strong>Tanggal Terima</strong></td>
            <td>: {{ $receipt->receipt_date->format('d F Y') }}</td>
            <td><strong>Gudang Penerima</strong></td>
            <td>: {{ $receipt->warehouse?->name }}</td>
        </tr>
        <tr>
            <td><strong>Petugas Penerima</strong></td>
            <td>: {{ $receipt->user?->name ?? 'Admin' }}</td>
            <td><strong>Catatan</strong></td>
            <td>: {{ $receipt->notes ?? '-' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Kode & Nama Produk Titipan</th>
                <th class="text-center" style="width: 15%;">Satuan</th>
                <th class="text-center" style="width: 15%;">Jumlah (Qty)</th>
                <th class="text-right" style="width: 20%;">Harga Setor (HPP)</th>
                <th class="text-right" style="width: 20%;">Total Estimasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name }}</strong><br>
                        <small style="color: #666;">SKU: {{ $item->product?->code }}</small>
                    </td>
                    <td class="text-center">{{ $item->unit?->name }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">Rp {{ number_format($item->consignment_cost, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #faf5ff; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-center">{{ number_format($receipt->total_quantity, 0) }} Pcs</td>
                <td></td>
                <td class="text-right" style="color: #6b21a8; font-size: 13px;">Rp {{ number_format($receipt->total_estimated_value, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="font-size: 10px; color: #777; font-style: italic;">
        * Catatan: Dokumen ini merupakan bukti sah penerimaan barang titip jual (konsinyasi). Hak pembayaran baru berlaku setelah barang dinyatakan laku terjual pada rekonsiliasi/settlement resmi.
    </p>

    <table class="signatures">
        <tr>
            <td>
                Yang Menyerahkan (Supplier),<br><br><br><br>
                ( ______________________ )
            </td>
            <td>
                Yang Menerima (Toko),<br><br><br><br>
                ( {{ $receipt->user?->name ?? 'Petugas Toko' }} )
            </td>
        </tr>
    </table>

</body>
</html>
