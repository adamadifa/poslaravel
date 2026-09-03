@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Purchase Order (PO)</h2>
                    <p class="text-xs text-slate-400">Kelola pemesanan pengadaan stok barang ke vendor / supplier</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreatePoModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat PO Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('purchase-orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor PO / Supplier
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: PO-2026 atau PT Sumber Makmur..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Status Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status PO
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim (Sent)</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Sebagian (Partial)</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Selesai (Received)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Outset Floating-label Supplier Filter + Reset (Col 4) -->
            <div class="lg:col-span-4 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
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

                @if(request()->hasAny(['search', 'status', 'supplier_id', 'start_date', 'end_date']))
                    <a href="{{ route('purchase-orders.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- PO TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Purchase Order</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $orders->total() }} Pesanan
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. PO</th>
                        <th class="py-3 px-5 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-5 border-b border-white/10">Supplier / Vendor</th>
                        <th class="py-3 px-5 border-b border-white/10">Gudang Tujuan</th>
                        <th class="py-3 px-5 border-b border-white/10">Total Nilai</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($orders as $po)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No PO -->
                            <td class="py-3.5 px-5 font-bold text-brand-600 font-mono">
                                {{ $po->po_number }}
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-5 text-slate-600">
                                <div class="font-medium">{{ $po->order_date->format('d/m/Y') }}</div>
                                @if($po->expected_date)
                                    <div class="text-[10px] text-slate-400">Exp: {{ $po->expected_date->format('d/m/Y') }}</div>
                                @endif
                            </td>

                            <!-- Supplier -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-800">{{ $po->supplier?->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $po->supplier?->phone ?? '-' }}</div>
                            </td>

                            <!-- Gudang -->
                            <td class="py-3.5 px-5 text-slate-600 font-medium">
                                {{ $po->warehouse?->name ?? 'Gudang Utama' }}
                            </td>

                            <!-- Grand Total -->
                            <td class="py-3.5 px-5 font-black text-slate-900 font-mono-num">
                                Rp {{ number_format($po->grand_total, 0, ',', '.') }}
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3.5 px-5 text-center">
                                @if($po->status === 'draft')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Draft
                                    </span>
                                @elseif($po->status === 'sent')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Terkirim
                                    </span>
                                @elseif($po->status === 'partial')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Sebagian
                                    </span>
                                @elseif($po->status === 'received')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai
                                    </span>
                                @elseif($po->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    @if(in_array($po->status, ['draft', 'sent']))
                                        <button type="button" onclick="openEditPoModal({{ $po->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition cursor-pointer" title="Edit / Koreksi PO">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                    @endif

                                    @if($po->status === 'draft')
                                        <form action="{{ route('purchase-orders.update-status', $po->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="sent">
                                            <button type="submit" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition cursor-pointer" title="Kirim Pesanan ke Supplier">
                                                <i data-lucide="send" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($po->status, ['sent', 'partial']))
                                        <a href="{{ route('purchase-receipts.index', ['purchase_order_id' => $po->id]) }}" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] transition flex items-center gap-1" title="Terima Barang">
                                            <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                                            <span>Terima</span>
                                        </a>
                                    @endif

                                    @if(in_array($po->status, ['draft', 'sent']))
                                        <form action="{{ route('purchase-orders.update-status', $po->id) }}" method="POST" class="inline" id="cancel_po_{{ $po->id }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="button" onclick="confirmDelete('cancel_po_{{ $po->id }}', 'Batalkan PO {{ $po->po_number }}?', 'Pesanan pembelian ini akan berstatus dibatalkan!')" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition cursor-pointer" title="Batalkan PO">
                                                <i data-lucide="ban" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($po->status, ['draft', 'cancelled']))
                                        <form action="{{ route('purchase-orders.destroy', $po->id) }}" method="POST" class="inline" id="delete_po_{{ $po->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete_po_{{ $po->id }}', 'Hapus PO {{ $po->po_number }}?', 'Data pesanan pembelian ini akan dihapus permanen!')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus PO">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="clipboard-x" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Purchase Order</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "+ Buat PO Baru" untuk membuat pesanan pembelian barang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<!-- CREATE PO MODAL & SELECTION MODALS -->
@include('purchases.orders._create_modal')
@endpush
