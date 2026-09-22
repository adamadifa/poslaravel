@extends('layouts.admin')

@section('title', 'Detail Transaksi Penjualan - ' . $sale->invoice_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ url()->previous() ?: route('sales.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition" title="Kembali">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Faktur {{ $sale->invoice_number }}</span>
                        @if($sale->status === 'void')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">VOID / DIBATALKAN</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">BERHASIL</span>
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Waktu Transaksi: {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i:s') : '-' }} WIB
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-xs cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Faktur</span>
            </button>
        </div>
    </div>

    <!-- Metadata Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kasir / Petugas</p>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $sale->user->name ?? '-' }}</h4>
            <p class="text-xs text-slate-400 mt-0.5">{{ $sale->warehouse->name ?? 'Cabang Utama' }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pelanggan</p>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $sale->customer->name ?? 'Pelanggan Umum' }}</h4>
            <p class="text-xs text-slate-400 mt-0.5">{{ $sale->customer->phone ?? 'Walk-in' }}</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Metode Pembayaran</p>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1 uppercase">{{ $sale->payment_method ?? 'CASH' }}</h4>
            <p class="text-xs text-slate-400 mt-0.5">Status: <strong class="text-emerald-600 dark:text-emerald-400">{{ ucfirst($sale->payment_status ?? 'paid') }}</strong></p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</p>
            <h4 class="text-base font-black text-brand-600 dark:text-brand-400 mt-1">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</h4>
            <p class="text-xs text-slate-400 mt-0.5">Diskon: -Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Items Detail Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Rincian Item & Layanan</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3">Nama Produk / Jasa</th>
                        <th class="px-4 py-3 text-center">Jumlah (Qty)</th>
                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                        <th class="px-4 py-3 text-right">Diskon Item</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($sale->items as $item)
                    <tr>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $item->product->name ?? 'Item Produk' }}</div>
                            @if($item->notes)
                                <div class="text-[11px] text-slate-400 mt-0.5">Catatan: {{ $item->notes }}</div>
                            @endif
                            @if($item->product && $item->product->product_type === 'service')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 mt-1">
                                    Layanan / Jasa
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center font-bold text-slate-800 dark:text-slate-200">
                            {{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }} {{ $item->unit->short_name ?? $item->product->baseUnit->short_name ?? 'Pcs' }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">
                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-500">
                            {{ $item->discount_amount > 0 ? '-Rp ' . number_format($item->discount_amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Calculation Footer -->
        <div class="p-5 bg-slate-50/60 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            <div class="max-w-xs ml-auto space-y-2 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Subtotal Total</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($sale->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                    <span>Total Diskon</span>
                    <span class="font-mono font-semibold">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($sale->tax_amount > 0)
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Pajak (Tax)</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between text-sm font-bold text-slate-900 dark:text-white">
                    <span>Grand Total</span>
                    <span class="font-mono text-brand-600 dark:text-brand-400">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400">
                    <span>Dibayar (Cash/Transfer)</span>
                    <span class="font-mono">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400">
                    <span>Kembalian</span>
                    <span class="font-mono">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
