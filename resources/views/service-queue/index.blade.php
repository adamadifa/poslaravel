@extends('layouts.admin')

@section('title', 'Antrian & Pengerjaan Layanan Jasa')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 text-white p-4 sm:p-5 rounded-3xl shadow-xl border border-slate-800">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-2xl bg-emerald-500 text-slate-950 font-black flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <i data-lucide="activity" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight text-white flex items-center gap-2">
                    Monitoring Antrian Layanan
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        LIVE QUEUE
                    </span>
                </h1>
                <p class="text-xs text-slate-400 font-medium">Tracking status pengerjaan teknisi/terapis hari ini</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('service-queue.index') }}" class="flex items-center">
                <select name="warehouse_id" onchange="this.form.submit()" class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-200 focus:ring-1 focus:ring-emerald-500 cursor-pointer">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </form>

            <button type="button" onclick="window.location.reload()" class="p-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition" title="Refresh Sekarang">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- 3-Column Queue Board -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1: Antrian Menunggu (Waiting) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-400">
                <div class="flex items-center gap-2 font-black text-xs uppercase tracking-wider">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span>1. Menunggu (Waiting)</span>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-white font-mono font-bold text-xs">
                    {{ $waitingOrders->count() }}
                </span>
            </div>

            <div class="space-y-3">
                @forelse($waitingOrders as $order)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                            <div>
                                <span class="font-mono font-bold text-xs text-slate-900 dark:text-white">{{ $order->invoice_number }}</span>
                                <div class="text-[11px] text-slate-400 font-bold">{{ $order->customer?->name ?? 'Walk-in Pelanggan' }}</div>
                            </div>
                            <span class="text-[10px] font-mono text-slate-400">
                                {{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            @foreach($order->items as $item)
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center justify-between">
                                    <span>{{ $item->product?->name ?? 'Layanan' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $item->product?->duration_minutes ?? 30 }}m</span>
                                </div>
                            @endforeach
                        </div>

                        <form action="{{ route('service-queue.start', $order->id) }}" method="POST" class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            @csrf
                            <div class="flex items-center gap-2">
                                <select name="staff_id" class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-[11px] font-semibold py-1.5 px-2">
                                    <option value="">Pilih Teknisi...</option>
                                    @foreach($staffMembers as $st)
                                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1">
                                    <i data-lucide="play" class="w-3.5 h-3.5"></i> Mulai
                                </button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-400 text-xs">
                        Tidak ada antrian menunggu
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Sedang Dikerjakan (In Progress) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-700 dark:text-blue-400">
                <div class="flex items-center gap-2 font-black text-xs uppercase tracking-wider">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    <span>2. Sedang Dikerjakan</span>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white font-mono font-bold text-xs">
                    {{ $inProgressOrders->count() }}
                </span>
            </div>

            <div class="space-y-3">
                @forelse($inProgressOrders as $order)
                    <div class="bg-white dark:bg-slate-900 border-2 border-blue-400 dark:border-blue-600 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                            <div>
                                <span class="font-mono font-bold text-xs text-slate-900 dark:text-white">{{ $order->invoice_number }}</span>
                                <div class="text-[11px] text-blue-600 font-bold">{{ $order->customer?->name ?? 'Walk-in Pelanggan' }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 flex items-center gap-1">
                                <i data-lucide="user" class="w-3 h-3"></i> {{ $order->assignedStaff?->name ?? 'Teknisi' }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            @foreach($order->items as $item)
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    {{ $item->product?->name ?? 'Layanan' }}
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 font-mono">
                            <span>Mulai: {{ \Carbon\Carbon::parse($order->service_started_at)->format('H:i') }}</span>
                            <span class="font-bold text-slate-600">({{ \Carbon\Carbon::parse($order->service_started_at)->diffForHumans(null, true) }})</span>
                        </div>

                        <form action="{{ route('service-queue.complete', $order->id) }}" method="POST" class="pt-1">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                                <i data-lucide="check-circle" class="w-4 h-4"></i> Tandai Selesai
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-400 text-xs">
                        Tidak ada layanan yang sedang berjalan
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Column 3: Selesai Hari Ini (Completed) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400">
                <div class="flex items-center gap-2 font-black text-xs uppercase tracking-wider">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>3. Selesai Hari Ini</span>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-emerald-600 text-white font-mono font-bold text-xs">
                    {{ $completedOrders->count() }}
                </span>
            </div>

            <div class="space-y-3">
                @forelse($completedOrders as $order)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 shadow-xs space-y-2 opacity-80 hover:opacity-100 transition">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                            <div>
                                <span class="font-mono font-bold text-xs text-slate-900 dark:text-white">{{ $order->invoice_number }}</span>
                                <div class="text-[11px] text-slate-400">{{ $order->customer?->name ?? 'Walk-in' }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Selesai
                            </span>
                        </div>

                        <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                            <span>Staff: {{ $order->assignedStaff?->name ?? '-' }}</span>
                            <span class="font-mono text-emerald-600 font-bold">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-400 text-xs">
                        Belum ada layanan yang diselesaikan hari ini
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>

<script>
    // Auto refresh every 15 seconds
    setInterval(() => {
        window.location.reload();
    }, 15000);
</script>
@endsection
