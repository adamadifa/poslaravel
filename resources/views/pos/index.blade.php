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

            <!-- Customer Picker -->
            <div class="relative">
                <select id="posCustomerSelect" onchange="onCustomerChange()" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 transition shadow-2xs cursor-pointer focus:outline-none focus:border-brand-500">
                    <option value="">Pelanggan Umum (Retail)</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" data-group="{{ $c->group?->name ?? 'Member' }}" data-discount="{{ floatval($c->group?->discount_percent ?? 0) }}">
                            {{ $c->name }} ({{ $c->group?->name ?? 'Member' }} - Disc {{ floatval($c->group?->discount_percent ?? 0) }}%)
                        </option>
                    @endforeach
                </select>
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
@include('pos._payment_modals')

@endsection

@push('scripts')
<script>
    // State Management
    let currentWarehouseId = {{ $defaultWarehouse ? $defaultWarehouse->id : 1 }};
    let activeShift = @json($activeShift);
    let allProducts = [];
    let cart = [];
    let selectedCategoryId = null;

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

    // Initialize POS
    document.addEventListener('DOMContentLoaded', () => {
        if (!activeShift) {
            openModal('openShiftModal');
        }

        loadProducts();

        // Barcode Scanner Listener
        const scannerInput = document.getElementById('barcodeScannerInput');
        scannerInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleBarcodeScan(scannerInput.value.trim());
                scannerInput.value = '';
            }
        });

        // Global Shortcuts
        window.addEventListener('keydown', (e) => {
            if (e.key === 'F1') {
                e.preventDefault();
                scannerInput.focus();
            } else if (e.key === 'F12') {
                e.preventDefault();
                openPaymentModal();
            } else if (e.key === 'F7') {
                e.preventDefault();
                openModal('holdModal');
            } else if (e.key === 'Escape') {
                closeModal('paymentModal');
                closeModal('holdModal');
                closeModal('recallModal');
                closeModal('receiptModal');
            }
        });
    });

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
                closeModal('openShiftModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Shift Kasir Dimulai!',
                    text: `Modal awal Rp ${parseInt(startingCash).toLocaleString('id-ID')}`,
                    timer: 2000,
                    showConfirmButton: false,
                    scrollbarPadding: false,
                    heightAuto: false
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, scrollbarPadding: false, heightAuto: false });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, scrollbarPadding: false, heightAuto: false });
        }
    }

    function openCloseShiftDialog() {
        if (!activeShift) return;
        document.getElementById('close_shift_starting_cash').innerText = `Rp ${parseInt(activeShift.starting_cash).toLocaleString('id-ID')}`;
        document.getElementById('close_shift_total_trx').innerText = `${activeShift.total_transactions} Struk`;
        document.getElementById('close_shift_total_sales').innerText = `Rp ${parseInt(activeShift.total_sales).toLocaleString('id-ID')}`;
        document.getElementById('close_shift_expected_cash').innerText = `Rp ${parseInt(activeShift.expected_cash).toLocaleString('id-ID')}`;
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
            card.onclick = () => addToCart(prod);

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

    // Barcode Scan Handler
    function handleBarcodeScan(barcode) {
        if (!barcode) return;
        const matched = allProducts.find(p => p.barcode === barcode || (p.barcodes && p.barcodes.some(b => b.barcode === barcode)));
        if (matched) {
            addToCart(matched);
        } else {
            // Fetch online if not in current list
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
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
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

    function clearCart() {
        if (cart.length === 0) return;
        cart = [];
        renderCart();
    }

    // Dynamic Price Recalculation (PricingService via AJAX)
    async function recalculateCartPrices() {
        const customerId = document.getElementById('posCustomerSelect').value;

        for (let i = 0; i < cart.length; i++) {
            const item = cart[i];
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
        let totalDiscount = 0;
        let totalQty = 0;

        cart.forEach((item, idx) => {
            const lineTotal = item.price * item.quantity;
            subtotal += lineTotal;
            totalQty += item.quantity;
            if (item.discount_amount) {
                totalDiscount += item.discount_amount * item.quantity;
            }

            let unitOptions = '';
            item.unitsList.forEach(u => {
                unitOptions += `<option value="${u.id}" ${u.id === item.unit_id ? 'selected' : ''}>${u.name}</option>`;
            });

            const row = document.createElement('div');
            row.className = 'cart-item-row p-3.5 bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl transition space-y-2.5 shadow-2xs';
            row.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <h5 class="text-xs font-bold text-slate-900 truncate">${item.product.name}</h5>
                            ${item.is_tiered ? `<span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Grosir</span>` : ''}
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                            <span class="font-bold text-slate-800 font-mono-num">@ Rp ${parseInt(item.price).toLocaleString('id-ID')}</span>
                            <select onchange="updateCartUnit(${idx}, this.value)" class="bg-slate-50 text-[10px] font-bold text-brand-600 px-2 py-0.5 rounded-md border border-slate-200 focus:outline-none focus:border-brand-500 cursor-pointer">
                                ${unitOptions}
                            </select>
                        </div>
                    </div>
                    <button onclick="removeCartItem(${idx})" class="text-slate-400 hover:text-rose-500 p-1 transition" title="Hapus Item">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl p-0.5">
                        <button onclick="updateCartQty(${idx}, -1)" class="w-6 h-6 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70">-</button>
                        <span class="w-8 text-center text-xs font-bold text-slate-900 font-mono-num">${item.quantity}</span>
                        <button onclick="updateCartQty(${idx}, 1)" class="w-6 h-6 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70">+</button>
                    </div>
                    <span class="text-xs font-black text-slate-900 font-mono-num">Rp ${parseInt(lineTotal).toLocaleString('id-ID')}</span>
                </div>
            `;
            container.appendChild(row);
        });

        updateSummary(subtotal, totalDiscount, totalQty);
        lucide.createIcons();
    }

    function updateSummary(subtotal, discount, qty) {
        const grandTotal = Math.max(0, subtotal - discount);
        document.getElementById('cartSubtotalText').innerText = `Rp ${parseInt(subtotal).toLocaleString('id-ID')}`;
        document.getElementById('cartDiscountText').innerText = `- Rp ${parseInt(discount).toLocaleString('id-ID')}`;
        document.getElementById('cartTotalQtyText').innerText = `${qty} Qty Total`;
        document.getElementById('cartGrandTotalText').innerText = `Rp ${parseInt(grandTotal).toLocaleString('id-ID')}`;
        window.currentCartGrandTotal = grandTotal;
    }

    // Payment Dialog & Checkout Execution
    function openPaymentModal() {
        if (cart.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Tambahkan produk terlebih dahulu sebelum checkout.', scrollbarPadding: false, heightAuto: false });
            return;
        }
        document.getElementById('pay_item_summary_text').innerText = `${cart.length} Item`;
        document.getElementById('pay_grand_total_display').innerText = `Rp ${parseInt(window.currentCartGrandTotal).toLocaleString('id-ID')}`;
        document.getElementById('pay_cash_received_input').value = window.currentCartGrandTotal;
        calculateChangeAmount();
        openModal('paymentModal');
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
        const paidAmount = parseFloat(document.getElementById('pay_cash_received_input').value) || window.currentCartGrandTotal;
        const refNo = document.getElementById('pay_reference_number_input').value;
        const notes = document.getElementById('pay_notes_input').value;
        const customerId = document.getElementById('posCustomerSelect').value;

        const payload = {
            warehouse_id: currentWarehouseId,
            customer_id: customerId || null,
            items: cart.map(i => ({ product_id: i.product.id, unit_id: i.unit_id, quantity: i.quantity, price: i.price })),
            paid_amount: method === 'cash' ? paidAmount : window.currentCartGrandTotal,
            payment_method: method,
            reference_number: refNo,
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
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal Checkout', text: data.message, scrollbarPadding: false, heightAuto: false });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, scrollbarPadding: false, heightAuto: false });
        }
    }

    // Thermal Receipt Renderer (58mm / 80mm ESC/POS layout)
    function renderThermalReceipt(sale) {
        const paper = document.getElementById('thermal_receipt_paper');
        let itemsHtml = '';
        (sale.items || []).forEach(it => {
            itemsHtml += `
                <div class="flex justify-between">
                    <span>${it.product ? it.product.name : 'Item'}</span>
                </div>
                <div class="flex justify-between text-slate-500 text-[10px]">
                    <span>${it.quantity} x ${parseInt(it.unit_price).toLocaleString('id-ID')}</span>
                    <span class="font-bold text-slate-800">Rp ${parseInt(it.subtotal).toLocaleString('id-ID')}</span>
                </div>
            `;
        });

        paper.innerHTML = `
            <div class="text-center space-y-0.5 pb-2 border-b border-dashed border-slate-300">
                <h4 class="font-black text-xs uppercase tracking-wider">POS RETAIL PRO</h4>
                <p class="text-[10px] text-slate-500">${sale.warehouse ? sale.warehouse.name : 'Cabang Utama'}</p>
                <p class="text-[9px] text-slate-400">Telp: ${sale.warehouse ? (sale.warehouse.phone || '-') : '-'}</p>
            </div>
            <div class="text-[10px] space-y-0.5 py-1 border-b border-dashed border-slate-300">
                <div class="flex justify-between"><span>No. Faktur</span><span class="font-bold">${sale.invoice_number}</span></div>
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
            <div class="text-center text-[9px] text-slate-400 pt-3 border-t border-dashed border-slate-300">
                <p>Terima kasih atas kunjungan Anda!</p>
                <p>Barang yang dibeli tidak dapat ditukar.</p>
            </div>
        `;
    }

    function printReceipt() {
        window.print();
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
                Swal.fire({ icon: 'success', title: 'Tersimpan!', text: data.message, timer: 1500, showConfirmButton: false, scrollbarPadding: false, heightAuto: false });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, scrollbarPadding: false, heightAuto: false });
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
                Swal.fire({ icon: 'success', title: 'Keranjang Dimuat!', timer: 1000, showConfirmButton: false, scrollbarPadding: false, heightAuto: false });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, scrollbarPadding: false, heightAuto: false });
        }
    }
</script>
@endpush
