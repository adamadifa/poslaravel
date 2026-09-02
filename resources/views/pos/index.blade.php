@extends('layouts.pos')

@section('content')
<div class="flex-1 flex flex-col lg:flex-row overflow-hidden w-full h-full bg-slate-100 transition-colors duration-200">

    <!-- ========================================================================= -->
    <!-- LEFT PANEL: PRODUCT CATALOG & BARCODE SCANNER (Theme Adaptive) -->
    <!-- ========================================================================= -->
    <div class="flex-1 flex flex-col min-w-0 border-r border-slate-200 overflow-hidden bg-slate-50/50">
        
        <!-- Search & Scanner Input Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-white flex flex-wrap items-center gap-3.5 shrink-0 transition-colors duration-200">
            <!-- Search & Barcode Scan -->
            <div class="relative flex-1 min-w-[280px]">
                <i data-lucide="barcode" class="w-5 h-5 text-brand-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="text" id="barcodeScannerInput" autofocus placeholder="Scan Barcode atau ketik nama produk... (Tekan F1)" class="w-full pl-11 pr-24 py-2.5 bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200 focus:border-brand-500 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition">
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1 text-[10px] font-bold text-slate-500 bg-white px-2 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                    SCAN READY
                </div>
            </div>

            <!-- Customer Picker Button -->
            <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 transition shadow-2xs">
                <i data-lucide="user-plus" class="w-4 h-4 text-brand-500"></i>
                <span>Customer: <strong class="text-slate-900 font-bold">Umum (Retail)</strong></span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
            </button>
        </div>

        <!-- Category Horizontal Tabs Filter -->
        <div class="px-5 py-3 border-b border-slate-200 bg-white flex items-center gap-2 overflow-x-auto shrink-0 no-scrollbar transition-colors duration-200">
            <button class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-brand-500 to-amber-500 text-white shadow-sm shadow-brand-500/25 whitespace-nowrap">
                Semua Kategori (64)
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 whitespace-nowrap transition">
                Makanan & Snack
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 whitespace-nowrap transition">
                Minuman Dingin
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 whitespace-nowrap transition">
                Sembako & Bumbu
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 whitespace-nowrap transition">
                Perawatan Diri
            </button>
            <button class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-slate-900 border border-slate-200/80 whitespace-nowrap transition">
                Rokok & Tembakau
            </button>
        </div>

        <!-- Product Cards Grid (Scrollable) -->
        <div class="flex-1 p-5 overflow-y-auto">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
                
                <!-- Product 1: Indomie Goreng -->
                <div class="group bg-white hover:bg-slate-50 border border-slate-200/90 hover:border-brand-500/60 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition shadow-2xs hover:shadow-md hover:shadow-brand-500/5 active:scale-[0.98]">
                    <div>
                        <div class="aspect-square bg-slate-50 rounded-xl mb-3 flex items-center justify-center relative overflow-hidden border border-slate-100">
                            <i data-lucide="package" class="w-10 h-10 text-slate-300 group-hover:text-brand-500 transition"></i>
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/90 text-slate-600 border border-slate-200 font-mono-num shadow-2xs">
                                Stk: 140
                            </span>
                            <!-- Multi-unit badge -->
                            <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md text-[9px] font-bold bg-brand-50 text-brand-600 border border-brand-200">
                                3 Satuan
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">Indomie Goreng Spesial 85g</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-mono-num">8992770001</p>
                    </div>

                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">Rp 3.500</span>
                        <span class="text-[11px] font-medium text-slate-400">/ Pcs</span>
                    </div>
                </div>

                <!-- Product 2: Aqua 600ml (Tiered Pricing Demo) -->
                <div class="group bg-white hover:bg-slate-50 border border-slate-200/90 hover:border-brand-500/60 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition shadow-2xs hover:shadow-md hover:shadow-brand-500/5 active:scale-[0.98]">
                    <div>
                        <div class="aspect-square bg-slate-50 rounded-xl mb-3 flex items-center justify-center relative overflow-hidden border border-slate-100">
                            <i data-lucide="droplet" class="w-10 h-10 text-slate-300 group-hover:text-brand-500 transition"></i>
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/90 text-slate-600 border border-slate-200 font-mono-num shadow-2xs">
                                Stk: 48
                            </span>
                            <span class="absolute top-2 right-2 px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                Grosir 24+
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">Aqua Air Mineral 600ml</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-mono-num">8886008101</p>
                    </div>

                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">Rp 3.000</span>
                        <span class="text-[11px] font-medium text-slate-400">/ Botol</span>
                    </div>
                </div>

                <!-- Product 3: Ultra Milk Cokelat -->
                <div class="group bg-white hover:bg-slate-50 border border-slate-200/90 hover:border-brand-500/60 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition shadow-2xs hover:shadow-md hover:shadow-brand-500/5 active:scale-[0.98]">
                    <div>
                        <div class="aspect-square bg-slate-50 rounded-xl mb-3 flex items-center justify-center relative overflow-hidden border border-slate-100">
                            <i data-lucide="coffee" class="w-10 h-10 text-slate-300 group-hover:text-brand-500 transition"></i>
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/90 text-slate-600 border border-slate-200 font-mono-num shadow-2xs">
                                Stk: 24
                            </span>
                            <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Promo B2G1
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">Ultra Milk UHT Cokelat 250ml</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-mono-num">8998009012</p>
                    </div>

                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">Rp 6.500</span>
                        <span class="text-[11px] font-medium text-slate-400">/ Kotak</span>
                    </div>
                </div>

                <!-- Product 4: Minyak Goreng Bimoli 2L -->
                <div class="group bg-white hover:bg-slate-50 border border-slate-200/90 hover:border-brand-500/60 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition shadow-2xs hover:shadow-md hover:shadow-brand-500/5 active:scale-[0.98]">
                    <div>
                        <div class="aspect-square bg-slate-50 rounded-xl mb-3 flex items-center justify-center relative overflow-hidden border border-slate-100">
                            <i data-lucide="flame" class="w-10 h-10 text-slate-300 group-hover:text-brand-500 transition"></i>
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/90 text-slate-600 border border-slate-200 font-mono-num shadow-2xs">
                                Stk: 12
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">Bimoli Minyak Goreng Pouch 2 Liter</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-mono-num">8992388102</p>
                    </div>

                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">Rp 38.500</span>
                        <span class="text-[11px] font-medium text-slate-400">/ Pouch</span>
                    </div>
                </div>

                <!-- Product 5: Chitato Sapi Panggang -->
                <div class="group bg-white hover:bg-slate-50 border border-slate-200/90 hover:border-brand-500/60 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition shadow-2xs hover:shadow-md hover:shadow-brand-500/5 active:scale-[0.98]">
                    <div>
                        <div class="aspect-square bg-slate-50 rounded-xl mb-3 flex items-center justify-center relative overflow-hidden border border-slate-100">
                            <i data-lucide="cookie" class="w-10 h-10 text-slate-300 group-hover:text-brand-500 transition"></i>
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-white/90 text-slate-600 border border-slate-200 font-mono-num shadow-2xs">
                                Stk: 35
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">Chitato Sapi Panggang 68g</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-mono-num">8991001201</p>
                    </div>

                    <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">Rp 11.000</span>
                        <span class="text-[11px] font-medium text-slate-400">/ Bungkus</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- RIGHT PANEL: CART, TIER PRICING, & CHECKOUT (Theme Adaptive) -->
    <!-- ========================================================================= -->
    <div class="w-full lg:w-[450px] xl:w-[490px] bg-white flex flex-col justify-between shrink-0 h-full border-t lg:border-t-0 select-none shadow-sm transition-colors duration-200">
        
        <!-- Cart Header Bar -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-white shrink-0 transition-colors duration-200">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-brand-500">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                </div>
                <h3 class="font-bold text-sm text-slate-900">Keranjang Penjualan</h3>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-brand-50 text-brand-600 border border-brand-200">
                    2 Item
                </span>
            </div>

            <!-- Clear & Hold Buttons -->
            <div class="flex items-center gap-2">
                <button title="Hold Transaction (F7)" class="px-2.5 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-amber-600 transition text-xs font-semibold flex items-center gap-1.5 border border-slate-200">
                    <i data-lucide="pause-circle" class="w-3.5 h-3.5"></i>
                    <span>Hold</span>
                </button>
                <button title="Clear Cart" class="p-1.5 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition text-xs font-medium border border-slate-200">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        <!-- Cart Items List (Scrollable) -->
        <div class="flex-1 p-5 overflow-y-auto space-y-3 bg-slate-50/50">
            
            <!-- Cart Item 1: Indomie Goreng (Multi Unit Example) -->
            <div class="p-4 bg-white border border-slate-200/90 hover:border-slate-300 rounded-2xl transition space-y-3 shadow-2xs">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <h5 class="text-xs font-bold text-slate-900 truncate">Indomie Goreng Spesial 85g</h5>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                            <span class="font-mono-num">@ Rp 3.500</span>
                            <!-- Unit Selector Dropdown -->
                            <select class="bg-slate-50 text-[11px] font-bold text-brand-600 px-2 py-0.5 rounded-lg border border-slate-200 focus:outline-none focus:border-brand-500">
                                <option>Pcs (1x)</option>
                                <option>Pak (5x)</option>
                                <option>Karton (40x)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Delete button -->
                    <button class="text-slate-400 hover:text-rose-500 p-1 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <!-- Qty Stepper -->
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl p-0.5">
                        <button class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70">-</button>
                        <input type="text" value="3" class="w-9 text-center bg-transparent text-xs font-bold text-slate-900 font-mono-num focus:outline-none">
                        <button class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70">+</button>
                    </div>

                    <!-- Item Total -->
                    <div class="text-right">
                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">Rp 10.500</span>
                    </div>
                </div>
            </div>

            <!-- Cart Item 2: Aqua (Tiered Price Triggered Example) -->
            <div class="p-4 bg-white border border-brand-300 rounded-2xl transition space-y-3 shadow-2xs relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-20 h-20 bg-brand-50 rounded-full blur-sm pointer-events-none"></div>

                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h5 class="text-xs font-bold text-slate-900 truncate">Aqua Air Mineral 600ml</h5>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">Tier Qty 24+</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                            <span class="line-through text-slate-400 font-mono-num">Rp 3.000</span>
                            <span class="font-bold text-emerald-600 font-mono-num">@ Rp 2.500</span>
                            <span class="text-[10px] text-slate-400 font-medium">(Grosir)</span>
                        </div>
                    </div>
                    
                    <button class="text-slate-400 hover:text-rose-500 p-1 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <!-- Qty Stepper -->
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl p-0.5">
                        <button class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70">-</button>
                        <input type="text" value="24" class="w-9 text-center bg-transparent text-xs font-bold text-slate-900 font-mono-num focus:outline-none">
                        <button class="w-7 h-7 rounded-lg bg-white hover:bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold transition shadow-2xs border border-slate-200/70">+</button>
                    </div>

                    <!-- Item Total -->
                    <div class="text-right">
                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">Rp 60.000</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Order Summary & Checkout Bottom Section -->
        <div class="p-5 sm:p-6 border-t border-slate-200 bg-white shrink-0 space-y-4 transition-colors duration-200">
            
            <!-- Calculation Breakdown -->
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span class="font-mono-num font-semibold text-slate-800">Rp 70.500</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                    <span class="flex items-center gap-1">Diskon Promo <i data-lucide="tag" class="w-3 h-3 text-brand-500"></i></span>
                    <span class="font-mono-num font-semibold text-emerald-600">- Rp 0</span>
                </div>
                <div class="flex items-center justify-between text-slate-500">
                    <span>PPN (11%)</span>
                    <span class="font-mono-num font-semibold text-slate-800">Rp 0 (Non-PPN)</span>
                </div>
                
                <!-- Grand Total Banner -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 block uppercase tracking-wider">Total Tagihan</span>
                        <span class="text-[11px] text-slate-500 font-medium">2 Item (27 Qty)</span>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-brand-600 font-mono-num tracking-tight">Rp 70.500</span>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Quick Grid -->
            <div class="grid grid-cols-4 gap-2 pt-1">
                <button class="py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="banknote" class="w-4 h-4 text-emerald-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-emerald-800">Tunai</span>
                </button>
                <button class="py-2.5 rounded-xl bg-slate-50 hover:bg-brand-50 border border-slate-200 hover:border-brand-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="qr-code" class="w-4 h-4 text-brand-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-brand-800">QRIS</span>
                </button>
                <button class="py-2.5 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="credit-card" class="w-4 h-4 text-blue-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-blue-800">Debit/EDC</span>
                </button>
                <button class="py-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200 hover:border-amber-300 text-center transition flex flex-col items-center justify-center gap-1 shadow-2xs group">
                    <i data-lucide="layers" class="w-4 h-4 text-amber-600 group-hover:scale-110 transition"></i>
                    <span class="text-[10px] font-bold text-slate-700 group-hover:text-amber-800">Split/Piutang</span>
                </button>
            </div>

            <!-- Main Big Pay Action Button (F12) -->
            <button class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 active:scale-[0.99] text-white font-extrabold text-base shadow-md shadow-brand-500/25 flex items-center justify-center gap-2 transition">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <span>BAYAR SEKARANG (F12)</span>
            </button>
        </div>

    </div>

</div>
@endsection
