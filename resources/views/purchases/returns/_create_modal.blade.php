<!-- CREATE PURCHASE RETURN MODAL -->
<div id="createReturnModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-5xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[94vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100/60 shadow-2xs">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Buat Retur Pembelian Baru</h3>
                    <p class="text-xs text-slate-400">Kembalikan barang rusak/cacat/salah kirim ke supplier dan kurangi hutang</p>
                </div>
            </div>
            <button onclick="closeModal('createReturnModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createReturnForm" action="{{ route('purchase-returns.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            
            <div class="flex-1 p-6 overflow-y-auto space-y-6">
                
                <!-- Quick GRN Autoload Selector -->
                <div class="bg-amber-50/50 border border-amber-200/70 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0">
                            <i data-lucide="package-search" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-amber-900">Tarik Data dari Penerimaan Barang (GRN)?</span>
                            <p class="text-[11px] text-amber-700">Pilih nomor GRN untuk otomatis mengisi supplier, gudang, dan daftar barang</p>
                        </div>
                    </div>
                    <select id="return_grn_selector" onchange="loadGrnDataForReturn(this.value)" class="w-full sm:w-72 bg-white border border-amber-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-amber-500 cursor-pointer shadow-2xs">
                        <option value="">-- Retur Manual (Tanpa GRN) --</option>
                        @foreach($receipts as $rc)
                            <option value="{{ $rc->id }}">
                                {{ $rc->grn_number }} - {{ $rc->supplier?->name }} ({{ $rc->receipt_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Main Form Header: 4 Columns Clean Flat Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end pb-1 border-b border-slate-100">
                    <input type="hidden" name="purchase_receipt_id" id="return_receipt_id">

                    <!-- Supplier Picker -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Supplier / Vendor <span class="text-rose-500">*</span>
                        </label>
                        <select name="supplier_id" id="return_supplier_id" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <option value="">Pilih Supplier...</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Warehouse -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Gudang Asal Barang <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_id" id="return_warehouse_id" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ $w->is_default ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Return Date -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Tanggal Retur <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input type="text" name="return_date" id="return_date_input" required value="{{ date('Y-m-d') }}" placeholder="Pilih tanggal" class="flatpickr-date w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl pl-3.5 pr-9 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Alasan Retur <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="reason" id="return_reason_input" required placeholder="Contoh: Barang rusak, cacat, expired..." class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:outline-none shadow-2xs h-10">
                    </div>
                </div>

                <!-- Products Table Card -->
                <div class="border border-slate-200/90 rounded-2xl bg-white overflow-hidden shadow-2xs">
                    
                    <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="boxes" class="w-4 h-4 text-amber-600"></i>
                            <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Item Barang yang Diretur</h4>
                        </div>
                        <button type="button" onclick="openProductPickerModal('pr')" class="px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-xs flex items-center gap-2 transition cursor-pointer border border-amber-200/80 shadow-2xs">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            <span>Cari & Tambah Produk</span>
                        </button>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/60 text-slate-700 font-extrabold text-[10px] uppercase border-b border-slate-200 tracking-wider">
                                    <th class="py-3 px-4 min-w-[220px]">Informasi Produk</th>
                                    <th class="py-3 px-3 w-32">Satuan Retur</th>
                                    <th class="py-3 px-3 w-28 text-center">Qty Retur</th>
                                    <th class="py-3 px-3 w-36 text-right">Harga Beli Satuan</th>
                                    <th class="py-3 px-3 w-28">No. Batch</th>
                                    <th class="py-3 px-4 w-40 text-right">Subtotal</th>
                                    <th class="py-3 px-3 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="return_items_tbody" class="divide-y divide-slate-100">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State in Table -->
                    <div id="return_items_empty" class="p-8 text-center text-slate-400">
                        <i data-lucide="package-x" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                        <p class="font-bold text-xs text-slate-600">Belum Ada Item Retur</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tarik dari GRN di atas atau klik <span class="text-amber-600 font-bold">"Cari & Tambah Produk"</span> untuk memasukkan barang.</p>
                    </div>
                </div>

                <!-- Total Summary -->
                <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-5 flex items-center justify-between shadow-2xs">
                    <div>
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Total Nilai Retur:</span>
                        <p class="text-[10px] text-slate-400">Stok akan langsung dikurangi dari gudang & nilai hutang berkurang</p>
                    </div>
                    <span id="return_summary_total" class="text-2xl font-black text-amber-600 font-mono-num">Rp 0</span>
                </div>

            </div>

            <!-- Modal Action Buttons -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('createReturnModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-xs shadow-md shadow-amber-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Konfirmasi & Simpan Retur</span>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Include Shared Product Picker Modal -->
<div id="productPickerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100/60">
                    <i data-lucide="package-search" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Katalog Produk Retur</h3>
                    <p class="text-[11px] text-slate-400">Pilih produk yang akan diretur ke supplier</p>
                </div>
            </div>
            <button onclick="closeModal('productPickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-amber-500 px-3.5 py-2 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="product_picker_search" 
                    oninput="filterProductPickerList()" 
                    placeholder="Ketik nama produk, barcode, SKU kode..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                >
            </div>
        </div>

        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="product_picker_container">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<script>
    const returnProducts = @json($products);
    const returnReceipts = @json($receipts);
    let returnRowIndex = 0;

    function openCreateReturnModal() {
        document.getElementById('createReturnForm').reset();
        document.getElementById('return_items_tbody').innerHTML = '';
        document.getElementById('return_receipt_id').value = '';
        document.getElementById('return_grn_selector').value = '';
        
        returnRowIndex = 0;
        toggleReturnEmptyState();
        calculateReturnTotals();
        openModal('createReturnModal');

        if (window.flatpickr) {
            flatpickr("#return_date_input", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }

    function toggleReturnEmptyState() {
        const empty = document.getElementById('return_items_empty');
        const rows = document.querySelectorAll('#return_items_tbody tr');
        if (empty) {
            empty.style.display = rows.length === 0 ? 'block' : 'none';
        }
    }

    function loadGrnDataForReturn(receiptId) {
        if (!receiptId) {
            document.getElementById('return_receipt_id').value = '';
            return;
        }

        const rc = returnReceipts.find(r => r.id == receiptId);
        if (!rc) return;

        document.getElementById('return_receipt_id').value = rc.id;
        document.getElementById('return_supplier_id').value = rc.supplier_id;
        document.getElementById('return_warehouse_id').value = rc.warehouse_id;

        // Clear and prefill items from GRN
        const tbody = document.getElementById('return_items_tbody');
        tbody.innerHTML = '';
        returnRowIndex = 0;

        if (rc.items && rc.items.length > 0) {
            rc.items.forEach(item => {
                addReturnItemRow({
                    product_id: item.product_id,
                    unit_id: item.unit_id,
                    purchase_receipt_item_id: item.id,
                    qty: item.quantity_received,
                    cost: item.unit_cost,
                    batch_number: item.batch_number
                });
            });
        }

        toggleReturnEmptyState();
        calculateReturnTotals();
    }

    // --- PRODUCT PICKER MODAL ---
    function openProductPickerModal(context = 'pr') {
        document.getElementById('product_picker_search').value = '';
        renderProductPickerList(returnProducts);
        openModal('productPickerModal');
        setTimeout(() => document.getElementById('product_picker_search').focus(), 100);
    }

    function filterProductPickerList() {
        const query = (document.getElementById('product_picker_search').value || '').toLowerCase();
        const filtered = returnProducts.filter(p => 
            (p.name && p.name.toLowerCase().includes(query)) ||
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.barcode && p.barcode.toLowerCase().includes(query))
        );
        renderProductPickerList(filtered);
    }

    function renderProductPickerList(list) {
        const container = document.getElementById('product_picker_container');
        if (!container) return;

        if (list.length === 0) {
            container.innerHTML = `
                <div class="col-span-full py-8 text-center text-slate-400">
                    <i data-lucide="package" class="w-8 h-8 mx-auto mb-1 text-slate-300"></i>
                    <p class="font-bold text-xs text-slate-600">Produk tidak ditemukan</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        container.innerHTML = list.map(p => `
            <div onclick="addProductToReturnList(${p.id})" class="p-3.5 rounded-xl border border-slate-200 hover:border-amber-500 hover:bg-amber-50/30 transition cursor-pointer flex items-center justify-between group">
                <div class="min-w-0 pr-2">
                    <div class="font-bold text-xs text-slate-800 group-hover:text-amber-600 transition truncate">${p.name}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                        <span class="font-mono text-[10px] text-amber-600 font-bold">${p.code}</span>
                        <span>•</span>
                        <span>Beli: Rp ${parseInt(p.purchase_price || 0).toLocaleString('id-ID')}</span>
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-500 group-hover:text-white flex items-center justify-center transition shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </div>
            </div>
        `).join('');

        lucide.createIcons();
    }

    function addProductToReturnList(productId) {
        const product = returnProducts.find(p => p.id === productId);
        if (!product) return;

        addReturnItemRow({
            product_id: product.id,
            unit_id: product.base_unit_id,
            purchase_receipt_item_id: null,
            qty: 1,
            cost: parseFloat(product.purchase_price || 0),
            batch_number: ''
        });

        closeModal('productPickerModal');
    }

    function addReturnItemRow(prefilled = null) {
        const tbody = document.getElementById('return_items_tbody');
        const idx = returnRowIndex++;

        let product = returnProducts.find(p => p.id == prefilled?.product_id);
        if (!product) return;

        // Populate unit options
        let unitOptions = `<option value="${product.base_unit_id}" selected>${product.base_unit ? product.base_unit.name : 'Pcs'}</option>`;
        if (product.conversions) {
            product.conversions.forEach(c => {
                if (c.from_unit) {
                    const isSel = prefilled && prefilled.unit_id == c.from_unit_id ? 'selected' : '';
                    unitOptions += `<option value="${c.from_unit_id}" ${isSel}>${c.from_unit.name}</option>`;
                }
            });
        }

        const tr = document.createElement('tr');
        tr.id = `return_row_${idx}`;
        tr.className = 'hover:bg-slate-50/70 transition border-b border-slate-100';
        tr.innerHTML = `
            <td class="py-3 px-4">
                <input type="hidden" name="items[${idx}][product_id]" value="${product.id}">
                ${prefilled && prefilled.purchase_receipt_item_id ? `<input type="hidden" name="items[${idx}][purchase_receipt_item_id]" value="${prefilled.purchase_receipt_item_id}">` : ''}
                <div class="font-bold text-xs text-slate-800">${product.name}</div>
                <div class="text-[10px] text-slate-400 font-mono">${product.code}</div>
            </td>
            <td class="py-3 px-3">
                <select name="items[${idx}][unit_id]" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-amber-700 focus:bg-white focus:outline-none focus:border-amber-500 cursor-pointer">
                    ${unitOptions}
                </select>
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0.0001" name="items[${idx}][quantity]" id="return_qty_${idx}" oninput="calculateReturnRow(${idx})" value="${prefilled ? prefilled.qty : '1'}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-amber-500">
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0" name="items[${idx}][unit_cost]" id="return_price_${idx}" oninput="calculateReturnRow(${idx})" value="${prefilled ? prefilled.cost : (product.purchase_price || 0)}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-right text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-amber-500">
            </td>
            <td class="py-3 px-3">
                <input type="text" name="items[${idx}][batch_number]" value="${prefilled?.batch_number || ''}" placeholder="Batch #" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-amber-500 font-mono">
            </td>
            <td class="py-3 px-4 text-right font-black text-slate-900 font-mono-num" id="return_subtotal_display_${idx}">
                Rp 0
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removeReturnItemRow(${idx})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Hapus Baris">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        calculateReturnRow(idx);
        toggleReturnEmptyState();
        lucide.createIcons();
    }

    function removeReturnItemRow(idx) {
        const el = document.getElementById(`return_row_${idx}`);
        if (el) el.remove();
        toggleReturnEmptyState();
        calculateReturnTotals();
    }

    function calculateReturnRow(idx) {
        const qty = parseFloat(document.getElementById(`return_qty_${idx}`)?.value) || 0;
        const price = parseFloat(document.getElementById(`return_price_${idx}`)?.value) || 0;
        const subtotal = qty * price;

        const disp = document.getElementById(`return_subtotal_display_${idx}`);
        if (disp) {
            disp.innerText = `Rp ${parseInt(subtotal).toLocaleString('id-ID')}`;
            disp.dataset.val = subtotal;
        }

        calculateReturnTotals();
    }

    function calculateReturnTotals() {
        let total = 0;
        document.querySelectorAll('#return_items_tbody td[id^="return_subtotal_display_"]').forEach(el => {
            total += parseFloat(el.dataset.val) || 0;
        });

        const disp = document.getElementById('return_summary_total');
        if (disp) disp.innerText = `Rp ${parseInt(total).toLocaleString('id-ID')}`;
    }
</script>
