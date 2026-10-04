<!-- MODAL WIZARD CREATE CONSIGNMENT SETTLEMENT -->
<div id="createSettlementModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-4xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/60 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-500/20">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 tracking-tight">Wizard Settlement & Rekonsiliasi Konsinyasi</h3>
                    <p class="text-[11px] text-slate-400">Tarik otomatis data penjualan produk titipan kasir & hitung hak bagi hasil supplier</p>
                </div>
            </div>
            <button onclick="closeCreateSettlementModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('consignments.settlements.store') }}" method="POST" id="createSettlementForm" class="flex-1 overflow-y-auto p-6 space-y-4">
            @csrf

            <!-- Step 1: Filter Parameters -->
            <div class="p-4 rounded-xl bg-purple-50/40 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/40 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Pilih Supplier Penitip <span class="text-rose-500">*</span>
                        </label>
                        <select name="supplier_id" id="settlement_supplier_id" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500">
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Dari Tanggal <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="start_date" id="settlement_start_date" value="{{ date('Y-m-01') }}" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Sampai Tanggal <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="end_date" id="settlement_end_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="button" onclick="fetchUnsettledSales()" id="btn_fetch_sales" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="fetch_sales_icon"></i>
                        <span>Tarik Data Penjualan</span>
                    </button>
                </div>
            </div>

            <!-- Loading Spinner Placeholder -->
            <div id="settlement_loading" class="hidden py-8 text-center text-slate-400">
                <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin text-purple-600"></i>
                <p class="text-xs font-bold">Sedang mengkalkulasi transaksi penjualan konsinyasi...</p>
            </div>

            <!-- Empty / Initial Placeholder -->
            <div id="settlement_empty" class="py-8 text-center text-slate-400 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                <i data-lucide="calculator" class="w-8 h-8 mx-auto mb-1 text-slate-300"></i>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Pilih supplier dan klik tombol "Tarik Data Penjualan" di atas.</p>
            </div>

            <!-- Calculation Results Section -->
            <div id="settlement_results" class="hidden space-y-4">
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                <th class="p-2.5">Produk Titipan</th>
                                <th class="p-2.5 w-24 text-center">Satuan</th>
                                <th class="p-2.5 w-24 text-center">Laku Terjual</th>
                                <th class="p-2.5 w-28 text-right">Harga Jual</th>
                                <th class="p-2.5 w-28 text-right">Harga Setor (HPP)</th>
                                <th class="p-2.5 w-32 text-right">Hak Supplier</th>
                                <th class="p-2.5 w-28 text-right">Laba Toko</th>
                            </tr>
                        </thead>
                        <tbody id="settlement_items_body" class="divide-y divide-slate-100 dark:divide-slate-800">
                            <!-- Populated via AJAX -->
                        </tbody>
                        <tfoot>
                            <tr class="bg-purple-50 dark:bg-purple-900/20 font-black text-slate-900 dark:text-slate-100">
                                <td colspan="2" class="p-2.5 text-right">TOTAL:</td>
                                <td id="res_total_qty" class="p-2.5 text-center">0</td>
                                <td id="res_total_gross" class="p-2.5 text-right">Rp 0</td>
                                <td></td>
                                <td id="res_total_supplier" class="p-2.5 text-right text-rose-600 dark:text-rose-400 font-mono">Rp 0</td>
                                <td id="res_total_commission" class="p-2.5 text-right text-emerald-600 dark:text-emerald-400 font-mono">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Instant Payment Option -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Lunasi Pembayaran Langsung Sekarang?</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="pay_now" id="settlement_pay_now" value="1" onchange="toggleSettlementPaymentFields()" class="sr-only peer">
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <!-- Payment Details (Toggleable) -->
                    <div id="settlement_payment_fields" class="hidden pt-3 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1">Pilih Akun Kas / Bank <span class="text-rose-500">*</span></label>
                            <select name="payment_account_id" id="settlement_payment_account_id" class="w-full text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1">Metode Pembayaran</label>
                            <select name="payment_method" class="w-full text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2">
                                <option value="cash">Tunai / Kasir</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-1">Tanggal Bayar</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCreateSettlementModal()" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" id="btn_submit_settlement" disabled class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs shadow-md shadow-purple-500/25 transition flex items-center gap-2">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                    <span>Approve & Buat Settlement</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openCreateSettlementModal() {
        document.getElementById('createSettlementModal').classList.remove('hidden');
        document.getElementById('createSettlementModal').classList.add('flex');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeCreateSettlementModal() {
        document.getElementById('createSettlementModal').classList.add('hidden');
        document.getElementById('createSettlementModal').classList.remove('flex');
    }

    function toggleSettlementPaymentFields() {
        const isChecked = document.getElementById('settlement_pay_now').checked;
        const container = document.getElementById('settlement_payment_fields');
        if (container) {
            if (isChecked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    }

    async function fetchUnsettledSales() {
        const suppId = document.getElementById('settlement_supplier_id').value;
        const startDate = document.getElementById('settlement_start_date').value;
        const endDate = document.getElementById('settlement_end_date').value;

        if (!suppId) {
            alert('Silakan pilih supplier penitip terlebih dahulu.');
            return;
        }

        const loading = document.getElementById('settlement_loading');
        const empty = document.getElementById('settlement_empty');
        const results = document.getElementById('settlement_results');
        const submitBtn = document.getElementById('btn_submit_settlement');

        loading.classList.remove('hidden');
        empty.classList.add('hidden');
        results.classList.add('hidden');

        try {
            const response = await fetch('{{ route("consignments.settlements.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    supplier_id: suppId,
                    start_date: startDate,
                    end_date: endDate
                })
            });

            const res = await response.json();
            loading.classList.add('hidden');

            if (!response.ok || res.status !== 'success') {
                alert(res.message || 'Gagal menarik data penjualan.');
                empty.classList.remove('hidden');
                submitBtn.disabled = true;
                return;
            }

            const data = res.data;
            if (!data.items || data.items.length === 0) {
                empty.innerHTML = `
                    <i data-lucide="info" class="w-8 h-8 mx-auto mb-1 text-amber-500"></i>
                    <p class="text-xs font-bold text-amber-600">Tidak ada transaksi penjualan konsinyasi yang belum diselesaikan pada periode ini.</p>
                `;
                empty.classList.remove('hidden');
                submitBtn.disabled = true;
                if (window.lucide) lucide.createIcons();
                return;
            }

            // Render Items
            const tbody = document.getElementById('settlement_items_body');
            tbody.innerHTML = '';

            data.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-800/40';
                tr.innerHTML = `
                    <td class="p-2.5">
                        <div class="font-bold text-slate-800 dark:text-slate-200">${item.product_name}</div>
                        <div class="text-[10px] text-slate-400 font-mono">${item.product_code}</div>
                    </td>
                    <td class="p-2.5 text-center text-slate-600 dark:text-slate-300 font-semibold">${item.unit_name}</td>
                    <td class="p-2.5 text-center font-bold text-purple-700 dark:text-purple-300 font-mono">${Number(item.quantity_sold).toLocaleString('id-ID')}</td>
                    <td class="p-2.5 text-right font-mono text-slate-700 dark:text-slate-300">Rp ${Number(item.selling_price).toLocaleString('id-ID')}</td>
                    <td class="p-2.5 text-right font-mono text-slate-700 dark:text-slate-300">Rp ${Number(item.consignment_cost).toLocaleString('id-ID')}</td>
                    <td class="p-2.5 text-right font-bold text-rose-600 dark:text-rose-400 font-mono">Rp ${Number(item.supplier_payable).toLocaleString('id-ID')}</td>
                    <td class="p-2.5 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp ${Number(item.store_commission).toLocaleString('id-ID')}</td>
                `;
                tbody.appendChild(tr);
            });

            // Update Summary
            document.getElementById('res_total_qty').textContent = Number(data.summary.total_sold_quantity).toLocaleString('id-ID');
            document.getElementById('res_total_gross').textContent = 'Rp ' + Number(data.summary.total_gross_sales).toLocaleString('id-ID');
            document.getElementById('res_total_supplier').textContent = 'Rp ' + Number(data.summary.total_supplier_amount).toLocaleString('id-ID');
            document.getElementById('res_total_commission').textContent = 'Rp ' + Number(data.summary.total_store_commission).toLocaleString('id-ID');

            results.classList.remove('hidden');
            submitBtn.disabled = false;

        } catch (e) {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
            alert('Terjadi kesalahan jaringan: ' + e.message);
        }
    }
</script>
