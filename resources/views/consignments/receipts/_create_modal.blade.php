<!-- MODAL CREATE CONSIGNMENT RECEIPT -->
<div id="createReceiptModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/60 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-500/20">
                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 tracking-tight">Catat Penerimaan Barang Konsinyasi</h3>
                    <p class="text-[11px] text-slate-400">Pencatatan produk titip jual dari supplier mitra UMKM ke stok toko</p>
                </div>
            </div>
            <button onclick="closeCreateReceiptModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('consignments.receipts.store') }}" method="POST" id="createReceiptForm" class="flex-1 overflow-y-auto p-6 space-y-4">
            @csrf

            <!-- Form Top Header Info -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Supplier Penitip <span class="text-rose-500">*</span>
                    </label>
                    <select name="supplier_id" id="receipt_supplier_id" required onchange="filterProductsBySupplier()" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Pilih Supplier Penitip</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Gudang Penerima <span class="text-rose-500">*</span>
                    </label>
                    <select name="warehouse_id" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $wh->is_default ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Tanggal Penerimaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan / No. Surat Jalan Supplier</label>
                <input type="text" name="notes" placeholder="Contoh: Titipan snack batch 1, Surat Jalan No. 123" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <!-- Items Table -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold uppercase text-slate-600 dark:text-slate-400">Daftar Barang Titipan</h4>
                    <button type="button" onclick="addReceiptItemRow()" class="px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Item</span>
                    </button>
                </div>

                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                <th class="p-2.5">Produk Titipan</th>
                                <th class="p-2.5 w-32">Satuan</th>
                                <th class="p-2.5 w-24">Jumlah (Qty)</th>
                                <th class="p-2.5 w-32">Harga Setor (HPP)</th>
                                <th class="p-2.5 w-32 text-right">Subtotal</th>
                                <th class="p-2.5 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="receipt_items_body" class="divide-y divide-slate-100 dark:divide-slate-800">
                            <!-- Dynamic Item Rows -->
                        </tbody>
                        <tfoot>
                            <tr class="bg-purple-50/50 dark:bg-purple-900/20 font-bold">
                                <td colspan="2" class="p-2.5 text-right text-slate-700 dark:text-slate-300">Total Estimasi:</td>
                                <td id="receipt_total_qty" class="p-2.5 text-purple-700 dark:text-purple-300">0</td>
                                <td></td>
                                <td id="receipt_total_subtotal" class="p-2.5 text-right font-black text-purple-700 dark:text-purple-300">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCreateReceiptModal()" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Penerimaan</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    const allConsignmentProducts = @json($products);
    let receiptRowIndex = 0;

    function openCreateReceiptModal() {
        document.getElementById('createReceiptModal').classList.remove('hidden');
        document.getElementById('createReceiptModal').classList.add('flex');
        
        const tbody = document.getElementById('receipt_items_body');
        if (tbody.children.length === 0) {
            addReceiptItemRow();
        }
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeCreateReceiptModal() {
        document.getElementById('createReceiptModal').classList.add('hidden');
        document.getElementById('createReceiptModal').classList.remove('flex');
    }

    function filterProductsBySupplier() {
        const suppId = document.getElementById('receipt_supplier_id').value;
        const selects = document.querySelectorAll('.receipt-product-select');
        selects.forEach(select => {
            const currentVal = select.value;
            select.innerHTML = '<option value="">Pilih Produk</option>';
            allConsignmentProducts.forEach(prod => {
                if (!suppId || !prod.consignment_supplier_id || prod.consignment_supplier_id == suppId) {
                    const opt = document.createElement('option');
                    opt.value = prod.id;
                    opt.textContent = `${prod.name} (${prod.code})`;
                    opt.dataset.unitId = prod.base_unit_id;
                    opt.dataset.unitName = prod.base_unit ? prod.base_unit.name : 'Pcs';
                    opt.dataset.cost = prod.consignment_rate > 0 ? prod.consignment_rate : prod.purchase_price;
                    select.appendChild(opt);
                }
            });
            select.value = currentVal;
        });
    }

    function addReceiptItemRow() {
        const tbody = document.getElementById('receipt_items_body');
        const idx = receiptRowIndex++;
        const suppId = document.getElementById('receipt_supplier_id')?.value || '';

        const tr = document.createElement('tr');
        tr.id = `receipt_row_${idx}`;
        tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-800/40';

        let productOptions = '<option value="">Pilih Produk</option>';
        allConsignmentProducts.forEach(prod => {
            if (!suppId || !prod.consignment_supplier_id || prod.consignment_supplier_id == suppId) {
                const cost = prod.consignment_rate > 0 ? prod.consignment_rate : prod.purchase_price;
                const unitName = prod.base_unit ? prod.base_unit.name : 'Pcs';
                productOptions += `<option value="${prod.id}" data-unit-id="${prod.base_unit_id}" data-unit-name="${unitName}" data-cost="${cost}">${prod.name} (${prod.code})</option>`;
            }
        });

        tr.innerHTML = `
            <td class="p-2">
                <select name="items[${idx}][product_id]" required onchange="handleReceiptProductChange(${idx}, this)" class="receipt-product-select w-full text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 focus:ring-purple-500">
                    ${productOptions}
                </select>
            </td>
            <td class="p-2">
                <input type="hidden" name="items[${idx}][unit_id]" id="receipt_unit_id_${idx}">
                <input type="text" id="receipt_unit_name_${idx}" readonly class="w-full text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-2 font-semibold text-slate-600 dark:text-slate-300">
            </td>
            <td class="p-2">
                <input type="number" step="any" name="items[${idx}][quantity]" id="receipt_qty_${idx}" value="1" required oninput="calculateReceiptRow(${idx})" class="w-full text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 focus:ring-purple-500">
            </td>
            <td class="p-2">
                <input type="number" step="any" name="items[${idx}][consignment_cost]" id="receipt_cost_${idx}" value="0" required oninput="calculateReceiptRow(${idx})" class="w-full text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2 focus:ring-purple-500">
            </td>
            <td class="p-2 text-right">
                <span id="receipt_subtotal_${idx}" class="font-bold text-slate-800 dark:text-slate-200">Rp 0</span>
            </td>
            <td class="p-2 text-center">
                <button type="button" onclick="removeReceiptRow(${idx})" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function handleReceiptProductChange(idx, select) {
        const selected = select.options[select.selectedIndex];
        if (selected && selected.value) {
            document.getElementById(`receipt_unit_id_${idx}`).value = selected.dataset.unitId || '';
            document.getElementById(`receipt_unit_name_${idx}`).value = selected.dataset.unitName || 'Pcs';
            document.getElementById(`receipt_cost_${idx}`).value = selected.dataset.cost || '0';
            calculateReceiptRow(idx);
        }
    }

    function calculateReceiptRow(idx) {
        const qty = parseFloat(document.getElementById(`receipt_qty_${idx}`)?.value || 0);
        const cost = parseFloat(document.getElementById(`receipt_cost_${idx}`)?.value || 0);
        const subtotal = qty * cost;
        const span = document.getElementById(`receipt_subtotal_${idx}`);
        if (span) {
            span.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        }
        calculateReceiptGrandTotal();
    }

    function calculateReceiptGrandTotal() {
        let totalQty = 0;
        let totalSubtotal = 0;
        const rows = document.querySelectorAll('#receipt_items_body tr');
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            const costInput = row.querySelector('input[name*="[consignment_cost]"]');
            if (qtyInput && costInput) {
                const q = parseFloat(qtyInput.value || 0);
                const c = parseFloat(costInput.value || 0);
                totalQty += q;
                totalSubtotal += (q * c);
            }
        });
        document.getElementById('receipt_total_qty').textContent = totalQty.toLocaleString('id-ID');
        document.getElementById('receipt_total_subtotal').textContent = 'Rp ' + totalSubtotal.toLocaleString('id-ID');
    }

    function removeReceiptRow(idx) {
        const row = document.getElementById(`receipt_row_${idx}`);
        if (row) {
            row.remove();
            calculateReceiptGrandTotal();
        }
    }
</script>
