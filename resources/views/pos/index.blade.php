@extends('layouts.pos')

@section('content')
<div class="flex-1 flex flex-col lg:flex-row overflow-hidden w-full h-full bg-slate-100 transition-colors duration-200">

    <!-- ========================================================================= -->
    <!-- LEFT PANEL: PRODUCT CATALOG & BARCODE SCANNER -->
    <!-- ========================================================================= -->
    <div class="flex-1 flex flex-col min-w-0 border-r border-slate-200 overflow-hidden bg-slate-50/50">
        
        <!-- Search & Scanner Input Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-white flex flex-wrap items-center gap-3.5 shrink-0 transition-colors duration-200">
            <!-- Search & Barcode Scan -->
            <div class="relative flex-1 min-w-[280px]">
                <i data-lucide="barcode" class="w-5 h-5 text-brand-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input 
                    type="text" 
                    id="barcodeScannerInput" 
                    autofocus 
                    placeholder="Scan Barcode atau ketik nama produk... (Tekan F1)" 
                    class="w-full pl-11 pr-24 py-2.5 bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200 focus:border-brand-500 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition"
                >
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-[10px] font-bold text-slate-500 bg-white px-2 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                    SCAN READY
                </div>
            </div>

            <!-- Customer Picker, Table Picker & Manual Price Setting Toggle -->
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <!-- Clickable Customer Card Button (F2) -->
                <button 
                    type="button" 
                    id="posCustomerBtn" 
                    onclick="openCustomerModal()" 
                    title="Pilih Pelanggan / Member (F2)" 
                    class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 hover:border-brand-500/60 text-xs font-semibold text-slate-700 transition shadow-2xs cursor-pointer group"
                >
                    <div class="w-6 h-6 rounded-lg bg-orange-50 text-brand-600 flex items-center justify-center font-bold text-xs shrink-0">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="text-left">
                        <span class="text-[9px] font-bold text-slate-400 block uppercase tracking-wider leading-none">Pelanggan (F2)</span>
                        <span id="posCustomerDisplay" class="font-bold text-slate-900 text-xs truncate max-w-[130px] inline-block mt-0.5">Umum (Retail)</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-500 transition ml-1"></i>
                </button>
                <input type="hidden" id="posCustomerSelect" value="">

                <!-- Toggle Setting: Input Qty & Harga Manual Modal -->
                <button 
                    type="button" 
                    id="toggleManualPriceBtn" 
                    onclick="toggleManualPriceSetting()" 
                    title="Aktifkan/Nonaktifkan Modal Input Qty & Ubah Harga Manual saat klik produk" 
                    class="flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 text-xs font-bold transition shadow-2xs whitespace-nowrap cursor-pointer"
                >
                    <i data-lucide="sliders" class="w-3.5 h-3.5 text-slate-500" id="manualPriceIcon"></i>
                    <span id="manualPriceLabel">Modal Qty/Harga: <strong class="text-slate-900 font-bold" id="manualPriceStatusText">ON</strong></span>
                </button>
            </div>
        </div>

        <!-- Category Filter Tabs -->
        <div class="px-5 py-2.5 border-b border-slate-200 bg-white flex items-center gap-2 overflow-x-auto shrink-0 no-scrollbar" id="categoryFilterBar">
            <button onclick="filterCategory(null, this)" class="category-pill-btn px-4 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-brand-500 to-amber-500 text-white shadow-xs whitespace-nowrap">
                Semua Kategori
            </button>
            @foreach($categories as $cat)
                <button onclick="filterCategory({{ $cat->id }}, this)" class="category-pill-btn px-4 py-1.5 rounded-xl text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 whitespace-nowrap transition">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Product Cards Grid (Scrollable) -->
        <div class="flex-1 p-5 overflow-y-auto">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3.5" id="posProductGrid">
                <!-- Products dynamically rendered here -->
            </div>
            
            <div id="productEmptyState" class="hidden flex-col items-center justify-center py-16 text-center text-slate-400 space-y-2">
                <i data-lucide="package-search" class="w-12 h-12 text-slate-300"></i>
                <p class="font-bold text-slate-700 text-sm">Produk Tidak Ditemukan</p>
                <p class="text-xs text-slate-400">Coba kata kunci pencarian atau scan barcode lain.</p>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- RIGHT PANEL: CART, TIER PRICING, & CHECKOUT -->
    <!-- ========================================================================= -->
    <div class="w-full lg:w-[450px] xl:w-[490px] bg-white flex flex-col justify-between shrink-0 h-full border-t lg:border-t-0 select-none shadow-sm transition-colors duration-200">
        
        <!-- Cart Header Bar -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-white shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-brand-500">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                </div>
                <h3 class="font-bold text-sm text-slate-900">Keranjang Penjualan</h3>
                <span id="cartCountBadge" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-brand-50 text-brand-600 border border-brand-200">
                    0 Item
                </span>
            </div>

            <!-- Clear, Recall, & Hold Buttons -->
            <div class="flex items-center gap-1.5">
                <button onclick="openModal('recallModal'); loadHeldList();" title="Recall (Buka Transaksi Tertahan)" class="px-2.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition text-xs font-bold flex items-center gap-1 border border-purple-200">
                    <i data-lucide="play-circle" class="w-3.5 h-3.5"></i>
                    <span>Recall</span>
                </button>
                <button onclick="openModal('holdModal')" title="Hold Transaction (F7)" class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 transition text-xs font-bold flex items-center gap-1 border border-amber-200">
                    <i data-lucide="pause-circle" class="w-3.5 h-3.5"></i>
                    <span>Hold</span>
                </button>
                <button onclick="clearCart()" title="Kosongkan Keranjang" class="p-1.5 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition text-xs font-medium border border-slate-200">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 p-4 overflow-y-auto space-y-2.5 bg-slate-50/50" id="cartItemsContainer">
            <!-- Dynamic Cart Items rendered via JavaScript -->
            <div id="cartEmptyState" class="flex flex-col items-center justify-center h-full py-20 text-center text-slate-400 space-y-2">
                <i data-lucide="shopping-cart" class="w-12 h-12 text-slate-300"></i>
                <p class="font-bold text-slate-700 text-sm">Keranjang Masih Kosong</p>
                <p class="text-xs text-slate-400 max-w-[200px]">Klik produk di katalog atau scan barcode untuk menambah belanjaan.</p>
            </div>
        </div>

        <!-- Order Summary & Checkout Section -->
        <div class="p-5 sm:p-6 border-t border-slate-200 bg-white shrink-0 space-y-3.5">
            
            <!-- Calculation Breakdown -->
            <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span class="font-mono-num font-semibold text-slate-800" id="cartSubtotalText">Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                    <span class="flex items-center gap-1">Diskon Promo / Member <i data-lucide="tag" class="w-3 h-3 text-brand-500"></i></span>
                    <span class="font-mono-num font-semibold text-emerald-600" id="cartDiscountText">- Rp 0</span>
                </div>
                
                <!-- Grand Total Banner -->
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 block uppercase tracking-wider">Total Tagihan</span>
                        <span class="text-[11px] text-slate-500 font-medium" id="cartTotalQtyText">0 Qty</span>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-brand-600 font-mono-num tracking-tight" id="cartGrandTotalText">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Quick Payment Method & Action Button -->
            <div class="grid grid-cols-4 gap-2 pt-1">
                <button type="button" onclick="openPaymentWithMethod('cash')" class="py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="banknote" class="w-4 h-4 text-emerald-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-emerald-800">Tunai</span>
                </button>
                <button type="button" onclick="openPaymentWithMethod('qris')" class="py-2.5 rounded-xl bg-slate-50 hover:bg-brand-50 border border-slate-200 hover:border-brand-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="qr-code" class="w-4 h-4 text-brand-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-brand-800">QRIS</span>
                </button>
                <button type="button" onclick="openPaymentWithMethod('transfer')" class="py-2.5 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="credit-card" class="w-4 h-4 text-blue-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-blue-800">Transfer</span>
                </button>
                <button type="button" onclick="openPaymentWithMethod('credit')" class="py-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200 hover:border-amber-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="user-check" class="w-4 h-4 text-amber-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-amber-800">Piutang</span>
                </button>
            </div>

            <!-- Big Pay Button (F12) -->
            <button onclick="openPaymentModal()" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 active:scale-[0.99] text-white font-extrabold text-base shadow-md shadow-brand-500/25 flex items-center justify-center gap-2 transition cursor-pointer">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>BAYAR SEKARANG (F12)</span>
            </button>
        </div>

    </div>

</div>

<!-- MODALS INCLUDE -->
@include('pos._shift_modals')
@include('pos._agent_modals')
@include('pos._payment_modals')
@include('pos._item_modal')
@include('pos._discount_modal')
@include('pos._customer_modals')
@include('pos._table_modal')

@endsection

@push('scripts')
<script>
    // State Management
    let currentWarehouseId = {{ $defaultWarehouse ? $defaultWarehouse->id : 1 }};
    let activeShift = @json($activeShift);
    let allProducts = [];
    let allCustomers = @json($customers);
    let allDiningTables = @json($diningTables);
    let selectedCustomer = null;
    let selectedServiceType = 'takeaway';
    let selectedTable = null;
    let guestCount = 1;
    let selectedTableArea = 'all';
    let cart = [];
    let selectedCategoryId = null;
    let appliedPromoCode = '';
    let appliedManualDiscount = 0;

    // Receipt & Branding Settings
    const receiptShowLogo = @json((bool) $receiptShowLogo);
    const companyLogoUrl = @json(!empty($companyLogo) ? asset('storage/' . $companyLogo) : null);
    const companyName = @json($companyName ?? 'POS RETAIL & RESTO');
    const companyTagline = @json($companyTagline ?? '');
    const companyAddress = @json($companyAddress ?? '');
    const companyPhone = @json($companyPhone ?? '');
    const receiptHeaderMsg = @json($receiptHeader ?? '');
    const receiptFooterMsg = @json($receiptFooter ?? '');
    const receiptPaperSize = @json($receiptPaperSize ?? '58mm');

    // Helper Modals
    function openModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('hidden');
            el.classList.add('flex');
            lucide.createIcons();
        }
    }

    function closeModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Initialize POS
    document.addEventListener('DOMContentLoaded', () => {
        initManualPriceSettingUI();
        updateBluetoothUiState();

        if (activeShift && activeShift.warehouse && activeShift.warehouse.name) {
            const whEl = document.getElementById('posWarehouseDisplayName');
            if (whEl) whEl.innerText = activeShift.warehouse.name;
        }

        if (!activeShift) {
            openModal('openShiftModal');
        }

        loadProducts();
        renderCustomerModalList();

        // Barcode Scanner & Search Listener
        const scannerInput = document.getElementById('barcodeScannerInput');
        
        // Live search saat mengetik
        let searchDebounce = null;
        scannerInput.addEventListener('input', (e) => {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                loadProducts(scannerInput.value.trim());
            }, 300);
        });

        // Scan barcode / Enter action
        scannerInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = scannerInput.value.trim();
                handleBarcodeScan(query);
            }
        });

        // Global Shortcuts (F1, F2, F3, F4, F7, F9, F12, Escape)
        window.addEventListener('keydown', (e) => {
            const key = e.key;

            if (key === 'F1') {
                e.preventDefault();
                const sIn = document.getElementById('barcodeScannerInput');
                if (sIn) {
                    sIn.focus();
                    sIn.select();
                }
            } else if (key === 'F2') {
                e.preventDefault();
                openCustomerModal();
            } else if (key === 'F3') {
                e.preventDefault();
                openTableModal();
            } else if (key === 'F4') {
                e.preventDefault();
                openShiftExpenseModal();
            } else if (key === 'F7') {
                e.preventDefault();
                openModal('holdModal');
                setTimeout(() => {
                    const hIn = document.getElementById('hold_reference_label');
                    if (hIn) hIn.focus();
                }, 100);
            } else if (key === 'F8') {
                e.preventDefault();
                openAgentServiceModal();
            } else if (key === 'F9') {
                e.preventDefault();
                openModal('discountModal');
                setTimeout(() => {
                    const dIn = document.getElementById('discount_promo_code');
                    if (dIn) dIn.focus();
                }, 100);
            } else if (key === 'F12') {
                e.preventDefault();
                openPaymentModal();
            } else if (key === 'Escape') {
                closeModal('itemModal');
                closeModal('tableModal');
                closeModal('paymentModal');
                closeModal('holdModal');
                closeModal('recallModal');
                closeModal('receiptModal');
                closeModal('discountModal');
                closeModal('customerModal');
                closeModal('newCustomerModal');
                closeModal('shiftExpenseModal');
                closeModal('closeShiftModal');
                closeModal('agentServiceModal');
                closeModal('agentBalancesModal');
                closeModal('bluetoothPrinterModal');
            }
        });

        updateHeaderShiftExpenseBadge();
    });

    // Customer Modal Handlers
    function openCustomerModal() {
        document.getElementById('customer_search_input').value = '';
        renderCustomerModalList();
        openModal('customerModal');
        setTimeout(() => {
            const sIn = document.getElementById('customer_search_input');
            if (sIn) sIn.focus();
        }, 100);
    }

    function renderCustomerModalList(query = '') {
        const container = document.getElementById('customer_modal_list_container');
        const q = query.toLowerCase().trim();

        // Preserve default Retail row
        let html = `
            <div onclick="selectCustomerFromModal(null)" class="customer-list-item p-3 rounded-xl hover:bg-brand-50/60 border border-transparent hover:border-brand-200 cursor-pointer transition flex items-center justify-between group ${!selectedCustomer ? 'bg-brand-50 border-brand-200' : ''}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-brand-100 text-slate-500 group-hover:text-brand-600 flex items-center justify-center font-bold text-xs transition">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-800 group-hover:text-brand-600">Pelanggan Umum (Retail)</div>
                        <div class="text-[10px] text-slate-400">Harga standar regular tanpa diskon grup</div>
                    </div>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md ${!selectedCustomer ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200'}">
                    ${!selectedCustomer ? 'Terpilih' : 'Pilih'}
                </span>
            </div>
        `;

        const filtered = allCustomers.filter(c => {
            const name = (c.name || '').toLowerCase();
            const phone = (c.phone || '').toLowerCase();
            const code = (c.code || '').toLowerCase();
            return name.includes(q) || phone.includes(q) || code.includes(q);
        });

        filtered.forEach(c => {
            const isSelected = selectedCustomer && selectedCustomer.id === c.id;
            const groupName = c.group ? c.group.name : 'Member';
            const discPercent = c.group ? parseFloat(c.group.discount_percent) : 0;

            html += `
                <div onclick="selectCustomerFromModal(${c.id})" class="customer-list-item p-3 rounded-xl hover:bg-brand-50/60 border border-transparent hover:border-brand-200 cursor-pointer transition flex items-center justify-between group ${isSelected ? 'bg-brand-50 border-brand-200' : ''}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl ${isSelected ? 'bg-brand-500 text-white' : 'bg-slate-100 group-hover:bg-brand-100 text-slate-600 group-hover:text-brand-600'} flex items-center justify-center font-bold text-xs transition">
                            ${c.name.substring(0, 1).toUpperCase()}
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-900 group-hover:text-brand-600 flex items-center gap-1.5">
                                <span>${c.name}</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 font-mono">
                                    ${c.code}
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2">
                                <span>${c.phone || 'Tanpa No. HP'}</span>
                                <span>•</span>
                                <span class="text-brand-600 font-bold">${groupName} ${discPercent > 0 ? `(Disc ${discPercent}%)` : ''}</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md ${isSelected ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200'}">
                        ${isSelected ? 'Terpilih' : 'Pilih'}
                    </span>
                </div>
            `;
        });

        container.innerHTML = html;
        lucide.createIcons();
    }

    function filterCustomerModalList() {
        const q = document.getElementById('customer_search_input').value;
        renderCustomerModalList(q);
    }

    function selectCustomerFromModal(customerId) {
        if (!customerId) {
            selectedCustomer = null;
            document.getElementById('posCustomerSelect').value = '';
            document.getElementById('posCustomerDisplay').innerText = 'Umum (Retail)';
        } else {
            const found = allCustomers.find(c => c.id === customerId);
            if (found) {
                selectedCustomer = found;
                document.getElementById('posCustomerSelect').value = found.id;
                const groupInfo = found.group ? ` (${found.group.name})` : '';
                document.getElementById('posCustomerDisplay').innerText = `${found.name}${groupInfo}`;
            }
        }

        closeModal('customerModal');
        onCustomerChange();
    }

    // ==========================================
    // TABLE & SERVICE TYPE MODAL HANDLERS
    // ==========================================
    function openTableModal() {
        renderTableModalUI();
        openModal('tableModal');
    }

    function selectServiceType(type) {
        selectedServiceType = type;
        const dineInSec = document.getElementById('dine_in_section');
        
        ['dine_in', 'takeaway', 'delivery'].forEach(t => {
            const btn = document.getElementById(`st_btn_${t}`);
            if (btn) {
                if (t === type) {
                    btn.className = 'p-3 rounded-xl border-2 border-brand-500 bg-brand-50/50 text-brand-700 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition shadow-2xs cursor-pointer';
                    const icon = btn.querySelector('i');
                    if (icon) icon.className = 'w-5 h-5 text-brand-600';
                } else {
                    btn.className = 'p-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-600 font-bold text-xs flex flex-col items-center justify-center gap-1.5 transition shadow-2xs cursor-pointer';
                    const icon = btn.querySelector('i');
                    if (icon) icon.className = 'w-5 h-5 text-slate-500';
                }
            }
        });

        if (type === 'dine_in') {
            if (dineInSec) dineInSec.classList.remove('hidden');
        } else {
            if (dineInSec) dineInSec.classList.add('hidden');
            selectedTable = null;
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function adjustGuestCount(delta) {
        const inEl = document.getElementById('modal_guest_count');
        if (!inEl) return;
        let current = parseInt(inEl.value) || 1;
        let next = Math.max(1, current + delta);
        inEl.value = next;
        guestCount = next;
    }

    function renderTableModalUI() {
        selectServiceType(selectedServiceType);
        const gIn = document.getElementById('modal_guest_count');
        if (gIn) gIn.value = guestCount;

        // Render Area Filter Pills
        const areaContainer = document.getElementById('table_area_filters');
        if (areaContainer) {
            const areas = [...new Set(allDiningTables.map(t => t.area || 'Utama'))];
            let areaHtml = `<button type="button" onclick="filterTableArea('all', this)" class="table-area-pill px-3 py-1 rounded-lg text-[11px] font-bold ${selectedTableArea === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'} shadow-2xs shrink-0 cursor-pointer">Semua Area</button>`;
            areas.forEach(area => {
                areaHtml += `<button type="button" onclick="filterTableArea('${area}', this)" class="table-area-pill px-3 py-1 rounded-lg text-[11px] font-bold ${selectedTableArea === area ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'} shadow-2xs shrink-0 cursor-pointer">${area}</button>`;
            });
            areaContainer.innerHTML = areaHtml;
        }

        renderTablesGrid();
    }

    function filterTableArea(area, btn) {
        selectedTableArea = area;
        document.querySelectorAll('.table-area-pill').forEach(el => {
            el.className = 'table-area-pill px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 shadow-2xs shrink-0 cursor-pointer';
        });
        btn.className = 'table-area-pill px-3 py-1 rounded-lg text-[11px] font-bold bg-slate-900 text-white shadow-2xs shrink-0 cursor-pointer';
        renderTablesGrid();
    }

    function renderTablesGrid() {
        const grid = document.getElementById('pos_tables_grid');
        if (!grid) return;
        grid.innerHTML = '';

        let filtered = allDiningTables;
        if (selectedTableArea !== 'all') {
            filtered = allDiningTables.filter(t => (t.area || 'Utama') === selectedTableArea);
        }

        if (filtered.length === 0) {
            grid.innerHTML = '<div class="col-span-full py-8 text-center text-slate-400 text-xs">Belum ada data meja di area ini.</div>';
            return;
        }

        filtered.forEach(table => {
            const isSelected = selectedTable && selectedTable.id === table.id;
            const isOccupied = table.status === 'occupied';

            const card = document.createElement('div');
            card.className = `p-3 rounded-xl border-2 transition cursor-pointer flex flex-col justify-between ${
                isSelected 
                    ? 'border-brand-500 bg-brand-50/70 shadow-xs' 
                    : isOccupied 
                        ? 'border-rose-200 bg-rose-50/40 hover:border-rose-300' 
                        : 'border-slate-200 hover:border-brand-500/50 bg-white hover:bg-slate-50/70'
            }`;
            card.onclick = () => {
                selectedTable = table;
                const infoText = document.getElementById('selected_table_info_text');
                if (infoText) infoText.innerText = `Terpilih: ${table.table_number} (${table.area || 'Utama'})`;
                renderTablesGrid();
            };

            card.innerHTML = `
                <div class="flex items-center justify-between mb-1.5">
                    <span class="font-bold text-xs ${isSelected ? 'text-brand-700' : 'text-slate-900'}">${table.table_number}</span>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold ${isOccupied ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'}">
                        ${isOccupied ? 'Terisi' : 'Kosong'}
                    </span>
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-400">
                    <span>${table.area || 'Utama'}</span>
                    <span>${table.capacity || 2} Kursi</span>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function confirmTableSelection() {
        const gIn = document.getElementById('modal_guest_count');
        guestCount = gIn ? (parseInt(gIn.value) || 1) : 1;
        selectedServiceType = 'dine_in';
        updatePaymentTableDisplay();
        onPaymentServiceTypeChange('dine_in');
        closeModal('tableModal');
    }

    function openNewCustomerModal() {
        closeModal('customerModal');
        document.getElementById('quick_cust_name').value = '';
        document.getElementById('quick_cust_phone').value = '';
        document.getElementById('quick_cust_address').value = '';
        openModal('newCustomerModal');
        setTimeout(() => {
            const nIn = document.getElementById('quick_cust_name');
            if (nIn) nIn.focus();
        }, 100);
    }

    async function handleQuickCreateCustomer(e) {
        e.preventDefault();
        const name = document.getElementById('quick_cust_name').value.trim();
        const phone = document.getElementById('quick_cust_phone').value.trim();
        const groupId = document.getElementById('quick_cust_group_id').value;
        const address = document.getElementById('quick_cust_address').value.trim();
        const btn = document.getElementById('btn_save_quick_cust');

        try {
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Menyimpan...</span>`;

            const res = await fetch('{{ route("customers.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    phone: phone,
                    customer_group_id: groupId || null,
                    address: address,
                    is_active: 1
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Simpan & Pilih</span>`;

            if (data.status === 'success') {
                const newCustomer = data.data;
                allCustomers.unshift(newCustomer);
                closeModal('newCustomerModal');
                selectCustomerFromModal(newCustomer.id);
                showPosToast('success', `Pelanggan ${newCustomer.name} berhasil didaftarkan.`);
            } else {
                showPosAlert('error', 'Gagal Menyimpan', data.message);
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Simpan & Pilih</span>`;
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        }
    }

    // Handle Discount Modal (F9)
    function handleApplyDiscountModal(e) {
        e.preventDefault();
        appliedPromoCode = document.getElementById('discount_promo_code').value.trim();
        appliedManualDiscount = parseFloat(document.getElementById('discount_manual_amount').value) || 0;
        closeModal('discountModal');
        recalculateCartPrices().then(renderCart);
        showPosToast('success', 'Diskon transaksi berhasil diterapkan.');
    }

    // Shift Handlers
    async function handleOpenShift(e) {
        e.preventDefault();
        const warehouseId = document.getElementById('shift_warehouse_id').value;
        const startingCash = document.getElementById('shift_starting_cash').value;
        const notes = document.getElementById('shift_open_notes').value;

        try {
            const res = await fetch('{{ route("shifts.open") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ warehouse_id: warehouseId, starting_cash: startingCash, notes })
            });
            const data = await res.json();
            if (data.status === 'success') {
                activeShift = data.data;
                currentWarehouseId = warehouseId;
                if (activeShift && activeShift.warehouse && activeShift.warehouse.name) {
                    const whEl = document.getElementById('posWarehouseDisplayName');
                    if (whEl) whEl.innerText = activeShift.warehouse.name;
                }
                updateHeaderShiftExpenseBadge();
                closeModal('openShiftModal');
                showPosAlert('success', 'Shift Kasir Dimulai!', `Modal awal kas: Rp ${parseInt(startingCash).toLocaleString('id-ID')}`, 2000);
            } else {
                showPosAlert('error', 'Gagal Buka Shift', data.message);
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        }
    }

    function openCloseShiftDialog() {
        if (!activeShift) {
            showPosToast('warning', 'Tidak ada sesi shift aktif.');
            return;
        }
        document.getElementById('close_shift_starting_cash').innerText = `Rp ${parseInt(activeShift.starting_cash || 0).toLocaleString('id-ID')}`;
        document.getElementById('close_shift_total_trx').innerText = `${activeShift.total_transactions || 0} Struk`;
        document.getElementById('close_shift_total_sales').innerText = `Rp ${parseInt(activeShift.total_sales || 0).toLocaleString('id-ID')}`;
        document.getElementById('close_shift_total_expenses').innerText = `- Rp ${parseInt(activeShift.total_expenses || 0).toLocaleString('id-ID')}`;
        
        const agentIn = parseFloat(activeShift.total_agent_cash_in || 0);
        const agentOut = parseFloat(activeShift.total_agent_cash_out || 0);
        const agentProfit = parseFloat(activeShift.total_agent_profit || 0);
        
        const elAgentIn = document.getElementById('close_shift_total_agent_in');
        if (elAgentIn) elAgentIn.innerText = `+ Rp ${parseInt(agentIn).toLocaleString('id-ID')}`;
        
        const elAgentOut = document.getElementById('close_shift_total_agent_out');
        if (elAgentOut) elAgentOut.innerText = `- Rp ${parseInt(agentOut).toLocaleString('id-ID')}`;
        
        const elAgentProfit = document.getElementById('close_shift_total_agent_profit');
        if (elAgentProfit) elAgentProfit.innerText = `+ Rp ${parseInt(agentProfit).toLocaleString('id-ID')}`;

        document.getElementById('close_shift_expected_cash').innerText = `Rp ${parseInt(activeShift.expected_cash || 0).toLocaleString('id-ID')}`;
        document.getElementById('shift_closing_cash').value = '';
        calculateShiftDifference();
        openModal('closeShiftModal');
    }

    function calculateShiftDifference() {
        if (!activeShift) return;
        const closingVal = parseFloat(document.getElementById('shift_closing_cash').value) || 0;
        const expectedVal = parseFloat(activeShift.expected_cash) || 0;
        const diff = closingVal - expectedVal;
        const badge = document.getElementById('shift_diff_badge');

        if (diff === 0) {
            badge.className = 'font-bold text-emerald-600';
            badge.innerText = 'Rp 0 (Sesuai / Pas)';
        } else if (diff > 0) {
            badge.className = 'font-bold text-blue-600';
            badge.innerText = `+ Rp ${parseInt(diff).toLocaleString('id-ID')} (Lebih)`;
        } else {
            badge.className = 'font-bold text-rose-600';
            badge.innerText = `- Rp ${parseInt(Math.abs(diff)).toLocaleString('id-ID')} (Kurang/Selisih)`;
        }
    }

    // ==========================================
    // SHIFT EXPENSE (KAS KELUAR) HANDLERS
    // ==========================================
    function openShiftExpenseModal() {
        if (!activeShift) {
            showPosToast('warning', 'Silakan buka sesi shift kasir terlebih dahulu.');
            return;
        }
        document.getElementById('shift_expense_amount').value = '';
        document.getElementById('shift_expense_notes').value = '';
        renderShiftExpensesTable();
        openModal('shiftExpenseModal');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function updateHeaderShiftExpenseBadge() {
        const badge = document.getElementById('header_shift_expense_badge');
        if (!badge) return;
        const totalExp = activeShift ? parseFloat(activeShift.total_expenses || 0) : 0;
        if (totalExp > 0) {
            badge.innerText = `-Rp ${parseInt(totalExp).toLocaleString('id-ID')}`;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function renderShiftExpensesTable() {
        const tbody = document.getElementById('shift_expenses_table_body');
        const badge = document.getElementById('shift_expenses_total_badge');
        updateHeaderShiftExpenseBadge();
        if (!tbody || !activeShift) return;

        const expenses = activeShift.expenses || [];
        const totalExp = parseFloat(activeShift.total_expenses || 0);

        if (badge) {
            badge.innerText = `Total: Rp ${parseInt(totalExp).toLocaleString('id-ID')}`;
        }

        if (expenses.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="py-6 text-center text-slate-400 text-xs">
                        Belum ada pengeluaran yang dicatat pada shift ini.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        expenses.forEach(exp => {
            const timeStr = exp.expense_date ? new Date(exp.expense_date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';
            html += `
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="py-2 px-3">
                        <div class="font-bold text-slate-900">${escapeHtml(exp.category)}</div>
                        <div class="text-[11px] text-slate-500">${escapeHtml(exp.notes || '-')}</div>
                        ${timeStr ? `<div class="text-[9px] text-slate-400 mt-0.5">${timeStr}</div>` : ''}
                    </td>
                    <td class="py-2 px-3 text-right font-black text-rose-600 font-mono-num whitespace-nowrap">
                        - Rp ${parseInt(exp.amount).toLocaleString('id-ID')}
                    </td>
                    <td class="py-2 px-2 text-center">
                        <button type="button" onclick="handleDeleteShiftExpense(${exp.id})" title="Hapus Pengeluaran" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    async function handleStoreShiftExpense(e) {
        e.preventDefault();
        if (!activeShift) return;

        const amount = document.getElementById('shift_expense_amount').value;
        const category = document.getElementById('shift_expense_category').value;
        const notes = document.getElementById('shift_expense_notes').value;
        const btn = document.getElementById('submitShiftExpenseBtn');

        if (!amount || parseFloat(amount) <= 0) {
            showPosToast('warning', 'Masukkan nominal pengeluaran yang valid.');
            return;
        }

        try {
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<span class="animate-spin inline-block mr-1">⏳</span> Menyimpan...`;
            }

            const res = await fetch(`/shifts/${activeShift.id}/expenses`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ amount, category, notes })
            });

            const data = await res.json();
            if (data.status === 'success') {
                activeShift = data.data;
                document.getElementById('shift_expense_amount').value = '';
                document.getElementById('shift_expense_notes').value = '';
                renderShiftExpensesTable();
                showPosToast('success', data.message || 'Pengeluaran berhasil dicatat.');
            } else {
                showPosAlert('error', 'Gagal Mencatat Pengeluaran', data.message);
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i> <span>Simpan Pengeluaran Kasir</span>`;
                if (window.lucide) lucide.createIcons();
            }
        }
    }

    async function handleDeleteShiftExpense(expenseId) {
        if (!activeShift) return;

        const result = await Swal.fire({
            title: 'Hapus Pengeluaran?',
            text: 'Pengeluaran ini akan dibatalkan dan kas sistem akan disesuaikan kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            scrollbarPadding: false,
            heightAuto: false
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch(`/shifts/${activeShift.id}/expenses/${expenseId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (data.status === 'success') {
                activeShift = data.data;
                renderShiftExpensesTable();
                showPosToast('success', 'Pengeluaran kasir berhasil dihapus.');
            } else {
                showPosAlert('error', 'Gagal Menghapus Pengeluaran', data.message);
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        }
    }

    async function handleCloseShift(e) {
        e.preventDefault();
        const closingCash = document.getElementById('shift_closing_cash').value;
        const notes = document.getElementById('shift_close_notes').value;

        try {
            const res = await fetch(`/shifts/${activeShift.id}/close`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ closing_cash: closingCash, notes })
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal('closeShiftModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Shift Ditutup!',
                    text: 'Sesi kasir berhasil direkap dan ditutup.',
                    scrollbarPadding: false,
                    heightAuto: false
                }).then(() => location.reload());
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, scrollbarPadding: false, heightAuto: false });
        }
    }

    // Load Products via AJAX
    async function loadProducts(query = '', categoryId = selectedCategoryId) {
        try {
            const url = `/pos/products?q=${encodeURIComponent(query)}&category_id=${categoryId || ''}&warehouse_id=${currentWarehouseId}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.status === 'success') {
                allProducts = data.data;
                renderProductGrid();
            }
        } catch (err) {
            console.error('Failed to load products:', err);
        }
    }

    function filterCategory(catId, btn) {
        selectedCategoryId = catId;
        document.querySelectorAll('.category-pill-btn').forEach(b => {
            b.className = 'category-pill-btn px-4 py-1.5 rounded-xl text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200/80 whitespace-nowrap transition';
        });
        btn.className = 'category-pill-btn px-4 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-brand-500 to-amber-500 text-white shadow-xs whitespace-nowrap';
        loadProducts('', catId);
    }

    function renderProductGrid() {
        const grid = document.getElementById('posProductGrid');
        const empty = document.getElementById('productEmptyState');
        grid.innerHTML = '';

        if (allProducts.length === 0) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
            return;
        }

        empty.classList.add('hidden');
        empty.classList.remove('flex');

        allProducts.forEach(prod => {
            const stockItem = prod.stocks && prod.stocks.length > 0 ? prod.stocks[0] : null;
            const stockQty = stockItem ? parseFloat(stockItem.quantity) : 0;
            const hasTier = prod.tiered_prices && prod.tiered_prices.length > 0;
            const hasMultiUnit = prod.conversions && prod.conversions.length > 0;

            const card = document.createElement('div');
            card.className = 'group bg-white hover:bg-slate-50 border border-slate-200/90 hover:border-brand-500/60 rounded-2xl p-3 flex flex-col justify-between cursor-pointer transition shadow-2xs hover:shadow-md hover:shadow-brand-500/5 active:scale-[0.98]';
            card.onclick = () => onProductCardClick(prod);

            card.innerHTML = `
                <div>
                    <div class="aspect-square bg-slate-50 rounded-xl mb-2.5 flex items-center justify-center relative overflow-hidden border border-slate-100">
                        ${prod.image_path ? `<img src="/storage/${prod.image_path}" class="w-full h-full object-cover">` : `<i data-lucide="package" class="w-8 h-8 text-slate-300 group-hover:text-brand-500 transition"></i>`}
                        <span class="absolute top-1.5 left-1.5 px-1.5 py-0.2 rounded text-[9px] font-bold ${stockQty <= 0 ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-white/90 text-slate-700 border border-slate-200'} shadow-2xs">
                            Stk: ${stockQty}
                        </span>
                        ${hasTier ? `<span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Grosir</span>` : ''}
                        ${hasMultiUnit ? `<span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.2 rounded text-[8px] font-bold bg-brand-50 text-brand-600 border border-brand-200">Multi-Unit</span>` : ''}
                    </div>
                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-tight">${prod.name}</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5 font-mono-num">${prod.barcode || prod.code}</p>
                </div>
                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 font-mono-num">Rp ${parseInt(prod.selling_price).toLocaleString('id-ID')}</span>
                    <span class="text-[10px] text-slate-400 font-medium">/ ${prod.base_unit ? prod.base_unit.short_name : 'pcs'}</span>
                </div>
            `;
            grid.appendChild(card);
        });
        lucide.createIcons();
    }

    // Toggle Setting: Modal Input Qty & Harga Manual
    let isManualPriceModalEnabled = localStorage.getItem('pos_manual_price_modal') === 'false' ? false : true;

    function initManualPriceSettingUI() {
        const btn = document.getElementById('toggleManualPriceBtn');
        const statusText = document.getElementById('manualPriceStatusText');
        const icon = document.getElementById('manualPriceIcon');

        if (isManualPriceModalEnabled) {
            btn.className = 'flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 text-xs font-bold transition shadow-2xs whitespace-nowrap cursor-pointer';
            statusText.innerText = 'ON';
            statusText.className = 'text-brand-700 font-bold';
            icon.className = 'w-3.5 h-3.5 text-brand-600';
        } else {
            btn.className = 'flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 text-xs font-bold transition shadow-2xs whitespace-nowrap cursor-pointer';
            statusText.innerText = 'OFF (Klik Cepat)';
            statusText.className = 'text-slate-500 font-bold';
            icon.className = 'w-3.5 h-3.5 text-slate-400';
        }
    }

    function toggleManualPriceSetting() {
        isManualPriceModalEnabled = !isManualPriceModalEnabled;
        localStorage.setItem('pos_manual_price_modal', isManualPriceModalEnabled ? 'true' : 'false');
        initManualPriceSettingUI();
    }

    // Product Card Click Logic
    function onProductCardClick(product) {
        if (isManualPriceModalEnabled) {
            openItemModal(product);
        } else {
            addToCart(product, null, 1);
        }
    }

    // State for Current Item in Modal (Supports both new item and editing existing cart item)
    let modalCurrentProduct = null;
    let modalUnitsList = [];
    let modalEditingCartIndex = null;

    async function openItemModal(product, existingCartIndex = null) {
        modalCurrentProduct = product;
        modalEditingCartIndex = existingCartIndex;

        const isEditing = existingCartIndex !== null;
        const currentCartItem = isEditing ? cart[existingCartIndex] : null;

        const stockItem = product.stocks && product.stocks.length > 0 ? product.stocks[0] : null;
        const stockQty = stockItem ? parseFloat(stockItem.quantity) : 0;

        document.getElementById('modal_product_name').innerText = product.name;
        document.getElementById('modal_product_code').innerText = product.code;
        document.getElementById('modal_product_stock').innerText = `Stok: ${stockQty}`;
        document.getElementById('modal_submit_btn_text').innerText = isEditing ? 'Simpan Perubahan Item' : 'Masukkan ke Keranjang';

        // Image
        const imgBox = document.getElementById('modal_product_img_box');
        if (product.image_path) {
            imgBox.innerHTML = `<img src="/storage/${product.image_path}" class="w-full h-full object-cover">`;
        } else {
            imgBox.innerHTML = `<i data-lucide="package" class="w-5 h-5 text-brand-500"></i>`;
        }

        // Build units
        modalUnitsList = [{ id: product.base_unit_id, name: product.base_unit ? product.base_unit.name : 'Pcs', short_name: product.base_unit ? product.base_unit.short_name : 'pcs', ratio: 1 }];
        if (product.conversions) {
            product.conversions.forEach(c => {
                if (c.from_unit) {
                    modalUnitsList.push({ id: c.from_unit_id, name: c.from_unit.name, short_name: c.from_unit.short_name, ratio: parseFloat(c.conversion_value) });
                }
            });
        }

        const unitSelect = document.getElementById('modal_item_unit');
        unitSelect.innerHTML = '';
        const selectedUnitId = isEditing ? currentCartItem.unit_id : product.base_unit_id;
        modalUnitsList.forEach(u => {
            unitSelect.innerHTML += `<option value="${u.id}" ${u.id === selectedUnitId ? 'selected' : ''}>${u.name}</option>`;
        });

        // Initial Qty & Price
        if (isEditing) {
            document.getElementById('modal_item_qty').value = currentCartItem.quantity;
            document.getElementById('modal_item_price').value = currentCartItem.base_price !== undefined ? currentCartItem.base_price : currentCartItem.price;
        } else {
            document.getElementById('modal_item_qty').value = '1';
            await resolveModalPrice();
        }

        // Render Modifier Groups & Toppings
        renderModalModifiersUI(product, currentCartItem);

        // Notes per item
        const notesInput = document.getElementById('modal_item_notes');
        if (notesInput) {
            notesInput.value = isEditing ? (currentCartItem.notes || '') : '';
        }

        calculateModalSubtotal();

        openModal('itemModal');
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Auto focus Qty
        setTimeout(() => {
            const qtyIn = document.getElementById('modal_item_qty');
            if (qtyIn) {
                qtyIn.focus();
                qtyIn.select();
            }
        }, 100);
    }

    function renderModalModifiersUI(product, currentCartItem = null) {
        const wrapper = document.getElementById('modal_modifiers_wrapper');
        const container = document.getElementById('modal_modifiers_container');
        if (!wrapper || !container) return;

        const modifierGroups = product.modifier_groups || [];
        if (modifierGroups.length === 0) {
            wrapper.classList.add('hidden');
            container.innerHTML = '';
            return;
        }

        wrapper.classList.remove('hidden');
        let html = '';

        const selectedModIds = currentCartItem && currentCartItem.modifiers ? currentCartItem.modifiers.map(m => m.id) : [];

        modifierGroups.forEach(group => {
            const isSingle = group.selection_type === 'single';
            const isRequired = group.is_required;
            const modifiers = group.modifiers || [];

            html += `
                <div class="p-3 bg-slate-50 border border-slate-200/90 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-800">${group.name}</span>
                            ${isRequired ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-50 text-rose-600 border border-rose-200">Wajib</span>' : '<span class="px-1.5 py-0.2 rounded text-[9px] font-medium text-slate-400">Opsional</span>'}
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">
                            ${isSingle ? 'Pilih 1' : 'Bisa lebih dari 1'}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
            `;

            modifiers.forEach((mod, modIdx) => {
                const priceAdj = parseFloat(mod.price_adjustment) || 0;
                let isChecked = false;
                if (currentCartItem) {
                    isChecked = selectedModIds.includes(mod.id);
                } else {
                    isChecked = isSingle ? (mod.is_default || modIdx === 0) : mod.is_default;
                }

                if (isSingle) {
                    html += `
                        <label class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200 hover:border-brand-500 hover:bg-brand-50/20 cursor-pointer transition text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <input type="radio" 
                                       name="mod_group_${group.id}" 
                                       value="${mod.id}" 
                                       data-group-id="${group.id}"
                                       data-group-name="${group.name}"
                                       data-group-required="${isRequired ? '1' : '0'}"
                                       data-mod-id="${mod.id}" 
                                       data-name="${mod.name}" 
                                       data-price="${priceAdj}" 
                                       onchange="calculateModalSubtotal()"
                                       ${isChecked ? 'checked' : ''}
                                       class="text-brand-600 focus:ring-brand-500">
                                <span class="font-medium text-slate-800 truncate">${mod.name}</span>
                            </div>
                            <span class="text-[10px] font-bold ${priceAdj > 0 ? 'text-amber-600 font-mono' : 'text-slate-400'} shrink-0 ml-1">
                                ${priceAdj > 0 ? '+Rp ' + Math.round(priceAdj).toLocaleString('id-ID') : 'Rp 0'}
                            </span>
                        </label>
                    `;
                } else {
                    html += `
                        <label class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200 hover:border-brand-500 hover:bg-brand-50/20 cursor-pointer transition text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <input type="checkbox" 
                                       name="mod_group_${group.id}[]" 
                                       value="${mod.id}" 
                                       data-group-id="${group.id}"
                                       data-group-name="${group.name}"
                                       data-group-required="${isRequired ? '1' : '0'}"
                                       data-mod-id="${mod.id}" 
                                       data-name="${mod.name}" 
                                       data-price="${priceAdj}" 
                                       onchange="calculateModalSubtotal()"
                                       ${isChecked ? 'checked' : ''}
                                       class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="font-medium text-slate-800 truncate">${mod.name}</span>
                            </div>
                            <span class="text-[10px] font-bold ${priceAdj > 0 ? 'text-amber-600 font-mono' : 'text-slate-400'} shrink-0 ml-1">
                                ${priceAdj > 0 ? '+Rp ' + Math.round(priceAdj).toLocaleString('id-ID') : 'Rp 0'}
                            </span>
                        </label>
                    `;
                }
            });

            html += `
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function onModalUnitChange() {
        await resolveModalPrice();
        calculateModalSubtotal();
    }

    async function resolveModalPrice() {
        if (!modalCurrentProduct) return;
        const unitId = document.getElementById('modal_item_unit').value;
        const qty = parseFloat(document.getElementById('modal_item_qty').value) || 1;
        const customerId = document.getElementById('posCustomerSelect').value;

        try {
            const res = await fetch(`/products/${modalCurrentProduct.id}/get-price?unit_id=${unitId}&quantity=${qty}&customer_id=${customerId || ''}`);
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('modal_item_price').value = data.data.final_unit_price;
            }
        } catch (err) {
            console.error(err);
        }
    }

    function adjustModalQty(delta) {
        const qtyIn = document.getElementById('modal_item_qty');
        let current = parseFloat(qtyIn.value) || 1;
        let next = Math.max(0.0001, current + delta);
        qtyIn.value = next;
        calculateModalSubtotal();
    }

    function calculateModalSubtotal() {
        const qty = parseFloat(document.getElementById('modal_item_qty').value) || 0;
        const basePrice = parseFloat(document.getElementById('modal_item_price').value) || 0;
        
        let modSum = 0;
        document.querySelectorAll('#modal_modifiers_container input:checked').forEach(inp => {
            modSum += parseFloat(inp.getAttribute('data-price')) || 0;
        });

        const unitTotal = basePrice + modSum;
        const subtotal = qty * unitTotal;

        if (modSum > 0) {
            document.getElementById('modal_subtotal_calc_text').innerText = `${qty} x (Rp ${Math.round(basePrice).toLocaleString('id-ID')} + Rp ${Math.round(modSum).toLocaleString('id-ID')} Topping)`;
        } else {
            document.getElementById('modal_subtotal_calc_text').innerText = `${qty} x Rp ${Math.round(basePrice).toLocaleString('id-ID')}`;
        }
        document.getElementById('modal_item_subtotal_display').innerText = `Rp ${Math.round(subtotal).toLocaleString('id-ID')}`;
    }

    async function handleItemModalSubmit(e) {
        e.preventDefault();
        if (!modalCurrentProduct) return;

        const unitId = parseInt(document.getElementById('modal_item_unit').value);
        const qty = parseFloat(document.getElementById('modal_item_qty').value) || 1;
        const basePrice = parseFloat(document.getElementById('modal_item_price').value) || 0;
        const itemNotes = document.getElementById('modal_item_notes') ? document.getElementById('modal_item_notes').value.trim() : '';

        // Collect Selected Modifiers
        const selectedModifiers = [];
        let modSum = 0;
        document.querySelectorAll('#modal_modifiers_container input:checked').forEach(inp => {
            const priceAdj = parseFloat(inp.getAttribute('data-price')) || 0;
            modSum += priceAdj;
            selectedModifiers.push({
                id: parseInt(inp.value),
                name: inp.getAttribute('data-name'),
                price_adjustment: priceAdj,
                group_id: parseInt(inp.getAttribute('data-group-id'))
            });
        });

        const finalUnitPrice = basePrice + modSum;

        if (modalEditingCartIndex !== null && cart[modalEditingCartIndex]) {
            // Update existing cart item
            cart[modalEditingCartIndex].unit_id = unitId;
            cart[modalEditingCartIndex].quantity = qty;
            cart[modalEditingCartIndex].base_price = basePrice;
            cart[modalEditingCartIndex].price = finalUnitPrice;
            cart[modalEditingCartIndex].modifiers = selectedModifiers;
            cart[modalEditingCartIndex].notes = itemNotes;
            cart[modalEditingCartIndex].is_custom_price = true;
            await recalculateCartPrices();
            renderCart();
        } else {
            // Add new to cart with custom price, modifiers, and notes
            await addToCartWithCustomPrice(modalCurrentProduct, unitId, qty, basePrice, finalUnitPrice, selectedModifiers, itemNotes, modalUnitsList);
        }

        closeModal('itemModal');
    }

    async function addToCartWithCustomPrice(product, unitId, qty, basePrice, finalUnitPrice, modifiers = [], notes = '', unitsList = null) {
        // If has modifiers or notes, treat as distinct cart line
        const hasCustomizations = (modifiers && modifiers.length > 0) || (notes && notes.length > 0);
        
        let existingIdx = -1;
        if (!hasCustomizations) {
            existingIdx = cart.findIndex(i => i.product.id === product.id && i.unit_id === unitId && (!i.modifiers || i.modifiers.length === 0) && !i.notes);
        }

        if (existingIdx > -1) {
            cart[existingIdx].quantity += qty;
            cart[existingIdx].base_price = basePrice;
            cart[existingIdx].price = finalUnitPrice;
            cart[existingIdx].is_custom_price = true;
        } else {
            cart.push({
                product,
                unit_id: unitId,
                quantity: qty,
                base_price: basePrice,
                price: finalUnitPrice,
                modifiers: modifiers || [],
                notes: notes || '',
                is_custom_price: true,
                unitsList: unitsList || [{ id: product.base_unit_id, name: product.base_unit ? product.base_unit.name : 'Pcs', short_name: product.base_unit ? product.base_unit.short_name : 'pcs', ratio: 1 }]
            });
        }

        await recalculateCartPrices();
        renderCart();
    }

    // Barcode Scan & Search Handler
    function handleBarcodeScan(barcode) {
        const scannerInput = document.getElementById('barcodeScannerInput');
        
        // Jika input kosong lalu ditekan Enter -> Tampilkan seluruh produk
        if (!barcode) {
            loadProducts('');
            return;
        }

        // Cari apakah ada barcode yang persis cocok
        const matched = allProducts.find(p => p.barcode === barcode || (p.barcodes && p.barcodes.some(b => b.barcode === barcode)));
        if (matched) {
            addToCart(matched);
            scannerInput.value = ''; // Kosongkan setelah barcode berhasil dimasukkan ke cart
            loadProducts(''); // Kembalikan katalog produk lengkap
        } else {
            // Jika bukan barcode fisik (misal pencarian teks manual nama barang), filter daftar produk
            loadProducts(barcode);
        }
    }

    // Cart Management
    async function addToCart(product, unitId = null, qty = 1) {
        const uId = unitId || product.base_unit_id;
        const existingIdx = cart.findIndex(i => i.product.id === product.id && i.unit_id === uId);

        if (existingIdx > -1) {
            cart[existingIdx].quantity += qty;
        } else {
            // Build available units array (base unit + converted units)
            const unitsList = [{ id: product.base_unit_id, name: product.base_unit ? product.base_unit.name : 'Pcs', short_name: product.base_unit ? product.base_unit.short_name : 'pcs', ratio: 1 }];
            if (product.conversions) {
                product.conversions.forEach(c => {
                    if (c.from_unit) {
                        unitsList.push({ id: c.from_unit_id, name: c.from_unit.name, short_name: c.from_unit.short_name, ratio: parseFloat(c.conversion_value) });
                    }
                });
            }

            cart.push({
                product,
                unit_id: uId,
                quantity: qty,
                price: parseFloat(product.selling_price),
                unitsList
            });
        }

        await recalculateCartPrices();
        renderCart();
    }

    function updateCartQty(index, delta) {
        let current = parseFloat(cart[index].quantity) || 0;
        let next = current + delta;
        setCartQty(index, next);
    }

    function setCartQty(index, val) {
        let parsed = parseFloat(val);
        if (isNaN(parsed) || parsed <= 0) {
            cart.splice(index, 1);
        } else {
            // Support precision up to 4 decimal places without trailing zeros
            cart[index].quantity = Math.round(parsed * 10000) / 10000;
        }
        recalculateCartPrices().then(renderCart);
    }

    function updateCartUnit(index, newUnitId) {
        cart[index].unit_id = parseInt(newUnitId);
        recalculateCartPrices().then(renderCart);
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        recalculateCartPrices().then(renderCart);
    }

    let cartCalculationResult = {
        subtotal: 0,
        total_discount: 0,
        grand_total: 0,
        item_discounts: [],
        invoice_discounts: [],
        free_rewards: []
    };

    function clearCart() {
        cart = [];
        appliedPromoCode = '';
        appliedManualDiscount = 0;
        selectedServiceType = 'takeaway';
        selectedTable = null;
        const dCode = document.getElementById('discount_promo_code');
        const dAmt = document.getElementById('discount_manual_amount');
        if (dCode) dCode.value = '';
        if (dAmt) dAmt.value = '';
        cartCalculationResult = {
            subtotal: 0,
            total_discount: 0,
            grand_total: 0,
            item_discounts: [],
            invoice_discounts: [],
            free_rewards: []
        };
        renderCart();
    }

    // Dynamic Price Recalculation (PricingService & Discounts via AJAX)
    async function recalculateCartPrices() {
        if (cart.length === 0) {
            cartCalculationResult = {
                subtotal: 0,
                total_discount: 0,
                grand_total: 0,
                item_discounts: [],
                invoice_discounts: [],
                free_rewards: []
            };
            return;
        }

        const customerId = document.getElementById('posCustomerSelect').value;

        // 1. Resolve individual item pricing & wholesale/tiered pricing
        for (let i = 0; i < cart.length; i++) {
            const item = cart[i];
            if (item.is_custom_price) continue; // preserve manual custom price

            try {
                const res = await fetch(`/products/${item.product.id}/get-price?unit_id=${item.unit_id}&quantity=${item.quantity}&customer_id=${customerId || ''}`);
                const data = await res.json();
                if (data.status === 'success') {
                    item.price = data.data.final_unit_price;
                    item.regular_price = data.data.regular_unit_price;
                    item.is_tiered = data.data.is_tiered_applied;
                    item.discount_amount = data.data.discount_amount;
                }
            } catch (err) {
                console.error(err);
            }
        }

        // 2. Call backend DiscountService for real-time automatic discounts, invoice promos, and Buy X Get Y
        try {
            const res = await fetch('{{ route("pos.calculate-cart") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    customer_id: customerId || null,
                    items: cart.map(item => ({
                        product_id: item.product.id,
                        unit_id: item.unit_id,
                        quantity: item.quantity,
                        price: item.price
                    })),
                    promo_code: appliedPromoCode || null,
                    manual_discount: appliedManualDiscount || 0
                })
            });

            const data = await res.json();
            if (data.status === 'success') {
                cartCalculationResult = data.data;
            }
        } catch (err) {
            console.error('Failed to calculate discounts:', err);
        }
    }

    function onCustomerChange() {
        recalculateCartPrices().then(renderCart);
    }

    function renderCart() {
        const container = document.getElementById('cartItemsContainer');
        const emptyState = document.getElementById('cartEmptyState');
        const countBadge = document.getElementById('cartCountBadge');
        
        container.querySelectorAll('.cart-item-row').forEach(el => el.remove());

        if (cart.length === 0) {
            emptyState.classList.remove('hidden');
            countBadge.innerText = '0 Item';
            updateSummary(0, 0, 0);
            return;
        }

        emptyState.classList.add('hidden');
        countBadge.innerText = `${cart.length} Item`;

        let subtotal = 0;
        let totalQty = 0;

        cart.forEach((item, idx) => {
            const lineSubtotal = item.price * item.quantity;
            subtotal += lineSubtotal;
            totalQty += item.quantity;

            let unitOptions = '';
            item.unitsList.forEach(u => {
                unitOptions += `<option value="${u.id}" ${u.id === item.unit_id ? 'selected' : ''}>${u.name}</option>`;
            });

            // Product image thumbnail / icon placeholder
            const imgHtml = item.product.image_path 
                ? `<img src="/storage/${item.product.image_path}" class="w-full h-full object-cover rounded-lg">`
                : `<i data-lucide="package" class="w-5 h-5 text-slate-400"></i>`;

            // Check if there are item-specific discounts from DiscountService
            const itemDiscounts = (cartCalculationResult.item_discounts || []).filter(d => d.cart_index === idx);
            let itemDiscountsHtml = '';
            let totalItemDisc = 0;

            if (itemDiscounts.length > 0) {
                itemDiscounts.forEach(d => {
                    totalItemDisc += parseFloat(d.amount);
                    itemDiscountsHtml += `
                        <div class="flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/70 mt-1">
                            <i data-lucide="tag" class="w-3 h-3 text-emerald-600 shrink-0"></i>
                            <span class="truncate">${d.discount_name} (-Rp ${parseInt(d.amount).toLocaleString('id-ID')})</span>
                        </div>
                    `;
                });
            }

            // Render Modifier Badges
            let modifiersHtml = '';
            if (item.modifiers && item.modifiers.length > 0) {
                modifiersHtml = '<div class="flex flex-wrap gap-1 mt-1">';
                item.modifiers.forEach(m => {
                    const adjText = m.price_adjustment > 0 ? ` (+Rp ${Math.round(m.price_adjustment).toLocaleString('id-ID')})` : '';
                    modifiersHtml += `<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-200">+ ${m.name}${adjText}</span>`;
                });
                modifiersHtml += '</div>';
            }

            // Render Item Note
            let notesHtml = '';
            if (item.notes && item.notes.trim() !== '') {
                notesHtml = `
                    <div class="flex items-center gap-1 text-[10px] text-slate-500 italic mt-0.5">
                        <i data-lucide="message-square" class="w-3 h-3 text-slate-400 shrink-0"></i>
                        <span class="truncate">${item.notes}</span>
                    </div>
                `;
            }

            const netLineTotal = Math.max(0, lineSubtotal - totalItemDisc);

            const row = document.createElement('div');
            row.className = 'cart-item-row p-3 bg-white border border-slate-200/90 hover:border-brand-500/60 hover:shadow-md hover:shadow-brand-500/5 rounded-2xl transition space-y-2 shadow-2xs cursor-pointer group';
            row.onclick = () => openItemModal(cart[idx].product, idx);

            row.innerHTML = `
                <div class="flex items-start gap-2.5">
                    <!-- Product Thumbnail -->
                    <div class="w-11 h-11 rounded-xl bg-slate-50 border border-slate-100 shrink-0 flex items-center justify-center overflow-hidden">
                        ${imgHtml}
                    </div>

                    <!-- Details -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <div class="flex items-center gap-1.5 min-w-0 flex-wrap">
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-brand-600 transition truncate">${item.product.name}</h5>
                                ${item.is_tiered ? `<span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Grosir</span>` : ''}
                                ${item.is_custom_price ? `<span class="px-1.5 py-0.2 rounded text-[8px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">Custom</span>` : ''}
                            </div>
                            <button onclick="event.stopPropagation(); removeCartItem(${idx});" class="text-slate-400 hover:text-rose-500 p-0.5 transition shrink-0 cursor-pointer" title="Hapus Item">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                            <span class="font-bold text-slate-800 font-mono-num">@ Rp ${parseInt(item.price).toLocaleString('id-ID')}</span>
                            <select onclick="event.stopPropagation();" onchange="updateCartUnit(${idx}, this.value)" class="bg-slate-50 text-[10px] font-bold text-brand-600 px-2 py-0.5 rounded-md border border-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                                ${unitOptions}
                            </select>
                        </div>
                        ${modifiersHtml}
                        ${notesHtml}
                        ${itemDiscountsHtml}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <!-- Manual & Decimal Stepper Input -->
                    <div onclick="event.stopPropagation();" class="flex items-center gap-1 bg-slate-50 border border-slate-200 rounded-xl p-0.5">
                        <button onclick="updateCartQty(${idx}, -1)" class="w-6 h-6 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70 cursor-pointer">-</button>
                        <input 
                            type="number" 
                            step="any" 
                            min="0.0001" 
                            value="${item.quantity}" 
                            onchange="setCartQty(${idx}, this.value)" 
                            onkeydown="if(event.key==='Enter'){this.blur();}" 
                            class="w-14 text-center bg-transparent border-0 p-0 text-xs font-bold text-slate-900 font-mono-num focus:ring-0 focus:outline-none cursor-text"
                            title="Ketik angka desimal (misal 0.5, 1.25) lalu Enter"
                        >
                        <button onclick="updateCartQty(${idx}, 1)" class="w-6 h-6 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70 cursor-pointer">+</button>
                    </div>
                    <div class="text-right">
                        ${totalItemDisc > 0 ? `<span class="text-[10px] text-slate-400 line-through block font-mono-num">Rp ${parseInt(lineSubtotal).toLocaleString('id-ID')}</span>` : ''}
                        <span class="text-xs font-black text-slate-900 font-mono-num">Rp ${parseInt(netLineTotal).toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;
            container.appendChild(row);
        });

        // Render Free Rewards (Buy X Get Y)
        if (cartCalculationResult.free_rewards && cartCalculationResult.free_rewards.length > 0) {
            cartCalculationResult.free_rewards.forEach(reward => {
                const rewardRow = document.createElement('div');
                rewardRow.className = 'cart-item-row p-3 bg-gradient-to-r from-amber-50 to-orange-50/80 border border-amber-200 rounded-2xl flex items-center justify-between shadow-2xs';
                rewardRow.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="gift" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <h6 class="text-xs font-bold text-slate-900 truncate">${reward.product_name}</h6>
                            <p class="text-[10px] text-amber-700 font-semibold truncate">${reward.discount_name} • Qty: ${reward.quantity}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white tracking-wider shrink-0 shadow-xs">GRATIS</span>
                `;
                container.appendChild(rewardRow);
            });
        }

        // Render Active Invoice Discounts (if any)
        if (cartCalculationResult.invoice_discounts && cartCalculationResult.invoice_discounts.length > 0) {
            cartCalculationResult.invoice_discounts.forEach(invDisc => {
                const invRow = document.createElement('div');
                invRow.className = 'cart-item-row p-2.5 bg-emerald-50/80 border border-emerald-200/80 rounded-xl flex items-center justify-between shadow-2xs text-xs';
                invRow.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="p-1 rounded-lg bg-emerald-100 text-emerald-700"><i data-lucide="percent" class="w-3.5 h-3.5"></i></span>
                        <div>
                            <span class="font-bold text-emerald-900">${invDisc.discount_name}</span>
                            <span class="text-[10px] text-emerald-600 block">${invDisc.type === 'percentage' ? invDisc.value + '% Diskon Faktur' : 'Potongan Faktur'}</span>
                        </div>
                    </div>
                    <span class="font-bold text-emerald-700 font-mono-num">- Rp ${parseInt(invDisc.amount).toLocaleString('id-ID')}</span>
                `;
                container.appendChild(invRow);
            });
        }

        const calculatedSubtotal = (cartCalculationResult && cartCalculationResult.subtotal > 0)
            ? cartCalculationResult.subtotal 
            : subtotal;
        const calculatedDiscount = (cartCalculationResult && cartCalculationResult.total_discount !== undefined) 
            ? cartCalculationResult.total_discount 
            : 0;

        updateSummary(
            calculatedSubtotal,
            calculatedDiscount,
            totalQty
        );
        lucide.createIcons();
    }

    function updateSummary(subtotal, discount, qty) {
        let grandTotal = Math.max(0, subtotal - discount);
        if (cartCalculationResult && cartCalculationResult.grand_total > 0) {
            grandTotal = cartCalculationResult.grand_total;
        }

        document.getElementById('cartSubtotalText').innerText = `Rp ${parseInt(subtotal).toLocaleString('id-ID')}`;
        document.getElementById('cartDiscountText').innerText = `- Rp ${parseInt(discount).toLocaleString('id-ID')}`;
        document.getElementById('cartTotalQtyText').innerText = `${qty} Qty Total`;
        document.getElementById('cartGrandTotalText').innerText = `Rp ${parseInt(grandTotal).toLocaleString('id-ID')}`;
        window.currentCartGrandTotal = grandTotal;
    }

    // Service Type Handlers in Payment Modal
    function onPaymentServiceTypeChange(type) {
        selectedServiceType = type;
        const types = ['takeaway', 'dine_in', 'delivery'];
        types.forEach(t => {
            const card = document.getElementById(`pay_service_card_${t}`);
            const radio = card ? card.querySelector('input') : null;
            const icon = card ? card.querySelector('i') : null;
            if (card && radio) {
                if (t === type) {
                    radio.checked = true;
                    card.className = 'pay-service-card flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-brand-500 bg-brand-50/70 text-brand-700 font-bold text-xs cursor-pointer transition shadow-2xs';
                    if (icon) icon.className = 'w-4 h-4 text-brand-600';
                } else {
                    card.className = 'pay-service-card flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-700 font-bold text-xs cursor-pointer transition shadow-2xs';
                    if (icon) icon.className = 'w-4 h-4 text-slate-500';
                }
            }
        });

        const tableSec = document.getElementById('pay_dine_in_table_section');
        if (tableSec) {
            if (type === 'dine_in') {
                tableSec.classList.remove('hidden');
                updatePaymentTableDisplay();
            } else {
                tableSec.classList.add('hidden');
            }
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function updatePaymentTableDisplay() {
        const textEl = document.getElementById('pay_table_selected_text');
        if (!textEl) return;
        if (selectedTable) {
            textEl.innerText = `Meja ${selectedTable.table_number} (${guestCount} Tamu)`;
        } else {
            textEl.innerText = `Tanpa Meja (${guestCount} Tamu)`;
        }
    }

    // Payment Dialog & Checkout Execution
    function renderSmartCashPresets(total) {
        const container = document.getElementById('quick_cash_pills_container');
        if (!container) return;

        const numTotal = Math.max(0, parseFloat(total) || 0);
        const presets = new Set();

        // 1. Uang pas selalu ada sebagai tombol pertama
        let html = `<button type="button" onclick="setCashAmount('exact')" class="px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold transition">Uang Pas</button>`;

        if (numTotal > 0) {
            // Pembulatan ke 5.000 terdekat jika bukan kelipatan 5.000
            const ceil5k = Math.ceil(numTotal / 5000) * 5000;
            if (ceil5k > numTotal) presets.add(ceil5k);

            // Pembulatan ke 10.000 terdekat
            const ceil10k = Math.ceil(numTotal / 10000) * 10000;
            if (ceil10k > numTotal) presets.add(ceil10k);

            // Pembulatan ke 20.000 terdekat
            const ceil20k = Math.ceil(numTotal / 20000) * 20000;
            if (ceil20k > numTotal) presets.add(ceil20k);

            // Pembulatan ke 50.000 terdekat
            const ceil50k = Math.ceil(numTotal / 50000) * 50000;
            if (ceil50k > numTotal) presets.add(ceil50k);

            // Pembulatan ke 100.000 terdekat
            const ceil100k = Math.ceil(numTotal / 100000) * 100000;
            if (ceil100k > numTotal) presets.add(ceil100k);

            // Pecahan uang standar di atas total
            [20000, 50000, 100000].forEach(denom => {
                if (denom > numTotal) presets.add(denom);
            });

            // Urutkan nominal rekomendasi
            const sortedPresets = Array.from(presets).sort((a, b) => a - b).slice(0, 5);
            sortedPresets.forEach(amount => {
                html += `<button type="button" onclick="setCashAmount(${amount})" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition font-mono-num">${parseInt(amount).toLocaleString('id-ID')}</button>`;
            });
        } else {
            [10000, 20000, 50000, 100000].forEach(amount => {
                html += `<button type="button" onclick="setCashAmount(${amount})" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition font-mono-num">${parseInt(amount).toLocaleString('id-ID')}</button>`;
            });
        }

        container.innerHTML = html;
    }

    function openPaymentModal() {
        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Tambahkan produk terlebih dahulu sebelum checkout.', scrollbarPadding: false, heightAuto: false });
            return;
        }

        // Safety fallback if currentCartGrandTotal is somehow 0 or unset but cart has items
        if ((!window.currentCartGrandTotal || window.currentCartGrandTotal <= 0) && cart.length > 0) {
            const rawSubtotal = cart.reduce((sum, item) => sum + ((parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0)), 0);
            window.currentCartGrandTotal = Math.max(0, rawSubtotal - ((cartCalculationResult && cartCalculationResult.total_discount) || 0));
        }

        document.getElementById('pay_item_summary_text').innerText = `${cart.length} Item`;
        document.getElementById('pay_grand_total_display').innerText = `Rp ${parseInt(window.currentCartGrandTotal).toLocaleString('id-ID')}`;
        document.getElementById('pay_cash_received_input').value = window.currentCartGrandTotal;
        renderSmartCashPresets(window.currentCartGrandTotal);
        calculateChangeAmount();
        onPaymentServiceTypeChange(selectedServiceType || 'takeaway');
        openModal('paymentModal');

        // Focus cash input
        setTimeout(() => {
            const cIn = document.getElementById('pay_cash_received_input');
            if (cIn) {
                cIn.focus();
                cIn.select();
            }
        }, 100);
    }

    function openPaymentWithMethod(method) {
        if (cart.length === 0) return openPaymentModal();
        const radio = document.querySelector(`input[name="payment_method"][value="${method}"]`);
        if (radio) radio.checked = true;
        onPaymentMethodChange(method);
        openPaymentModal();
    }

    function onPaymentMethodChange(method) {
        document.querySelectorAll('.payment-method-card').forEach(card => {
            const r = card.querySelector('input');
            if (r.value === method) {
                card.className = 'payment-method-card flex flex-col items-center justify-center p-3 rounded-xl border border-brand-500 bg-brand-50/60 text-brand-700 cursor-pointer transition shadow-2xs';
            } else {
                card.className = 'payment-method-card flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-slate-700 cursor-pointer transition shadow-2xs';
            }
        });

        const cashSec = document.getElementById('cash_input_section');
        const nonCashSec = document.getElementById('non_cash_input_section');
        if (method === 'cash') {
            cashSec.classList.remove('hidden');
            nonCashSec.classList.add('hidden');
        } else {
            cashSec.classList.add('hidden');
            nonCashSec.classList.remove('hidden');
        }
    }

    function setCashAmount(val) {
        if (val === 'exact') {
            document.getElementById('pay_cash_received_input').value = window.currentCartGrandTotal;
        } else {
            document.getElementById('pay_cash_received_input').value = val;
        }
        calculateChangeAmount();
    }

    function calculateChangeAmount() {
        const received = parseFloat(document.getElementById('pay_cash_received_input').value) || 0;
        const total = window.currentCartGrandTotal || 0;
        const change = received - total;

        const changeDisplay = document.getElementById('pay_change_amount_display');
        const changeStatus = document.getElementById('pay_change_status');

        if (change >= 0) {
            changeDisplay.innerText = `Rp ${parseInt(change).toLocaleString('id-ID')}`;
            changeDisplay.className = 'text-xl sm:text-2xl font-black text-emerald-600 font-mono-num';
            changeStatus.innerText = change === 0 ? 'Uang Pas' : 'Kembalian diserahkan ke pelanggan';
        } else {
            changeDisplay.innerText = `- Rp ${parseInt(Math.abs(change)).toLocaleString('id-ID')}`;
            changeDisplay.className = 'text-xl sm:text-2xl font-black text-rose-500 font-mono-num';
            changeStatus.innerText = 'Nominal pembayaran kurang';
        }
    }

    async function handleProcessCheckout(e) {
        e.preventDefault();
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const rawCashReceived = parseFloat(document.getElementById('pay_cash_received_input').value);
        const paidAmount = (!isNaN(rawCashReceived) && rawCashReceived > 0) 
            ? rawCashReceived 
            : window.currentCartGrandTotal;
        const refNo = document.getElementById('pay_reference_number_input').value;
        const notes = document.getElementById('pay_notes_input').value;
        const customerId = document.getElementById('posCustomerSelect').value;

        const payload = {
            warehouse_id: currentWarehouseId,
            customer_id: customerId || null,
            service_type: selectedServiceType,
            dining_table_id: selectedTable ? selectedTable.id : null,
            guest_count: selectedServiceType === 'dine_in' ? guestCount : null,
            items: cart.map(i => ({
                product_id: i.product.id,
                unit_id: i.unit_id,
                quantity: i.quantity,
                price: i.price,
                notes: i.notes || null,
                modifiers: (i.modifiers || []).map(m => ({ id: m.id, name: m.name, price_adjustment: m.price_adjustment }))
            })),
            paid_amount: method === 'cash' ? paidAmount : window.currentCartGrandTotal,
            payment_method: method,
            reference_number: refNo,
            promo_code: appliedPromoCode || null,
            manual_discount: appliedManualDiscount || 0,
            notes: notes
        };

        try {
            const btn = document.getElementById('btn_submit_payment');
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Memproses...</span>`;

            const res = await fetch('{{ route("pos.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Selesaikan & Cetak Struk</span>`;

            if (data.status === 'success') {
                closeModal('paymentModal');
                renderThermalReceipt(data.data);
                openModal('receiptModal');
                clearCart();
                loadProducts(); // refresh stock numbers

                // Direct Auto-Print jika fitur aktif & printer terhubung
                if (window.posBtPrinter && window.posBtPrinter.isConnected && window.posBtPrinter.autoPrint) {
                    printViaBluetooth(true);
                }
            } else {
                showPosAlert('error', 'Gagal Checkout', data.message);
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        }
    }

    // Active receipt references for Bluetooth printing
    let currentReceiptType = null; // 'sale' | 'agent'
    let currentSaleData = null;
    let currentAgentData = null;

    // Thermal Receipt Renderer (58mm / 80mm ESC/POS layout)
    function renderThermalReceipt(sale) {
        currentReceiptType = 'sale';
        currentSaleData = sale;
        currentAgentData = null;

        const paper = document.getElementById('thermal_receipt_paper');
        let itemsHtml = '';
        (sale.items || []).forEach(it => {
            const isFreeReward = parseFloat(it.unit_price) === 0;
            
            let itemModHtml = '';
            if (it.modifiers && it.modifiers.length > 0) {
                itemModHtml = `<div class="text-[9px] text-slate-500 pl-2 font-medium">${it.modifiers.map(m => '+ ' + (m.modifier_name || m.name)).join(', ')}</div>`;
            }
            let itemNoteHtml = '';
            if (it.notes) {
                itemNoteHtml = `<div class="text-[9px] text-slate-400 italic pl-2">"${it.notes}"</div>`;
            }

            itemsHtml += `
                <div class="flex justify-between">
                    <span>${it.product ? it.product.name : 'Item'} ${isFreeReward ? '<b class="text-emerald-700">[GRATIS]</b>' : ''}</span>
                </div>
                ${itemModHtml}
                ${itemNoteHtml}
                <div class="flex justify-between text-slate-500 text-[10px]">
                    <span>${it.quantity} x ${parseInt(it.unit_price).toLocaleString('id-ID')} ${it.discount_amount > 0 ? `(Disc: -Rp ${parseInt(it.discount_amount).toLocaleString('id-ID')})` : ''}</span>
                    <span class="font-bold text-slate-800">Rp ${parseInt(it.subtotal).toLocaleString('id-ID')}</span>
                </div>
            `;
        });

        const serviceTypeLabel = sale.service_type ? sale.service_type.toUpperCase().replace('_', ' ') : 'DINE IN';
        const tableLabel = sale.dining_table ? `Meja ${sale.dining_table.table_number}` : '';

        // Dynamic Logo Header
        const logoHtml = (receiptShowLogo && companyLogoUrl)
            ? `<div class="flex justify-center mb-1.5"><img src="${companyLogoUrl}" alt="Logo" class="max-h-12 max-w-[140px] object-contain filter grayscale contrast-125"></div>`
            : '';

        const storeNameDisplay = (sale.warehouse && sale.warehouse.name) ? sale.warehouse.name : companyName;
        const storePhoneDisplay = (sale.warehouse && sale.warehouse.phone) ? sale.warehouse.phone : companyPhone;
        const storeAddressDisplay = (sale.warehouse && sale.warehouse.address) ? sale.warehouse.address : companyAddress;

        paper.innerHTML = `
            <div class="text-center space-y-0.5 pb-2 border-b border-dashed border-slate-300">
                ${logoHtml}
                <h4 class="font-black text-xs uppercase tracking-wider">${storeNameDisplay}</h4>
                ${companyTagline ? `<p class="text-[10px] text-slate-600 font-medium">${companyTagline}</p>` : ''}
                ${storeAddressDisplay ? `<p class="text-[9px] text-slate-500">${storeAddressDisplay}</p>` : ''}
                ${storePhoneDisplay ? `<p class="text-[9px] text-slate-400">Telp: ${storePhoneDisplay}</p>` : ''}
                ${receiptHeaderMsg ? `<p class="text-[9px] text-slate-500 italic pt-0.5">${receiptHeaderMsg}</p>` : ''}
            </div>
            <div class="text-[10px] space-y-0.5 py-1 border-b border-dashed border-slate-300">
                <div class="flex justify-between"><span>No. Faktur</span><span class="font-bold">${sale.invoice_number}</span></div>
                <div class="flex justify-between"><span>Layanan</span><span class="font-bold text-brand-700">${serviceTypeLabel} ${tableLabel ? '• ' + tableLabel : ''}</span></div>
                <div class="flex justify-between"><span>Kasir</span><span>${sale.user ? sale.user.name : '-'}</span></div>
                <div class="flex justify-between"><span>Pelanggan</span><span>${sale.customer ? sale.customer.name : 'Umum (Retail)'}</span></div>
                <div class="flex justify-between"><span>Waktu</span><span>${new Date(sale.sale_date).toLocaleString('id-ID')}</span></div>
            </div>
            <div class="space-y-1.5 py-2 border-b border-dashed border-slate-300">
                ${itemsHtml}
            </div>
            <div class="space-y-1 pt-1 text-[10px]">
                <div class="flex justify-between"><span>Subtotal</span><span>Rp ${parseInt(sale.subtotal).toLocaleString('id-ID')}</span></div>
                ${sale.discount_amount > 0 ? `<div class="flex justify-between text-emerald-600"><span>Diskon</span><span>- Rp ${parseInt(sale.discount_amount).toLocaleString('id-ID')}</span></div>` : ''}
                <div class="flex justify-between text-xs font-black pt-1 border-t border-slate-200"><span>TOTAL</span><span>Rp ${parseInt(sale.grand_total).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between"><span>Bayar (${sale.payment_method.toUpperCase()})</span><span>Rp ${parseInt(sale.paid_amount).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between"><span>Kembalian</span><span>Rp ${parseInt(sale.change_amount).toLocaleString('id-ID')}</span></div>
            </div>
            <div class="text-center text-[9px] text-slate-400 pt-3 border-t border-dashed border-slate-300 space-y-0.5">
                ${receiptFooterMsg ? `<p class="font-medium text-slate-600">${receiptFooterMsg.replace(/\n/g, '<br>')}</p>` : `
                    <p>Terima kasih atas kunjungan Anda!</p>
                    <p>Barang yang dibeli tidak dapat ditukar.</p>
                `}
            </div>
        `;
    }

    function showAgentReceiptModal(tx) {
        currentReceiptType = 'agent';
        currentAgentData = tx;
        currentSaleData = null;

        const modal = document.getElementById('receiptModal');
        const paper = document.getElementById('thermal_receipt_paper');

        let title = 'STRUK TRANSAKSI AGEN';
        let detailRows = '';

        if (tx.service_type === 'BANK_TRANSFER') {
            title = 'STRUK TRANSFER BANK';
            detailRows = `
                <div class="flex justify-between"><span>Rekening Agen</span><span class="font-bold">${tx.account ? tx.account.name : '-'}</span></div>
                <div class="flex justify-between"><span>Tujuan / Bank</span><span class="font-bold">${tx.destination_target || '-'}</span></div>
                ${tx.destination_holder ? `<div class="flex justify-between"><span>Nama Penerima</span><span class="font-bold">${tx.destination_holder}</span></div>` : ''}
                <div class="flex justify-between"><span>Nominal Transfer</span><span>Rp ${parseInt(tx.principal_amount).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between"><span>Biaya Layanan/Admin</span><span>Rp ${parseInt(tx.admin_fee).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between text-xs font-black pt-1 border-t border-slate-200"><span>TOTAL DIBAYAR</span><span>Rp ${parseInt(tx.total_customer_paid).toLocaleString('id-ID')}</span></div>
            `;
        } else if (tx.service_type === 'CASH_WITHDRAWAL') {
            title = 'STRUK TARIK TUNAI';
            detailRows = `
                <div class="flex justify-between"><span>Rekening Penampung</span><span class="font-bold">${tx.account ? tx.account.name : '-'}</span></div>
                <div class="flex justify-between"><span>Identitas/Kartu</span><span class="font-bold">${tx.destination_target || '-'}</span></div>
                ${tx.destination_holder ? `<div class="flex justify-between"><span>Nama Nasabah</span><span class="font-bold">${tx.destination_holder}</span></div>` : ''}
                <div class="flex justify-between"><span>Nominal Tarik Tunai</span><span>Rp ${parseInt(tx.principal_amount).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between"><span>Biaya Admin Tarik</span><span>Rp ${parseInt(tx.admin_fee).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between text-xs font-black pt-1 border-t border-slate-200"><span>UANG DITERIMA</span><span>Rp ${parseInt(tx.principal_amount).toLocaleString('id-ID')}</span></div>
            `;
        } else {
            title = 'STRUK PULSA & PPOB';
            detailRows = `
                <div class="flex justify-between"><span>Server / Provider</span><span class="font-bold">${tx.account ? tx.account.name : '-'}</span></div>
                <div class="flex justify-between"><span>Layanan</span><span class="font-bold">${tx.service_type}</span></div>
                <div class="flex justify-between"><span>Nomor Tujuan</span><span class="font-bold">${tx.destination_target || '-'}</span></div>
                ${tx.reference_number ? `<div class="flex justify-between"><span>SN / Token</span><span class="font-mono font-bold text-amber-600">${tx.reference_number}</span></div>` : ''}
                <div class="flex justify-between text-xs font-black pt-1 border-t border-slate-200"><span>TOTAL BAYAR</span><span>Rp ${parseInt(tx.total_customer_paid).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between text-[10px]"><span>Metode Bayar</span><span class="uppercase">${tx.payment_method}</span></div>
            `;
        }

        const logoHtml = (receiptShowLogo && companyLogoUrl)
            ? `<div class="flex justify-center mb-1.5"><img src="${companyLogoUrl}" alt="Logo" class="max-h-12 max-w-[140px] object-contain filter grayscale contrast-125"></div>`
            : '';

        paper.innerHTML = `
            <div class="text-center space-y-0.5 pb-2 border-b border-dashed border-slate-300">
                ${logoHtml}
                <h4 class="font-black text-xs uppercase tracking-wider">${title}</h4>
                <p class="text-[10px] text-slate-500">${companyName}</p>
                <p class="text-[9px] text-slate-400">LAYANAN DIGITAL & PERBANKAN • BUKTI TRANSAKSI SAH</p>
                ${companyPhone ? `<p class="text-[9px] text-slate-400">Telp: ${companyPhone}</p>` : ''}
            </div>
            <div class="text-[10px] space-y-0.5 py-1 border-b border-dashed border-slate-300">
                <div class="flex justify-between"><span>No. Referensi</span><span class="font-bold font-mono">${tx.transaction_number}</span></div>
                <div class="flex justify-between"><span>Waktu</span><span>${new Date(tx.created_at).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between"><span>Status</span><span class="text-emerald-600 font-bold uppercase">${tx.status || 'SUKSES'}</span></div>
                ${tx.reference_number && tx.service_type !== 'ppob' ? `<div class="flex justify-between"><span>Ref / EDC SN</span><span class="font-mono">${tx.reference_number}</span></div>` : ''}
            </div>
            <div class="space-y-1.5 py-2 text-[10px]">
                ${detailRows}
            </div>
            <div class="text-center text-[9px] text-slate-400 pt-3 border-t border-dashed border-slate-300 space-y-0.5">
                <p>Simpan struk ini sebagai bukti transaksi yang sah.</p>
                <p>Terima kasih telah bertransaksi di outlet kami.</p>
            </div>
        `;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function printReceipt() {
        window.print();
    }

    // =========================================================================
    // BLUETOOTH THERMAL PRINTER INTEGRATION
    // =========================================================================
    function openBluetoothPrinterModal() {
        openModal('bluetoothPrinterModal');
        updateBluetoothUiState();
        if (window.lucide) lucide.createIcons();
    }

    function updateBluetoothUiState() {
        const isConn = window.posBtPrinter && window.posBtPrinter.isConnected;
        const name = (window.posBtPrinter && window.posBtPrinter.deviceName) || 'Belum Terhubung';
        const width = (window.posBtPrinter && window.posBtPrinter.paperWidth) || '58';
        const autoPrint = window.posBtPrinter && window.posBtPrinter.autoPrint;

        const dot = document.getElementById('bt_indicator_dot');
        const ping = document.getElementById('bt_indicator_ping');
        const label = document.getElementById('bt_printer_status_label');
        const icon = document.getElementById('bt_printer_icon');

        const card = document.getElementById('bt_printer_status_card');
        const circle = document.getElementById('bt_status_circle');
        const devName = document.getElementById('bt_status_device_name');
        const badge = document.getElementById('bt_status_badge');

        const btnConnect = document.getElementById('btnBtConnect');
        const btnDisconnect = document.getElementById('btnBtDisconnect');
        const btnTest = document.getElementById('btnBtTestPrint');
        const autoPrintToggle = document.getElementById('bt_auto_print_toggle');

        // Radios
        const radios = document.querySelectorAll('input[name="bt_paper_width"]');
        radios.forEach(r => { r.checked = (r.value === width); });

        if (autoPrintToggle) {
            autoPrintToggle.checked = !!autoPrint;
        }

        if (isConn) {
            if (dot) dot.className = 'relative inline-flex rounded-full h-2 w-2 bg-emerald-500';
            if (ping) ping.classList.remove('hidden');
            if (label) { label.innerText = name.length > 12 ? name.substring(0, 10) + '..' : name; label.className = 'hidden sm:inline text-[11px] font-bold text-emerald-700'; }
            if (icon) icon.className = 'w-4 h-4 text-emerald-600';

            if (card) { card.style.backgroundColor = '#ecfdf5'; card.style.borderColor = '#a7f3d0'; }
            if (circle) circle.className = 'w-3 h-3 rounded-full bg-emerald-500';
            if (devName) { devName.innerText = name; devName.className = 'text-sm font-black text-emerald-900 block'; }
            if (badge) { badge.innerText = 'Online / Tersimpan'; badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700'; }

            if (btnConnect) btnConnect.classList.add('hidden');
            if (btnDisconnect) btnDisconnect.classList.remove('hidden');
            if (btnTest) btnTest.classList.remove('hidden');
        } else {
            if (dot) dot.className = 'relative inline-flex rounded-full h-2 w-2 bg-slate-400';
            if (ping) ping.classList.add('hidden');
            if (label) { label.innerText = 'Printer BT'; label.className = 'hidden sm:inline text-[11px] font-bold text-slate-600'; }
            if (icon) icon.className = 'w-4 h-4 text-slate-500';

            if (card) { card.style.backgroundColor = '#f8fafc'; card.style.borderColor = '#e2e8f0'; }
            if (circle) circle.className = 'w-3 h-3 rounded-full bg-slate-400';
            if (devName) { devName.innerText = 'Belum Terhubung'; devName.className = 'text-sm font-black text-slate-800 block'; }
            if (badge) { badge.innerText = 'Offline'; badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200/70 text-slate-600'; }

            if (btnConnect) btnConnect.classList.remove('hidden');
            if (btnDisconnect) btnDisconnect.classList.add('hidden');
            if (btnTest) btnTest.classList.add('hidden');
        }
    }

    function toggleBtAutoPrint(checked) {
        if (window.posBtPrinter) {
            window.posBtPrinter.setAutoPrint(checked);
            showPosToast('info', checked ? 'Direct Auto-Print Aktif: Struk akan langsung dicetak otomatis.' : 'Direct Auto-Print Nonaktif.');
        }
    }

    // Listener from driver
    if (window.posBtPrinter) {
        window.posBtPrinter.onStatusChange(function(info) {
            updateBluetoothUiState();
            if (info.status === 'connected') {
                showPosToast('success', info.message || `Printer ${info.name} terhubung!`);
            } else if (info.status === 'disconnected') {
                showPosToast('warning', info.message || 'Printer Bluetooth terputus.');
            }
        });

        // Try Auto Reconnect saved printer on startup
        setTimeout(async () => {
            if (!window.posBtPrinter.isConnected) {
                const reconnected = await window.posBtPrinter.autoReconnect();
                if (reconnected) {
                    console.log('Bluetooth printer auto-reconnected to', reconnected);
                    updateBluetoothUiState();
                }
            }
        }, 800);
    }

    async function connectBluetoothPrinter() {
        if (!window.posBtPrinter) {
            showPosAlert('error', 'Gagal', 'Driver printer Bluetooth belum dimuat.');
            return;
        }
        try {
            const name = await window.posBtPrinter.connect();
            updateBluetoothUiState();
            showPosToast('success', `Berhasil terhubung ke ${name}! Printer tersimpan untuk cetak langsung.`);
        } catch (err) {
            if (err.name !== 'NotFoundError') { // Not canceled by user
                showPosAlert('error', 'Koneksi Bluetooth Gagal', err.message || 'Tidak dapat terhubung ke printer.');
            }
        }
    }

    async function disconnectBluetoothPrinter() {
        if (window.posBtPrinter) {
            await window.posBtPrinter.disconnect();
            updateBluetoothUiState();
            showPosToast('info', 'Printer Bluetooth diputus.');
        }
    }

    function changePrinterPaperWidth(width) {
        if (window.posBtPrinter) {
            window.posBtPrinter.setPaperWidth(width);
            showPosToast('info', `Ukuran kertas diatur ke ${width}mm.`);
        }
    }

    async function testPrintBluetooth() {
        if (!window.posBtPrinter || !window.posBtPrinter.isConnected) {
            showPosToast('warning', 'Hubungkan printer Bluetooth terlebih dahulu.');
            return;
        }
        try {
            showPosToast('info', 'Mengirim perintah cetak test...');
            await window.posBtPrinter.printTestPage();
            showPosToast('success', 'Halaman tes cetak berhasil dikirim!');
        } catch (e) {
            showPosAlert('error', 'Gagal Cetak', e.message);
        }
    }

    async function printViaBluetooth(silent = false) {
        if (!window.posBtPrinter) {
            if (!silent) showPosAlert('error', 'Driver Error', 'Driver Bluetooth tidak tersedia.');
            return false;
        }

        if (!window.posBtPrinter.isConnected) {
            if (silent) return false; // Don't interrupt if running in silent auto mode

            // Prompt to connect
            Swal.fire({
                title: 'Printer Bluetooth Belum Terhubung',
                text: 'Ingin mencari dan menghubungkan printer Bluetooth sekarang?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Hubungkan Sekarang',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        await window.posBtPrinter.connect();
                        // Recursive print once connected
                        printViaBluetooth();
                    } catch (err) {
                        if (err.name !== 'NotFoundError') {
                            showPosAlert('error', 'Koneksi Gagal', err.message);
                        }
                    }
                }
            });
            return false;
        }

        const btn = document.getElementById('btnBtPrintModal');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i><span>Mencetak...</span>`;
        }

        try {
            if (currentReceiptType === 'sale' && currentSaleData) {
                await window.posBtPrinter.printSaleReceipt(currentSaleData, {
                    name: companyName,
                    tagline: companyTagline,
                    address: companyAddress,
                    phone: companyPhone,
                    showLogo: receiptShowLogo,
                    logoUrl: companyLogoUrl
                });
                showPosToast('success', 'Struk penjualan langsung dicetak via Bluetooth!');
                return true;
            } else if (currentReceiptType === 'agent' && currentAgentData) {
                await window.posBtPrinter.printAgentReceipt(currentAgentData, {
                    name: companyName,
                    phone: companyPhone,
                    showLogo: receiptShowLogo,
                    logoUrl: companyLogoUrl
                });
                showPosToast('success', 'Struk agen langsung dicetak via Bluetooth!');
                return true;
            } else {
                if (!silent) showPosToast('warning', 'Tidak ada data struk aktif untuk dicetak.');
            }
        } catch (err) {
            if (!silent) showPosAlert('error', 'Gagal Cetak Bluetooth', err.message || 'Terjadi gangguan saat mencetak.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="bluetooth" class="w-3.5 h-3.5"></i><span>Cetak Bluetooth</span>`;
                if (window.lucide) lucide.createIcons();
            }
        }
        return false;
    }

    // Hold & Recall Cart Management
    async function handleHoldCart(e) {
        e.preventDefault();
        if (cart.length === 0) return;
        const label = document.getElementById('hold_reference_label').value;
        const customerId = document.getElementById('posCustomerSelect').value;

        try {
            const res = await fetch('{{ route("pos.hold") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ reference_label: label, warehouse_id: currentWarehouseId, customer_id: customerId || null, cart_payload: cart })
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal('holdModal');
                clearCart();
                document.getElementById('hold_reference_label').value = '';
                showPosToast('success', 'Keranjang transaksi berhasil ditahan (Hold).');
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        }
    }

    async function loadHeldList() {
        const container = document.getElementById('held_transactions_container');
        container.innerHTML = '<div class="py-6 text-center text-xs text-slate-400">Memuat...</div>';
        try {
            const res = await fetch(`/pos/held-list?warehouse_id=${currentWarehouseId}`);
            const data = await res.json();
            container.innerHTML = '';
            if (data.data.length === 0) {
                container.innerHTML = '<div class="py-8 text-center text-xs text-slate-400">Tidak ada keranjang yang sedang ditahan.</div>';
                return;
            }
            data.data.forEach(h => {
                const itemTotal = (h.cart_payload || []).length;
                const row = document.createElement('div');
                row.className = 'py-3 flex items-center justify-between gap-3';
                row.innerHTML = `
                    <div>
                        <div class="font-bold text-xs text-slate-800">${h.reference_label}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">${itemTotal} Item • ${new Date(h.created_at).toLocaleTimeString('id-ID')}</div>
                    </div>
                    <button onclick="recallHeldCart(${h.id})" class="px-3 py-1.5 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition flex items-center gap-1">
                        <i data-lucide="play" class="w-3 h-3"></i>
                        <span>Muat</span>
                    </button>
                `;
                container.appendChild(row);
            });
            lucide.createIcons();
        } catch (err) {
            console.error(err);
        }
    }

    async function recallHeldCart(heldId) {
        try {
            const res = await fetch(`/pos/recall/${heldId}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.status === 'success') {
                cart = data.data || [];
                closeModal('recallModal');
                recalculateCartPrices().then(renderCart);
                showPosToast('success', 'Keranjang berhasil dimuat kembali.');
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        }
    }

    // =========================================================================
    // LAYANAN AGEN BANK & PPOB JS LOGIC
    // =========================================================================
    let agentAccountsData = @json($accountsSummary ?? null);

    // Initial render balances on header bar
    document.addEventListener('DOMContentLoaded', () => {
        renderAgentHeaderBalances();
        fetchLatestAgentBalances(false);
    });

    async function fetchLatestAgentBalances(showToast = false) {
        const refreshIcon = document.getElementById('refresh_balance_icon');
        if (refreshIcon) refreshIcon.classList.add('animate-spin');

        try {
            const res = await fetch('{{ route("agent.balances") }}');
            const result = await res.json();
            if (result.status === 'success') {
                agentAccountsData = result.data;
                renderAgentHeaderBalances();
                renderAgentBalancesModalContent();
                populateAgentServiceDropdowns();
                if (showToast) {
                    showPosToast('success', 'Status seluruh saldo berhasil diperbarui.');
                }
            }
        } catch (err) {
            console.error('Failed to fetch balances:', err);
        } finally {
            if (refreshIcon) refreshIcon.classList.remove('animate-spin');
        }
    }

    function renderAgentHeaderBalances() {
        if (!agentAccountsData) return;

        const totalBankAgent = agentAccountsData.total_bank_agent_balance || 0;
        const totalPpob = agentAccountsData.total_ppob_balance || 0;
        const totalDigital = totalBankAgent + totalPpob;

        const elCombined = document.getElementById('pos_header_combined_balance');
        if (elCombined) {
            elCombined.innerText = `Rp ${parseInt(totalDigital).toLocaleString('id-ID')}`;
        }

        const elDropdownBank = document.getElementById('pos_dropdown_bank_balance');
        if (elDropdownBank) {
            elDropdownBank.innerText = `Rp ${parseInt(totalBankAgent).toLocaleString('id-ID')}`;
        }

        const elDropdownPpob = document.getElementById('pos_dropdown_ppob_balance');
        if (elDropdownPpob) {
            elDropdownPpob.innerText = `Rp ${parseInt(totalPpob).toLocaleString('id-ID')}`;
        }

        const elBank = document.getElementById('pos_header_bank_balance');
        if (elBank) {
            elBank.innerText = `Rp ${parseInt(totalBankAgent).toLocaleString('id-ID')}`;
        }

        const elPpob = document.getElementById('pos_header_ppob_balance');
        if (elPpob) {
            elPpob.innerText = `Rp ${parseInt(totalPpob).toLocaleString('id-ID')}`;
        }
    }

    function renderAgentBalancesModalContent() {
        if (!agentAccountsData) return;

        const modalTotal = document.getElementById('modal_total_liquid_balance');
        if (modalTotal) {
            modalTotal.innerText = `Rp ${parseInt(agentAccountsData.total_balance || 0).toLocaleString('id-ID')}`;
        }

        const badgeBank = document.getElementById('badge_total_bank_agent');
        if (badgeBank) {
            badgeBank.innerText = `Total: Rp ${parseInt(agentAccountsData.total_bank_agent_balance || 0).toLocaleString('id-ID')}`;
        }

        const badgePpob = document.getElementById('badge_total_ppob_provider');
        if (badgePpob) {
            badgePpob.innerText = `Total: Rp ${parseInt(agentAccountsData.total_ppob_balance || 0).toLocaleString('id-ID')}`;
        }

        // Render Bank Agent Accounts
        const containerBank = document.getElementById('container_bank_agent_accounts');
        if (containerBank) {
            const bankList = agentAccountsData.bank_agents || [];
            if (bankList.length === 0) {
                containerBank.innerHTML = '<div class="p-3 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">Belum ada akun bank agen terdaftar.</div>';
            } else {
                containerBank.innerHTML = bankList.map(acc => {
                    const isLow = parseFloat(acc.alert_minimum_balance || 0) > 0 && parseFloat(acc.current_balance) <= parseFloat(acc.alert_minimum_balance);
                    return `
                        <div class="p-3.5 rounded-xl border ${isLow ? 'border-amber-300 bg-amber-50/60' : 'border-slate-200 bg-white'} flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl ${isLow ? 'bg-amber-500 text-white' : 'bg-blue-50 text-blue-600'} flex items-center justify-center font-bold text-xs">
                                    <i data-lucide="building-2" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-xs text-slate-900">${escapeHtml(acc.name)}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">${escapeHtml(acc.bank_name || '')} ${escapeHtml(acc.account_number || '')} ${acc.account_holder ? `• a.n. ${escapeHtml(acc.account_holder)}` : ''}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-sm text-slate-900 font-mono-num">Rp ${parseInt(acc.current_balance).toLocaleString('id-ID')}</div>
                                ${isLow ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-200 text-amber-900">Saldo Menipis</span>' : '<span class="text-[10px] text-emerald-600 font-semibold">Aktif & Siap</span>'}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Render PPOB Accounts
        const containerPpob = document.getElementById('container_ppob_accounts');
        if (containerPpob) {
            const ppobList = agentAccountsData.ppob_providers || [];
            if (ppobList.length === 0) {
                containerPpob.innerHTML = '<div class="p-3 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">Belum ada akun saldo PPOB terdaftar.</div>';
            } else {
                containerPpob.innerHTML = ppobList.map(acc => {
                    const isLow = parseFloat(acc.alert_minimum_balance || 0) > 0 && parseFloat(acc.current_balance) <= parseFloat(acc.alert_minimum_balance);
                    return `
                        <div class="p-3.5 rounded-xl border ${isLow ? 'border-amber-300 bg-amber-50/60' : 'border-slate-200 bg-white'} flex items-center justify-between shadow-2xs">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl ${isLow ? 'bg-amber-500 text-white' : 'bg-emerald-50 text-emerald-600'} flex items-center justify-center font-bold text-xs">
                                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-xs text-slate-900">${escapeHtml(acc.name)}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">ID: ${escapeHtml(acc.account_number || acc.account_code)}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-sm text-slate-900 font-mono-num">Rp ${parseInt(acc.current_balance).toLocaleString('id-ID')}</div>
                                ${isLow ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-200 text-amber-900">Segera Top Up</span>' : '<span class="text-[10px] text-emerald-600 font-semibold">Aktif</span>'}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Render Other Accounts (Kas & Bank Toko)
        const containerOther = document.getElementById('container_other_accounts');
        if (containerOther) {
            const otherList = [
                ...(agentAccountsData.cash_accounts || []),
                ...(agentAccountsData.bank_accounts || [])
            ];
            containerOther.innerHTML = otherList.map(acc => `
                <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/70 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-semibold text-slate-700">${escapeHtml(acc.name)}</span>
                        <span class="text-[10px] text-slate-400 block">${acc.type === 'cash' ? 'Uang Kas Fisik' : 'Rekening Bank'}</span>
                    </div>
                    <span class="font-bold text-slate-900 font-mono-num">Rp ${parseInt(acc.current_balance).toLocaleString('id-ID')}</span>
                </div>
            `).join('');
        }

        lucide.createIcons();
    }

    function populateAgentServiceDropdowns() {
        if (!agentAccountsData) return;

        // Populate Transfer Accounts (Bank Agents & Banks)
        const transferSelect = document.getElementById('transfer_account_id');
        if (transferSelect) {
            const list = [
                ...(agentAccountsData.bank_agents || []),
                ...(agentAccountsData.bank_accounts || [])
            ];
            transferSelect.innerHTML = list.map(a => `
                <option value="${a.id}" data-balance="${a.current_balance}" data-alert="${a.alert_minimum_balance || 0}">
                    ${escapeHtml(a.name)} - Sisa Saldo: Rp ${parseInt(a.current_balance).toLocaleString('id-ID')}
                </option>
            `).join('');
            onTransferAccountChanged();
        }

        // Populate Withdraw Accounts
        const withdrawSelect = document.getElementById('withdraw_account_id');
        if (withdrawSelect) {
            const list = [
                ...(agentAccountsData.bank_agents || []),
                ...(agentAccountsData.bank_accounts || [])
            ];
            withdrawSelect.innerHTML = list.map(a => `
                <option value="${a.id}">
                    ${escapeHtml(a.name)} (Saldo Saat Ini: Rp ${parseInt(a.current_balance).toLocaleString('id-ID')})
                </option>
            `).join('');
        }

        // Populate PPOB Accounts
        const ppobSelect = document.getElementById('ppob_account_id');
        if (ppobSelect) {
            const list = agentAccountsData.ppob_providers || [];
            ppobSelect.innerHTML = list.map(a => `
                <option value="${a.id}" data-balance="${a.current_balance}" data-alert="${a.alert_minimum_balance || 0}">
                    ${escapeHtml(a.name)} - Sisa Deposit: Rp ${parseInt(a.current_balance).toLocaleString('id-ID')}
                </option>
            `).join('');
            onPpobAccountChanged();
        }
    }

    function onTransferAccountChanged() {
        const sel = document.getElementById('transfer_account_id');
        if (!sel || !sel.selectedOptions[0]) return;
        const bal = parseFloat(sel.selectedOptions[0].getAttribute('data-balance') || 0);
        const alertThreshold = parseFloat(sel.selectedOptions[0].getAttribute('data-alert') || 0);

        const elDisp = document.getElementById('transfer_avail_balance_display');
        if (elDisp) elDisp.innerText = `Rp ${parseInt(bal).toLocaleString('id-ID')}`;

        const alertBadge = document.getElementById('transfer_balance_alert_badge');
        if (alertBadge) {
            if (alertThreshold > 0 && bal <= alertThreshold) {
                alertBadge.classList.remove('hidden');
            } else {
                alertBadge.classList.add('hidden');
            }
        }
    }

    function onPpobAccountChanged() {
        const sel = document.getElementById('ppob_account_id');
        if (!sel || !sel.selectedOptions[0]) return;
        const bal = parseFloat(sel.selectedOptions[0].getAttribute('data-balance') || 0);
        const alertThreshold = parseFloat(sel.selectedOptions[0].getAttribute('data-alert') || 0);

        const elDisp = document.getElementById('ppob_avail_balance_display');
        if (elDisp) elDisp.innerText = `Rp ${parseInt(bal).toLocaleString('id-ID')}`;

        const alertBadge = document.getElementById('ppob_balance_alert_badge');
        if (alertBadge) {
            if (alertThreshold > 0 && bal <= alertThreshold) {
                alertBadge.classList.remove('hidden');
            } else {
                alertBadge.classList.add('hidden');
            }
        }
    }

    function openAgentServiceModal(targetTab = 'transfer') {
        if (!activeShift) {
            showPosToast('warning', 'Silakan buka sesi shift kasir terlebih dahulu.');
            return;
        }
        fetchLatestAgentBalances(false);
        loadPpobCatalogProducts();
        switchAgentTab(targetTab);
        openModal('agentServiceModal');
        lucide.createIcons();
    }

    function switchAgentTab(tab) {
        const tabs = ['transfer', 'withdraw', 'ppob', 'history'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tabBtn_${t}`);
            const form = t === 'history' ? document.getElementById('tabAgentHistory') : document.getElementById(`formAgent${t.charAt(0).toUpperCase() + t.slice(1)}`);
            
            if (t === tab) {
                btn.className = 'agent-nav-tab pb-2.5 px-3 text-xs font-bold border-b-2 border-blue-600 text-blue-600 flex items-center gap-1.5 transition';
                if (form) form.classList.remove('hidden');
            } else {
                btn.className = 'agent-nav-tab pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-1.5 transition';
                if (form) form.classList.add('hidden');
            }
        });

        if (tab === 'ppob') {
            loadPpobCatalogProducts();
        } else if (tab === 'history') {
            loadAgentRecentTransactions();
        }
        lucide.createIcons();
    }

    const agentTransferTiers = @json($agentTransferTiers ?? []);
    const agentWithdrawTiers = @json($agentWithdrawTiers ?? []);
    const defaultTransferAdminFee = {{ $agentTransferAdminFee ?? 5000 }};
    const defaultWithdrawAdminFee = {{ $agentWithdrawAdminFee ?? 5000 }};

    function resolveAdminFeeFromTiers(principal, tiers, defaultFee) {
        if (principal <= 0) return defaultFee;
        if (!Array.isArray(tiers) || tiers.length === 0) return defaultFee;

        // Search matching range [min, max]
        for (const tier of tiers) {
            const min = parseFloat(tier.min) || 0;
            const max = parseFloat(tier.max) || 0;
            const fee = parseFloat(tier.fee) || 0;
            if (principal >= min && (max === 0 || principal <= max)) {
                return fee;
            }
        }
        return defaultFee;
    }

    function calculateTransferTotal() {
        const principal = parseFloat(document.getElementById('transfer_principal_amount').value) || 0;
        
        // Cari fee berdasarkan tiering range nominal
        const dynamicFee = resolveAdminFeeFromTiers(principal, agentTransferTiers, defaultTransferAdminFee);
        const adminFeeInput = document.getElementById('transfer_admin_fee');
        if (adminFeeInput) {
            adminFeeInput.value = dynamicFee;
        }

        const costPrice = parseFloat(document.getElementById('transfer_cost_price').value) || 0;
        const totalPaid = principal + dynamicFee;
        const netProfit = Math.max(0, dynamicFee - costPrice);

        document.getElementById('transfer_total_paid_display').innerText = `Rp ${parseInt(totalPaid).toLocaleString('id-ID')}`;
        document.getElementById('transfer_net_profit_display').innerText = `+ Rp ${parseInt(netProfit).toLocaleString('id-ID')}`;
    }

    function calculateWithdrawTotal() {
        const principal = parseFloat(document.getElementById('withdraw_principal_amount').value) || 0;

        // Cari fee berdasarkan tiering range nominal
        const dynamicFee = resolveAdminFeeFromTiers(principal, agentWithdrawTiers, defaultWithdrawAdminFee);
        const adminFeeInput = document.getElementById('withdraw_admin_fee');
        if (adminFeeInput) {
            adminFeeInput.value = dynamicFee;
        }

        const method = document.querySelector('input[name="withdraw_payment_method"]:checked')?.value || 'deduct_balance';
        const bankIn = method === 'deduct_balance' ? (principal + dynamicFee) : principal;

        document.getElementById('withdraw_cash_out_display').innerText = `- Rp ${parseInt(principal).toLocaleString('id-ID')}`;
        document.getElementById('withdraw_bank_in_display').innerText = `+ Rp ${parseInt(bankIn).toLocaleString('id-ID')}`;
        document.getElementById('withdraw_net_profit_display').innerText = `+ Rp ${parseInt(dynamicFee).toLocaleString('id-ID')}`;
    }

    function calculatePpobTotal() {
        const cost = parseFloat(document.getElementById('ppob_cost_price').value) || 0;
        const sell = parseFloat(document.getElementById('ppob_selling_price').value) || 0;
        const profit = Math.max(0, sell - cost);

        document.getElementById('ppob_net_profit_display').innerText = `+ Rp ${parseInt(profit).toLocaleString('id-ID')}`;
    }

    let ppobCatalogList = [];

    async function loadPpobCatalogProducts() {
        try {
            const res = await fetch('{{ route("api.ppob-products") }}');
            const data = await res.json();
            if (data.success && data.products) {
                ppobCatalogList = data.products;
                renderPpobCatalogOptions();
            }
        } catch (e) {
            console.error('Failed to load PPOB products:', e);
        }
    }

    function renderPpobCatalogOptions() {
        const select = document.getElementById('ppob_catalog_select');
        if (!select) return;

        if (ppobCatalogList.length === 0) {
            select.innerHTML = '<option value="">-- Belum ada produk PPOB (Input Manual) --</option>';
            return;
        }

        // Group by category/provider
        const grouped = {};
        ppobCatalogList.forEach(item => {
            const groupName = (item.provider || item.category || 'Lainnya').toUpperCase();
            if (!grouped[groupName]) grouped[groupName] = [];
            grouped[groupName].push(item);
        });

        let html = '<option value="">-- Pilih Produk / Paket PPOB Cepat (Auto-Fill) --</option>';
        for (const [group, items] of Object.entries(grouped)) {
            html += `<optgroup label="${escapeHtml(group)}">`;
            items.forEach(prod => {
                const margin = prod.selling_price - prod.cost_price;
                html += `
                    <option value="${prod.id}">
                        ${escapeHtml(prod.name)} • Jual: Rp ${parseInt(prod.selling_price).toLocaleString('id-ID')} (Laba: +Rp ${parseInt(margin).toLocaleString('id-ID')})
                    </option>
                `;
            });
            html += `</optgroup>`;
        }

        select.innerHTML = html;
    }

    function onPpobCatalogProductSelected(productId) {
        if (!productId) return;
        const product = ppobCatalogList.find(p => p.id == productId);
        if (!product) return;

        // Auto-fill category
        const catSelect = document.getElementById('ppob_service_type');
        if (catSelect && product.category) {
            catSelect.value = product.category;
        }

        // Auto-fill cost & selling price
        const costInput = document.getElementById('ppob_cost_price');
        const sellInput = document.getElementById('ppob_selling_price');
        if (costInput) costInput.value = parseInt(product.cost_price);
        if (sellInput) sellInput.value = parseInt(product.selling_price);

        // Auto-select account if specified
        if (product.default_account_id) {
            const accSelect = document.getElementById('ppob_account_id');
            if (accSelect) {
                accSelect.value = product.default_account_id;
                onPpobAccountChanged();
            }
        }

        calculatePpobTotal();

        // Focus on destination number
        const destInput = document.getElementById('ppob_destination_target');
        if (destInput) {
            destInput.focus();
        }
    }

    function resetPpobToCustom() {
        const select = document.getElementById('ppob_catalog_select');
        if (select) select.value = '';
        document.getElementById('ppob_cost_price').value = '';
        document.getElementById('ppob_selling_price').value = '';
        calculatePpobTotal();
        const destInput = document.getElementById('ppob_destination_target');
        if (destInput) destInput.focus();
    }

    async function handleAgentTransferSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitTransfer');
        const payload = {
            account_id: document.getElementById('transfer_account_id').value,
            destination_target: document.getElementById('transfer_destination_target').value.trim(),
            destination_holder: document.getElementById('transfer_destination_holder').value.trim(),
            principal_amount: document.getElementById('transfer_principal_amount').value,
            admin_fee: document.getElementById('transfer_admin_fee').value,
            cost_price: document.getElementById('transfer_cost_price').value,
            reference_number: document.getElementById('transfer_reference_number').value.trim(),
            notes: document.getElementById('transfer_notes').value.trim(),
        };

        try {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Memproses...</span>';

            const res = await fetch('{{ route("agent.transfer") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal('agentServiceModal');
                document.getElementById('formAgentTransfer').reset();
                fetchLatestAgentBalances(false);

                // Refresh current active shift state
                refreshCurrentShift();

                // Buka preview & cetak struk agen
                showAgentReceiptModal(data.data);
                if (window.posBtPrinter && window.posBtPrinter.isConnected && window.posBtPrinter.autoPrint) {
                    printViaBluetooth(true);
                }
            } else {
                showPosAlert('error', 'Gagal Memproses Transfer', data.message);
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i><span>Proses & Simpan Transaksi Transfer</span>';
            lucide.createIcons();
        }
    }

    async function handleAgentWithdrawSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitWithdraw');
        const payload = {
            account_id: document.getElementById('withdraw_account_id').value,
            principal_amount: document.getElementById('withdraw_principal_amount').value,
            admin_fee: document.getElementById('withdraw_admin_fee').value,
            payment_method: document.querySelector('input[name="withdraw_payment_method"]:checked')?.value || 'deduct_balance',
            destination_holder: document.getElementById('withdraw_destination_holder').value.trim(),
            reference_number: document.getElementById('withdraw_reference_number').value.trim(),
        };

        try {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Memproses...</span>';

            const res = await fetch('{{ route("agent.withdraw") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal('agentServiceModal');
                document.getElementById('formAgentWithdraw').reset();
                fetchLatestAgentBalances(false);

                // Refresh current active shift state
                refreshCurrentShift();

                // Buka preview & cetak struk tarik tunai
                showAgentReceiptModal(data.data);
                if (window.posBtPrinter && window.posBtPrinter.isConnected && window.posBtPrinter.autoPrint) {
                    printViaBluetooth(true);
                }
            } else {
                showPosAlert('error', 'Gagal Tarik Tunai', data.message);
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i><span>Proses & Simpan Tarik Tunai</span>';
            lucide.createIcons();
        }
    }

    async function handleAgentPpobSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitPpob');
        const payload = {
            account_id: document.getElementById('ppob_account_id').value,
            service_type: document.getElementById('ppob_service_type').value,
            destination_target: document.getElementById('ppob_destination_target').value.trim(),
            cost_price: document.getElementById('ppob_cost_price').value,
            selling_price: document.getElementById('ppob_selling_price').value,
            payment_method: document.querySelector('input[name="ppob_payment_method"]:checked')?.value || 'cash',
            reference_number: document.getElementById('ppob_reference_number').value.trim(),
        };

        try {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Memproses...</span>';

            const res = await fetch('{{ route("agent.ppob") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                closeModal('agentServiceModal');
                document.getElementById('formAgentPpob').reset();
                fetchLatestAgentBalances(false);

                // Refresh current active shift state
                refreshCurrentShift();

                // Buka preview & cetak struk PPOB
                showAgentReceiptModal(data.data);
                if (window.posBtPrinter && window.posBtPrinter.isConnected && window.posBtPrinter.autoPrint) {
                    printViaBluetooth(true);
                }
            } else {
                showPosAlert('error', 'Gagal Transaksi PPOB', data.message);
            }
        } catch (err) {
            showPosAlert('error', 'Terjadi Kesalahan', err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i><span>Proses & Simpan Transaksi PPOB</span>';
            lucide.createIcons();
        }
    }

    async function refreshCurrentShift() {
        try {
            const res = await fetch('{{ route("shifts.current") }}');
            const data = await res.json();
            if (data.status === 'success' && data.data) {
                activeShift = data.data;
                updateHeaderShiftExpenseBadge();
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function loadAgentRecentTransactions() {
        const tbody = document.getElementById('agent_history_table_body');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-xs text-slate-400">Memuat data...</td></tr>';

        try {
            const res = await fetch('{{ route("agent.recent") }}');
            const data = await res.json();
            if (data.status === 'success') {
                const list = data.data || [];
                if (list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-xs text-slate-400">Belum ada transaksi pada shift ini.</td></tr>';
                    return;
                }
                tbody.innerHTML = list.map(tx => {
                    const typeLabel = tx.service_type.replace('_', ' ').toUpperCase();
                    const isCashIn = tx.service_type !== 'tarik_tunai';
                    return `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-xs text-slate-900 block">${typeLabel}</span>
                                <span class="text-[10px] text-slate-400">${new Date(tx.created_at).toLocaleTimeString('id-ID')} • ${tx.transaction_number}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="font-semibold text-slate-800 text-xs">${escapeHtml(tx.destination_target)}</span>
                                <span class="text-[10px] text-slate-400 block">${escapeHtml(tx.account ? tx.account.name : '-')}</span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono-num font-bold text-slate-900">
                                Rp ${parseInt(tx.principal_amount).toLocaleString('id-ID')}
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <span class="font-bold text-emerald-600 font-mono-num">+Rp ${parseInt(tx.net_profit).toLocaleString('id-ID')}</span>
                            </td>
                        </tr>
                    `;
                }).join('');
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-xs text-rose-500">${err.message}</td></tr>`;
        }
    }
</script>
@endpush
