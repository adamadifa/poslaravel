<!-- CREATE GOODS RECEIPT (GRN) MODAL -->
<div id="createGrnModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-6xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[94vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="package-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Catat Penerimaan Barang (GRN)</h3>
                    <p class="text-xs text-slate-400">Penerimaan pasokan barang dari Purchase Order atau barang masuk langsung</p>
                </div>
            </div>
            <button onclick="closeModal('createGrnModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createGrnForm" action="{{ route('purchase-receipts.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            
            <div class="flex-1 p-6 overflow-y-auto space-y-6">
                
                <!-- Autoload from PO Option -->
                <div class="p-4 bg-brand-50/70 border border-brand-200/80 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-500 text-white flex items-center justify-center shadow-xs">
                            <i data-lucide="link" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-xs font-black text-brand-900 uppercase tracking-wider block">Tautkan Purchase Order (PO):</span>
                            <span class="text-[11px] text-brand-700/80">Otomatis memuat daftar barang pesanan & sisa kuantitas</span>
                        </div>
                    </div>
                    <select id="grn_po_selector" name="purchase_order_id" onchange="onPoSelectChange(this.value)" class="bg-white border border-brand-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:border-brand-500 cursor-pointer min-w-[320px] shadow-2xs">
                        <option value="">-- Tanpa PO (Input Barang Masuk Langsung) --</option>
                        @foreach($openPOs as $opo)
                            <option value="{{ $opo->id }}" {{ request('purchase_order_id') == $opo->id ? 'selected' : '' }}>
                                {{ $opo->po_number }} - {{ $opo->supplier?->name }} (Rp {{ number_format($opo->grand_total, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Main Form Header: 4 Columns Clean Flat Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end pb-1 border-b border-slate-100">
                    
                    <!-- Supplier Picker -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700">
                                Supplier / Vendor <span class="text-rose-500">*</span>
                            </label>
                            <span id="grn_selected_supplier_top" class="text-brand-600 font-bold text-[10px]"></span>
                        </div>
                        <input type="hidden" name="supplier_id" id="grn_supplier_id" required>
                        <button type="button" onclick="openSupplierPickerModal('grn')" class="w-full text-left bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2.5 flex items-center justify-between transition cursor-pointer shadow-2xs h-10">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i data-lucide="truck" class="w-4 h-4 text-brand-500 shrink-0"></i>
                                <span id="grn_selected_supplier_name" class="font-bold text-xs text-slate-800 truncate">Pilih Supplier...</span>
                            </div>
                            <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        </button>
                    </div>

                    <!-- Warehouse -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Gudang Penerima <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_id" id="grn_warehouse_id" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ $w->is_default ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Receipt Date -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Tanggal Terima <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input type="text" name="receipt_date" id="grn_receipt_date" required value="{{ date('Y-m-d') }}" placeholder="Pilih tanggal" class="flatpickr-date w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl pl-3.5 pr-9 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Supplier Invoice Number -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            No. Surat Jalan / Faktur
                        </label>
                        <input type="text" name="supplier_invoice_number" placeholder="Contoh: SJ-99812" class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:outline-none shadow-2xs h-10">
                    </div>
                </div>

                <!-- Goods Table Card (Full Width & Clean) -->
                <div class="border border-slate-200/90 rounded-2xl bg-white overflow-hidden shadow-2xs">
                    
                    <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="package-check" class="w-4 h-4 text-brand-500"></i>
                            <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Item Barang Masuk & Batch FIFO</h4>
                        </div>
                        <button type="button" onclick="openProductPickerModal('grn')" class="px-4 py-2 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-xs flex items-center gap-2 transition cursor-pointer border border-brand-200/80 shadow-2xs">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            <span>Cari & Tambah Produk</span>
                        </button>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/60 text-slate-700 font-extrabold text-[10px] uppercase border-b border-slate-200 tracking-wider">
                                    <th class="py-3 px-4 min-w-[240px]">Informasi Produk</th>
                                    <th class="py-3 px-3 w-32">Satuan Beli</th>
                                    <th class="py-3 px-3 w-28 text-center">Qty Diterima</th>
                                    <th class="py-3 px-3 w-36 text-right">Harga Beli Satuan</th>
                                    <th class="py-3 px-3 w-32">No. Batch</th>
                                    <th class="py-3 px-3 w-32">Kadaluarsa</th>
                                    <th class="py-3 px-4 w-40 text-right">Subtotal</th>
                                    <th class="py-3 px-3 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="grn_items_tbody" class="divide-y divide-slate-100">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State in Table -->
                    <div id="grn_items_empty" class="p-8 text-center text-slate-400">
                        <i data-lucide="package-search" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                        <p class="font-bold text-xs text-slate-600">Belum Ada Item Barang Ditambahkan</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Pilih PO di atas atau klik tombol <span class="text-brand-600 font-bold">"Cari & Tambah Produk"</span>.</p>
                    </div>
                </div>

                <!-- Footer Summary & Notes Grid -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pt-1">
                    
                    <!-- Notes -->
                    <div class="md:col-span-6 relative rounded-2xl border border-slate-200 hover:border-slate-300 bg-white transition p-4 flex flex-col justify-between">
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan Penerimaan / Kondisi Barang
                        </label>
                        <textarea name="notes" rows="4" placeholder="Contoh: Seluruh barang diterima dalam kondisi baik, tersegel utuh, dan sesuai nomor surat jalan..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500 resize-none h-full"></textarea>
                    </div>

                    <!-- Financial Summary Card -->
                    <div class="md:col-span-6 bg-slate-50/80 border border-slate-200/90 rounded-2xl p-5 space-y-3 text-xs shadow-2xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-medium">Subtotal Barang Masuk:</span>
                            <span id="grn_summary_subtotal" class="font-bold text-slate-800 font-mono-num text-sm">Rp 0</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-600 font-medium">Pajak PPN (Rp):</span>
                            <input type="number" name="tax_amount" id="grn_tax_amount" oninput="calculateGrnTotals()" value="0" min="0" class="w-40 bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-right font-bold text-slate-800 font-mono-num text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-200/90">
                            <div>
                                <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Total Nilai Penerimaan:</span>
                                <p class="text-[10px] text-slate-400">Total nilai stok yang masuk ke neraca persediaan</p>
                            </div>
                            <span id="grn_summary_grandtotal" class="text-xl font-black text-brand-600 font-mono-num">Rp 0</span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Action Buttons -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('createGrnModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Konfirmasi & Tambah Stok FIFO</span>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- SHARED SELECTION MODALS INCLUDED ON PAGE -->
@if(!View::hasSection('shared_pickers'))
<!-- SUPPLIER PICKER MODAL -->
<div id="supplierPickerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Pilih Rekanan Supplier</h3>
                    <p class="text-[11px] text-slate-400">Pencarian cepat database supplier</p>
                </div>
            </div>
            <button onclick="closeModal('supplierPickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 px-3.5 py-2 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="supplier_picker_search" 
                    oninput="filterSupplierPickerList()" 
                    placeholder="Ketik nama pemasok, kontak PIC, kota, telepon, atau kode..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                >
            </div>
        </div>

        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="supplier_picker_container">
            </div>
        </div>
    </div>
</div>

<!-- PRODUCT PICKER MODAL -->
<div id="productPickerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="package-search" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Katalog Produk Pembelian</h3>
                    <p class="text-[11px] text-slate-400">Pilih produk untuk ditambahkan ke penerimaan barang</p>
                </div>
            </div>
            <button onclick="closeModal('productPickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 px-3.5 py-2 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="product_picker_search" 
                    oninput="filterProductPickerList()" 
                    placeholder="Ketik nama produk, barcode, SKU kode, atau merk..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                >
            </div>
        </div>

        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="product_picker_container">
            </div>
        </div>
    </div>
</div>
@endif

<script>
    const grnSuppliers = @json($suppliers);
    const grnProducts = @json($products);
    let grnRowIndex = 0;
    let grnTargetContext = 'grn';

    function openCreateGrnModal() {
        document.getElementById('createGrnForm').reset();
        document.getElementById('grn_items_tbody').innerHTML = '';
        document.getElementById('grn_supplier_id').value = '';
        
        const nameEl = document.getElementById('grn_selected_supplier_name');
        if (nameEl) nameEl.innerText = 'Pilih Supplier...';

        const topEl = document.getElementById('grn_selected_supplier_top');
        if (topEl) topEl.innerText = '';
        
        grnRowIndex = 0;
        toggleGrnEmptyState();
        calculateGrnTotals();
        openModal('createGrnModal');

        // Initialize Flatpickr modern datepickers
        if (window.flatpickr) {
            flatpickr("#grn_receipt_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }

    function toggleGrnEmptyState() {
        const empty = document.getElementById('grn_items_empty');
        const rows = document.querySelectorAll('#grn_items_tbody tr');
        if (empty) {
            empty.style.display = rows.length === 0 ? 'block' : 'none';
        }
    }

    function selectGrnSupplier(s) {
        document.getElementById('grn_supplier_id').value = s.id;
        const nameEl = document.getElementById('grn_selected_supplier_name');
        if (nameEl) nameEl.innerText = s.name;
        
        const topEl = document.getElementById('grn_selected_supplier_top');
        if (topEl) topEl.innerText = `Tempo: ${s.payment_term_days} hari`;
    }

    function addProductToGrnList(productId) {
        const product = grnProducts.find(p => p.id === productId);
        if (!product) return;

        addGrnItemRow({
            product_id: product.id,
            unit_id: product.base_unit_id,
            po_item_id: null,
            qty: 1,
            cost: parseFloat(product.purchase_price || 0)
        });

        closeModal('productPickerModal');
    }

    function addGrnItemRow(prefilled = null) {
        const tbody = document.getElementById('grn_items_tbody');
        const idx = grnRowIndex++;

        let product = grnProducts.find(p => p.id == prefilled?.product_id);
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

        const autoBatch = 'BATCH-' + new Date().toISOString().slice(2,10).replace(/-/g,'') + '-' + Math.floor(100 + Math.random() * 900);

        const tr = document.createElement('tr');
        tr.id = `grn_row_${idx}`;
        tr.className = 'hover:bg-slate-50/70 transition border-b border-slate-100';
        tr.innerHTML = `
            <td class="py-3 px-4">
                <input type="hidden" name="items[${idx}][product_id]" value="${product.id}">
                ${prefilled && prefilled.po_item_id ? `<input type="hidden" name="items[${idx}][purchase_order_item_id]" value="${prefilled.po_item_id}">` : ''}
                <div class="font-bold text-xs text-slate-800">${product.name}</div>
                <div class="text-[10px] text-slate-400 font-mono">${product.code}</div>
            </td>
            <td class="py-3 px-3">
                <select name="items[${idx}][unit_id]" id="grn_unit_${idx}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-600 focus:bg-white focus:outline-none focus:border-brand-500 cursor-pointer">
                    ${unitOptions}
                </select>
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0.0001" name="items[${idx}][quantity_received]" id="grn_qty_${idx}" oninput="calculateGrnRow(${idx})" value="${prefilled ? prefilled.qty : '1'}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0" name="items[${idx}][unit_cost]" id="grn_price_${idx}" oninput="calculateGrnRow(${idx})" value="${prefilled ? prefilled.cost : (product.purchase_price || 0)}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-right text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3">
                <input type="text" name="items[${idx}][batch_number]" value="${autoBatch}" placeholder="Batch #" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-brand-500 font-mono">
            </td>
            <td class="py-3 px-3">
                <input type="text" name="items[${idx}][expiry_date]" id="grn_expiry_${idx}" placeholder="Pilih Exp" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-brand-500 cursor-pointer">
            </td>
            <td class="py-3 px-4 text-right font-black text-slate-900 font-mono-num" id="grn_subtotal_display_${idx}">
                Rp 0
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removeGrnItemRow(${idx})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Hapus Baris">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        if (window.flatpickr) {
            flatpickr(`#grn_expiry_${idx}`, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                locale: "id"
            });
        }

        calculateGrnRow(idx);
        toggleGrnEmptyState();
        lucide.createIcons();
    }

    function removeGrnItemRow(idx) {
        const el = document.getElementById(`grn_row_${idx}`);
        if (el) el.remove();
        toggleGrnEmptyState();
        calculateGrnTotals();
    }

    function calculateGrnRow(idx) {
        const qty = parseFloat(document.getElementById(`grn_qty_${idx}`)?.value) || 0;
        const cost = parseFloat(document.getElementById(`grn_price_${idx}`)?.value) || 0;
        const subtotal = qty * cost;

        const disp = document.getElementById(`grn_subtotal_display_${idx}`);
        if (disp) {
            disp.innerText = `Rp ${parseInt(subtotal).toLocaleString('id-ID')}`;
            disp.dataset.val = subtotal;
        }

        calculateGrnTotals();
    }

    function calculateGrnTotals() {
        let subtotal = 0;
        document.querySelectorAll('[id^="grn_subtotal_display_"]').forEach(el => {
            subtotal += parseFloat(el.dataset.val) || 0;
        });

        const taxAmount = parseFloat(document.getElementById('grn_tax_amount')?.value) || 0;
        const grandTotal = subtotal + taxAmount;

        document.getElementById('grn_summary_subtotal').innerText = `Rp ${parseInt(subtotal).toLocaleString('id-ID')}`;
        document.getElementById('grn_summary_grandtotal').innerText = `Rp ${parseInt(grandTotal).toLocaleString('id-ID')}`;
    }

    async function onPoSelectChange(poId) {
        if (!poId) {
            document.getElementById('grn_items_tbody').innerHTML = '';
            toggleGrnEmptyState();
            calculateGrnTotals();
            return;
        }

        try {
            const res = await fetch(`/purchase-orders/${poId}/details`);
            const data = await res.json();
            if (data.status === 'success') {
                const po = data.data;

                // Auto select supplier and warehouse
                const s = grnSuppliers.find(x => x.id === po.supplier_id);
                if (s) selectGrnSupplier(s);
                document.getElementById('grn_warehouse_id').value = po.warehouse_id;

                // Populate items from PO
                const tbody = document.getElementById('grn_items_tbody');
                tbody.innerHTML = '';
                grnRowIndex = 0;

                (po.items || []).forEach(item => {
                    const remainingQty = Math.max(0, parseFloat(item.quantity_ordered) - parseFloat(item.quantity_received));
                    if (remainingQty > 0) {
                        addGrnItemRow({
                            product_id: item.product_id,
                            unit_id: item.unit_id,
                            po_item_id: item.id,
                            qty: remainingQty,
                            cost: parseFloat(item.unit_price)
                        });
                    }
                });

                calculateGrnTotals();
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Check if opened with pre-selected PO
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const poParam = urlParams.get('purchase_order_id');
        if (poParam) {
            openCreateGrnModal();
            document.getElementById('grn_po_selector').value = poParam;
            onPoSelectChange(poParam);
        }
    });
</script>
