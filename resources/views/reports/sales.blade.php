@extends('layouts.admin')

@section('title', 'Laporan Penjualan Harian & Kasir')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Penjualan & Kasir',
        'subtitle' => 'Pantau ringkasan transaksi, penerimaan omset, laba kotor, serta rincian penjualan per outlet dan kasir.',
        'exportPdfUrl' => route('reports.sales.export-pdf', request()->query()),
        'exportExcelUrl' => route('reports.sales.export-excel', request()->query())
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Omset -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total Omset Penjualan</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span>Diskon: <strong class="text-rose-500">-Rp {{ number_format($totalDiscount, 0, ',', '.') }}</strong></span>
                <span>•</span>
                <span>Pajak: <strong class="text-slate-700">Rp {{ number_format($totalTax, 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <!-- Card 2: Total Transaksi -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Jumlah Transaksi</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">{{ number_format($totalTransactions, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Struk</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Rata-rata Struk (AOV): <strong class="text-blue-600">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</strong>
            </div>
        </div>

        <!-- Card 3: Total HPP Modal -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total Modal / HPP FIFO</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalHpp, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Biaya pokok persediaan yang dikonsumsi
            </div>
        </div>

        <!-- Card 4: Laba Kotor & Margin -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Laba Kotor (Gross Profit)</p>
                    <h3 class="text-xl font-black text-emerald-600 mt-1.5">Rp {{ number_format($grossProfit, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                <span>Margin Profit:</span>
                <span class="px-1.5 py-0.5 rounded bg-emerald-50 font-bold">{{ number_format($profitMarginPercent, 1) }}%</span>
            </div>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
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

            <!-- Gudang / Cabang (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Gudang / Cabang
                </label>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Cabang / Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Kasir / User (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Kasir
                </label>
                <select name="user_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Kasir</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c->id }}" {{ $userId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Metode Pembayaran + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Metode Bayar
                    </label>
                    <select name="payment_method" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ $paymentMethod == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="qris" {{ $paymentMethod == 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="transfer" {{ $paymentMethod == 'transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="credit" {{ $paymentMethod == 'credit' ? 'selected' : '' }}>Tempo / Kredit</option>
                    </select>
                </div>

                @if(request()->hasAny(['start_date', 'end_date', 'warehouse_id', 'user_id', 'payment_method', 'payment_status']))
                    <a href="{{ route('reports.sales') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- SALES TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Transaksi Penjualan</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $sales->total() }} Transaksi
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Invoice</th>
                        <th class="py-3 px-4 border-b border-white/10">Waktu Transaksi</th>
                        <th class="py-3 px-4 border-b border-white/10">Kasir / Outlet</th>
                        <th class="py-3 px-4 border-b border-white/10">Pelanggan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Metode Bayar</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Subtotal</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Diskon / Pajak</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Total Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $s)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5 font-mono font-bold text-brand-600">
                            {{ $s->invoice_number }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $s->sale_date ? \Carbon\Carbon::parse($s->sale_date)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $s->user->name ?? 'Kasir' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $s->warehouse->name ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium">
                            {{ $s->customer->name ?? 'Pelanggan Umum' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $s->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-700' : ($s->payment_method === 'credit' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700') }}">
                                {{ $s->payment_method ?? 'CASH' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($s->subtotal, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-500">
                            @if($s->discount_amount > 0)
                                <span class="text-rose-600 font-semibold">-Rp {{ number_format($s->discount_amount, 0, ',', '.') }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td class="py-3 px-5 text-right font-bold text-slate-900">
                            Rp {{ number_format($s->grand_total, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="receipt" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Tidak ada transaksi penjualan pada periode ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter tanggal atau outlet di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
