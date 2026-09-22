@extends($isKiosk ? 'layouts.kds' : 'layouts.admin')

@section('title', 'Kitchen Display System (KDS) - Layar Dapur')

@section('content')
<div class="{{ $isKiosk ? 'h-full flex flex-col p-4 sm:p-6 overflow-hidden bg-slate-100 dark:bg-slate-950' : 'space-y-6' }}">

    <!-- KDS Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900/90 backdrop-blur-md text-slate-800 dark:text-white p-4 sm:p-5 rounded-3xl shadow-lg border border-slate-200 dark:border-slate-800 shrink-0">
        <div class="flex items-center gap-3.5">
            <div class="p-3 rounded-2xl bg-amber-500 text-slate-950 font-black flex items-center justify-center shadow-lg shadow-amber-500/30">
                <i data-lucide="flame" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    Kitchen Display System
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                        LIVE KDS
                    </span>
                    @if($isKiosk)
                        <span class="px-2 py-0.5 rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 text-[10px] font-black uppercase tracking-wider">
                            KIOSK MODE
                        </span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">Antrian pesanan dapur & bar real-time • Auto-refresh 10s</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <!-- Outlet Selector -->
            <form method="GET" action="{{ route('kitchen.index') }}" class="flex items-center">
                @if($isKiosk)
                    <input type="hidden" name="kiosk" value="1">
                @endif
                <div class="flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 px-3 py-2 shadow-xs">
                    <i data-lucide="warehouse" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="warehouse_id" onchange="this.form.submit()" class="select2-filter bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-0 focus:outline-none cursor-pointer">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <!-- Digital Clock Display -->
            <div class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-800 dark:text-slate-200">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                <span id="kdsLiveClock">--:--:--</span>
            </div>

            <!-- Theme Toggle (Light / Dark) -->
            @if($isKiosk)
                <button type="button" onclick="toggleKdsTheme()" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-amber-500 dark:hover:text-amber-400 transition border border-slate-200 dark:border-slate-700 shadow-xs" title="Ganti Tema (Terang / Gelap)">
                    <i data-lucide="sun-moon" class="w-4 h-4"></i>
                </button>
            @endif

            <!-- Fullscreen / Kiosk Toggle Button -->
            @if(!$isKiosk)
                <a href="{{ route('kitchen.index', ['kiosk' => 1, 'warehouse_id' => $warehouseId]) }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs transition flex items-center gap-1.5 shadow-md shadow-amber-500/20" title="Buka Mode Kiosk Layar Penuh">
                    <i data-lucide="maximize" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Mode Kiosk</span>
                </a>
            @else
                <button type="button" onclick="toggleFullscreen()" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5" title="Toggle Fullscreen Layar">
                    <i data-lucide="expand" class="w-4 h-4 text-amber-500 dark:text-amber-400"></i>
                    <span class="hidden sm:inline">Fullscreen</span>
                </button>
                <a href="{{ route('kitchen.index', ['warehouse_id' => $warehouseId]) }}" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-bold transition flex items-center gap-1.5" title="Keluar dari Mode Kiosk">
                    <i data-lucide="minimize-2" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Keluar Kiosk</span>
                </a>
                <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/50 text-rose-600 dark:text-rose-300 text-xs font-bold transition flex items-center gap-1.5" title="Kembali ke Dashboard">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
            @endif

            <!-- Refresh Button -->
            <button type="button" onclick="window.location.reload()" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 transition border border-slate-200 dark:border-slate-700 shadow-xs" title="Refresh Sekarang">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Active Orders Grid Container -->
    <div class="{{ $isKiosk ? 'flex-1 overflow-y-auto mt-4 pr-1' : 'mt-6' }}">
        <div id="kdsOrdersContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
            @forelse($activeOrders as $order)
                @php
                    $isReady = $order->order_status === 'ready';
                    $isPreparing = $order->order_status === 'preparing';
                    $totalItems = $order->items->count();
                    $readyItems = $order->items->where('item_status', 'ready')->count();
                    $progressPercent = $totalItems > 0 ? round(($readyItems / $totalItems) * 100) : 0;
                    $createdAt = \Carbon\Carbon::parse($order->created_at);
                    $minsElapsed = (int) $createdAt->diffInMinutes();
                    $isUrgent = $minsElapsed >= 15 && !$isReady;
                @endphp

                <div class="bg-white dark:bg-slate-900 border rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between transition-all duration-200 {{ $isReady ? 'border-emerald-500/50 ring-2 ring-emerald-500/20' : ($isUrgent ? 'border-rose-500/60 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-800') }}">
                    
                    <div>
                        <!-- Clean Header Section -->
                        <div class="p-4 border-b border-slate-100 dark:border-slate-800 {{ $isReady ? 'bg-emerald-500/5' : ($isUrgent ? 'bg-rose-500/5' : 'bg-slate-50/70 dark:bg-slate-800/40') }}">
                            <!-- Top Row: Table/Queue & Order Type & Time -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 flex-wrap min-w-0">
                                    <!-- Table / Queue Name -->
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black tracking-tight {{ $isReady ? 'bg-emerald-500 text-white' : ($isUrgent ? 'bg-rose-500 text-white' : 'bg-slate-900 dark:bg-slate-700 text-white') }}">
                                        @if(in_array($order->service_type, ['take_away', 'takeaway', 'delivery']))
                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span class="truncate">Antrian #{{ $order->queue_number ?? $order->id }}</span>
                                        @else
                                            <i data-lucide="utensils" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span class="truncate">{{ $order->diningTable->table_number ? 'Meja ' . $order->diningTable->table_number : 'Dine-In' }}</span>
                                        @endif
                                    </span>

                                    <!-- Service Type Pill -->
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ str_replace('_', ' ', $order->service_type ?? 'Order') }}
                                    </span>
                                </div>

                                <!-- Elapsed Time -->
                                <div class="flex items-center gap-1.5 shrink-0 text-right">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold flex items-center gap-1 {{ $isUrgent ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>{{ $createdAt->format('H:i') }}</span>
                                        <span class="font-sans font-medium text-[10px] opacity-75">({{ $createdAt->diffForHumans(null, true) }})</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Bottom Row: Customer & Invoice & Progress -->
                            <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                                <div class="flex items-center gap-1.5 truncate">
                                    <i data-lucide="user" class="w-3.5 h-3.5 shrink-0 opacity-70"></i>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $order->customer?->name ?? 'Tamu Walk-in' }}</span>
                                    <span class="opacity-40">•</span>
                                    <span class="font-mono text-[11px] opacity-80 shrink-0">{{ $order->invoice_number }}</span>
                                </div>
                                <span class="font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 shrink-0">{{ $readyItems }}/{{ $totalItems }} Siap</span>
                            </div>

                            <!-- Clean Progress Line -->
                            <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-1 mt-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300 {{ $isReady ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>

                        <!-- Items Checklist -->
                        <div class="p-3.5 space-y-2 max-h-[320px] overflow-y-auto">
                            @foreach($order->items as $item)
                                @php
                                    $itemIsReady = $item->item_status === 'ready';
                                @endphp
                                <div class="p-2.5 rounded-xl border transition-all duration-150 flex items-center justify-between gap-3 {{ $itemIsReady ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200/70 dark:border-emerald-900/40' : 'bg-white dark:bg-slate-800/50 border-slate-100 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                    
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-md text-xs font-mono font-bold shrink-0 {{ $itemIsReady ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-900 dark:bg-amber-500/20 dark:text-amber-300' }}">
                                                {{ (int)$item->quantity }}x
                                            </span>
                                            <span class="text-xs font-bold truncate {{ $itemIsReady ? 'text-slate-400 dark:text-slate-500 line-through' : 'text-slate-900 dark:text-slate-100' }}">
                                                {{ $item->product?->name ?? 'Item Menu' }}
                                            </span>
                                        </div>

                                        <!-- Modifiers -->
                                        @if($item->modifiers->count() > 0)
                                            <div class="flex flex-wrap gap-1 mt-1 pl-7">
                                                @foreach($item->modifiers as $mod)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                        + {{ $mod->modifier_name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Notes -->
                                        @if($item->notes)
                                            <div class="mt-1 pl-7 text-[11px] text-rose-500 dark:text-rose-400 font-semibold flex items-center gap-1">
                                                <i data-lucide="message-square" class="w-3 h-3 shrink-0"></i>
                                                <span class="italic">"{{ $item->notes }}"</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Status Item Checklist Button -->
                                    <button type="button" onclick="toggleItemStatus({{ $item->id }}, '{{ $itemIsReady ? 'pending' : 'ready' }}')" class="shrink-0 w-8 h-8 rounded-lg border flex items-center justify-center transition-all cursor-pointer {{ $itemIsReady ? 'bg-emerald-500 text-white border-emerald-500 shadow-xs' : 'bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 border-slate-200 dark:border-slate-700 hover:text-slate-600 hover:border-slate-400' }}" title="{{ $itemIsReady ? 'Batalkan status selesai' : 'Tandai item selesai' }}">
                                        <i data-lucide="check" class="w-4 h-4 {{ $itemIsReady ? 'stroke-[3]' : 'stroke-[2]' }}"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Order Action Card Footer -->
                    <div class="p-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                        @if(!$isReady)
                            <button type="button" onclick="markOrderReady({{ $order->id }})" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black tracking-wide transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                <i data-lucide="check-check" class="w-4 h-4"></i>
                                <span>SIAP SAJI</span>
                            </button>
                        @else
                            <button type="button" onclick="bumpOrder({{ $order->id }})" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black tracking-wide transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                                <span>DISAJIKAN (BUMP)</span>
                            </button>
                        @endif
                    </div>

                </div>
            @empty
                <div class="col-span-full py-20 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-8">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto mb-3 border border-amber-500/20">
                        <i data-lucide="chef-hat" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100">Tidak ada antrian order dapur aktif</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pesanan makanan & minuman dari kasir POS akan muncul otomatis di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

<script>
    // Live Digital Clock
    function updateKdsClock() {
        const now = new Date();
        const clockEl = document.getElementById('kdsLiveClock');
        if (clockEl) {
            clockEl.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
        }
    }
    setInterval(updateKdsClock, 1000);
    updateKdsClock();

    // Toggle Fullscreen Web API
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch((err) => {
                console.log(`Error attempting to enable fullscreen: ${err.message}`);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    // Toggle individual item status
    async function toggleItemStatus(itemId, newStatus) {
        try {
            const res = await fetch(`/kitchen/items/${itemId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: newStatus })
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Mark entire order ready
    async function markOrderReady(saleId) {
        try {
            const res = await fetch(`/kitchen/orders/${saleId}/ready`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Bump order (mark served)
    async function bumpOrder(saleId) {
        try {
            const res = await fetch(`/kitchen/orders/${saleId}/bump`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            if (res.ok) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Auto refresh every 10 seconds
    setInterval(() => {
        window.location.reload();
    }, 10000);
</script>
@endsection

