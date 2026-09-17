<!-- MODAL SHIFT KASIR (BUKA / TUTUP SHIFT) -->

<!-- 1. BUKA SHIFT MODAL -->
<div id="openShiftModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="unlock" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Buka Sesi Shift Kasir</h3>
                    <p class="text-[11px] text-slate-400">Masukkan modal laci kas awal sebelum memulai transaksi</p>
                </div>
            </div>
        </div>

        <form id="openShiftForm" onsubmit="handleOpenShift(event)" class="p-6 space-y-4">
            <!-- Pilih Gudang / Outlet -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cabang Outlet / Gudang <span class="text-rose-500">*</span>
                </label>
                <select name="warehouse_id" id="shift_warehouse_id" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $defaultWarehouse && $defaultWarehouse->id == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} ({{ $wh->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Modal Awal Uang Fisik Kasir -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Modal Kas Awal di Laci (Uang Pecahan) <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-brand-500">Rp</span>
                    <input type="number" step="any" name="starting_cash" id="shift_starting_cash" value="100000" placeholder="100.000" required class="w-full bg-transparent border-0 p-0 text-sm font-bold text-slate-900 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Catatan -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Catatan Shift (Opsional)</label>
                <input type="text" name="notes" id="shift_open_notes" placeholder="Shift Pagi / Shift 1" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center justify-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Mulai Sesi Shift Sekarang</span>
            </button>
        </form>
    </div>
</div>

<!-- 2. TUTUP SHIFT MODAL -->
<div id="closeShiftModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center border border-rose-100/60">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Tutup Sesi Shift Kasir</h3>
                    <p class="text-[11px] text-slate-400">Rekap total omset, transaksi, dan audit selisih kas fisik</p>
                </div>
            </div>
            <button onclick="closeModal('closeShiftModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="closeShiftForm" onsubmit="handleCloseShift(event)" class="p-6 space-y-4">
            <!-- Shift Summary Box -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Modal Awal Kas</span>
                    <span class="font-bold text-slate-800" id="close_shift_starting_cash">Rp 0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total Transaksi</span>
                    <span class="font-bold text-slate-800" id="close_shift_total_trx">0 Struk</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total Penjualan Kas</span>
                    <span class="font-bold text-emerald-600" id="close_shift_total_sales">Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-rose-600">
                    <span class="font-medium">Total Kas Keluar / Biaya</span>
                    <span class="font-bold" id="close_shift_total_expenses">- Rp 0</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-700 block">Uang Kas Sistem (Expected)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Modal + Penjualan - Biaya</span>
                    </div>
                    <span class="font-black text-brand-600 text-sm" id="close_shift_expected_cash">Rp 0</span>
                </div>
            </div>

            <!-- Input Hitungan Fisik Aktual Kasir -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Hitungan Uang Fisik Kasir (Aktual di Laci) <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold text-brand-500">Rp</span>
                    <input type="number" step="any" name="closing_cash" id="shift_closing_cash" oninput="calculateShiftDifference()" placeholder="Hitung uang fisik di laci" required class="w-full bg-transparent border-0 p-0 text-sm font-bold text-slate-900 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Selisih Kas Display -->
            <div id="shift_diff_container" class="p-3 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-600">Selisih Kas Fisik vs Sistem:</span>
                <span id="shift_diff_badge" class="font-bold text-slate-700">Rp 0 (Pas)</span>
            </div>

            <!-- Catatan Penutupan -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Catatan Penutupan</label>
                <input type="text" name="notes" id="shift_close_notes" placeholder="Catatan selisih / serah terima shift..." class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('closeShiftModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-bold text-xs shadow-md shadow-rose-500/25 transition flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Tutup Shift & Rekap</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3. KAS KELUAR / BIAYA SHIFT KASIR MODAL -->
<div id="shiftExpenseModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-lg w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-amber-50/50 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center border border-amber-200/60">
                    <i data-lucide="wallet-cards" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Kas Keluar / Biaya Kasir</h3>
                    <p class="text-[11px] text-slate-500">Catat pengeluaran uang kas kecil selama shift berjalan</p>
                </div>
            </div>
            <button onclick="closeModal('shiftExpenseModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-5">
            <!-- Form Catat Biaya -->
            <form id="shiftExpenseForm" onsubmit="handleStoreShiftExpense(event)" class="space-y-3.5 bg-slate-50/70 p-4 rounded-xl border border-slate-200/80">
                <div class="font-bold text-xs text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>Input Pengeluaran Baru</span>
                </div>

                <!-- Nominal Pengeluaran -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Nominal Pengeluaran (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-amber-600">Rp</span>
                        <input type="number" step="any" name="expense_amount" id="shift_expense_amount" placeholder="Contoh: 25000" required class="w-full bg-transparent border-0 p-0 text-sm font-bold text-slate-900 focus:ring-0 focus:outline-none">
                    </div>
                </div>

                <!-- Kategori Biaya -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Kategori Biaya <span class="text-rose-500">*</span>
                    </label>
                    <select name="expense_category" id="shift_expense_category" required class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="Operasional Toko">Operasional Toko</option>
                        <option value="Konsumsi / Makan Minum">Konsumsi / Makan Minum</option>
                        <option value="Plastik & Kemasan">Plastik & Kemasan</option>
                        <option value="ATK & Perlengkapan">ATK & Perlengkapan</option>
                        <option value="Bensin & Transport">Bensin & Transport</option>
                        <option value="Kebersihan & Sanitasi">Kebersihan & Sanitasi</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <!-- Keterangan / Keperluan -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20 bg-white transition px-4 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">Keterangan / Keperluan</label>
                    <input type="text" name="expense_notes" id="shift_expense_notes" placeholder="Contoh: Beli kantong plastik 2 pack, Galon air..." class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
                </div>

                <button type="submit" id="submitShiftExpenseBtn" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm shadow-amber-500/20 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Pengeluaran Kasir</span>
                </button>
            </form>

            <!-- Riwayat Pengeluaran Shift Ini -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="history" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Riwayat Pengeluaran Shift Aktif</span>
                    </h4>
                    <span id="shift_expenses_total_badge" class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold text-xs border border-rose-100">
                        Total: Rp 0
                    </span>
                </div>

                <div class="rounded-xl border border-slate-200 overflow-hidden bg-white">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-500 font-bold text-[11px] border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Kategori & Keterangan</th>
                                <th class="py-2.5 px-3 text-right">Nominal</th>
                                <th class="py-2.5 px-2 text-center w-10">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="shift_expenses_table_body" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-400 text-xs">
                                    Belum ada pengeluaran yang dicatat pada shift ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-end shrink-0">
            <button type="button" onclick="closeModal('shiftExpenseModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition">
                Selesai / Tutup
            </button>
        </div>
    </div>
</div>
