<!-- ========================================================================= -->
<!-- MODALS & WIDGET LAYANAN AGEN BANK (BRILINK) & PRODUK DIGITAL PPOB -->
<!-- ========================================================================= -->

<!-- 1. MODAL RINCIAN SEMUA SALDO AKUN (MULTI-ACCOUNT BALANCE POPOVER/MODAL) -->
<div id="agentBalancesModal" class="fixed inset-0 z-[120] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-blue-50/70 via-indigo-50/50 to-white shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm shadow-blue-600/30">
                    <i data-lucide="wallet-cards" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-black text-base text-slate-900 tracking-tight">Status Saldo Seluruh Rekening & PPOB</h3>
                    <p class="text-[11px] text-slate-500">Monitoring saldo kas laci, rekening bank agen, dan deposit server PPOB</p>
                </div>
            </div>
            <button onclick="closeModal('agentBalancesModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-5">
            <!-- Ringkasan Total Saldo Likuid -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between shadow-md">
                <div>
                    <span class="text-[11px] text-slate-300 font-semibold block uppercase tracking-wider">Total Seluruh Dana Digital & Kas</span>
                    <span class="text-2xl font-black font-mono-num" id="modal_total_liquid_balance">Rp 0</span>
                </div>
                <button onclick="fetchLatestAgentBalances(true)" title="Perbarui / Refresh Saldo" class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" id="refresh_balance_icon"></i>
                    <span>Refresh</span>
                </button>
            </div>

            <!-- Bagian Rekening Bank & EDC Agen -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>Rekening Bank & EDC Keagenan (BRILink / Mandiri / dll)</span>
                    </h4>
                    <span class="text-[11px] font-bold text-blue-600" id="badge_total_bank_agent">Rp 0</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" id="container_bank_agent_accounts">
                    <!-- Populated via JS -->
                </div>
            </div>

            <!-- Bagian Deposit Server PPOB & Pulsa -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="smartphone" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Deposit Server PPOB, Pulsa & Token PLN</span>
                    </h4>
                    <span class="text-[11px] font-bold text-emerald-600" id="badge_total_ppob_provider">Rp 0</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" id="container_ppob_accounts">
                    <!-- Populated via JS -->
                </div>
            </div>

            <!-- Bagian Kas Laci Fisik & Rekening Operasional Lainnya -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="banknote" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Kas Fisik Toko & Rekening Operasional</span>
                    </h4>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" id="container_other_accounts">
                    <!-- Populated via JS -->
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
            <span class="text-[11px] text-slate-400 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Saldo Tersinkron Real-time
            </span>
            <button type="button" onclick="closeModal('agentBalancesModal')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>


<!-- 2. MODAL TRANSAKSI UTAMA: LAYANAN AGEN PERBANKAN & PPOB -->
<div id="agentServiceModal" class="fixed inset-0 z-[115] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-sm shadow-blue-500/25">
                    <i data-lucide="repeat" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Layanan Agen Bank & Produk PPOB</h3>
                    <p class="text-[11px] text-slate-400">Pencatatan transfer bank, tarik tunai, dan penjualan pulsa/token PLN</p>
                </div>
            </div>
            <button onclick="closeModal('agentServiceModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- 3 Tab Layanan -->
        <div class="px-6 pt-3 bg-slate-50/50 border-b border-slate-200 flex items-center gap-2 overflow-x-auto shrink-0">
            <button type="button" onclick="switchAgentTab('transfer')" id="tabBtn_transfer" class="agent-nav-tab pb-2.5 px-3 text-xs font-bold border-b-2 border-blue-600 text-blue-600 flex items-center gap-1.5 transition">
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                <span>Transfer / Kirim Uang</span>
            </button>
            <button type="button" onclick="switchAgentTab('withdraw')" id="tabBtn_withdraw" class="agent-nav-tab pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition">
                <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                <span>Tarik Tunai Nasabah</span>
            </button>
            <button type="button" onclick="switchAgentTab('ppob')" id="tabBtn_ppob" class="agent-nav-tab pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition">
                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                <span>Pulsa & PPOB</span>
            </button>
            <button type="button" onclick="switchAgentTab('history')" id="tabBtn_history" class="agent-nav-tab pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition ml-auto">
                <i data-lucide="history" class="w-3.5 h-3.5"></i>
                <span>Riwayat Shift</span>
            </button>
        </div>

        <!-- Tab Body Container (Scrollable) -->
        <div class="p-6 overflow-y-auto space-y-4">

            <!-- ============================================================= -->
            <!-- TAB 1: FORM TRANSFER / KIRIM UANG -->
            <!-- ============================================================= -->
            <form id="formAgentTransfer" onsubmit="handleAgentTransferSubmit(event)" class="space-y-4">
                <!-- Info Banner Transaksi -->
                <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 flex items-start gap-2.5 text-xs text-blue-900">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold block">Alur Kas Transfer:</span>
                        <span>Kasir menerima uang fisik dari pelanggan (Pokok + Admin Toko). Saldo rekening toko berkurang sebesar pokok transfer via EDC/Bank.</span>
                    </div>
                </div>

                <!-- Pilih Rekening Pengirim Toko -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Rekening Toko Pengirim (Potong Saldo) <span class="text-rose-500">*</span>
                    </label>
                    <select id="transfer_account_id" onchange="onTransferAccountChanged()" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <!-- Populated by JS -->
                    </select>
                </div>
                <div id="transfer_account_balance_info" class="text-[11px] text-slate-500 px-1 font-medium flex items-center justify-between">
                    <span>Sisa Saldo Rekening Ini: <strong class="text-slate-900 font-mono-num" id="transfer_avail_balance_display">Rp 0</strong></span>
                    <span id="transfer_balance_alert_badge" class="hidden text-rose-600 font-bold">Saldo Terbatas!</span>
                </div>

                <!-- Rekening / Bank Tujuan Nasabah -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Bank & No. Rekening Tujuan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="transfer_destination_target" placeholder="Contoh: BRI 012901928392" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nama Penerima (Opsional)
                        </label>
                        <input type="text" id="transfer_destination_holder" placeholder="Nama pemilik rekening" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <!-- Nominal Transfer & Fee -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nominal Pokok <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-blue-600">Rp</span>
                            <input type="number" step="any" min="1000" id="transfer_principal_amount" oninput="calculateTransferTotal()" placeholder="0" required class="w-full bg-transparent border-0 p-0 text-sm font-black text-slate-900 focus:ring-0 focus:outline-none font-mono-num">
                        </div>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 bg-slate-100/70 transition px-4 pt-3 pb-2 shadow-2xs cursor-not-allowed">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-500 flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                            <span>Biaya Admin Toko (Terkunci)</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" step="any" min="0" id="transfer_admin_fee" value="{{ $agentTransferAdminFee ?? 5000 }}" readonly tabindex="-1" class="w-full bg-transparent border-0 p-0 text-sm font-black text-slate-700 focus:ring-0 focus:outline-none font-mono-num cursor-not-allowed select-none">
                        </div>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Biaya Bank (HPP Saldo)
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" step="any" min="0" id="transfer_cost_price" value="0" placeholder="0" oninput="calculateTransferTotal()" class="w-full bg-transparent border-0 p-0 text-sm font-bold text-slate-600 focus:ring-0 focus:outline-none font-mono-num">
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Kalkulasi Kasir -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Total Uang Diterima dari Pelanggan:</span>
                        <strong class="text-sm font-black text-blue-700 font-mono-num" id="transfer_total_paid_display">Rp 0</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Keuntungan Jasa Toko (Laba Bersih):</span>
                        <strong class="text-emerald-600 font-bold font-mono-num" id="transfer_net_profit_display">+ Rp 5.000</strong>
                    </div>
                </div>

                <!-- Catatan / No Referensi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">No. Ref Struk Bank / EDC (Opsional)</label>
                        <input type="text" id="transfer_reference_number" placeholder="Ref: 890123" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Catatan Tambahan</label>
                        <input type="text" id="transfer_notes" placeholder="Catatan transaksi..." class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <button type="submit" id="btnSubmitTransfer" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Proses & Simpan Transaksi Transfer</span>
                </button>
            </form>

            <!-- ============================================================= -->
            <!-- TAB 2: FORM TARIK TUNAI -->
            <!-- ============================================================= -->
            <form id="formAgentWithdraw" onsubmit="handleAgentWithdrawSubmit(event)" class="space-y-4 hidden">
                <!-- Info Banner Transaksi -->
                <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100 flex items-start gap-2.5 text-xs text-emerald-900">
                    <i data-lucide="info" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold block">Alur Kas Tarik Tunai:</span>
                        <span>Kasir memberikan uang fisik dari laci kasir ke nasabah. Saldo rekening toko bertambah melalui gesek EDC atau transfer masuk dari nasabah.</span>
                    </div>
                </div>

                <!-- Pilih Rekening Penampung Toko -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Rekening Toko Penampung (Saldo Masuk) <span class="text-rose-500">*</span>
                    </label>
                    <select id="withdraw_account_id" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <!-- Populated by JS -->
                    </select>
                </div>

                <!-- Nominal Tarik & Fee Kasir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nominal Tarik Tunai <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-emerald-600">Rp</span>
                            <input type="number" step="any" min="10000" id="withdraw_principal_amount" oninput="calculateWithdrawTotal()" placeholder="0" required class="w-full bg-transparent border-0 p-0 text-sm font-black text-slate-900 focus:ring-0 focus:outline-none font-mono-num">
                        </div>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 bg-slate-100/70 transition px-4 pt-3 pb-2 shadow-2xs cursor-not-allowed">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-500 flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                            <span>Biaya Admin Toko (Terkunci)</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" step="any" min="0" id="withdraw_admin_fee" value="{{ $agentWithdrawAdminFee ?? 5000 }}" readonly tabindex="-1" class="w-full bg-transparent border-0 p-0 text-sm font-black text-slate-700 focus:ring-0 focus:outline-none font-mono-num cursor-not-allowed select-none">
                        </div>
                    </div>
                </div>

                <!-- Cara Pembayaran Admin Fee -->
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-slate-700 block">Metode Pembayaran Biaya Admin:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs">
                            <input type="radio" name="withdraw_payment_method" value="deduct_balance" checked onchange="calculateWithdrawTotal()" class="text-emerald-600 focus:ring-emerald-500">
                            <span>Digesek di EDC (+ Pokok)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs">
                            <input type="radio" name="withdraw_payment_method" value="cash" onchange="calculateWithdrawTotal()" class="text-emerald-600 focus:ring-emerald-500">
                            <span>Pelanggan Bayar Tunai</span>
                        </label>
                    </div>
                </div>

                <!-- Ringkasan Uang Fisik Kasir -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-rose-600">
                        <span>Uang Fisik Kasir Diserahkan ke Nasabah:</span>
                        <strong class="text-sm font-black font-mono-num" id="withdraw_cash_out_display">- Rp 0</strong>
                    </div>
                    <div class="flex items-center justify-between text-blue-700">
                        <span>Saldo Masuk ke Rekening Toko:</span>
                        <strong class="font-bold font-mono-num" id="withdraw_bank_in_display">+ Rp 0</strong>
                    </div>
                    <div class="flex items-center justify-between text-emerald-600 pt-1 border-t border-slate-200">
                        <span>Keuntungan Jasa Toko:</span>
                        <strong class="font-bold font-mono-num" id="withdraw_net_profit_display">+ Rp 5.000</strong>
                    </div>
                </div>

                <!-- Nama Nasabah & Referensi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Nama Nasabah (Opsional)</label>
                        <input type="text" id="withdraw_destination_holder" placeholder="Nama nasabah" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">No. Ref EDC / Struk</label>
                        <input type="text" id="withdraw_reference_number" placeholder="Contoh: 781290" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <button type="submit" id="btnSubmitWithdraw" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Proses & Simpan Tarik Tunai</span>
                </button>
            </form>

            <!-- ============================================================= -->
            <!-- TAB 3: FORM PULSA & PRODUK DIGITAL PPOB -->
            <!-- ============================================================= -->
            <form id="formAgentPpob" onsubmit="handleAgentPpobSubmit(event)" class="space-y-4 hidden">
                <!-- Info Banner Transaksi -->
                <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-100 flex items-start gap-2.5 text-xs text-amber-900">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold block">Alur Kas PPOB:</span>
                        <span>Saldo deposit server PPOB terpotong sebesar modal (HPP). Kasir menerima pembayaran dari pelanggan (tunai/non-tunai). Margin tercatat sebagai laba kotor.</span>
                    </div>
                </div>

                <!-- Pilih Akun Deposit PPOB -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-600 focus-within:ring-2 focus-within:ring-amber-600/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Akun Deposit Server PPOB (Potong Saldo) <span class="text-rose-500">*</span>
                    </label>
                    <select id="ppob_account_id" onchange="onPpobAccountChanged()" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <!-- Populated by JS -->
                    </select>
                </div>
                <div id="ppob_account_balance_info" class="text-[11px] text-slate-500 px-1 font-medium flex items-center justify-between">
                    <span>Sisa Saldo Deposit Server: <strong class="text-slate-900 font-mono-num" id="ppob_avail_balance_display">Rp 0</strong></span>
                    <span id="ppob_balance_alert_badge" class="hidden text-rose-600 font-bold">Saldo Menipis!</span>
                </div>

                <!-- Pilihan Cepat Katalog Produk PPOB (Dinamis Auto-Fill) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Pilih Cepat Produk / Paket PPOB (Otomatis Isi Harga)</span>
                        </label>
                        <button type="button" onclick="resetPpobToCustom()" class="text-[10px] font-bold text-slate-400 hover:text-amber-600 transition cursor-pointer">
                            Reset Pilihan
                        </button>
                    </div>
                    <div class="relative rounded-xl border border-amber-300 bg-amber-50/40 p-2 shadow-2xs">
                        <select id="ppob_catalog_select" onchange="onPpobCatalogProductSelected(this.value)" class="w-full bg-white border border-amber-200 rounded-lg py-2 px-3 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none cursor-pointer">
                            <option value="">-- Cari / Pilih Produk PPOB (Pulsa, Token, E-Wallet) --</option>
                            <!-- Dynamically loaded via API -->
                        </select>
                    </div>
                </div>

                <!-- Jenis Layanan & No Tujuan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-600 focus-within:ring-2 focus-within:ring-amber-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Jenis Layanan PPOB <span class="text-rose-500">*</span>
                        </label>
                        <select id="ppob_service_type" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="pulsa">Pulsa Reguler</option>
                            <option value="paket_data">Paket Data Internet</option>
                            <option value="token_pln">Token Listrik PLN</option>
                            <option value="ewallet">Top Up E-Wallet (Dana/OVO/Shopee)</option>
                            <option value="tagihan">Bayar Tagihan Bulanan</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-600 focus-within:ring-2 focus-within:ring-amber-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Nomor HP / No. Meteran PLN <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="ppob_destination_target" placeholder="Contoh: 081234567890" required class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none font-mono-num">
                    </div>
                </div>

                <!-- Harga Modal (HPP Saldo) vs Harga Jual Pelanggan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative rounded-xl border border-slate-200 bg-slate-100/70 transition px-4 pt-3 pb-2 shadow-2xs cursor-not-allowed">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-500 flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3 text-slate-400"></i>
                            <span>Modal (HPP Otomatis)</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" step="any" min="100" id="ppob_cost_price" readonly tabindex="-1" placeholder="0" required class="w-full bg-transparent border-0 p-0 text-sm font-bold text-slate-500 focus:ring-0 focus:outline-none font-mono-num cursor-not-allowed select-none">
                        </div>
                    </div>

                    <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-600 focus-within:ring-2 focus-within:ring-amber-600/20 bg-white transition px-4 pt-3 pb-2">
                        <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                            Harga Jual Kasir <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-amber-600">Rp</span>
                            <input type="number" step="any" min="100" id="ppob_selling_price" oninput="calculatePpobTotal()" placeholder="Contoh: 12000" required class="w-full bg-transparent border-0 p-0 text-sm font-black text-slate-900 focus:ring-0 focus:outline-none font-mono-num">
                        </div>
                    </div>
                </div>

                <!-- Metode Pembayaran Pelanggan -->
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-slate-700 block">Metode Pembayaran Pelanggan:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs">
                            <input type="radio" name="ppob_payment_method" value="cash" checked class="text-amber-600 focus:ring-amber-500">
                            <span>Uang Tunai (Masuk Laci)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs">
                            <input type="radio" name="ppob_payment_method" value="non_cash" class="text-amber-600 focus:ring-amber-500">
                            <span>QRIS / Non-Tunai</span>
                        </label>
                    </div>
                </div>

                <!-- Ringkasan Margin Laba -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <span class="text-slate-600">Keuntungan Margin Produk:</span>
                    <strong class="text-emerald-600 font-bold font-mono-num text-sm" id="ppob_net_profit_display">+ Rp 0</strong>
                </div>

                <!-- Serial Number / No Token PLN -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-600 focus-within:ring-2 focus-within:ring-amber-600/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Serial Number (SN) / Kode Token Listrik 20 Digit (Opsional)
                    </label>
                    <input type="text" id="ppob_reference_number" placeholder="Contoh SN: 2891028392819283 / Token PLN" class="w-full bg-transparent border-0 p-0 text-xs font-mono font-bold text-slate-900 focus:ring-0 focus:outline-none">
                </div>

                <button type="submit" id="btnSubmitPpob" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/25 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Proses & Simpan Transaksi PPOB</span>
                </button>
            </form>

            <!-- ============================================================= -->
            <!-- TAB 4: RIWAYAT TRANSAKSI AGEN SHIFT INI -->
            <!-- ============================================================= -->
            <div id="tabAgentHistory" class="space-y-3 hidden">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-800">Daftar Transaksi Agen & PPOB Shift Ini</h4>
                    <button type="button" onclick="loadAgentRecentTransactions()" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refresh
                    </button>
                </div>

                <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-500 font-bold text-[11px] border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Waktu & Layanan</th>
                                <th class="py-2.5 px-3">Tujuan & Akun</th>
                                <th class="py-2.5 px-3 text-right">Nominal Pokok</th>
                                <th class="py-2.5 px-3 text-right">Fee / Laba</th>
                            </tr>
                        </thead>
                        <tbody id="agent_history_table_body" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                    Memuat histori transaksi...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
            <button type="button" onclick="openModal('agentBalancesModal')" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="wallet-cards" class="w-3.5 h-3.5"></i>
                <span>Cek Detail Semua Saldo</span>
            </button>
            <button type="button" onclick="closeModal('agentServiceModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>
