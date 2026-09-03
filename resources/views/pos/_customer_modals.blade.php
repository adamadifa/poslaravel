<!-- MODAL PILIH & CARI PELANGGAN (F2) -->
<div id="customerModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-3xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[88vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Pilih Pelanggan / Member (F2)</h3>
                    <p class="text-[11px] text-slate-400">Cari database pelanggan atau daftarkan member baru</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="openNewCustomerModal()" class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>+ Tambah Pelanggan Baru</span>
                </button>
                <button onclick="closeModal('customerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="px-6 py-3.5 border-b border-slate-100 bg-white">
            <div class="relative rounded-xl border border-slate-200 bg-slate-50/70 focus-within:bg-white focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500 px-4 py-2 transition flex items-center gap-2.5">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                <input 
                    type="text" 
                    id="customer_search_input" 
                    oninput="filterCustomerModalList()" 
                    placeholder="Ketik nama, no. telp/WA, atau kode pelanggan..." 
                    class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                >
            </div>
        </div>

        <!-- Customer List Grid (Scrollable) -->
        <div class="flex-1 p-6 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="customer_modal_list_container">
                <!-- Dynamic Customers rendered from state -->
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PELANGGAN CEPAT (QUICK ADD CUSTOMER) -->
<div id="newCustomerModal" class="fixed inset-0 z-[110] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight">Daftarkan Pelanggan Baru</h3>
                    <p class="text-[11px] text-slate-400">Input data member baru langsung dari kasir</p>
                </div>
            </div>
            <button onclick="closeModal('newCustomerModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="newCustomerForm" onsubmit="handleQuickCreateCustomer(event)" class="p-5 space-y-3.5">
            
            <!-- Nama Lengkap -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="quick_cust_name" required placeholder="Contoh: Bpk. Bambang Wijaya" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
            </div>

            <!-- No. WhatsApp / Telepon -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    No. Handphone / WhatsApp
                </label>
                <input type="text" id="quick_cust_phone" placeholder="081234567890" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none">
            </div>

            <!-- Grup Pelanggan (Member Tier) -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Grup Pelanggan
                </label>
                <select id="quick_cust_group_id" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-brand-600 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Umum (Tanpa Diskon Khusus)</option>
                    @foreach($customerGroups as $grp)
                        <option value="{{ $grp->id }}">
                            {{ $grp->name }} (Diskon {{ floatval($grp->discount_percent) }}%)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Alamat Singkat -->
            <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Alamat / Catatan
                </label>
                <input type="text" id="quick_cust_address" placeholder="Contoh: Jl. Merdeka No. 12" class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 focus:ring-0 focus:outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('newCustomerModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" id="btn_save_quick_cust" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan & Pilih</span>
                </button>
            </div>
        </form>
    </div>
</div>
