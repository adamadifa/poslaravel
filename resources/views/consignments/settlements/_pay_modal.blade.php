<!-- MODAL PAY CONSIGNMENT SETTLEMENT -->
<div id="paySettlementModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/60 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-500/20">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 tracking-tight">Pelunasan Settlement</h3>
                    <p class="text-[11px] text-slate-400" id="pay_modal_subtitle">Bayar bagi hasil konsinyasi</p>
                </div>
            </div>
            <button onclick="closePaySettlementModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="paySettlementForm" action="#" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Amount Card -->
            <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 text-center">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold block">Total Hak Bagi Hasil Supplier</span>
                <span id="pay_modal_amount" class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono-num">
                    Rp 0
                </span>
                <span id="pay_modal_supplier" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mt-1"></span>
            </div>

            <!-- Select Financial Account -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Bayar dari Akun Kas / Bank <span class="text-rose-500">*</span>
                </label>
                <select name="account_id" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-emerald-500">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Method & Date -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Metode Bayar</label>
                    <select name="payment_method" class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-emerald-500">
                        <option value="cash">Tunai / Kasir</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="check">Cek / Giro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal Bayar</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Reference Number -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">No. Bukti Transfer / Referensi (Opsional)</label>
                <input type="text" name="reference_number" placeholder="Contoh: TRF-BCA-982341" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closePaySettlementModal()" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Konfirmasi Pelunasan</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openPaySettlementModal(id, number, supplierName, amount) {
        const form = document.getElementById('paySettlementForm');
        form.action = `/consignments/settlements/${id}/pay`;
        document.getElementById('pay_modal_subtitle').textContent = `Faktur ${number}`;
        document.getElementById('pay_modal_supplier').textContent = `Penerima: ${supplierName}`;
        document.getElementById('pay_modal_amount').textContent = 'Rp ' + Number(amount).toLocaleString('id-ID');

        document.getElementById('paySettlementModal').classList.remove('hidden');
        document.getElementById('paySettlementModal').classList.add('flex');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closePaySettlementModal() {
        document.getElementById('paySettlementModal').classList.add('hidden');
        document.getElementById('paySettlementModal').classList.remove('flex');
    }
</script>
