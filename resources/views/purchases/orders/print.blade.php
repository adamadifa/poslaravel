<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchaseOrder->po_number }} - {{ $company['name'] }}</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS CDN for Standalone Print Layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        @media print {
            body {
                background: white !important;
                color: #0f172a !important;
                font-size: 11pt;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
                border: none !important;
            }
            .page-break-inside-avoid {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen py-6 sm:py-10 print:py-0">

    <!-- ACTION / TOOLBAR (Hidden in Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 px-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali</span>
                </a>
                <div class="h-5 w-px bg-slate-200"></div>
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Purchase Order:</span>
                    <span class="text-xs font-black text-slate-900 font-mono ml-1">{{ $purchaseOrder->po_number }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-500/20 transition cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>
    </div>

    <!-- DOCUMENT CONTAINER (A4 FORMAT) -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 shadow-xl border border-slate-200 rounded-2xl print:p-0 print:border-none print:shadow-none print:rounded-none">
        
        <!-- HEADER & KOP PERUSAHAAN -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-6 mb-6">
            <!-- Left: Company Profile -->
            <div class="flex items-start gap-4 max-w-md">
                @if($company['logo'])
                    <img src="{{ asset('storage/' . $company['logo']) }}" alt="Logo" class="w-16 h-16 object-contain rounded-xl border border-slate-100 p-1">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-xl tracking-wider shrink-0 shadow-xs">
                        {{ strtoupper(substr($company['name'], 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none mb-1">{{ $company['name'] }}</h1>
                    @if(!empty($company['tagline']))
                        <p class="text-[11px] font-semibold text-slate-500 mb-1.5">{{ $company['tagline'] }}</p>
                    @endif
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $company['address'] }}</p>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-slate-500 mt-1">
                        @if(!empty($company['phone']))
                            <span><strong>Telp:</strong> {{ $company['phone'] }}</span>
                        @endif
                        @if(!empty($company['email']))
                            <span><strong>Email:</strong> {{ $company['email'] }}</span>
                        @endif
                        @if(!empty($company['npwp']) && $company['npwp'] !== '-')
                            <span><strong>NPWP:</strong> {{ $company['npwp'] }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Document Title & PO Identifiers -->
            <div class="text-right shrink-0">
                <div class="inline-block px-3 py-1 rounded-lg bg-slate-900 text-white font-extrabold text-xs uppercase tracking-widest mb-2">
                    PURCHASE ORDER
                </div>
                <div class="text-sm font-black text-brand-600 font-mono">{{ $purchaseOrder->po_number }}</div>
                
                <table class="mt-2 text-left text-xs ml-auto border-separate border-spacing-y-1">
                    <tr>
                        <td class="text-slate-500 font-medium pr-3">Tanggal PO</td>
                        <td class="font-bold text-slate-800">: {{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @if($purchaseOrder->expected_date)
                    <tr>
                        <td class="text-slate-500 font-medium pr-3">Estimasi Tiba</td>
                        <td class="font-bold text-slate-800">: {{ $purchaseOrder->expected_date->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-slate-500 font-medium pr-3">Status Dokumen</td>
                        <td class="font-bold">
                            @if($purchaseOrder->status === 'draft')
                                <span class="text-slate-600 font-extrabold">: DRAFT (Belum Dikirim)</span>
                            @elseif($purchaseOrder->status === 'sent')
                                <span class="text-blue-600 font-extrabold">: TERKIRIM</span>
                            @elseif($purchaseOrder->status === 'partial')
                                <span class="text-amber-600 font-extrabold">: SEBAGIAN DITERIMA</span>
                            @elseif($purchaseOrder->status === 'received')
                                <span class="text-emerald-600 font-extrabold">: SELESAI (DITERIMA)</span>
                            @elseif($purchaseOrder->status === 'cancelled')
                                <span class="text-rose-600 font-extrabold">: DIBATALKAN</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- 2-COLUMN INFO: SUPPLIER & DESTINASI GUDANG -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <!-- Box Supplier -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 print:bg-transparent">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>KEPADA SUPPLIER / VENDOR:</span>
                </div>
                <div class="text-sm font-black text-slate-900 mb-1">{{ $purchaseOrder->supplier?->name ?? '-' }}</div>
                @if($purchaseOrder->supplier?->contact_person)
                    <div class="text-xs text-slate-700 font-medium"><strong>U.P / PIC:</strong> {{ $purchaseOrder->supplier->contact_person }}</div>
                @endif
                <div class="text-xs text-slate-600 leading-relaxed mt-1">{{ $purchaseOrder->supplier?->address ?? '-' }}</div>
                <div class="text-xs text-slate-600 mt-1">
                    @if($purchaseOrder->supplier?->phone)
                        <span><strong>Telp/WA:</strong> {{ $purchaseOrder->supplier->phone }}</span>
                    @endif
                    @if($purchaseOrder->supplier?->email)
                        <span class="ml-2"><strong>Email:</strong> {{ $purchaseOrder->supplier->email }}</span>
                    @endif
                </div>
                @if($purchaseOrder->supplier?->payment_term_days)
                    <div class="text-[11px] font-bold text-slate-700 mt-1.5 pt-1.5 border-t border-slate-200">
                        Termin Pembayaran: <span class="text-brand-600">{{ $purchaseOrder->supplier->payment_term_days }} Hari</span>
                    </div>
                @endif
            </div>

            <!-- Box Destinasi Gudang -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 print:bg-transparent">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>KIRIM KE (DESTINASI GUDANG):</span>
                </div>
                <div class="text-sm font-black text-slate-900 mb-1">{{ $purchaseOrder->warehouse?->name ?? 'Gudang Utama' }}</div>
                <div class="text-xs text-slate-600 leading-relaxed">
                    {{ $purchaseOrder->warehouse?->address ?? $company['address'] }}
                </div>
                @if($purchaseOrder->warehouse?->phone)
                    <div class="text-xs text-slate-600 mt-1">
                        <strong>Telp Gudang:</strong> {{ $purchaseOrder->warehouse->phone }}
                    </div>
                @endif
                <div class="text-[11px] text-slate-500 mt-2 pt-1.5 border-t border-slate-200">
                    <strong>Petugas Purchasing:</strong> {{ $purchaseOrder->user?->name ?? 'Staff Pengadaan' }}
                </div>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <div class="overflow-hidden border border-slate-200 rounded-xl mb-6">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white font-extrabold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3 border-r border-slate-800 text-center w-10">No</th>
                        <th class="py-2.5 px-3 border-r border-slate-800">Kode & Nama Produk / Bahan Baku</th>
                        <th class="py-2.5 px-3 border-r border-slate-800 text-center w-20">Satuan</th>
                        <th class="py-2.5 px-3 border-r border-slate-800 text-center w-20">Qty Order</th>
                        <th class="py-2.5 px-3 border-r border-slate-800 text-right w-28">Harga Satuan</th>
                        <th class="py-2.5 px-3 border-r border-slate-800 text-center w-16">Disc %</th>
                        <th class="py-2.5 px-3 text-right w-32">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($purchaseOrder->items as $index => $item)
                        @php
                            $qtyFormatted = str_replace('.', ',', (string) (float) $item->quantity_ordered);
                            $discFormatted = (float) $item->discount_percent > 0 ? str_replace('.', ',', (string) (float) $item->discount_percent) . '%' : '-';
                            $unitName = $item->unit?->name ?? $item->product?->baseUnit?->name ?? 'Pcs';
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3 border-r border-slate-200 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-3 border-r border-slate-200">
                                <div class="font-black text-slate-900">{{ $item->product?->name ?? 'Item #'.$item->product_id }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">SKU: {{ $item->product?->sku ?? '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 text-center font-bold text-slate-700">{{ $unitName }}</td>
                            <td class="py-2.5 px-3 border-r border-slate-200 text-center font-black text-slate-900 font-mono text-xs">{{ $qtyFormatted }}</td>
                            <td class="py-2.5 px-3 border-r border-slate-200 text-right font-mono text-slate-800">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 text-center text-slate-600 font-mono">{{ $discFormatted }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400 italic">Tidak ada item dalam purchase order ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- SUMMARY & NOTES (Avoid Page Break) -->
        <div class="page-break-inside-avoid grid grid-cols-12 gap-6 mb-8">
            <!-- Left: Notes & Terms (7 Cols) -->
            <div class="col-span-7 space-y-3">
                @if(!empty($purchaseOrder->notes))
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Catatan Khusus Pesanan:</div>
                        <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed">{{ $purchaseOrder->notes }}</p>
                    </div>
                @endif

                <div class="p-3 bg-slate-50/70 border border-slate-200 rounded-xl text-[11px] text-slate-600 space-y-1">
                    <div class="font-bold text-slate-800 uppercase text-[10px] tracking-wider mb-0.5">Syarat & Ketentuan Pemesanan:</div>
                    <p>1. Harap cantumkan Nomor PO <strong>({{ $purchaseOrder->po_number }})</strong> pada Surat Jalan dan Faktur Tagihan.</p>
                    <p>2. Barang yang dikirim harus dalam kondisi baik, baru, dan sesuai dengan spesifikasi yang tercantum.</p>
                    <p>3. Tim gudang berhak menolak barang apabila kuantiti atau spesifikasi tidak sesuai dengan Purchase Order resmi ini.</p>
                </div>
            </div>

            <!-- Right: Financial Summary (5 Cols) -->
            <div class="col-span-5 bg-slate-50 p-4 rounded-xl border border-slate-200 print:bg-transparent">
                <table class="w-full text-xs">
                    <tbody>
                        <tr>
                            <td class="py-1 text-slate-600 font-medium">Subtotal Produk</td>
                            <td class="py-1 text-right font-mono font-bold text-slate-800">
                                Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if((float) $purchaseOrder->discount_amount > 0)
                        <tr>
                            <td class="py-1 text-emerald-600 font-medium">Potongan Diskon</td>
                            <td class="py-1 text-right font-mono font-bold text-emerald-600">
                                - Rp {{ number_format($purchaseOrder->discount_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        @if((float) $purchaseOrder->tax_amount > 0)
                        <tr>
                            <td class="py-1 text-slate-600 font-medium">Pajak (PPN)</td>
                            <td class="py-1 text-right font-mono font-bold text-slate-800">
                                + Rp {{ number_format($purchaseOrder->tax_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        @if((float) $purchaseOrder->shipping_cost > 0)
                        <tr>
                            <td class="py-1 text-slate-600 font-medium">Ongkos Kirim</td>
                            <td class="py-1 text-right font-mono font-bold text-slate-800">
                                + Rp {{ number_format($purchaseOrder->shipping_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endif
                        <tr class="border-t-2 border-slate-900">
                            <td class="pt-2.5 pb-1 font-black text-slate-900 text-sm uppercase">Grand Total</td>
                            <td class="pt-2.5 pb-1 text-right font-mono font-black text-slate-900 text-base">
                                Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SIGNATURES (Tanda Tangan 3 Pihak) -->
        <div class="page-break-inside-avoid border-t border-slate-200 pt-6 mt-6">
            <div class="grid grid-cols-3 gap-6 text-center">
                <!-- Dibuat Oleh -->
                <div class="flex flex-col items-center">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">DIBUAT OLEH (PURCHASING)</p>
                    <div class="h-20 flex items-end justify-center w-full">
                        <div class="w-48 border-b border-slate-700 pb-1 text-center">
                            <span class="font-bold text-xs text-slate-900 whitespace-nowrap">{{ $purchaseOrder->user?->name ?? 'Staff Purchasing' }}</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1.5 font-medium">Tanggal: {{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('d/m/Y') : '_____ / _____ / 20___' }}</p>
                </div>

                <!-- Disetujui Oleh -->
                <div class="flex flex-col items-center">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">DISETUJUI OLEH (MANAJER / OWNER)</p>
                    <div class="h-20 flex items-end justify-center w-full">
                        <div class="w-48 border-b border-slate-700 pb-1 text-center">
                            <span class="font-bold text-xs text-slate-700 whitespace-nowrap">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1.5 font-medium">Tanggal: &nbsp;_____ / _____ / 20___</p>
                </div>

                <!-- Diterima & Disetujui Vendor -->
                <div class="flex flex-col items-center">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">KONFIRMASI SUPPLIER / VENDOR</p>
                    <div class="h-20 flex items-end justify-center w-full">
                        <div class="w-48 border-b border-slate-700 pb-1 text-center">
                            <span class="font-bold text-xs text-slate-900 whitespace-nowrap">
                                @if(!empty($purchaseOrder->supplier?->contact_person))
                                    {{ $purchaseOrder->supplier->contact_person }}
                                @elseif(!empty($purchaseOrder->supplier?->name))
                                    {{ $purchaseOrder->supplier->name }}
                                @else
                                    ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
                                @endif
                            </span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1.5 font-medium">Tanda Tangan & Cap / Stempel</p>
                </div>
            </div>
        </div>

        <!-- PRINT FOOTER TIMESTAMP -->
        <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
            <span>Dokumen dicetak resmi dari sistem <strong>{{ $company['name'] }}</strong></span>
            <span>Waktu Cetak: {{ now()->format('d/m/Y H:i') }} WIB</span>
        </div>

    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
