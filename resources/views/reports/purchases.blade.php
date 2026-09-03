@extends('layouts.admin')

@section('title', 'Laporan Pembelian & Supplier')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Pembelian & Supplier',
        'subtitle' => 'Pantau rekap transaksi Purchase Order (PO), pengadaan barang dari supplier, diskon, dan total belanja pengadaan.',
        'exportPdfUrl' => route('reports.purchases.export-pdf', request()->query()),
        'exportExcelUrl' => route('reports.purchases.export-excel', request()->query())
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Nilai Pembelian -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total Nilai Pembelian</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Akumulasi PO aktif (di luar dibatalkan)
            </div>
        </div>

        <!-- Card 2: Jumlah PO -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Jumlah Dokumen PO</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">{{ number_format($totalOrders, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">PO</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Rata-rata PO: <strong class="text-blue-600">Rp {{ number_format($totalOrders > 0 ? ($totalPurchases / $totalOrders) : 0, 0, ',', '.') }}</strong>
            </div>
        </div>

        <!-- Card 3: Total Potongan Diskon PO -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total Hemat / Diskon</p>
                    <h3 class="text-xl font-black text-emerald-600 mt-1.5">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="badge-percent" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Potongan harga yang diberikan supplier
            </div>
        </div>

        <!-- Card 4: Ongkos Kirim & Pajak -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total Ongkir & Pajak</p>
                    <h3 class="text-xl font-black text-amber-600 mt-1.5">Rp {{ number_format($totalShipping + $totalTax, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="calculator" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span>Ongkir: <strong>Rp {{ number_format($totalShipping, 0, ',', '.') }}</strong></span>
                <span>•</span>
                <span>PPN: <strong>Rp {{ number_format($totalTax, 0, ',', '.') }}</strong></span>
            </div>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.purchases') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Dari Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Dari Tanggal
                </label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Sampai Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Sampai Tanggal
                </label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Supplier (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Supplier
                </label>
                <select name="supplier_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ $supplierId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Gudang / Cabang (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Gudang Tujuan
                </label>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status PO + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Status Dokumen
                    </label>
                    <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Terkirim (Sent)</option>
                        <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Sebagian (Partial)</option>
                        <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Selesai (Received)</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                @if(request()->hasAny(['start_date', 'end_date', 'supplier_id', 'warehouse_id', 'status']))
                    <a href="{{ route('reports.purchases') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- SUPPLIER CONTRIBUTION BREAKDOWN SUMMARY -->
    @if($supplierBreakdown->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($supplierBreakdown->take(3) as $sb)
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-brand-50 text-brand-600 font-mono">{{ $sb->supplier_code }}</span>
                <h4 class="font-bold text-xs text-slate-800 mt-1.5">{{ $sb->supplier_name }}</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $sb->total_po_count }} Dokumen PO</p>
            </div>
            <div class="text-right">
                <span class="text-[11px] text-slate-400">Total Pengadaan</span>
                <p class="font-bold text-sm text-slate-900 mt-0.5">Rp {{ number_format($sb->total_amount, 0, ',', '.') }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- PURCHASES TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="truck" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Transaksi Purchase Order (PO)</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $purchases->total() }} Dokumen
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. PO</th>
                        <th class="py-3 px-4 border-b border-white/10">Tgl Pesan</th>
                        <th class="py-3 px-4 border-b border-white/10">Supplier</th>
                        <th class="py-3 px-4 border-b border-white/10">Gudang Tujuan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status PO</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Subtotal</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Diskon / Ongkir</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Grand Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5 font-mono font-bold text-brand-600">
                            {{ $p->po_number }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $p->order_date ? \Carbon\Carbon::parse($p->order_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $p->supplier->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $p->warehouse->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-slate-100 text-slate-700',
                                    'sent' => 'bg-blue-50 text-blue-700',
                                    'partial' => 'bg-amber-50 text-amber-700',
                                    'received' => 'bg-emerald-50 text-emerald-700',
                                    'cancelled' => 'bg-rose-50 text-rose-700',
                                ];
                                $color = $statusColors[$p->status] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $color }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($p->subtotal, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-500">
                            @if($p->discount_amount > 0)
                                <span class="text-rose-600 font-semibold">-Rp {{ number_format($p->discount_amount, 0, ',', '.') }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td class="py-3 px-5 text-right font-bold text-slate-900">
                            Rp {{ number_format($p->grand_total, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="truck" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Tidak ada transaksi pembelian (PO) pada periode ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter supplier atau tanggal di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $purchases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
