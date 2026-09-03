<!-- CREATE / EDIT STOCK OPNAME MODAL -->
<div id="createOpnameModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-5xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[94vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="opnameModalTitle" class="font-extrabold text-base text-slate-900 tracking-tight">Buat Dokumen Stok Opname Baru</h3>
                    <p class="text-xs text-slate-400">Audit fisik inventaris gudang dan rekonsiliasi selisih stok sistem</p>
                </div>
            </div>
            <button onclick="closeModal('createOpnameModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createOpnameForm" action="{{ route('stock-opnames.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <div id="opname_method_field"></div>
            
            <div class="flex-1 p-6 overflow-y-auto space-y-6">

                <!-- Main Form Header: 4 Columns Clean Flat Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end pb-1 border-b border-slate-100">
                    
                    <!-- Warehouse -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Lokasi Gudang Audit <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_id" id="opname_warehouse_id" onchange="onOpnameWarehouseChange(this.value)" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ $w->is_default ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Opname Date -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Tanggal Opname <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <input type="text" name="opname_date" id="opname_date_input" required value="{{ date('Y-m-d') }}" placeholder="Pilih tanggal" class="flatpickr-date w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl pl-3.5 pr-9 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute right-3 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Status Dokumen <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="opname_status_input" required class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:outline-none cursor-pointer shadow-2xs h-10">
                            <option value="draft">Draft (Disimpan Sementara)</option>
                            <option value="in_progress">Dalam Proses Audit (In Progress)</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">
                            Catatan / Keterangan
                        </label>
                        <input type="text" name="notes" id="opname_notes_input" placeholder="Contoh: Opname bulanan akhir September..." class="w-full bg-white border border-slate-200 hover:border-slate-300 focus:border-brand-500 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:outline-none shadow-2xs h-10">
                    </div>
                </div>

                <!-- Products Table Card -->
                <div class="border border-slate-200/90 rounded-2xl bg-white overflow-hidden shadow-2xs">
                    
                    <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="boxes" class="w-4 h-4 text-brand-600"></i>
                            <h4 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider">Item Produk yang Di-Opname</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="addAllWarehouseProducts()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer border border-slate-200">
                                Muat Semua Produk Gudang
                            </button>
                            <button type="button" onclick="openProductPickerModalOpname()" class="px-3.5 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-xs flex items-center gap-1.5 transition cursor-pointer border border-brand-200 shadow-2xs">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Pilih Produk Manual</span>
                            </button>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100/60 text-slate-700 font-extrabold text-[10px] uppercase border-b border-slate-200 tracking-wider">
                                    <th class="py-3 px-4 min-w-[220px]">Informasi Produk</th>
                                    <th class="py-3 px-3 w-28 text-right">Stok Sistem</th>
                                    <th class="py-3 px-3 w-32 text-center">Fisik Riil</th>
                                    <th class="py-3 px-3 w-28 text-right">Selisih Qty</th>
                                    <th class="py-3 px-4 w-44">Keterangan / Alasan Selisih</th>
                                    <th class="py-3 px-3 w-12 text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="opname_items_tbody" class="divide-y divide-slate-100">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State in Table -->
                    <div id="opname_items_empty" class="p-8 text-center text-slate-400">
                        <i data-lucide="package-search" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                        <p class="font-bold text-xs text-slate-600">Belum Ada Produk yang Dimasukkan</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Klik <span class="text-brand-600 font-bold">"Muat Semua Produk Gudang"</span> atau pilih item manual di atas.</p>
                    </div>
                </div>

                <!-- Summary Bar -->
                <div class="bg-slate-50/80 border border-slate-200/90 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-4 shadow-2xs text-xs font-semibold">
                    <div class="flex items-center gap-6">
                        <div>
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Total Produk:</span>
                            <span id="opname_total_items_count" class="font-bold text-slate-800">0 Produk</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Item Selisih Fisik:</span>
                            <span id="opname_diff_items_count" class="font-bold text-amber-600">0 Item</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Estimasi Nilai Selisih:</span>
                        <span id="opname_diff_value_total" class="text-base font-black font-mono-num text-slate-900">Rp 0</span>
                    </div>
                </div>

            </div>

            <!-- Modal Action Buttons -->
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/70">
                <button type="button" onclick="closeModal('createOpnameModal')" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Batal</button>
                <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Dokumen Opname</span>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Include Shared Product Picker Modal for Opname -->
<div id="opnameProductPickerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="package-search" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Katalog Produk Opname</h3>
                    <p class="text-[11px] text-slate-400">Pilih produk untuk ditambahkan ke daftar audit opname</p>
                </div>
            </div>
            <button onclick="closeModal('opnameProductPickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 px-3.5 py-2 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="opname_product_picker_search" 
                    oninput="filterOpnameProductPickerList()" 
                    placeholder="Ketik nama produk, SKU kode, atau barcode..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                >
            </div>
        </div>

        <div class="flex-1 p-4 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="opname_product_picker_container">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<script>
    const opnameAllProducts = @json($products);
    let opnameRowIndex = 0;

    function openCreateOpnameModal() {
        document.getElementById('createOpnameForm').reset();
        document.getElementById('createOpnameForm').action = "{{ route('stock-opnames.store') }}";
        document.getElementById('opname_method_field').innerHTML = '';
        document.getElementById('opnameModalTitle').innerText = 'Buat Dokumen Stok Opname Baru';
        document.getElementById('opname_items_tbody').innerHTML = '';
        
        opnameRowIndex = 0;
        toggleOpnameEmptyState();
        calculateOpnameTotals();
        openModal('createOpnameModal');

        if (window.flatpickr) {
            flatpickr("#opname_date_input", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: new Date(),
                locale: "id"
            });
        }
    }

    function openEditOpnameModal(opnameId) {
        openCreateOpnameModal();
        document.getElementById('opnameModalTitle').innerText = 'Edit Dokumen Stok Opname';
        document.getElementById('createOpnameForm').action = `/stock-opnames/${opnameId}`;
        document.getElementById('opname_method_field').innerHTML = '@method("PUT")';

        fetch(`/stock-opnames/${opnameId}`)
            .then(res => res.json())
            .then(opname => {
                document.getElementById('opname_warehouse_id').value = opname.warehouse_id;
                document.getElementById('opname_date_input').value = opname.opname_date ? opname.opname_date.split('T')[0] : '';
                document.getElementById('opname_status_input').value = opname.status;
                document.getElementById('opname_notes_input').value = opname.notes || '';

                const tbody = document.getElementById('opname_items_tbody');
                tbody.innerHTML = '';
                opnameRowIndex = 0;

                if (opname.items && opname.items.length > 0) {
                    opname.items.forEach(item => {
                        addOpnameItemRow({
                            product_id: item.product_id,
                            system_qty: parseFloat(item.system_qty),
                            physical_qty: parseFloat(item.physical_qty),
                            unit_cost: parseFloat(item.unit_cost),
                            reason: item.reason || ''
                        });
                    });
                }

                toggleOpnameEmptyState();
                calculateOpnameTotals();
            });
    }

    function onOpnameWarehouseChange(warehouseId) {
        // Recalculate system stock for all existing rows based on selected warehouse
        document.querySelectorAll('#opname_items_tbody tr').forEach(tr => {
            const idx = tr.id.replace('opname_row_', '');
            const prodId = tr.querySelector(`input[name="items[${idx}][product_id]"]`)?.value;
            if (prodId) {
                const sysQty = getProductSystemStock(prodId, warehouseId);
                const sysInput = document.getElementById(`opname_system_qty_${idx}`);
                const sysDisp = document.getElementById(`opname_system_qty_disp_${idx}`);
                if (sysInput && sysDisp) {
                    sysInput.value = sysQty;
                    sysDisp.innerText = sysQty.toLocaleString('id-ID');
                }
                calculateOpnameRow(idx);
            }
        });
    }

    function getProductSystemStock(productId, warehouseId) {
        const prod = opnameAllProducts.find(p => p.id == productId);
        if (!prod || !prod.stocks) return 0;
        const st = prod.stocks.find(s => s.warehouse_id == warehouseId);
        return st ? parseFloat(st.quantity) : 0;
    }

    function addAllWarehouseProducts() {
        const warehouseId = document.getElementById('opname_warehouse_id').value;
        const tbody = document.getElementById('opname_items_tbody');
        tbody.innerHTML = '';
        opnameRowIndex = 0;

        opnameAllProducts.forEach(prod => {
            const sysQty = getProductSystemStock(prod.id, warehouseId);
            addOpnameItemRow({
                product_id: prod.id,
                system_qty: sysQty,
                physical_qty: sysQty, // Default physical to match system for convenience
                unit_cost: parseFloat(prod.purchase_price || 0),
                reason: ''
            });
        });

        toggleOpnameEmptyState();
        calculateOpnameTotals();
    }

    function toggleOpnameEmptyState() {
        const empty = document.getElementById('opname_items_empty');
        const rows = document.querySelectorAll('#opname_items_tbody tr');
        if (empty) {
            empty.style.display = rows.length === 0 ? 'block' : 'none';
        }
    }

    function openProductPickerModalOpname() {
        document.getElementById('opname_product_picker_search').value = '';
        renderOpnameProductPickerList(opnameAllProducts);
        openModal('opnameProductPickerModal');
        setTimeout(() => document.getElementById('opname_product_picker_search').focus(), 100);
    }

    function filterOpnameProductPickerList() {
        const query = (document.getElementById('opname_product_picker_search').value || '').toLowerCase();
        const filtered = opnameAllProducts.filter(p => 
            (p.name && p.name.toLowerCase().includes(query)) ||
            (p.code && p.code.toLowerCase().includes(query)) ||
            (p.barcode && p.barcode.toLowerCase().includes(query))
        );
        renderOpnameProductPickerList(filtered);
    }

    function renderOpnameProductPickerList(list) {
        const container = document.getElementById('opname_product_picker_container');
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
            <div onclick="addProductToOpnameList(${p.id})" class="p-3.5 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/30 transition cursor-pointer flex items-center justify-between group">
                <div class="min-w-0 pr-2">
                    <div class="font-bold text-xs text-slate-800 group-hover:text-brand-600 transition truncate">${p.name}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                        <span class="font-mono text-[10px] text-brand-600 font-bold">${p.code}</span>
                        <span>•</span>
                        <span>Satuan: ${p.base_unit ? p.base_unit.name : 'Unit'}</span>
                    </div>
                </div>
                <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-600 group-hover:bg-brand-500 group-hover:text-white flex items-center justify-center transition shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </div>
            </div>
        `).join('');

        lucide.createIcons();
    }

    function addProductToOpnameList(productId) {
        const warehouseId = document.getElementById('opname_warehouse_id').value;
        const product = opnameAllProducts.find(p => p.id === productId);
        if (!product) return;

        // Check if already in list
        const existing = Array.from(document.querySelectorAll('#opname_items_tbody input[name$="[product_id]"]'))
            .some(input => input.value == productId);

        if (existing) {
            Swal.fire({
                icon: 'info',
                title: 'Produk Sudah Ada',
                text: 'Produk ini sudah ada di daftar audit opname.',
                confirmButtonColor: '#f97316'
            });
            return;
        }

        const sysQty = getProductSystemStock(product.id, warehouseId);
        addOpnameItemRow({
            product_id: product.id,
            system_qty: sysQty,
            physical_qty: sysQty,
            unit_cost: parseFloat(product.purchase_price || 0),
            reason: ''
        });

        closeModal('opnameProductPickerModal');
    }

    function addOpnameItemRow(data) {
        const tbody = document.getElementById('opname_items_tbody');
        const idx = opnameRowIndex++;
        const product = opnameAllProducts.find(p => p.id == data.product_id);
        if (!product) return;

        const tr = document.createElement('tr');
        tr.id = `opname_row_${idx}`;
        tr.className = 'hover:bg-slate-50/70 transition border-b border-slate-100';
        tr.innerHTML = `
            <td class="py-3 px-4">
                <input type="hidden" name="items[${idx}][product_id]" value="${product.id}">
                <input type="hidden" id="opname_unit_cost_${idx}" value="${data.unit_cost}">
                <input type="hidden" id="opname_system_qty_${idx}" value="${data.system_qty}">
                <div class="font-bold text-xs text-slate-800">${product.name}</div>
                <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1.5">
                    <span>${product.code}</span>
                    <span>•</span>
                    <span>${product.base_unit ? product.base_unit.name : 'Unit'}</span>
                </div>
            </td>
            <td class="py-3 px-3 text-right font-mono-num font-bold text-slate-600" id="opname_system_qty_disp_${idx}">
                ${data.system_qty.toLocaleString('id-ID')}
            </td>
            <td class="py-3 px-3">
                <input type="number" step="any" min="0" name="items[${idx}][physical_qty]" id="opname_physical_qty_${idx}" oninput="calculateOpnameRow(${idx})" value="${data.physical_qty}" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-center text-xs font-bold text-slate-900 font-mono-num focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3 text-right font-mono-num font-black" id="opname_diff_qty_disp_${idx}">
                0.00
            </td>
            <td class="py-3 px-4">
                <input type="text" name="items[${idx}][reason]" value="${data.reason}" placeholder="Alasan selisih..." class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-brand-500">
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removeOpnameItemRow(${idx})" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Hapus Baris">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        calculateOpnameRow(idx);
        toggleOpnameEmptyState();
        lucide.createIcons();
    }

    function removeOpnameItemRow(idx) {
        const el = document.getElementById(`opname_row_${idx}`);
        if (el) el.remove();
        toggleOpnameEmptyState();
        calculateOpnameTotals();
    }

    function calculateOpnameRow(idx) {
        const sys = parseFloat(document.getElementById(`opname_system_qty_${idx}`)?.value) || 0;
        const phys = parseFloat(document.getElementById(`opname_physical_qty_${idx}`)?.value) || 0;
        const unitCost = parseFloat(document.getElementById(`opname_unit_cost_${idx}`)?.value) || 0;
        const diff = phys - sys;
        const diffVal = diff * unitCost;

        const disp = document.getElementById(`opname_diff_qty_disp_${idx}`);
        if (disp) {
            disp.innerText = (diff > 0 ? '+' : '') + diff.toFixed(2);
            disp.dataset.diffVal = diffVal;
            disp.dataset.hasDiff = diff !== 0 ? '1' : '0';

            if (diff > 0) {
                disp.className = 'py-3 px-3 text-right font-mono-num font-black text-emerald-600';
            } else if (diff < 0) {
                disp.className = 'py-3 px-3 text-right font-mono-num font-black text-rose-600';
            } else {
                disp.className = 'py-3 px-3 text-right font-mono-num font-bold text-slate-400';
            }
        }

        calculateOpnameTotals();
    }

    function calculateOpnameTotals() {
        let totalItems = 0;
        let diffItems = 0;
        let totalDiffValue = 0;

        document.querySelectorAll('#opname_items_tbody tr').forEach(tr => {
            totalItems++;
            const idx = tr.id.replace('opname_row_', '');
            const disp = document.getElementById(`opname_diff_qty_disp_${idx}`);
            if (disp) {
                if (disp.dataset.hasDiff === '1') diffItems++;
                totalDiffValue += parseFloat(disp.dataset.diffVal) || 0;
            }
        });

        document.getElementById('opname_total_items_count').innerText = `${totalItems} Produk`;
        document.getElementById('opname_diff_items_count').innerText = `${diffItems} Item`;
        
        const valEl = document.getElementById('opname_diff_value_total');
        if (valEl) {
            valEl.innerText = (totalDiffValue < 0 ? '-Rp ' : 'Rp ') + parseInt(Math.abs(totalDiffValue)).toLocaleString('id-ID');
            valEl.className = 'text-base font-black font-mono-num ' + (totalDiffValue < 0 ? 'text-rose-600' : (totalDiffValue > 0 ? 'text-emerald-600' : 'text-slate-900'));
        }
    }
</script>
