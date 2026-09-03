<!-- MODAL CARI & PILIH INVOICE PENJUALAN -->
<div id="invoicePickerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[88vh]">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Pilih Faktur Invoice Penjualan</h3>
                    <p class="text-xs text-slate-400">Cari berdasarkan nomor invoice POS atau nama pelanggan</p>
                </div>
            </div>
            <button onclick="closeModal('invoicePickerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 px-3.5 py-2.5 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="invoice_picker_search" 
                    oninput="debounceFetchInvoices()" 
                    placeholder="Ketik nomor invoice (INV-...) atau nama pelanggan..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    autocomplete="off"
                >
                <div id="invoice_picker_spinner" class="hidden shrink-0">
                    <i data-lucide="loader-2" class="w-4 h-4 text-brand-500 animate-spin"></i>
                </div>
            </div>
        </div>

        <!-- Invoices List Container -->
        <div class="flex-1 p-4 overflow-y-auto min-h-[280px]">
            
            <!-- Table View of Invoices -->
            <div class="border border-slate-200/90 rounded-xl overflow-hidden bg-white shadow-2xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-700 font-bold text-[11px] border-b border-slate-200">
                            <th class="py-3 px-4">No. Invoice & Tanggal</th>
                            <th class="py-3 px-3">Pelanggan</th>
                            <th class="py-3 px-3">Kasir / Shift</th>
                            <th class="py-3 px-4 text-right">Total Belanja</th>
                            <th class="py-3 px-4 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="invoice_picker_tbody" class="divide-y divide-slate-100">
                        <!-- Dynamically populated via AJAX -->
                    </tbody>
                </table>

                <!-- Empty State -->
                <div id="invoice_picker_empty" class="hidden p-8 text-center text-slate-400">
                    <i data-lucide="receipt-text" class="w-8 h-8 mx-auto mb-1.5 text-slate-300"></i>
                    <p class="font-bold text-xs text-slate-600">Faktur Invoice Tidak Ditemukan</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Coba gunakan kata kunci pencarian nomor invoice atau nama pelanggan yang lain.</p>
                </div>

                <!-- Initial Loading Skeleton -->
                <div id="invoice_picker_loading" class="p-8 text-center text-slate-400">
                    <i data-lucide="loader-2" class="w-7 h-7 mx-auto mb-2 text-brand-500 animate-spin"></i>
                    <p class="font-bold text-xs text-slate-600">Memuat daftar invoice...</p>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/70 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">Hanya menampilkan invoice penjualan berstatus Selesai (Completed)</span>
            <button type="button" onclick="closeModal('invoicePickerModal')" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                Tutup
            </button>
        </div>

    </div>
</div>
