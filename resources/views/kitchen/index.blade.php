@extends('layouts.admin')

@section('title', 'Kitchen Display System (KDS) - Layar Dapur')

@section('content')
<div class="space-y-6">

    <!-- KDS Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 text-white p-4 sm:p-5 rounded-3xl shadow-xl border border-slate-800">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-2xl bg-amber-500 text-slate-950 font-black flex items-center justify-center shadow-lg shadow-amber-500/30">
                <i data-lucide="flame" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight text-white flex items-center gap-2">
                    Kitchen Display System
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        LIVE
                    </span>
                </h1>
                <p class="text-xs text-slate-400 font-medium">Antrian pesanan dapur & bar real-time</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Outlet Selector -->
            <form method="GET" action="{{ route('kitchen.index') }}" class="flex items-center">
                <div class="flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5">
                    <i data-lucide="warehouse" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="warehouse_id" onchange="this.form.submit()" class="select2-filter bg-transparent border-0 p-0 text-xs font-bold text-slate-200 focus:ring-0 focus:outline-none cursor-pointer">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <!-- Auto refresh status indicator -->
            <button type="button" onclick="window.location.reload()" class="p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition" title="Refresh Sekarang">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Active Orders Grid -->
    <div id="kdsOrdersContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($activeOrders as $order)
            <div class="bg-white dark:bg-slate-900 border-2 rounded-3xl shadow-sm overflow-hidden flex flex-col justify-between transition-all {{ $order->order_status === 'ready' ? 'border-emerald-400 dark:border-emerald-600 shadow-emerald-500/10' : ($order->order_status === 'preparing' ? 'border-amber-400 dark:border-amber-600 shadow-amber-500/10' : 'border-rose-400 dark:border-rose-600 animate-pulse-once') }}">
                
                <!-- Order Card Header -->
                <div>
                    <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between {{ $order->order_status === 'ready' ? 'bg-emerald-500 text-white' : ($order->order_status === 'preparing' ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white') }}">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-base font-black tracking-tight">
                                    {{ $order->service_type === 'take_away' ? 'Antrian: ' . ($order->queue_number ?? 'TA') : 'Meja: ' . ($order->diningTable->table_number ?? 'Dine-In') }}
                                </span>
                            </div>
                            <div class="text-[11px] font-medium opacity-90">
                                {{ $order->invoice_number }} • {{ $order->customer?->name ?? 'Tamu Walk-in' }}
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xs font-mono font-black block">
                                {{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}
                            </span>
                            <span class="text-[10px] font-bold opacity-80">
                                {{ \Carbon\Carbon::parse($order->created_at)->diffForHumans(null, true) }}
                            </span>
                        </div>
                    </div>

                    <!-- Items Checklist -->
                    <div class="p-4 space-y-3">
                        @foreach($order->items as $item)
                            <div class="p-2.5 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 font-mono text-xs">
                                            {{ (int)$item->quantity }}x
                                        </span>
                                        <span>{{ $item->product?->name ?? 'Item' }}</span>
                                    </div>

                                    <!-- Modifiers & Notes -->
                                    @if($item->modifiers->count() > 0)
                                        <div class="text-[11px] text-amber-700 dark:text-amber-400 font-semibold mt-1 space-x-1">
                                            @foreach($item->modifiers as $mod)
                                                <span class="inline-block px-1.5 py-0.2 rounded bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40">
                                                    + {{ $mod->modifier_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($item->notes)
                                        <div class="text-[11px] text-rose-600 font-bold mt-1 flex items-center gap-1">
                                            <i data-lucide="message-square" class="w-3 h-3"></i>
                                            <span>"{{ $item->notes }}"</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Status Badge Toggle -->
                                <button type="button" onclick="toggleItemStatus({{ $item->id }}, '{{ $item->item_status === 'ready' ? 'pending' : 'ready' }}')" class="shrink-0 p-1.5 rounded-xl border transition cursor-pointer {{ $item->item_status === 'ready' ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-white dark:bg-slate-800 text-slate-400 border-slate-200' }}" title="Tandai Item Selesai">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Action Card Footer -->
                <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-between gap-2">
                    @if($order->order_status !== 'ready')
                        <button type="button" onclick="markOrderReady({{ $order->id }})" class="flex-1 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-black transition flex items-center justify-center gap-1.5 shadow-md shadow-amber-500/20 cursor-pointer">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                            <span>Siap Saji</span>
                        </button>
                    @else
                        <button type="button" onclick="bumpOrder({{ $order->id }})" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black transition flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/20 cursor-pointer">
                            <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                            <span>Disajikan (Bump)</span>
                        </button>
                    @endif
                </div>

            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
                <i data-lucide="coffee" class="w-14 h-14 text-slate-300 mx-auto mb-3"></i>
                <h3 class="text-base font-black text-slate-700 dark:text-slate-200">Tidak ada antrian order dapur aktif</h3>
                <p class="text-xs text-slate-400 mt-1">Pesanan makanan & minuman dari kasir POS akan muncul otomatis di layar ini.</p>
            </div>
        @endforelse
    </div>

</div>

<script>
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
