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
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-50 to-orange-100 text-brand-600 flex items-center justify-center border border-brand-200/60 shadow-xs">
                    <i data-lucide="user-plus" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Daftarkan Pelanggan Baru</span>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-brand-50 text-brand-600 border border-brand-200/60">POS Kasir</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Input data member atau pelanggan baru secara langsung tanpa keluar dari transaksi</p>
                </div>
            </div>
            <button onclick="cancelNewCustomerModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer" title="Tutup / Kembali ke daftar pelanggan">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="newCustomerForm" onsubmit="handleQuickCreateCustomer(event)" novalidate class="p-6 space-y-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kolom Kiri: Data Utama Pelanggan -->
                <div class="space-y-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <div class="cust-input-container relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2.5">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                <i data-lucide="user" class="w-3 h-3 text-brand-500"></i>
                                <span>Nama Lengkap</span>
                                <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="quick_cust_name" 
                                required 
                                oninput="clearSingleCustomerError('quick_cust_name')"
                                placeholder="Contoh: Bpk. Bambang Wijaya" 
                                class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder:text-slate-300 focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="err_quick_cust_name" class="hidden text-[11px] text-rose-500 font-semibold mt-1 px-1 flex items-center gap-1"></p>
                    </div>

                    <!-- No. WhatsApp / Telepon -->
                    <div>
                        <div class="cust-input-container relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2.5">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                <i data-lucide="phone" class="w-3 h-3 text-brand-500"></i>
                                <span>No. HP / WhatsApp</span>
                            </label>
                            <input 
                                type="tel" 
                                id="quick_cust_phone" 
                                oninput="clearSingleCustomerError('quick_cust_phone')"
                                placeholder="Contoh: 081234567890" 
                                class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder:text-slate-300 focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="err_quick_cust_phone" class="hidden text-[11px] text-rose-500 font-semibold mt-1 px-1 flex items-center gap-1"></p>
                    </div>

                    <!-- Email Pelanggan -->
                    <div>
                        <div class="cust-input-container relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2.5">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                <i data-lucide="mail" class="w-3 h-3 text-slate-400"></i>
                                <span>Email Pelanggan (Opsional)</span>
                            </label>
                            <input 
                                type="email" 
                                id="quick_cust_email" 
                                oninput="clearSingleCustomerError('quick_cust_email')"
                                placeholder="pelanggan@email.com" 
                                class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder:text-slate-300 focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="err_quick_cust_email" class="hidden text-[11px] text-rose-500 font-semibold mt-1 px-1 flex items-center gap-1"></p>
                    </div>
                </div>

                <!-- Kolom Kanan: Grup Member, Limit Kredit & Alamat -->
                <div class="space-y-4">
                    <!-- Grup Pelanggan (Member Tier) -->
                    <div>
                        <div class="cust-input-container relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2.5">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                <i data-lucide="tag" class="w-3 h-3 text-brand-500"></i>
                                <span>Grup Member / Diskon</span>
                            </label>
                            <select 
                                id="quick_cust_group_id" 
                                onchange="clearSingleCustomerError('quick_cust_customer_group_id')"
                                class="w-full bg-transparent border-0 p-0 text-xs font-bold text-brand-600 focus:ring-0 focus:outline-none cursor-pointer"
                            >
                                <option value="">Umum / Regular (Harga Standar)</option>
                                @foreach($customerGroups as $grp)
                                    <option value="{{ $grp->id }}">
                                        {{ $grp->name }} @if((float)$grp->discount_percent > 0)(Diskon {{ floatval($grp->discount_percent) }}%)@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p id="err_quick_cust_customer_group_id" class="hidden text-[11px] text-rose-500 font-semibold mt-1 px-1 flex items-center gap-1"></p>
                    </div>

                    <!-- Limit Piutang / Kredit -->
                    <div>
                        <div class="cust-input-container relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2.5">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                <i data-lucide="coins" class="w-3 h-3 text-slate-400"></i>
                                <span>Limit Piutang (Rp)</span>
                            </label>
                            <input 
                                type="number" 
                                id="quick_cust_credit_limit" 
                                oninput="clearSingleCustomerError('quick_cust_credit_limit')"
                                min="0"
                                placeholder="0 (Tanpa Limit)" 
                                class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder:text-slate-300 focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="err_quick_cust_credit_limit" class="hidden text-[11px] text-rose-500 font-semibold mt-1 px-1 flex items-center gap-1"></p>
                    </div>

                    <!-- Alamat Singkat -->
                    <div>
                        <div class="cust-input-container relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-3 pb-2.5">
                            <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                <span>Alamat / Catatan Domisili</span>
                            </label>
                            <input 
                                type="text" 
                                id="quick_cust_address" 
                                oninput="clearSingleCustomerError('quick_cust_address')"
                                placeholder="Contoh: Jl. Merdeka No. 12, RT 01/RW 02" 
                                class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder:text-slate-300 focus:ring-0 focus:outline-none"
                            >
                        </div>
                        <p id="err_quick_cust_address" class="hidden text-[11px] text-rose-500 font-semibold mt-1 px-1 flex items-center gap-1"></p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <button 
                    type="button" 
                    onclick="cancelNewCustomerModal()" 
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 transition flex items-center gap-1.5 cursor-pointer"
                >
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Daftar</span>
                </button>
                <div class="flex items-center gap-2">
                    <button 
                        type="submit" 
                        id="btn_save_quick_cust" 
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 via-orange-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/20 hover:shadow-brand-500/35 transition flex items-center gap-2 cursor-pointer"
                    >
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan & Pilih Pelanggan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
