<!-- CREATE STOCK ADJUSTMENT MODAL -->
<div id="createAdjustmentModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-5xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[94vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="adjModalTitle" class="font-extrabold text-base text-slate-900 tracking-tight">Buat Penyesuaian Stok Baru</h3>
                    <p class="text-xs text-slate-400">Koreksi manual stok barang untuk barang rusak, kedaluwarsa, atau bonus</p>
                </div>
            </div>
            <button onclick="closeModal('createAdjustmentModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createAdjustmentForm" action="{{ route('stock-adjustments.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <div id="adj_method_field"></div>
            
            <div class="flex-1 p-6 overflow-y-auto space-y-6">

                <!-- Main Form Header: 4 Columns Clean Flat Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end pb-1 border-b border-slate-100">
                    
                    <!-- Warehouse -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Lokasi Gudang <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_id" id="adj_warehouse_id" onchange="onAdjustmentWarehouseChange()" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ $w->is_default ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type: Addition or Reduction -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Tipe Penyesuaian <span class="text-rose-500">*</span>
                        </label>
                        <select name="type" id="adj_type_select" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <option value="reduction">Pengurangan Stok (-) [Rusak/Hilang/Expired]</option>
                            <option value="addition">Penambahan Stok (+) [Bonus/Sample/Temuan]</option>
                        </select>
                    </div>

                    <!-- Reason -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Alasan Koreksi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="reason" id="adj_reason_input" required placeholder="Contoh: Barang pecah, expired, dll..." class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:outline-none shadow-2xs h-10">
                    </div>

                    <!-- Adjustment Date -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Tanggal Koreksi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input type="text" name="adjustment_date" id="adj_date_input" required value="{{ date('Y-m-d') }}" placeholder="Pilih tanggal" class="flatpickr-date w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl pl-3.5 pr-9 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Products Table Card -->
                <div class="border border-slate-200/90 rounded-2xl bg-white overflow-hidden shadow-2xs">
                    
                    <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="boxes" class="w-4 h-4 text-brand-600"></i>
                            <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Item Produk yang Disesuaikan</h4>
                        </div>
                        <button type="button" onclick="openProductPickerModalAdj()" class="px-4 py-2 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-xs flex items-center gap-2 transition cursor-pointer border border-brand-200 shadow-2xs">
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
                                    <th class="py-3 px-3 w-28 text-right">Stok Sistem</th>
                                    <th class="py-3 px-3 w-32">Satuan</th>
                                    <th class="py-3 px-3 w-32 text-center">Qty Penyesuaian</th>
                                    <th class="py-3 px-3 w-36 text-right">HPP Dasar (Rp)</th>
                                    <th class="py-3 px-3 w-28">No. Batch</th>
                                    <th class="py-3 px-3 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="adj_items_tbody" class="divide-y divide-slate-100">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State in Table -->
                    <div id="adj_items_empty" class="p-8 text-center text-slate-400">
                        <i data-lucide="package-search" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                        <p class="font-bold text-xs text-slate-600">Belum Ada Item Penyesuaian</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol <span class="text-brand-600 font-bold">"Cari & Tambah Produk"</span> di atas.</p>
                    </div>
                </div>

                <!-- Total Count Summary -->
                <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-4 flex items-center justify-between shadow-2xs">
                    <div>
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Total Item:</span>
                        <p class="text-[11px] text-slate-400">Pilih "Setujui & Posting" untuk langsung memperbarui stok fisik gudang</p>
                    </div>
                    <span id="adj_summary_total" class="text-lg font-black text-brand-600 font-mono-num">0 Item</span>
                </div>

            </div>

            <!-- Modal Action Buttons -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('createAdjustmentModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" name="action" value="draft" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition cursor-pointer">
                    Simpan Draft
                </button>
                <button type="submit" name="action" value="approve" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Setujui & Posting Stok</span>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Include Shared Product Picker Modal for Adjustment -->
<div id="adjProductPickerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="package-search" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Katalog Produk Penyesuaian</h3>
                    <p class="text-[11px] text-slate-400">Pilih produk untuk disesuaikan kuantitinya</p>
                </div>
            </div>
            <button onclick="closeModal('adjProductPickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 px-3.5 py-2 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="adj_product_picker_search" 
                    oninput="filterAdjProductPickerList()" 
                    placeholder="Ketik nama produk, SKU kode, atau barcode..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                >
            </div>
        </div>

        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="adj_product_picker_container">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<script>
    const adjAllProducts = @json($products);
    let adjRowIndex = 0;

    function openCreateAdjustmentModal() {
        document.getElementById('createAdjustmentForm').reset();
        document.getElementById('createAdjustmentForm').action = "{{ route('stock-adjustments.store') }}";
        document.getElementById('adj_method_field').innerHTML = '';
        document.getElementById('adjModalTitle').innerText = 'Buat Penyesuaian Stok Baru';
        document.getElementById('adj_items_tbody').innerHTML = '';
        adjRowIndex = 0;
        toggleAdjEmptyState();
        calculateAdjTotals();
        openModal('createAdjustmentModal');

        if (window.flatpickr) {
            flatpickr("#adj_date_input", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }

    function openEditAdjustmentModal(adjId) {
        openCreateAdjustmentModal();
        document.getElementById('adjModalTitle').innerText = 'Edit Penyesuaian Stok';
        document.getElementById('createAdjustmentForm').action = `/stock-adjustments/${adjId}`;
        document.getElementById('adj_method_field').innerHTML = '@method("PUT")';

        fetch(`/stock-adjustments/${adjId}`)
            .then(res => res.json())
            .then(adj => {
                document.getElementById('adj_warehouse_id').value = adj.warehouse_id;
                document.getElementById('adj_type_select').value = adj.type;
                document.getElementById('adj_reason_input').value = adj.reason || '';
                document.getElementById('adj_date_input').value = adj.adjustment_date ? adj.adjustment_date.split('T')[0] : '';

                const tbody = document.getElementById('adj_items_tbody');
                tbody.innerHTML = '';
                adjRowIndex = 0;

                if (adj.items && adj.items.length > 0) {
                    adj.items.forEach(item => {
                        const stock = getProductStockForAdj(item.product_id, adj.warehouse_id);
                        addAdjItemRow({
                            product_id: item.product_id,
                            unit_id: item.unit_id,
                            qty: parseFloat(item.quantity),
                            unit_cost: parseFloat(item.unit_cost),
                            available_stock: stock,
                            batch_number: item.batch_number || ''
                        });
                    });
                }

                toggleAdjEmptyState();
                calculateAdjTotals();
            });
    }

    function toggleAdjEmptyState() {
        const empty = document.getElementById('adj_items_empty');
        const rows = document.querySelectorAll('#adj_items_tbody tr');
        if (empty) {
            empty.style.display = rows.length === 0 ? 'block' : 'none';
        }
    }

    function onAdjustmentWarehouseChange() {
        const whId = document.getElementById('adj_warehouse_id').value;
        document.querySelectorAll('#adj_items_tbody tr').forEach(tr => {
            const idx = tr.id.replace('adj_row_', '');
            const prodId = tr.querySelector(`input[name="items[${idx}][product_id]"]`)?.value;
            if (prodId) {
                const stock = getProductStockForAdj(prodId, whId);
                const disp = document.getElementById(`adj_stock_disp_${idx}`);
                if (disp) disp.innerText = stock.toLocaleString('id-ID');
            }
        });
    }

    function getProductStockForAdj(productId, warehouseId) {
        const prod = adjAllProducts.find(p => p.id == productId);
        if (!prod || !prod.stocks) return 0;
        const st = prod.stocks.find(s => s.warehouse_id == warehouseId);
        return st ? parseFloat(st.quantity) : 0;
    }

    function openProductPickerModalAdj() {
        document.getElementById('adj_product_picker_search').value = '';
        renderAdjProductPickerList(adjAllProducts);
        openModal('adjProductPickerModal');
        setTimeout(() => document.getElementById('adj_product_picker_search').focus(), 100);
    }

    function filterAdjProductPickerList() {
        const query = (document.getElementById('adj_product_picker_search').value || '').toLowerCase();
        const filtered = adjAllProducts.filter(p => 
            (p.name && p.name.toLowerCase().includes(query)) ||
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.barcode && p.barcode.toLowerCase().includes(query))
        );
        renderAdjProductPickerList(filtered);
    }

    function renderAdjProductPickerList(list) {
        const container = document.getElementById('adj_product_picker_container');
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

        const whId = document.getElementById('adj_warehouse_id').value;

        container.innerHTML = list.map(p => {
            const currentStock = getProductStockForAdj(p.id, whId);
            return `
                <div onclick="addProductToAdjList(${p.id})" class="p-3.5 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/30 transition cursor-pointer flex items-center justify-between group">
                    <div class="min-w-0 pr-2">
                        <div class="font-bold text-xs text-slate-800 group-hover:text-brand-600 transition truncate">${p.name}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                            <span class="font-mono text-[10px] text-brand-600 font-bold">${p.code}</span>
                            <span>•</span>
                            <span>Stok: <strong class="text-slate-700">${currentStock.toLocaleString('id-ID')} ${p.base_unit ? p.base_unit.name : 'Unit'}</strong></span>
                        </div>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 group-hover:bg-brand-500 group-hover:text-white flex items-center justify-center transition shrink-0">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </div>
                </div>
            `;
        }).join('');

        lucide.createIcons();
    }

    function addProductToAdjList(productId) {
        const whId = document.getElementById('adj_warehouse_id').value;
        const product = adjAllProducts.find(p => p.id === productId);
        if (!product) return;

        const currentStock = getProductStockForAdj(product.id, whId);

        addAdjItemRow({
            product_id: product.id,
            unit_id: product.base_unit_id,
            qty: 1,
            unit_cost: parseFloat(product.purchase_price || 0),
            available_stock: currentStock,
            batch_number: ''
        });

        closeModal('adjProductPickerModal');
    }

    function addAdjItemRow(data) {
        const tbody = document.getElementById('adj_items_tbody');
        const idx = adjRowIndex++;
        const product = adjAllProducts.find(p => p.id == data.product_id);
        if (!product) return;

        // Populate unit options
        let unitOptions = `<option value="${product.base_unit_id}" selected>${product.base_unit ? product.base_unit.name : 'Pcs'}</option>`;
        if (product.conversions) {
            product.conversions.forEach(c => {
                if (c.from_unit) {
                    unitOptions += `<option value="${c.from_unit_id}">${c.from_unit.name}</option>`;
                }
            });
        }

        const tr = document.createElement('tr');
        tr.id = `adj_row_${idx}`;
        tr.className = 'hover:bg-slate-50/70 transition border-b border-slate-100';
        tr.innerHTML = `
            <td class="py-3 px-4">
                <input type="hidden" name="items[${idx}][product_id]" value="${product.id}">
                <div class="font-bold text-xs text-slate-800">${product.name}</div>
                <div class="text-[10px] text-slate-400 font-mono">${product.code}</div>
            </td>
            <td class="py-3 px-3 text-right font-mono-num font-bold text-slate-600" id="adj_stock_disp_${idx}">
                ${data.available_stock.toLocaleString('id-ID')}
            </td>
            <td class="py-3 px-3">
                <select name="items[${idx}][unit_id]" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-brand-600 focus:bg-white focus:outline-none focus:border-brand-500 cursor-pointer">
                    ${unitOptions}
                </select>
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0.0001" name="items[${idx}][quantity]" value="${data.qty}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3 text-right">
                <input type="number" step="any" min="0" name="items[${idx}][unit_cost]" value="${data.unit_cost}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-right text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3">
                <input type="text" name="items[${idx}][batch_number]" placeholder="Batch #" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 font-mono focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removeAdjItemRow(${idx})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Hapus Baris">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        toggleAdjEmptyState();
        calculateAdjTotals();
        lucide.createIcons();
    }

    function removeAdjItemRow(idx) {
        const el = document.getElementById(`adj_row_${idx}`);
        if (el) el.remove();
        toggleAdjEmptyState();
        calculateAdjTotals();
    }

    function calculateAdjTotals() {
        const count = document.querySelectorAll('#adj_items_tbody tr').length;
        document.getElementById('adj_summary_total').innerText = `${count} Jenis Produk`;
    }
</script>
