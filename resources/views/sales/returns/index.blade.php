@extends('layouts.admin')

@section('title', 'Retur Penjualan - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Top Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Total Refund (Solid Orange Hero Card) -->
        <div class="p-5 rounded-2xl bg-brand-500 text-white shadow-md shadow-brand-500/20 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-white/90">Total Nilai Retur Penjualan</span>
                <div class="text-2xl font-black text-white font-mono-num tracking-tight">
                    Rp {{ number_format($totalRefund, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-white/80 font-medium">Akumulasi pengembalian barang dari pelanggan</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 text-white flex items-center justify-center shrink-0">
                <i data-lucide="rotate-ccw" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Info Card -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-500">Pemulihan Stok & Refund Kas</span>
                <div class="text-sm font-bold text-slate-800 leading-snug">
                    Otomatis Restock Barang & Potong Kas / Piutang
                </div>
                <div class="text-[11px] text-slate-400 font-medium">Kartu stok dan buku kas diperbarui secara real-time</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 border border-brand-100/80">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Daftar Retur Penjualan (Sales Return)</h2>
                    <p class="text-xs text-slate-400">Kelola pengembalian barang konsumen, refund kasir, dan penyesuaian piutang</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateReturnModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Retur Penjualan</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Filter Form -->
        <form action="{{ route('sale-returns.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search (Col 6) -->
            <div class="lg:col-span-6 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor Retur / Invoice / Pelanggan
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: SR-2026 atau INV-POS..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Status Filter (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status Dokumen
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                </select>
            </div>

            <!-- Reset (Col 2) -->
            <div class="lg:col-span-2 flex items-center justify-end">
                @if(request()->hasAny(['search', 'status', 'start_date', 'end_date']))
                    <a href="{{ route('sale-returns.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- SALE RETURNS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="rotate-ccw" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Transaksi Retur Penjualan</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $returns->total() }} Dokumen
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Retur & Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Invoice Referensi</th>
                        <th class="py-3 px-4 border-b border-white/10">Pelanggan</th>
                        <th class="py-3 px-4 border-b border-white/10">Alasan & Metode Refund</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Nilai Refund</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 text-right w-28 border-b border-white/10">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($returns as $r)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Retur & Tanggal -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-brand-600 font-mono tracking-tight">{{ $r->return_number }}</div>
                                <div class="text-[10px] text-slate-400">{{ $r->return_date->format('d/m/Y') }} • Kasir: {{ $r->creator?->name ?? 'Admin' }}</div>
                            </td>

                            <!-- Invoice -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 font-mono">{{ $r->sale?->invoice_number }}</div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $r->returnItems->count() }} Produk Diretur
                                    @if($r->replacementItems->count() > 0)
                                        • <span class="font-bold text-brand-600">{{ $r->replacementItems->count() }} Ditukar</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Pelanggan -->
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $r->customer?->name ?? 'Pelanggan Umum' }}
                            </td>

                            <!-- Alasan & Refund -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $r->reason }}</div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Metode: 
                                    @if($r->refund_method === 'cash')
                                        <span class="font-bold text-brand-600">Tunai (Akun: {{ $r->account?->name ?? '-' }})</span>
                                    @elseif($r->refund_method === 'credit_deduction')
                                        <span class="font-bold text-blue-600">Potong Piutang</span>
                                    @else
                                        <span class="font-bold text-purple-600">Tukar Barang</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Nilai Refund -->
                            <td class="py-3.5 px-4 text-right font-black font-mono-num text-sm text-rose-600">
                                Rp {{ number_format($r->refund_amount, 0, ',', '.') }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($r->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3"></i> Selesai
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                @if($r->status === 'completed')
                                    <form action="{{ route('sale-returns.destroy', $r->id) }}" method="POST" class="inline" id="cancel_sr_{{ $r->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('cancel_sr_{{ $r->id }}', 'Batalkan Retur {{ $r->return_number }}?', 'Stok gudang akan otomatis dipotong kembali dan dana refund dipulihkan!')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Batalkan Retur">
                                            <i data-lucide="ban" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('sale-returns.destroy', $r->id) }}" method="POST" class="inline" id="delete_sr_{{ $r->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('delete_sr_{{ $r->id }}', 'Hapus Data Retur {{ $r->return_number }}?', 'Data yang telah dibatalkan ini akan dihapus permanen.')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Permanen">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="rotate-ccw" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Transaksi Retur Penjualan</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Buat Retur Penjualan" di atas jika ada pengembalian produk dari konsumen.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
    @include('sales.returns._create_modal')
    @include('sales.returns._invoice_picker_modal')
    @include('sales.returns._replacement_picker_modal')
@endpush

@push('scripts')
<script>
    const availableProductsList = @json($products ?? []);
    let currentInvoiceItems = [];
    let replacementItems = []; // Array of {product_id, unit_id, quantity, unit_price, product_name, product_code}
    let invoiceSearchTimeout = null;

    function openCreateReturnModal() {
        document.getElementById('createReturnForm').reset();
        document.getElementById('sr_sale_id').value = '';
        document.getElementById('sr_invoice_selected_card').classList.add('hidden');
        document.getElementById('sr_invoice_unselected_prompt').classList.remove('hidden');
        document.getElementById('sr_items_tbody').innerHTML = '';
        document.getElementById('sr_items_empty').style.display = 'block';
        document.getElementById('sr_total_refund_display').innerText = 'Rp 0';
        currentInvoiceItems = [];
        replacementItems = [];
        renderReplacementItemsTable();
        
        clearValidationErrors();
        toggleRefundAccountField();
        openModal('createReturnModal');

        if (window.flatpickr) {
            flatpickr("#sr_date_input", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }

    function toggleRefundAccountField() {
        const method = document.getElementById('sr_refund_method_select').value;
        const container = document.getElementById('sr_account_container');
        const replacementCard = document.getElementById('sr_replacement_card');
        const submitBtnText = document.getElementById('sr_submit_btn_text');
        const summaryTitle = document.getElementById('sr_summary_title');
        const summaryDesc = document.getElementById('sr_summary_desc');
        const summaryBadge = document.getElementById('sr_summary_badge');
        const breakdownWrap = document.getElementById('sr_breakdown_wrap');
        const finalAmountLabel = document.getElementById('sr_final_amount_label');

        if (method === 'cash') {
            container.style.display = 'block';
            replacementCard.classList.add('hidden');
            breakdownWrap.classList.add('hidden');
            if (submitBtnText) submitBtnText.innerText = 'Proses Retur & Refund';
            if (summaryTitle) summaryTitle.innerText = 'Total Nilai Pengembalian Dana (Refund)';
            if (summaryDesc) summaryDesc.innerText = 'Stok barang retur akan otomatis dipulihkan (IN) ke inventaris toko';
            if (summaryBadge) {
                summaryBadge.innerText = 'Pengembalian Tunai';
                summaryBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800';
            }
            if (finalAmountLabel) finalAmountLabel.innerText = 'Dana Dikembalikan ke Pembeli';
        } else if (method === 'exchange') {
            container.style.display = 'none';
            replacementCard.classList.remove('hidden');
            breakdownWrap.classList.remove('hidden');
            if (submitBtnText) submitBtnText.innerText = 'Proses Tukar Barang';
            if (summaryTitle) summaryTitle.innerText = 'Ringkasan & Selisih Tukar Barang';
            if (summaryDesc) summaryDesc.innerText = 'Mutasi masuk (IN) untuk barang retur dan mutasi keluar (OUT) untuk barang pengganti';
            if (summaryBadge) {
                summaryBadge.innerText = 'Tukar Barang';
                summaryBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800';
            }
            if (finalAmountLabel) finalAmountLabel.innerText = 'Selisih Transaksi';
        } else {
            container.style.display = 'none';
            replacementCard.classList.add('hidden');
            breakdownWrap.classList.add('hidden');
            if (submitBtnText) submitBtnText.innerText = 'Proses Retur & Potong Piutang';
            if (summaryTitle) summaryTitle.innerText = 'Total Nilai Pemotongan Piutang';
            if (summaryDesc) summaryDesc.innerText = 'Stok barang retur akan otomatis dipulihkan (IN) dan piutang penjualan dikurangi';
            if (summaryBadge) {
                summaryBadge.innerText = 'Potong Piutang';
                summaryBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800';
            }
            if (finalAmountLabel) finalAmountLabel.innerText = 'Pengurangan Nilai Invoice';
        }

        calculateReturnTotals();
    }

    // --- REPLACEMENT PRODUCTS MODAL & MANAGEMENT ---

    function openReplacementProductPickerModal() {
        document.getElementById('replacement_product_search').value = '';
        renderReplacementProductGrid(availableProductsList);
        openModal('replacementProductPickerModal');
    }

    function filterReplacementProducts() {
        const q = (document.getElementById('replacement_product_search').value || '').toLowerCase().trim();
        const filtered = availableProductsList.filter(p => {
            return (p.name && p.name.toLowerCase().includes(q)) ||
                   (p.code && p.code.toLowerCase().includes(q)) ||
                   (p.barcode && p.barcode.toLowerCase().includes(q));
        });
        renderReplacementProductGrid(filtered);
    }

    function renderReplacementProductGrid(products) {
        const container = document.getElementById('replacement_product_container');
        const empty = document.getElementById('replacement_product_empty');
        container.innerHTML = '';

        if (!products || products.length === 0) {
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');

        products.forEach(p => {
            const card = document.createElement('div');
            card.className = 'p-3.5 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/20 bg-white transition flex items-center justify-between gap-3 cursor-pointer shadow-2xs';
            card.onclick = () => addReplacementProduct(p);

            const priceFormatted = 'Rp ' + Math.round(p.selling_price || 0).toLocaleString('id-ID');
            const unitName = p.base_unit ? p.base_unit.name : 'Unit';

            card.innerHTML = `
                <div class="min-w-0 flex-1">
                    <div class="font-bold text-slate-800 text-xs truncate">${p.name}</div>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">${p.code || '-'} • Satuan: ${unitName}</div>
                    <div class="text-xs font-black text-brand-600 font-mono-num mt-1">${priceFormatted}</div>
                </div>
                <button type="button" class="px-3 py-1.5 rounded-lg bg-brand-500 text-white font-bold text-xs shrink-0 hover:bg-brand-600 transition">
                    Pilih
                </button>
            `;
            container.appendChild(card);
        });

        if (window.lucide) lucide.createIcons();
    }

    function addReplacementProduct(product) {
        const existingIdx = replacementItems.findIndex(item => item.product_id === product.id);
        if (existingIdx >= 0) {
            replacementItems[existingIdx].quantity += 1;
        } else {
            replacementItems.push({
                product_id: product.id,
                unit_id: product.base_unit_id || null,
                quantity: 1,
                unit_price: parseFloat(product.selling_price || 0),
                product_name: product.name,
                product_code: product.code || '',
                unit_name: product.base_unit ? product.base_unit.name : 'Unit'
            });
        }

        renderReplacementItemsTable();
        closeModal('replacementProductPickerModal');
        calculateReturnTotals();
    }

    function updateReplacementQty(idx, qty) {
        const parsed = parseFloat(qty);
        if (isNaN(parsed) || parsed < 0) {
            replacementItems[idx].quantity = 0;
        } else {
            replacementItems[idx].quantity = parsed;
        }
        calculateReturnTotals();
    }

    function removeReplacementItem(idx) {
        replacementItems.splice(idx, 1);
        renderReplacementItemsTable();
        calculateReturnTotals();
    }

    function renderReplacementItemsTable() {
        const tbody = document.getElementById('sr_rep_items_tbody');
        const empty = document.getElementById('sr_rep_items_empty');
        tbody.innerHTML = '';

        if (!replacementItems || replacementItems.length === 0) {
            empty.style.display = 'block';
            return;
        }

        empty.style.display = 'none';

        replacementItems.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 border-b border-slate-100';

            const subtotal = item.quantity * item.unit_price;

            tr.innerHTML = `
                <td class="py-3 px-4">
                    <input type="hidden" name="replacement_items[${idx}][product_id]" value="${item.product_id}">
                    <input type="hidden" name="replacement_items[${idx}][unit_id]" value="${item.unit_id || ''}">
                    <input type="hidden" name="replacement_items[${idx}][unit_price]" value="${item.unit_price}">
                    <div class="font-bold text-slate-800">${item.product_name}</div>
                    <div class="text-[10px] text-slate-400 font-mono">${item.product_code} • ${item.unit_name}</div>
                </td>
                <td class="py-3 px-3 text-right font-mono-num text-slate-700">
                    Rp ${item.unit_price.toLocaleString('id-ID')}
                </td>
                <td class="py-3 px-3 text-center">
                    <input 
                        type="number" 
                        step="any" 
                        min="0.0001" 
                        name="replacement_items[${idx}][quantity]" 
                        value="${item.quantity}" 
                        oninput="updateReplacementQty(${idx}, this.value)"
                        class="w-24 mx-auto bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 rounded-lg px-2 py-1 text-center font-bold text-slate-900 font-mono-num text-xs focus:outline-none"
                    >
                </td>
                <td class="py-3 px-4 text-right font-black font-mono-num text-slate-900" id="sr_rep_line_total_${idx}">
                    Rp ${subtotal.toLocaleString('id-ID')}
                </td>
                <td class="py-3 px-3 text-center">
                    <button type="button" onclick="removeReplacementItem(${idx})" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (window.lucide) lucide.createIcons();
    }

    // --- INVOICE PICKER MODAL FUNCTIONS ---

    function openInvoicePickerModal() {
        document.getElementById('invoice_picker_search').value = '';
        openModal('invoicePickerModal');
        fetchInvoicesList('');
    }

    function debounceFetchInvoices() {
        clearTimeout(invoiceSearchTimeout);
        invoiceSearchTimeout = setTimeout(() => {
            const query = document.getElementById('invoice_picker_search').value.trim();
            fetchInvoicesList(query);
        }, 300);
    }

    function fetchInvoicesList(query) {
        const spinner = document.getElementById('invoice_picker_spinner');
        const loading = document.getElementById('invoice_picker_loading');
        const empty = document.getElementById('invoice_picker_empty');
        const tbody = document.getElementById('invoice_picker_tbody');

        if (spinner) spinner.classList.remove('hidden');
        if (loading && !tbody.children.length) loading.classList.remove('hidden');
        empty.classList.add('hidden');

        fetch(`/sale-returns/list-invoices?search=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(sales => {
                if (spinner) spinner.classList.add('hidden');
                if (loading) loading.classList.add('hidden');
                tbody.innerHTML = '';

                if (!sales || sales.length === 0) {
                    empty.classList.remove('hidden');
                    return;
                }

                empty.classList.add('hidden');

                sales.forEach(sale => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition border-b border-slate-100';

                    const dateStr = sale.sale_date ? new Date(sale.sale_date).toLocaleDateString('id-ID', {
                        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                    }) : '-';
                    const customerName = sale.customer ? sale.customer.name : 'Pelanggan Umum';
                    const cashierName = sale.user ? sale.user.name : '-';
                    const totalFormatted = 'Rp ' + Math.round(sale.grand_total).toLocaleString('id-ID');
                    const itemsCount = sale.items ? sale.items.length : 0;

                    tr.innerHTML = `
                        <td class="py-3 px-4">
                            <div class="font-bold text-brand-600 font-mono tracking-tight">${sale.invoice_number}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">${dateStr} • ${itemsCount} Produk</div>
                        </td>
                        <td class="py-3 px-3 font-semibold text-slate-800">
                            ${customerName}
                        </td>
                        <td class="py-3 px-3 text-slate-500 font-medium text-[11px]">
                            ${cashierName}
                        </td>
                        <td class="py-3 px-4 text-right font-black font-mono-num text-slate-900">
                            ${totalFormatted}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button 
                                type="button" 
                                onclick="selectInvoiceFromPicker(${sale.id})" 
                                class="px-3.5 py-1.5 rounded-lg bg-brand-50 hover:bg-brand-500 text-brand-600 hover:text-white font-bold text-xs transition cursor-pointer border border-brand-200 hover:border-brand-500"
                            >
                                Pilih
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                if (window.lucide) {
                    window.lucide.createIcons();
                }
            })
            .catch(err => {
                if (spinner) spinner.classList.add('hidden');
                if (loading) loading.classList.add('hidden');
                console.error('Error fetching invoices:', err);
            });
    }

    function selectInvoiceFromPicker(saleId) {
        fetch(`/sale-returns/search-invoice?invoice_number=${encodeURIComponent(saleId)}`)
            .then(res => {
                if (!res.ok) throw new Error('Detail invoice tidak dapat dimuat.');
                return res.json();
            })
            .then(sale => {
                document.getElementById('sr_sale_id').value = sale.id;
                document.getElementById('sr_info_inv_num').innerText = sale.invoice_number;
                
                const dateStr = sale.sale_date ? new Date(sale.sale_date).toLocaleDateString('id-ID', {
                    day: '2-digit', month: '2-digit', year: 'numeric'
                }) : '-';
                document.getElementById('sr_info_inv_date').innerText = dateStr;

                const custName = sale.customer ? sale.customer.name : 'Pelanggan Umum';
                const userName = sale.user ? sale.user.name : 'Admin';
                document.getElementById('sr_info_inv_cust').innerText = `Pelanggan: ${custName} • Kasir: ${userName}`;
                
                document.getElementById('sr_info_inv_total').innerText = `Rp ${parseFloat(sale.grand_total).toLocaleString('id-ID')}`;
                
                document.getElementById('sr_invoice_selected_card').classList.remove('hidden');
                document.getElementById('sr_invoice_unselected_prompt').classList.add('hidden');
                document.getElementById('sr_error_sale_id').classList.add('hidden');

                const tbody = document.getElementById('sr_items_tbody');
                tbody.innerHTML = '';
                document.getElementById('sr_items_empty').style.display = 'none';

                currentInvoiceItems = sale.items || [];
                currentInvoiceItems.forEach((item, idx) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 border-b border-slate-100';
                    tr.innerHTML = `
                        <td class="py-3 px-4">
                            <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                            <input type="hidden" name="items[${idx}][unit_id]" value="${item.unit_id || ''}">
                            <input type="hidden" name="items[${idx}][unit_price]" value="${item.unit_price}">
                            <div class="font-bold text-slate-800">${item.product ? item.product.name : 'Produk'}</div>
                            <div class="text-[10px] text-slate-400 font-mono">${item.product ? item.product.code : '-'}</div>
                        </td>
                        <td class="py-3 px-3 text-right font-mono-num font-bold text-slate-600">
                            ${parseFloat(item.quantity).toLocaleString('id-ID')}
                        </td>
                        <td class="py-3 px-3 text-right font-mono-num text-slate-700">
                            Rp ${parseFloat(item.unit_price).toLocaleString('id-ID')}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <input 
                                type="number" 
                                step="any" 
                                min="0" 
                                max="${item.quantity}" 
                                name="items[${idx}][quantity]" 
                                value="0" 
                                oninput="calculateReturnTotals()"
                                class="w-24 mx-auto bg-slate-50 border border-slate-200 focus:bg-white focus:border-brand-500 rounded-lg px-2 py-1 text-center font-bold text-slate-900 font-mono-num text-xs focus:outline-none"
                            >
                        </td>
                        <td class="py-3 px-4 text-right font-black font-mono-num text-slate-900" id="sr_line_total_${idx}">
                            Rp 0
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                calculateReturnTotals();
                closeModal('invoicePickerModal');
            })
            .catch(err => {
                alert(err.message);
            });
    }

    function calculateReturnTotals() {
        let returnTotal = 0;
        currentInvoiceItems.forEach((item, idx) => {
            const qtyInput = document.querySelector(`input[name="items[${idx}][quantity]"]`);
            const qty = qtyInput ? parseFloat(qtyInput.value || 0) : 0;
            const lineTotal = qty * parseFloat(item.unit_price);
            returnTotal += lineTotal;

            const disp = document.getElementById(`sr_line_total_${idx}`);
            if (disp) disp.innerText = `Rp ${lineTotal.toLocaleString('id-ID')}`;
        });

        const method = document.getElementById('sr_refund_method_select').value;
        let repTotal = 0;
        const displayEl = document.getElementById('sr_total_refund_display');
        const statusBadge = document.getElementById('sr_status_badge');
        const retSubVal = document.getElementById('sr_subtotal_return_val');
        const repSubVal = document.getElementById('sr_subtotal_rep_val');

        if (retSubVal) retSubVal.innerText = `Rp ${returnTotal.toLocaleString('id-ID')}`;

        if (method === 'exchange') {
            replacementItems.forEach((item, idx) => {
                const sub = item.quantity * item.unit_price;
                repTotal += sub;
                const dispRep = document.getElementById(`sr_rep_line_total_${idx}`);
                if (dispRep) dispRep.innerText = `Rp ${sub.toLocaleString('id-ID')}`;
            });

            if (repSubVal) repSubVal.innerText = `Rp ${repTotal.toLocaleString('id-ID')}`;

            const diff = returnTotal - repTotal;

            if (statusBadge) statusBadge.classList.remove('hidden');

            if (diff > 0) {
                displayEl.innerText = `Rp ${diff.toLocaleString('id-ID')}`;
                displayEl.className = 'text-xl sm:text-2xl font-black text-rose-600 font-mono-num';
                if (statusBadge) {
                    statusBadge.innerText = 'Sisa Dana Dikembalikan Toko';
                    statusBadge.className = 'px-2.5 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200';
                }
            } else if (diff < 0) {
                displayEl.innerText = `Rp ${Math.abs(diff).toLocaleString('id-ID')}`;
                displayEl.className = 'text-xl sm:text-2xl font-black text-emerald-600 font-mono-num';
                if (statusBadge) {
                    statusBadge.innerText = 'Pelanggan Menambah Pembayaran';
                    statusBadge.className = 'px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200';
                }
            } else {
                displayEl.innerText = `Rp 0`;
                displayEl.className = 'text-xl sm:text-2xl font-black text-slate-800 font-mono-num';
                if (statusBadge) {
                    statusBadge.innerText = 'Tukar Seimbang (Pas)';
                    statusBadge.className = 'px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200';
                }
            }
        } else {
            if (statusBadge) statusBadge.classList.add('hidden');
            displayEl.innerText = `Rp ${returnTotal.toLocaleString('id-ID')}`;
            displayEl.className = 'text-xl sm:text-2xl font-black text-rose-600 font-mono-num';
        }

        if (returnTotal > 0) {
            document.getElementById('sr_error_items').classList.add('hidden');
        }
        if (replacementItems.length > 0) {
            document.getElementById('sr_error_replacement').classList.add('hidden');
        }
    }

    function clearValidationErrors() {
        document.getElementById('sr_error_sale_id').classList.add('hidden');
        document.getElementById('sr_error_reason').classList.add('hidden');
        document.getElementById('sr_error_items').classList.add('hidden');
        document.getElementById('sr_error_replacement').classList.add('hidden');
        
        const boxReason = document.getElementById('sr_box_reason');
        if (boxReason) {
            boxReason.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/10');
            boxReason.classList.add('border-slate-200');
        }
        const labelReason = document.getElementById('sr_label_reason');
        if (labelReason) {
            labelReason.classList.remove('text-rose-500');
            labelReason.classList.add('text-slate-700');
        }
    }

    function validateSaleReturnForm(e) {
        clearValidationErrors();
        let isValid = true;

        const saleId = document.getElementById('sr_sale_id').value;
        if (!saleId) {
            document.getElementById('sr_error_sale_id').classList.remove('hidden');
            isValid = false;
        }

        const reason = document.getElementById('sr_input_reason').value.trim();
        if (!reason) {
            document.getElementById('sr_error_reason').classList.remove('hidden');
            const boxReason = document.getElementById('sr_box_reason');
            if (boxReason) {
                boxReason.classList.remove('border-slate-200');
                boxReason.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/10');
            }
            const labelReason = document.getElementById('sr_label_reason');
            if (labelReason) {
                labelReason.classList.remove('text-slate-700');
                labelReason.classList.add('text-rose-500');
            }
            isValid = false;
        }

        let totalQty = 0;
        currentInvoiceItems.forEach((item, idx) => {
            const qtyInput = document.querySelector(`input[name="items[${idx}][quantity]"]`);
            const qty = qtyInput ? parseFloat(qtyInput.value || 0) : 0;
            totalQty += qty;
        });

        if (totalQty <= 0) {
            document.getElementById('sr_error_items').classList.remove('hidden');
            isValid = false;
        }

        const method = document.getElementById('sr_refund_method_select').value;
        if (method === 'exchange') {
            let totalRepQty = 0;
            replacementItems.forEach(item => {
                totalRepQty += (item.quantity || 0);
            });

            if (totalRepQty <= 0) {
                document.getElementById('sr_error_replacement').classList.remove('hidden');
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
            return false;
        }

        return true;
    }
</script>
@endpush
