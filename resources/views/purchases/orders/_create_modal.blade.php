<!-- CREATE PURCHASE ORDER MODAL -->
<div id="createPoModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-6xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[94vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="clipboard-plus" class="w-5 h-5" id="po_modal_icon"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight" id="po_modal_title">Buat Purchase Order (PO) Baru</h3>
                    <p class="text-xs text-slate-400" id="po_modal_subtitle">Pesan pasokan stok barang ke rekanan supplier resmi</p>
                </div>
            </div>
            <button onclick="closeModal('createPoModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createPoForm" action="{{ route('purchase-orders.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <div id="po_form_method"></div>
            <input type="hidden" name="po_id" id="po_id_input">
            
            <div class="flex-1 p-6 overflow-y-auto space-y-6">
                
                <!-- Main Header: 4 Columns Clean Flat Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end pb-1 border-b border-slate-100">
                    
                    <!-- Supplier Picker -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700">
                                Supplier / Vendor <span class="text-rose-500">*</span>
                            </label>
                            <span id="po_selected_supplier_top" class="text-brand-600 font-bold text-[10px]"></span>
                        </div>
                        <input type="hidden" name="supplier_id" id="po_supplier_id" required>
                        <button type="button" onclick="openSupplierPickerModal('po')" class="w-full text-left bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2.5 flex items-center justify-between transition cursor-pointer shadow-2xs h-10">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i data-lucide="truck" class="w-4 h-4 text-brand-500 shrink-0"></i>
                                <span id="po_selected_supplier_name" class="font-bold text-xs text-slate-800 truncate">Pilih Supplier...</span>
                            </div>
                            <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        </button>
                    </div>

                    <!-- Warehouse -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Gudang Tujuan <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_id" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ $w->is_default ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Order Date -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Tanggal Order <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input type="text" name="order_date" id="po_order_date" required value="{{ date('Y-m-d') }}" placeholder="Pilih tanggal" class="flatpickr-date w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl pl-3.5 pr-9 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Expected Date -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Estimasi Tiba
                        </label>
                        <div class="relative flex items-center">
                            <input type="text" name="expected_date" id="po_expected_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" placeholder="Pilih estimasi tiba" class="flatpickr-date w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl pl-3.5 pr-9 py-2 text-xs font-semibold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <i data-lucide="calendar-clock" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Products Table Card (Full Width & Clean) -->
                <div class="border border-slate-200/90 rounded-2xl bg-white overflow-hidden shadow-2xs">
                    
                    <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="boxes" class="w-4 h-4 text-brand-500"></i>
                            <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Item Barang yang Dipesan</h4>
                        </div>
                        <button type="button" onclick="openProductPickerModal('po')" class="px-4 py-2 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-xs flex items-center gap-2 transition cursor-pointer border border-brand-200/80 shadow-2xs">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            <span>Cari & Tambah Produk</span>
                        </button>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/60 text-slate-700 font-extrabold text-[10px] uppercase border-b border-slate-200 tracking-wider">
                                    <th class="py-3 px-4 min-w-[260px]">Informasi Produk</th>
                                    <th class="py-3 px-3 w-36">Satuan Beli</th>
                                    <th class="py-3 px-3 w-28 text-center">Qty Order</th>
                                    <th class="py-3 px-3 w-40 text-right">Harga Beli Satuan</th>
                                    <th class="py-3 px-3 w-24 text-center">Diskon (%)</th>
                                    <th class="py-3 px-4 w-44 text-right">Subtotal</th>
                                    <th class="py-3 px-3 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="po_items_tbody" class="divide-y divide-slate-100">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State in Table -->
                    <div id="po_items_empty" class="p-8 text-center text-slate-400">
                        <i data-lucide="package-search" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                        <p class="font-bold text-xs text-slate-600">Belum Ada Item Produk Ditambahkan</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol <span class="text-brand-600 font-bold">"Cari & Tambah Produk"</span> di atas untuk memilih barang pesanan.</p>
                    </div>
                </div>

                <!-- Footer Notes & Calculations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pt-1">
                    
                    <!-- Notes -->
                    <div class="md:col-span-6 relative rounded-2xl border border-slate-200 hover:border-slate-300 bg-white transition p-4 flex flex-col justify-between">
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Catatan Pesanan / Instruksi Pengiriman
                        </label>
                        <textarea name="notes" id="po_notes_input" rows="4" placeholder="Contoh: Kirim via armada ekspedisi langganan, harap sertakan faktur pajak dan surat jalan fisik..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-medium text-slate-800 focus:outline-none focus:border-brand-500 resize-none h-full"></textarea>
                    </div>

                    <!-- Financial Summary Card -->
                    <div class="md:col-span-6 bg-slate-50/80 border border-slate-200/90 rounded-2xl p-5 space-y-3 text-xs shadow-2xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="font-medium">Subtotal Produk:</span>
                            <span id="po_summary_subtotal" class="font-bold text-slate-800 font-mono-num text-sm">Rp 0</span>
                        </div>
                        
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-600 font-medium">Diskon Faktur (Rp):</span>
                            <input type="number" name="discount_amount" id="po_discount_amount" oninput="calculatePoTotals()" value="0" min="0" class="w-40 bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-right font-bold text-slate-800 font-mono-num text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-600 font-medium">Pajak PPN (Rp):</span>
                            <input type="number" name="tax_amount" id="po_tax_amount" oninput="calculatePoTotals()" value="0" min="0" class="w-40 bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-right font-bold text-slate-800 font-mono-num text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-600 font-medium">Ongkos Kirim (Rp):</span>
                            <input type="number" name="shipping_cost" id="po_shipping_cost" oninput="calculatePoTotals()" value="0" min="0" class="w-40 bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-right font-bold text-slate-800 font-mono-num text-xs focus:outline-none focus:border-brand-500">
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-200/90">
                            <div>
                                <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Grand Total Pesanan:</span>
                                <p class="text-[10px] text-slate-400">Total nilai estimasi tagihan pembelian</p>
                            </div>
                            <span id="po_summary_grandtotal" class="text-xl font-black text-brand-600 font-mono-num">Rp 0</span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Action Buttons -->
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/70">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="status" value="draft" class="text-brand-500 focus:ring-brand-500">
                        <span>Simpan sebagai Draft</span>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="radio" name="status" value="sent" checked class="text-brand-500 focus:ring-brand-500">
                        <span>Langsung Kirim ke Supplier (Sent)</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" onclick="closeModal('createPoModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                    <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Simpan Purchase Order</span>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- MODAL CARI SUPPLIER (INSTANT FILTER FOR 1000s OF SUPPLIERS) -->
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

        <!-- Search Bar -->
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

        <!-- Scrollable Suppliers Grid -->
        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="supplier_picker_container">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<!-- MODAL CARI PRODUK (INSTANT FILTER FOR 1000s OF PRODUCTS) -->
<div id="productPickerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="package-search" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Katalog Produk Pembelian</h3>
                    <p class="text-[11px] text-slate-400">Pilih produk untuk ditambahkan ke daftar pesanan</p>
                </div>
            </div>
            <button onclick="closeModal('productPickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Search Bar -->
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

        <!-- Scrollable Products Grid -->
        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="product_picker_container">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<script>
    const availableSuppliers = @json($suppliers);
    const availableProducts = @json($products);
    let poTargetContext = 'po';
    let poRowIndex = 0;
    let selectedPoProducts = []; // Array of {productId, unitId, qty, price, disc}

    function openCreatePoModal() {
        document.getElementById('createPoForm').reset();
        document.getElementById('createPoForm').action = "{{ route('purchase-orders.store') }}";
        document.getElementById('po_form_method').innerHTML = '';
        document.getElementById('po_id_input').value = '';
        document.getElementById('po_items_tbody').innerHTML = '';
        document.getElementById('po_supplier_id').value = '';
        
        document.getElementById('po_modal_title').innerText = 'Buat Purchase Order (PO) Baru';
        document.getElementById('po_modal_subtitle').innerText = 'Pesan pasokan stok barang ke rekanan supplier resmi';
        
        const nameEl = document.getElementById('po_selected_supplier_name');
        if (nameEl) nameEl.innerText = 'Pilih Supplier...';
        
        const topEl = document.getElementById('po_selected_supplier_top');
        if (topEl) topEl.innerText = '';
        
        poRowIndex = 0;
        selectedPoProducts = [];
        toggleEmptyState();
        calculatePoTotals();
        openModal('createPoModal');

        // Initialize Flatpickr modern datepickers
        if (window.flatpickr) {
            flatpickr("#po_order_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });

            const next3Days = new Date();
            next3Days.setDate(next3Days.getDate() + 3);
            flatpickr("#po_expected_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: next3Days,
                locale: "id"
            });
        }
    }

    // Function to Edit / Correct Existing PO
    async function openEditPoModal(poId) {
        try {
            const res = await fetch(`/purchase-orders/${poId}/details`);
            const json = await res.json();
            if (json.status !== 'success' || !json.data) {
                showToast('error', 'Gagal memuat data PO untuk diedit');
                return;
            }

            const po = json.data;
            document.getElementById('createPoForm').reset();
            document.getElementById('createPoForm').action = `/purchase-orders/${po.id}`;
            document.getElementById('po_form_method').innerHTML = `<input type="hidden" name="_method" value="PUT">`;
            document.getElementById('po_id_input').value = po.id;
            document.getElementById('po_items_tbody').innerHTML = '';

            document.getElementById('po_modal_title').innerText = `Edit Purchase Order (${po.po_number})`;
            document.getElementById('po_modal_subtitle').innerText = 'Koreksi rincian barang, supplier, harga, atau gudang';

            // Supplier
            document.getElementById('po_supplier_id').value = po.supplier_id;
            const nameEl = document.getElementById('po_selected_supplier_name');
            if (nameEl) nameEl.innerText = po.supplier ? po.supplier.name : 'Pilih Supplier...';
            const topEl = document.getElementById('po_selected_supplier_top');
            if (topEl) topEl.innerText = po.supplier ? `Tempo: ${po.supplier.payment_term_days} hari` : '';

            // Warehouse & Dates
            const whSelect = document.querySelector('[name="warehouse_id"]');
            if (whSelect) whSelect.value = po.warehouse_id;

            if (window.flatpickr) {
                flatpickr("#po_order_date", {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d/m/Y",
                    defaultDate: po.order_date,
                    locale: "id"
                });
                flatpickr("#po_expected_date", {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d/m/Y",
                    defaultDate: po.expected_date || '',
                    locale: "id"
                });
            } else {
                document.getElementById('po_order_date').value = po.order_date;
                if (po.expected_date) document.getElementById('po_expected_date').value = po.expected_date;
            }

            // Financial Summary
            document.getElementById('po_discount_amount').value = parseFloat(po.discount_amount) || 0;
            document.getElementById('po_tax_amount').value = parseFloat(po.tax_amount) || 0;
            document.getElementById('po_shipping_cost').value = parseFloat(po.shipping_cost) || 0;
            const notesEl = document.getElementById('po_notes_input');
            if (notesEl) notesEl.value = po.notes || '';

            // Status Radio
            const statusRadios = document.querySelectorAll('input[name="status"]');
            statusRadios.forEach(r => {
                if (r.value === po.status) r.checked = true;
            });

            // Populate Items
            poRowIndex = 0;
            if (po.items && po.items.length > 0) {
                po.items.forEach(item => {
                    addPoItemWithPrefilled(item);
                });
            }

            toggleEmptyState();
            calculatePoTotals();
            openModal('createPoModal');
        } catch (err) {
            console.error(err);
            showToast('error', 'Terjadi kesalahan saat memuat data PO');
        }
    }

    function addPoItemWithPrefilled(item) {
        const product = availableProducts.find(p => p.id === item.product_id) || item.product;
        if (!product) return;

        const tbody = document.getElementById('po_items_tbody');
        const idx = poRowIndex++;

        // Build Unit Options
        let unitOptions = `<option value="${product.base_unit_id}" ${item.unit_id == product.base_unit_id ? 'selected' : ''}>${product.base_unit ? product.base_unit.name : 'Pcs'}</option>`;
        if (product.conversions) {
            product.conversions.forEach(c => {
                if (c.from_unit) {
                    const isSel = item.unit_id == c.from_unit_id ? 'selected' : '';
                    unitOptions += `<option value="${c.from_unit_id}" ${isSel}>${c.from_unit.name}</option>`;
                }
            });
        }

        const tr = document.createElement('tr');
        tr.id = `po_row_${idx}`;
        tr.className = 'hover:bg-slate-50/70 transition border-b border-slate-100';
        tr.innerHTML = `
            <td class="py-3 px-4">
                <input type="hidden" name="items[${idx}][product_id]" value="${product.id}">
                <div class="font-bold text-xs text-slate-800">${product.name}</div>
                <div class="text-[10px] text-slate-400 font-mono">${product.code}</div>
            </td>
            <td class="py-3 px-3">
                <select name="items[${idx}][unit_id]" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-600 focus:bg-white focus:outline-none focus:border-brand-500 cursor-pointer">
                    ${unitOptions}
                </select>
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0.0001" name="items[${idx}][quantity_ordered]" id="po_qty_${idx}" oninput="calculatePoRow(${idx})" value="${item.quantity_ordered}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0" name="items[${idx}][unit_price]" id="po_price_${idx}" oninput="calculatePoRow(${idx})" value="${item.unit_price}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-right text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0" max="100" name="items[${idx}][discount_percent]" id="po_disc_${idx}" oninput="calculatePoRow(${idx})" value="${item.discount_percent || 0}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-xs font-medium text-slate-800 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-4 text-right font-black text-slate-900 font-mono-num" id="po_subtotal_display_${idx}">
                Rp 0
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removePoItemRow(${idx})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Hapus Baris">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        calculatePoRow(idx);
        lucide.createIcons();
    }

    function toggleEmptyState() {
        const empty = document.getElementById('po_items_empty');
        const rows = document.querySelectorAll('#po_items_tbody tr');
        if (empty) {
            empty.style.display = rows.length === 0 ? 'block' : 'none';
        }
    }

    // --- SUPPLIER PICKER MODAL ---
    function openSupplierPickerModal(context = 'po') {
        poTargetContext = context;
        document.getElementById('supplier_picker_search').value = '';
        renderSupplierPickerList(availableSuppliers);
        openModal('supplierPickerModal');
        setTimeout(() => document.getElementById('supplier_picker_search').focus(), 100);
    }

    function filterSupplierPickerList() {
        const query = (document.getElementById('supplier_picker_search').value || '').toLowerCase();
        const filtered = availableSuppliers.filter(s => 
            (s.name && s.name.toLowerCase().includes(query)) ||
            (s.code && s.code.toLowerCase().includes(query)) ||
            (s.contact_person && s.contact_person.toLowerCase().includes(query)) ||
            (s.city && s.city.toLowerCase().includes(query)) ||
            (s.phone && s.phone.includes(query))
        );
        renderSupplierPickerList(filtered);
    }

    function renderSupplierPickerList(list) {
        const container = document.getElementById('supplier_picker_container');
        if (!container) return;

        if (list.length === 0) {
            container.innerHTML = `
                <div class="col-span-full py-8 text-center text-slate-400">
                    <i data-lucide="truck" class="w-8 h-8 mx-auto mb-1 text-slate-300"></i>
                    <p class="font-bold text-xs text-slate-600">Supplier tidak ditemukan</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        container.innerHTML = list.map(s => `
            <div onclick="selectSupplierFromPicker(${s.id})" class="p-3.5 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/30 transition cursor-pointer flex items-center justify-between group">
                <div class="min-w-0 pr-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs text-slate-800 group-hover:text-brand-600 transition truncate">${s.name}</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600">${s.code}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2 truncate">
                        <span>PIC: ${s.contact_person || '-'}</span>
                        <span>•</span>
                        <span>Tempo: ${s.payment_term_days} Hari</span>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-brand-500 transition shrink-0"></i>
            </div>
        `).join('');

        lucide.createIcons();
    }

    function selectSupplierFromPicker(supplierId) {
        const s = availableSuppliers.find(x => x.id === supplierId);
        if (!s) return;

        if (poTargetContext === 'po') {
            document.getElementById('po_supplier_id').value = s.id;
            const nameEl = document.getElementById('po_selected_supplier_name');
            if (nameEl) nameEl.innerText = s.name;
            
            const topEl = document.getElementById('po_selected_supplier_top');
            if (topEl) topEl.innerText = `Tempo: ${s.payment_term_days} hari`;
        } else if (poTargetContext === 'grn') {
            if (typeof selectGrnSupplier === 'function') {
                selectGrnSupplier(s);
            }
        }

        closeModal('supplierPickerModal');
    }

    // --- PRODUCT PICKER MODAL ---
    function openProductPickerModal(context = 'po') {
        poTargetContext = context;
        document.getElementById('product_picker_search').value = '';
        renderProductPickerList(availableProducts);
        openModal('productPickerModal');
        setTimeout(() => document.getElementById('product_picker_search').focus(), 100);
    }

    function filterProductPickerList() {
        const query = (document.getElementById('product_picker_search').value || '').toLowerCase();
        const filtered = availableProducts.filter(p => 
            (p.name && p.name.toLowerCase().includes(query)) ||
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.barcode && p.barcode.toLowerCase().includes(query)) ||
            (p.brand && p.brand.toLowerCase().includes(query))
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
            <div onclick="addProductToPoList(${p.id})" class="p-3.5 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/30 transition cursor-pointer flex items-center justify-between group">
                <div class="min-w-0 pr-2">
                    <div class="font-bold text-xs text-slate-800 group-hover:text-brand-600 transition truncate">${p.name}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                        <span class="font-mono text-[10px] text-brand-600 font-bold">${p.code}</span>
                        <span>•</span>
                        <span>Beli: Rp ${parseInt(p.purchase_price || 0).toLocaleString('id-ID')}</span>
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 group-hover:bg-brand-500 group-hover:text-white flex items-center justify-center transition shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </div>
            </div>
        `).join('');

        lucide.createIcons();
    }

    function addProductToPoList(productId) {
        const product = availableProducts.find(p => p.id === productId);
        if (!product) return;

        const tbody = document.getElementById('po_items_tbody');
        const idx = poRowIndex++;

        // Build Unit Options
        let unitOptions = `<option value="${product.base_unit_id}" selected>${product.base_unit ? product.base_unit.name : 'Pcs'}</option>`;
        if (product.conversions) {
            product.conversions.forEach(c => {
                if (c.from_unit) {
                    unitOptions += `<option value="${c.from_unit_id}">${c.from_unit.name}</option>`;
                }
            });
        }

        const tr = document.createElement('tr');
        tr.id = `po_row_${idx}`;
        tr.className = 'hover:bg-slate-50/70 transition border-b border-slate-100';
        tr.innerHTML = `
            <td class="py-3 px-4">
                <input type="hidden" name="items[${idx}][product_id]" value="${product.id}">
                <div class="font-bold text-xs text-slate-800">${product.name}</div>
                <div class="text-[10px] text-slate-400 font-mono">${product.code}</div>
            </td>
            <td class="py-3 px-3">
                <select name="items[${idx}][unit_id]" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-600 focus:bg-white focus:outline-none focus:border-brand-500 cursor-pointer">
                    ${unitOptions}
                </select>
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0.0001" name="items[${idx}][quantity_ordered]" id="po_qty_${idx}" oninput="calculatePoRow(${idx})" value="1" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0" name="items[${idx}][unit_price]" id="po_price_${idx}" oninput="calculatePoRow(${idx})" value="${product.purchase_price || 0}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-right text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0" max="100" name="items[${idx}][discount_percent]" id="po_disc_${idx}" oninput="calculatePoRow(${idx})" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-center text-xs font-medium text-slate-800 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-4 text-right font-black text-slate-900 font-mono-num" id="po_subtotal_display_${idx}">
                Rp 0
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removePoItemRow(${idx})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Hapus Baris">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        calculatePoRow(idx);
        toggleEmptyState();
        closeModal('productPickerModal');
        lucide.createIcons();
    }

    function removePoItemRow(idx) {
        const el = document.getElementById(`po_row_${idx}`);
        if (el) el.remove();
        toggleEmptyState();
        calculatePoTotals();
    }

    function calculatePoRow(idx) {
        const qty = parseFloat(document.getElementById(`po_qty_${idx}`)?.value) || 0;
        const price = parseFloat(document.getElementById(`po_price_${idx}`)?.value) || 0;
        const discPct = parseFloat(document.getElementById(`po_disc_${idx}`)?.value) || 0;

        const discAmt = (qty * price) * (discPct / 100);
        const subtotal = Math.max(0, (qty * price) - discAmt);

        const disp = document.getElementById(`po_subtotal_display_${idx}`);
        if (disp) {
            disp.innerText = `Rp ${parseInt(subtotal).toLocaleString('id-ID')}`;
            disp.dataset.val = subtotal;
        }

        calculatePoTotals();
    }

    function calculatePoTotals() {
        let subtotal = 0;
        document.querySelectorAll('[id^="po_subtotal_display_"]').forEach(el => {
            subtotal += parseFloat(el.dataset.val) || 0;
        });

        const discAmount = parseFloat(document.getElementById('po_discount_amount')?.value) || 0;
        const taxAmount = parseFloat(document.getElementById('po_tax_amount')?.value) || 0;
        const shippingCost = parseFloat(document.getElementById('po_shipping_cost')?.value) || 0;

        const grandTotal = Math.max(0, subtotal - discAmount + taxAmount + shippingCost);

        document.getElementById('po_summary_subtotal').innerText = `Rp ${parseInt(subtotal).toLocaleString('id-ID')}`;
        document.getElementById('po_summary_grandtotal').innerText = `Rp ${parseInt(grandTotal).toLocaleString('id-ID')}`;
    }
</script>
