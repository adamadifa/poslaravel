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
                    <span class="text-slate-500">Modal Awal</span>
                    <span class="font-bold text-slate-800" id="close_shift_starting_cash">Rp 0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total Transaksi</span>
                    <span class="font-bold text-slate-800" id="close_shift_total_trx">0 Struk</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total Omset Penjualan</span>
                    <span class="font-bold text-emerald-600" id="close_shift_total_sales">Rp 0</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                    <span class="font-bold text-slate-700">Uang Kas Sistem (Expected)</span>
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
                    <span>Tutup Shift & Cetak Rekap</span>
                </button>
            </div>
        </form>
    </div>
</div>
