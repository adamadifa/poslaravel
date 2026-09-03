@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="package-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Penerimaan Barang (GRN)</h2>
                    <p class="text-xs text-slate-400">Pencatatan pasokan masuk gudang & alokasi batch stok FIFO</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateGrnModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="package-plus" class="w-4 h-4"></i>
                <span>Catat Barang Masuk</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('purchase-receipts.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari No. GRN / Surat Jalan / Supplier
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: GRN-2026 atau No. Faktur..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Supplier Filter (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Supplier
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

            <!-- Outset Floating-label Warehouse Filter + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Gudang
                    </label>
                    <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(request()->hasAny(['search', 'supplier_id', 'warehouse_id', 'start_date', 'end_date']))
                    <a href="{{ route('purchase-receipts.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- GRN TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="package-check" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Riwayat Penerimaan Barang (GRN)</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $receipts->total() }} Penerimaan
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. GRN</th>
                        <th class="py-3 px-5 border-b border-white/10">Tanggal Terima</th>
                        <th class="py-3 px-5 border-b border-white/10">Ref. PO</th>
                        <th class="py-3 px-5 border-b border-white/10">Supplier</th>
                        <th class="py-3 px-5 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-5 border-b border-white/10">Total Nilai</th>
                        <th class="py-3 px-5 border-b border-white/10">Jatuh Tempo Hutang</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Status Hutang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($receipts as $grn)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No GRN -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-brand-600 font-mono">{{ $grn->grn_number }}</div>
                                @if($grn->supplier_invoice_number)
                                    <div class="text-[10px] text-slate-400">Faktur: {{ $grn->supplier_invoice_number }}</div>
                                @endif
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-5 text-slate-600 font-medium">
                                {{ $grn->receipt_date->format('d/m/Y') }}
                            </td>

                            <!-- Ref PO -->
                            <td class="py-3.5 px-5 font-mono text-[11px] font-bold text-slate-700">
                                {{ $grn->purchaseOrder?->po_number ?? 'Non-PO (Langsung)' }}
                            </td>

                            <!-- Supplier -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-800">{{ $grn->supplier?->name ?? '-' }}</div>
                            </td>

                            <!-- Gudang -->
                            <td class="py-3.5 px-5 text-slate-600 font-medium">
                                {{ $grn->warehouse?->name ?? 'Gudang Utama' }}
                            </td>

                            <!-- Grand Total -->
                            <td class="py-3.5 px-5 font-black text-slate-900 font-mono-num">
                                Rp {{ number_format($grn->grand_total, 0, ',', '.') }}
                            </td>

                            <!-- Jatuh Tempo -->
                            <td class="py-3.5 px-5 text-slate-600">
                                @if($grn->payment_due_date)
                                    <span class="font-bold text-slate-800">{{ $grn->payment_due_date->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-slate-400">Tunai / Langsung</span>
                                @endif
                            </td>

                            <!-- Status Hutang -->
                            <td class="py-3.5 px-5 text-center">
                                @if($grn->payment_status === 'unpaid')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Belum Lunas
                                    </span>
                                @elseif($grn->payment_status === 'partial')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Sebagian
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Lunas
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="package-x" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Riwayat Barang Masuk</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "+ Catat Barang Masuk" untuk mencatat penerimaan barang dari supplier atau PO.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<!-- CREATE GRN MODAL -->
@include('purchases.receipts._create_modal')
@endpush
