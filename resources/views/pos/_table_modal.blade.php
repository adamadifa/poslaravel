<!-- MODAL PILIH MEJA & TIPE LAYANAN (DINE IN / TAKE AWAY / DELIVERY) -->
<div id="tableModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-amber-500 to-brand-500 text-white shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center shadow-xs">
                    <i data-lucide="utensils" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-white tracking-tight">Tipe Pesanan & Pilih Meja</h3>
                    <p class="text-[11px] text-white/80">Tentukan layanan makan di tempat, bungkus, atau nomor meja pelanggan</p>
                </div>
            </div>
            <button onclick="closeModal('tableModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto space-y-5">
            
            <!-- Service Type Segmented Buttons -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Tipe Layanan Pesanan
                </label>
                <div class="grid grid-cols-3 gap-2.5">
                    <button type="button" onclick="selectServiceType('dine_in')" id="st_btn_dine_in" class="p-3 rounded-xl border-2 border-brand-500 bg-brand-50/50 text-brand-700 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition shadow-2xs cursor-pointer">
                        <i data-lucide="coffee" class="w-5 h-5 text-brand-600"></i>
                        <span>Dine In (Makan di Tempat)</span>
                    </button>
                    <button type="button" onclick="selectServiceType('takeaway')" id="st_btn_takeaway" class="p-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-600 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition shadow-2xs cursor-pointer">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-slate-500"></i>
                        <span>Take Away (Bungkus)</span>
                    </button>
                    <button type="button" onclick="selectServiceType('delivery')" id="st_btn_delivery" class="p-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-600 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition shadow-2xs cursor-pointer">
                        <i data-lucide="truck" class="w-5 h-5 text-slate-500"></i>
                        <span>Delivery (Kirim)</span>
                    </button>
                </div>
            </div>

            <!-- Dine-In Specific: Table Grid & Guest Count -->
            <div id="dine_in_section" class="space-y-4">
                
                <!-- Guest Count Input -->
                <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">Jumlah Tamu / Pengunjung</span>
                        <span class="text-[11px] text-slate-400">Digunakan untuk statistik kapasitas dan layanan</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="adjustGuestCount(-1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 flex items-center justify-center font-bold text-sm shadow-2xs cursor-pointer">-</button>
                        <input type="number" min="1" id="modal_guest_count" value="1" class="w-12 text-center text-xs font-bold bg-white border border-slate-200 rounded-lg py-1 px-1 focus:border-brand-500 focus:outline-none">
                        <button type="button" onclick="adjustGuestCount(1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 flex items-center justify-center font-bold text-sm shadow-2xs cursor-pointer">+</button>
                    </div>
                </div>

                <!-- Table Cards Grid Header -->
                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-brand-500"></i>
                        <span>Pilih Meja Resto / Cafe</span>
                    </span>
                    <span class="text-[11px] text-slate-500 font-semibold" id="selected_table_info_text">Pilih salah satu meja</span>
                </div>

                <!-- Area Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1" id="table_area_filters">
                    <button type="button" onclick="filterTableArea('all', this)" class="table-area-pill px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-900 text-white shadow-2xs shrink-0 cursor-pointer">
                        Semua Area
                    </button>
                </div>

                <!-- Tables Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 max-h-[260px] overflow-y-auto p-1" id="pos_tables_grid">
                    <!-- Injected via JS -->
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
            <button onclick="closeModal('tableModal')" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                Batal
            </button>
            <button onclick="confirmTableSelection()" type="button" class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/20 transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Terapkan Layanan</span>
            </button>
        </div>

    </div>
</div>
