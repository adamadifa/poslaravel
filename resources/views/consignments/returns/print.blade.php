<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Retur Konsinyasi - {{ $return->return_number }}</title>
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
            Cetak Surat Retur
        </button>
    </div>

    <div class="header">
        <h1>{{ $storeName }}</h1>
        <p>{{ $storeAddress }} | Telp: {{ $storePhone }}</p>
        <h2 style="font-size: 14px; margin-top: 10px; text-transform: uppercase;">SURAT PENGEMBALIAN / RETUR BARANG KONSINYASI</h2>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>No. Retur</strong></td>
            <td style="width: 35%;">: {{ $return->return_number }}</td>
            <td style="width: 15%;"><strong>Supplier Penitip</strong></td>
            <td style="width: 35%;">: {{ $return->supplier?->name }} ({{ $return->supplier?->phone ?? '-' }})</td>
        </tr>
        <tr>
            <td><strong>Tanggal Retur</strong></td>
            <td>: {{ $return->return_date->format('d F Y') }}</td>
            <td><strong>Gudang Pengirim</strong></td>
            <td>: {{ $return->warehouse?->name }}</td>
        </tr>
        <tr>
            <td><strong>Petugas Toko</strong></td>
            <td>: {{ $return->user?->name ?? 'Admin' }}</td>
            <td><strong>Catatan</strong></td>
            <td>: {{ $return->notes ?? '-' }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Kode & Nama Produk</th>
                <th class="text-center" style="width: 15%;">Satuan</th>
                <th class="text-center" style="width: 15%;">Jumlah Diretur</th>
                <th style="width: 30%;">Alasan Retur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($return->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name }}</strong><br>
                        <small style="color: #666;">SKU: {{ $item->product?->code }}</small>
                    </td>
                    <td class="text-center">{{ $item->unit?->name }}</td>
                    <td class="text-center" style="font-weight: bold; color: #9f1239;">{{ number_format($item->quantity, 0) }}</td>
                    <td>{{ $item->reason ?? 'Pengembalian barang sisa' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #faf5ff; font-weight: bold;">
                <td colspan="3" style="text-align: right;">TOTAL ITEM DIRETUR:</td>
                <td class="text-center" style="color: #9f1239;">{{ number_format($return->total_quantity, 0) }} Pcs</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Yang Menyerahkan (Toko),<br><br><br><br>
                ( {{ $return->user?->name ?? 'Petugas Toko' }} )
            </td>
            <td>
                Yang Menerima (Supplier),<br><br><br><br>
                ( {{ $return->supplier?->name }} )
            </td>
        </tr>
    </table>

</body>
</html>
