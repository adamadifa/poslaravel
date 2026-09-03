@extends('layouts.admin')

@section('title', 'Hutang Pembelian (AP) - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Top Financial Summary Card -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Total Outstanding Payables (Solid Orange Hero Card) -->
        <div class="p-5 rounded-2xl bg-brand-500 text-white shadow-md shadow-brand-500/20 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-white/90">Total Sisa Hutang Usaha</span>
                <div class="text-2xl font-black text-white font-mono-num tracking-tight">
                    Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-white/80 font-medium">Tagihan pembelian supplier belum lunas</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center shrink-0">
                <i data-lucide="receipt-text" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Info Card -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-500">Status Pembayaran Otomatis</span>
                <div class="text-sm font-bold text-slate-800 leading-snug">
                    Mendukung Pelunasan Penuh & Cicilan / Parsial
                </div>
                <div class="text-[11px] text-slate-400 font-medium">Pengeluaran kas otomatis tercatat ke buku kas (Cash Flow)</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 border border-brand-100/80">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="receipt-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Daftar Hutang Pembelian (AP)</h2>
                    <p class="text-xs text-slate-400">Kelola tagihan faktur penerimaan barang dari supplier & bayar hutang</p>
                </div>
            </div>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('payables.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari No. Penerimaan / No. Invoice
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: REC-2026 atau INV-SUP..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Status Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status Pelunasan
                </label>
                <select name="payment_status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="" {{ !request()->has('payment_status') ? 'selected' : '' }}>Hutang Aktif (Belum Lunas & Cicilan)</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Dibayar (Unpaid)</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Dibayar Sebagian (Partial)</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                </select>
            </div>

            <!-- Outset Floating-label Supplier Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Supplier / Vendor
                </label>
                <select name="supplier_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-center">
                @if(request()->hasAny(['search', 'supplier_id', 'payment_status']))
                    <a href="{{ route('payables.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- PAYABLES TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="receipt-text" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Tagihan Hutang Pembelian</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $receipts->total() }} Faktur
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Penerimaan / Faktur</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Supplier</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total Tagihan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Sudah Dibayar</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Sisa Hutang</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 text-right w-36 border-b border-white/10">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($receipts as $r)
                        @php
                            $remaining = (float) $r->grand_total - (float) $r->paid_amount;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Penerimaan -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-brand-600 font-mono tracking-tight">{{ $r->receipt_number }}</div>
                                <div class="text-[10px] text-slate-400">Inv: {{ $r->invoice_number ?? '-' }}</div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $r->receipt_date->format('d/m/Y') }}
                            </td>

                            <!-- Supplier -->
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $r->supplier?->name ?? '-' }}
                            </td>

                            <!-- Total Tagihan -->
                            <td class="py-3.5 px-4 text-right font-black font-mono-num text-slate-900">
                                Rp {{ number_format($r->grand_total, 0, ',', '.') }}
                            </td>

                            <!-- Sudah Dibayar -->
                            <td class="py-3.5 px-4 text-right font-bold font-mono-num text-emerald-600">
                                Rp {{ number_format($r->paid_amount, 0, ',', '.') }}
                            </td>

                            <!-- Sisa Hutang -->
                            <td class="py-3.5 px-4 text-right font-black font-mono-num {{ $remaining > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                Rp {{ number_format($remaining > 0 ? $remaining : 0, 0, ',', '.') }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($r->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3"></i> Lunas
                                    </span>
                                @elseif($r->payment_status === 'partial')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Cicilan
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Belum Lunas
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                @if($remaining > 0)
                                    <button 
                                        type="button" 
                                        onclick="openPayModal({{ $r->id }}, '{{ $r->receipt_number }}', '{{ addslashes($r->supplier?->name) }}', {{ $remaining }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-xs transition cursor-pointer"
                                    >
                                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                        <span>Bayar</span>
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400 font-semibold italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto mb-2 text-emerald-400"></i>
                                <p class="font-bold text-sm text-slate-600">Tidak Ada Hutang Pembelian Outstanding</p>
                                <p class="text-xs text-slate-400 mt-0.5">Semua tagihan supplier telah lunas terbayar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

</div>

<!-- PAY AP MODAL -->
<div id="payModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Form Pembayaran Hutang Supplier</h3>
                    <p class="text-xs text-slate-400" id="payModalSubtitle">Faktur Penerimaan Barang</p>
                </div>
            </div>
            <button onclick="closeModal('payModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('payables.store') }}" method="POST">
            @csrf
            <input type="hidden" name="purchase_receipt_id" id="modal_receipt_id">

            <div class="p-6 space-y-4">
                
                <!-- Sisa Tagihan Info Banner -->
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-semibold text-amber-800 block">Sisa Hutang Belum Lunas</span>
                        <span class="text-xs text-amber-700 font-medium" id="modal_supplier_name">Supplier</span>
                    </div>
                    <div class="text-lg font-black text-amber-800 font-mono-num" id="modal_remaining_display">
                        Rp 0
                    </div>
                </div>

                <!-- Account & Method -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Bayar dari Akun <span class="text-rose-500">*</span>
                        </label>
                        <select name="account_id" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ $acc->is_default ? 'selected' : '' }}>
                                    {{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Metode Pembayaran <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_method" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="cash">Tunai / Kas (Cash)</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="check">Cek / Bilyet Giro</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                </div>

                <!-- Amount & Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nominal Dibayar (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="any" 
                            min="1" 
                            name="amount" 
                            id="modal_amount_input" 
                            required 
                            class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-900 font-mono-num focus:ring-0 focus:outline-none"
                        >
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Tanggal Bayar <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="payment_date" 
                            id="modal_payment_date" 
                            value="{{ date('Y-m-d') }}" 
                            required 
                            class="flatpickr-date w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer"
                        >
                    </div>

                </div>

                <!-- Reference & Notes -->
                <div class="space-y-3.5">
                    
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            No. Referensi / Bukti Transfer
                        </label>
                        <input 
                            type="text" 
                            name="reference_number" 
                            placeholder="Contoh: TRF-8392183 atau No. Giro..." 
                            class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2 shadow-2xs">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Catatan Pembayaran
                        </label>
                        <input 
                            type="text" 
                            name="notes" 
                            placeholder="Catatan tambahan pelunasan / cicilan..." 
                            class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                        >
                    </div>

                </div>

            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('payModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Proses Pembayaran</span>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    function openPayModal(receiptId, receiptNum, supplierName, remaining) {
        document.getElementById('modal_receipt_id').value = receiptId;
        document.getElementById('payModalSubtitle').innerText = `Pelunasan Faktur ${receiptNum}`;
        document.getElementById('modal_supplier_name').innerText = `Supplier: ${supplierName}`;
        document.getElementById('modal_remaining_display').innerText = `Rp ${remaining.toLocaleString('id-ID')}`;
        document.getElementById('modal_amount_input').value = remaining;
        document.getElementById('modal_amount_input').max = remaining;

        openModal('payModal');

        if (window.flatpickr) {
            flatpickr("#modal_payment_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }
</script>

@endsection
