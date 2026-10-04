<!-- MODAL CREATE CONSIGNMENT RETURN -->
<div id="createReturnModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/60 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-500/20">
                    <i data-lucide="undo-2" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 tracking-tight">Form Retur Barang Konsinyasi</h3>
                    <p class="text-[11px] text-slate-400">Pengembalian fisik sisa stok produk titip jual kepada supplier</p>
                </div>
            </div>
            <button onclick="closeCreateReturnModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('consignments.returns.store') }}" method="POST" id="createReturnForm" class="flex-1 overflow-y-auto p-6 space-y-4">
            @csrf

            <!-- Form Top Header Info -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Supplier Penitip <span class="text-rose-500">*</span>
                    </label>
                    <select name="supplier_id" id="return_supplier_id" required onchange="filterReturnProductsBySupplier()" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Pilih Supplier Penitip</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Gudang Pengirim <span class="text-rose-500">*</span>
                    </label>
                    <select name="warehouse_id" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $wh->is_default ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Tanggal Retur <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan / Alasan Retur Umum</label>
                <input type="text" name="notes" placeholder="Contoh: Pengembalian sisa produk tidak laku / expired" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <!-- Items Table -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold uppercase text-slate-600 dark:text-slate-400">Daftar Barang yang Diretur</h4>
                    <button type="button" onclick="addReturnItemRow()" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Item</span>
                    </button>
                </div>

                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                <th class="p-2.5">Produk Titipan</th>
                                <th class="p-2.5 w-28">Satuan</th>
                                <th class="p-2.5 w-24">Jumlah Retur</th>
                                <th class="p-2.5 w-48">Alasan Retur</th>
                                <th class="p-2.5 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="return_items_body" class="divide-y divide-slate-100 dark:divide-slate-800">
                            <!-- Dynamic Rows -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCreateReturnModal()" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Proses Retur</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    const allReturnConsignmentProducts = @json($products);
    let returnRowIndex = 0;

    function openCreateReturnModal() {
        document.getElementById('createReturnModal').classList.remove('hidden');
        document.getElementById('createReturnModal').classList.add('flex');
        
        const tbody = document.getElementById('return_items_body');
        if (tbody.children.length === 0) {
            addReturnItemRow();
        }
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeCreateReturnModal() {
        document.getElementById('createReturnModal').classList.add('hidden');
        document.getElementById('createReturnModal').classList.remove('flex');
    }

    function filterReturnProductsBySupplier() {
        const suppId = document.getElementById('return_supplier_id').value;
        const selects = document.querySelectorAll('.return-product-select');
        selects.forEach(select => {
            const currentVal = select.value;
            select.innerHTML = '<option value="">Pilih Produk</option>';
            allReturnConsignmentProducts.forEach(prod => {
                if (!suppId || !prod.consignment_supplier_id || prod.consignment_supplier_id == suppId) {
                    const opt = document.createElement('option');
                    opt.value = prod.id;
                    opt.textContent = `${prod.name} (${prod.code}) - Stok: ${prod.total_stock}`;
                    opt.dataset.unitId = prod.base_unit_id;
                    opt.dataset.unitName = prod.base_unit ? prod.base_unit.name : 'Pcs';
                    select.appendChild(opt);
                }
            });
            select.value = currentVal;
        });
    }

    function addReturnItemRow() {
        const tbody = document.getElementById('return_items_body');
        const idx = returnRowIndex++;
        const suppId = document.getElementById('return_supplier_id')?.value || '';

        const tr = document.createElement('tr');
        tr.id = `return_row_${idx}`;
        tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-800/40';

        let productOptions = '<option value="">Pilih Produk</option>';
        allReturnConsignmentProducts.forEach(prod => {
            if (!suppId || !prod.consignment_supplier_id || prod.consignment_supplier_id == suppId) {
                const unitName = prod.base_unit ? prod.base_unit.name : 'Pcs';
                productOptions += `<option value="${prod.id}" data-unit-id="${prod.base_unit_id}" data-unit-name="${unitName}">${prod.name} (${prod.code}) - Stok: ${prod.total_stock}</option>`;
            }
        });

        tr.innerHTML = `
            <td class="p-2">
                <select name="items[${idx}][product_id]" required onchange="handleReturnProductChange(${idx}, this)" class="return-product-select w-full text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 focus:ring-purple-500">
                    ${productOptions}
                </select>
            </td>
            <td class="p-2">
                <input type="hidden" name="items[${idx}][unit_id]" id="return_unit_id_${idx}">
                <input type="text" id="return_unit_name_${idx}" readonly class="w-full text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2 font-semibold text-slate-600 dark:text-slate-300">
            </td>
            <td class="p-2">
                <input type="number" step="any" name="items[${idx}][quantity]" id="return_qty_${idx}" value="1" required class="w-full text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 focus:ring-purple-500">
            </td>
            <td class="p-2">
                <input type="text" name="items[${idx}][reason]" placeholder="Sisa tidak laku / Expired" class="w-full text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 focus:ring-purple-500">
            </td>
            <td class="p-2 text-center">
                <button type="button" onclick="removeReturnRow(${idx})" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function handleReturnProductChange(idx, select) {
        const selected = select.options[select.selectedIndex];
        if (selected && selected.value) {
            document.getElementById(`return_unit_id_${idx}`).value = selected.dataset.unitId || '';
            document.getElementById(`return_unit_name_${idx}`).value = selected.dataset.unitName || 'Pcs';
        }
    }

    function removeReturnRow(idx) {
        const row = document.getElementById(`return_row_${idx}`);
        if (row) {
            row.remove();
        }
    }
</script>
