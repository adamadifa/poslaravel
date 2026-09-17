<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Kartu Stok & Riwayat Mutasi Barang</title>
    <style>
        @page {
            margin: 18px 20px 22px 20px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .kop-container {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #ea580c;
            margin-bottom: 3px;
        }
        .report-meta {
            font-size: 8px;
            font-style: italic;
            color: #64748b;
        }

        /* Product Detail Header Card */
        .product-card {
            width: 100%;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 4px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .product-card-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
        }
        .product-card-body {
            padding: 6px 8px;
        }
        .product-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .product-info-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 8.5px;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
            width: 15%;
        }
        .info-value {
            color: #0f172a;
            font-weight: bold;
            width: 35%;
        }
        .stat-box {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4px 8px;
            text-align: center;
        }
        .stat-label {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .stat-val {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Data Mutation Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 8px;
            padding: 5px 4px;
            border: 1px solid #334155;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .data-table td {
            padding: 4px 4px;
            font-size: 8px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .summary-row td {
            background-color: #f1f5f9 !important;
            font-weight: bold;
            font-size: 8.5px;
            color: #0f172a;
            border-top: 1px solid #94a3b8 !important;
            border-bottom: 3px double #0f172a !important;
            padding: 5px 4px;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-in { background-color: #dcfce7; color: #15803d; }
        .badge-out { background-color: #fee2e2; color: #b91c1c; }
        .footer-note {
            margin-top: 8px;
            font-size: 7.5px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>
    @php
        $companyName = \App\Models\Setting::get('company_name', \App\Models\Setting::get('app_name', 'POS Retail Pro'));
    @endphp

    <div class="kop-container">
        <div class="company-name">{{ strtoupper($companyName) }}</div>
        <div class="report-title">KARTU STOK & RIWAYAT MUTASI BARANG (STOCK CARD)</div>
        <div class="report-meta">
            @if($startDate && $endDate)
                Periode Mutasi: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} &nbsp;|&nbsp;
            @endif
            Gudang/Cabang: {{ $warehouse->name ?? 'Semua Cabang / Gudang' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
            &nbsp;|&nbsp; Operator: {{ auth()->user()->name ?? 'Administrator' }}
        </div>
    </div>

    @if($product)
    <!-- Header Detail Produk -->
    <table class="product-card">
        <tr>
            <td class="product-card-header" colspan="2">
                <strong>DETAIL INFORMASI PRODUK / BARANG</strong>
            </td>
        </tr>
        <tr>
            <td class="product-card-body" style="width: 58%; border-right: 1px solid #e2e8f0;">
                <table class="product-info-table">
                    <tr>
                        <td class="info-label">Kode SKU</td>
                        <td class="info-value" style="color: #ea580c;">: {{ $product->code ?? '-' }}</td>
                        <td class="info-label">Satuan Dasar</td>
                        <td class="info-value">: {{ $product->baseUnit->name ?? 'Pcs' }} ({{ $product->baseUnit->symbol ?? 'pcs' }})</td>
                    </tr>
                    <tr>
                        <td class="info-label">Barcode</td>
                        <td class="info-value">: {{ $product->barcode ?: '-' }}</td>
                        <td class="info-label">Kategori</td>
                        <td class="info-value">: {{ $product->category->name ?? 'Tanpa Kategori' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Nama Produk</td>
                        <td class="info-value">: {{ $product->name }}</td>
                        <td class="info-label">Harga Beli / Jual</td>
                        <td class="info-value">: Rp {{ number_format($product->purchase_price, 0, ',', '.') }} / Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
            <td class="product-card-body" style="width: 42%;">
                <table style="width: 100%; border-collapse: separate; border-spacing: 4px 0;">
                    <tr>
                        <td style="width: 25%;">
                            <div class="stat-box">
                                <div class="stat-label">Stok Awal</div>
                                <div class="stat-val">{{ number_format($initialStock, 0, ',', '.') }}</div>
                            </div>
                        </td>
                        <td style="width: 25%;">
                            <div class="stat-box">
                                <div class="stat-label" style="color: #15803d;">Total Masuk</div>
                                <div class="stat-val" style="color: #15803d;">+{{ number_format($totalIn, 0, ',', '.') }}</div>
                            </div>
                        </td>
                        <td style="width: 25%;">
                            <div class="stat-box">
                                <div class="stat-label" style="color: #b91c1c;">Total Keluar</div>
                                <div class="stat-val" style="color: #b91c1c;">-{{ number_format($totalOut, 0, ',', '.') }}</div>
                            </div>
                        </td>
                        <td style="width: 25%;">
                            <div class="stat-box" style="background-color: #e0f2fe; border-color: #bae6fd;">
                                <div class="stat-label" style="color: #0369a1;">Stok Akhir</div>
                                <div class="stat-val" style="color: #0284c7;">{{ number_format($finalStock, 0, ',', '.') }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @endif

    <!-- Tabel Detail Riwayat Mutasi -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%" class="text-center">Tanggal & Waktu</th>
                @if(!$product)
                    <th width="10%" class="text-left">Kode SKU</th>
                    <th width="18%" class="text-left">Nama Produk</th>
                @endif
                <th width="12%" class="text-left">Gudang / Lokasi</th>
                <th width="16%" class="text-left">Dokumen / Ref</th>
                <th width="8%" class="text-center">Tipe</th>
                <th width="8%" class="text-right">Masuk (+)</th>
                <th width="8%" class="text-right">Keluar (-)</th>
                <th width="8%" class="text-right">Saldo Akhir</th>
                <th width="{{ $product ? '24%' : '14%' }}" class="text-left">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $index => $m)
            @php
                $qty = (float) $m->quantity;
                $isIn = ($m->type === 'in');
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center text-muted">
                    {{ $m->created_at ? $m->created_at->format('d/m/Y H:i') : '-' }}
                </td>
                @if(!$product)
                    <td class="text-left font-bold" style="color: #ea580c;">{{ $m->product?->code ?? '-' }}</td>
                    <td class="text-left font-bold">{{ $m->product?->name ?? '-' }}</td>
                @endif
                <td class="text-left text-muted">{{ $m->warehouse?->name ?? 'Gudang Utama' }}</td>
                <td class="text-left font-bold" style="color: #475569;">
                    {{ $m->reference_type ? class_basename($m->reference_type) . ($m->reference_id ? ' #'.$m->reference_id : '') : '-' }}
                </td>
                <td class="text-center">
                    <span class="badge {{ $isIn ? 'badge-in' : 'badge-out' }}">
                        {{ $isIn ? 'MASUK' : 'KELUAR' }}
                    </span>
                </td>
                <td class="text-right font-bold" style="color: #059669;">
                    {{ $isIn ? number_format($qty, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right font-bold" style="color: #e11d48;">
                    {{ !$isIn ? number_format($qty, 0, ',', '.') : '-' }}
                </td>
                <td class="text-right font-bold" style="background-color: #f1f5f9;">
                    {{ number_format((float) $m->after_stock, 0, ',', '.') }}
                </td>
                <td class="text-left text-muted">
                    {{ $m->description ?: '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $product ? 8 : 10 }}" class="text-center" style="padding: 16px; color: #94a3b8;">
                    Tidak ada catatan mutasi persediaan untuk filter ini.
                </td>
            </tr>
            @endforelse

            @if(count($movements) > 0)
            <tr class="summary-row">
                <td colspan="{{ $product ? 4 : 6 }}" class="text-right">TOTAL MUTASI PERIODE INI:</td>
                <td class="text-right" style="color: #059669;">
                    +{{ number_format($totalIn ?? $movements->where('type', 'in')->sum('quantity'), 0, ',', '.') }}
                </td>
                <td class="text-right" style="color: #e11d48;">
                    -{{ number_format($totalOut ?? $movements->where('type', 'out')->sum('quantity'), 0, ',', '.') }}
                </td>
                <td class="text-right" style="color: #0284c7;">
                    {{ number_format($finalStock ?? ($movements->last()->after_stock ?? 0), 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer-note">
        <div class="footer-left">Dokumen ini dihasilkan secara otomatis oleh Sistem POS & Inventori.</div>
        <div class="footer-right">Halaman 1 dari 1</div>
    </div>
</body>
</html>
