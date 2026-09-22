@extends($isKiosk ? 'layouts.kds' : 'layouts.admin')

@section('title', 'Monitoring Antrian Layanan & Workshop')

@section('content')
<div class="{{ $isKiosk ? 'h-full flex flex-col p-4 sm:p-6 overflow-hidden bg-slate-100 text-slate-800' : 'space-y-6' }}">

    <!-- Top Command & Control Toolbar (Clean Light Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-4 sm:p-5 shadow-xs space-y-4 shrink-0">
        <!-- Top Row: Title & Action Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Left: Brand Title & Live Status -->
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-bold tracking-tight text-slate-900">Antrian Layanan & Pengerjaan</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live
                        </span>
                        @if($isKiosk)
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-mono uppercase font-bold tracking-wider">
                                Kiosk
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Papan monitoring status pengerjaan teknisi, terapis & operator</p>
                </div>
            </div>

            <!-- Right: Control Tools (Consistent Comfortable Height & Alignment) -->
            <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                <!-- Warehouse Selector -->
                <form method="GET" action="{{ route('service-queue.index') }}" class="flex items-center">
                    @if($isKiosk)
                        <input type="hidden" name="kiosk" value="1">
                    @endif
                    <div class="h-11 flex items-center bg-slate-50 hover:bg-slate-100/80 border border-slate-200 rounded-xl px-3.5 text-xs font-semibold text-slate-800 shadow-xs transition">
                        <i data-lucide="store" class="w-4 h-4 text-slate-400 mr-2.5 shrink-0"></i>
                        <select name="warehouse_id" onchange="this.form.submit()" class="bg-transparent border-0 p-0 pr-6 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <!-- Digital Clock -->
                <div class="hidden md:flex h-11 items-center gap-2.5 px-4 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-700 shadow-xs">
                    <i data-lucide="clock" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span id="serviceLiveClock">--:--:--</span>
                </div>

                <!-- Mode Kiosk / Fullscreen -->
                @if(!$isKiosk)
                    <a href="{{ route('service-queue.index', ['kiosk' => 1, 'warehouse_id' => $warehouseId]) }}" class="h-11 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs" title="Buka Mode Kiosk Layar Penuh">
                        <i data-lucide="maximize" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Mode Kiosk</span>
                    </a>
                @else
                    <button type="button" onclick="toggleFullscreen()" class="h-11 px-4 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold transition flex items-center gap-2 shadow-xs" title="Toggle Fullscreen Layar">
                        <i data-lucide="expand" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Fullscreen</span>
                    </button>
                    <a href="{{ route('service-queue.index', ['warehouse_id' => $warehouseId]) }}" class="h-11 px-4 rounded-xl bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 border border-slate-200 text-xs font-bold transition flex items-center gap-2 shadow-xs" title="Keluar dari Kiosk">
                        <i data-lucide="minimize-2" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Keluar</span>
                    </a>
                @endif

                <!-- Refresh Button -->
                <button type="button" onclick="window.location.reload()" class="h-11 w-11 rounded-xl bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 border border-slate-200 shadow-xs transition flex items-center justify-center shrink-0" title="Refresh Sekarang">
                    <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Bottom Row: Unified 3-Column Status Metric Ribbon -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100">
            <!-- Menunggu -->
            <div class="flex items-center justify-between px-4 py-2 rounded-xl bg-amber-50/70 border border-amber-200/80">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-semibold text-amber-800">Menunggu Antrian</span>
                </div>
                <span class="font-mono text-xs font-bold text-amber-900 bg-white px-2 py-0.5 rounded-md border border-amber-200/60 shadow-2xs">{{ $waitingOrders->count() }}</span>
            </div>

            <!-- Dikerjakan -->
            <div class="flex items-center justify-between px-4 py-2 rounded-xl bg-blue-50/70 border border-blue-200/80">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <span class="text-xs font-semibold text-blue-800">Sedang Dikerjakan</span>
                </div>
                <span class="font-mono text-xs font-bold text-blue-900 bg-white px-2 py-0.5 rounded-md border border-blue-200/60 shadow-2xs">{{ $inProgressOrders->count() }}</span>
            </div>

            <!-- Selesai Hari Ini -->
            <div class="flex items-center justify-between px-4 py-2 rounded-xl bg-emerald-50/70 border border-emerald-200/80">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span class="text-xs font-semibold text-emerald-800">Selesai Hari Ini</span>
                </div>
                <span class="font-mono text-xs font-bold text-emerald-900 bg-white px-2 py-0.5 rounded-md border border-emerald-200/60 shadow-2xs">{{ $completedOrders->count() }}</span>
            </div>
        </div>
    </div>

    <!-- 3-Column Board with Comfortable Spacing -->
    <div class="{{ $isKiosk ? 'flex-1 overflow-hidden min-h-0 mt-4' : 'mt-6' }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 {{ $isKiosk ? 'h-full overflow-hidden' : '' }}">

            <!-- 1. COLUMN: MENUNGGU (QUEUED) -->
            <div class="flex flex-col {{ $isKiosk ? 'h-full overflow-hidden' : '' }} bg-slate-100/90 border border-slate-200/90 rounded-2xl shadow-xs">
                <!-- Column Header -->
                <div class="px-5 py-3.5 border-b border-slate-200/90 flex items-center justify-between bg-white rounded-t-2xl shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">1. Menunggu Antrian</h2>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 font-mono text-xs font-bold">
                        {{ $waitingOrders->count() }}
                    </span>
                </div>

                <!-- Cards Container with Comfortable Padding and Spacing -->
                <div class="p-4 sm:p-5 space-y-4 {{ $isKiosk ? 'flex-1 overflow-y-auto' : '' }}">
                    @forelse($waitingOrders as $order)
                        @php
                            $createdAt = \Carbon\Carbon::parse($order->created_at);
                            $minsWaiting = (int) $createdAt->diffInMinutes();
                            $isUrgent = $minsWaiting >= 20;
                            $totalDuration = $order->items->sum(fn($it) => (int) ($it->product?->duration_minutes ?? 30));
                        @endphp
                        <div class="bg-white border {{ $isUrgent ? 'border-rose-300 ring-2 ring-rose-500/10' : 'border-slate-200' }} rounded-2xl p-4 sm:p-5 space-y-4 shadow-sm hover:shadow-md transition">
                            <!-- Ticket Header -->
                            <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm font-bold text-slate-900 tracking-tight">{{ $order->invoice_number }}</span>
                                        @if($order->table_id && $order->diningTable)
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-mono font-bold">
                                                Meja {{ $order->diningTable->table_number }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs font-bold text-slate-800 mt-1">
                                        {{ $order->customer?->name ?? 'Walk-in Customer' }}
                                    </div>
                                    @if($order->customer?->phone)
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $order->customer->phone }}</div>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono font-bold {{ $isUrgent ? 'bg-rose-50 text-rose-700 border border-rose-200 animate-pulse' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                        {{ $minsWaiting > 0 ? $minsWaiting . 'm lalu' : '< 1m lalu' }}
                                    </span>
                                    <div class="text-[11px] text-slate-400 font-mono mt-1">{{ $createdAt->format('H:i') }} WIB</div>
                                </div>
                            </div>

                            <!-- Services List with Generous Item Spacing -->
                            <div class="space-y-2">
                                @foreach($order->items as $item)
                                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 flex items-start justify-between gap-3 text-xs">
                                        <div>
                                            <div class="font-bold text-slate-800">
                                                {{ $item->product?->name ?? 'Layanan Jasa' }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                                Qty: <strong>{{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }} {{ $item->unit?->short_name ?? $item->product?->baseUnit?->short_name ?? 'Item' }}</strong>
                                            </div>
                                            @if($item->notes)
                                                <div class="text-[11px] text-amber-700 mt-1.5 font-medium bg-amber-50/80 border border-amber-200/60 rounded-md px-2 py-0.5 inline-block">
                                                    Catatan: {{ $item->notes }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-xs font-mono font-bold text-slate-600 bg-white border border-slate-200 px-2 py-1 rounded-lg shrink-0 shadow-2xs">
                                            {{ (int) ($item->product?->duration_minutes ?? 30) }}m
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Total Duration Estimate -->
                            <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                                <span>Estimasi Total Waktu:</span>
                                <span class="font-mono font-bold text-slate-800">~{{ $totalDuration }} Menit</span>
                            </div>

                            <!-- Start Service Form -->
                            <form action="{{ route('service-queue.start', $order->id) }}" method="POST" class="pt-3 border-t border-slate-100 flex items-center gap-2.5">
                                @csrf
                                <select name="staff_id" required class="flex-1 bg-slate-50 border border-slate-300 text-slate-800 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer font-medium">
                                    <option value="">Pilih Teknisi / Staf...</option>
                                    @foreach($staffMembers as $st)
                                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm shrink-0">
                                    <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                    <span>Mulai</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-10 text-center bg-white border border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs">
                            <i data-lucide="inbox" class="w-7 h-7 mx-auto text-slate-300 mb-2"></i>
                            Tidak ada antrian menunggu
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 2. COLUMN: SEDANG DIKERJAKAN (IN PROGRESS) -->
            <div class="flex flex-col {{ $isKiosk ? 'h-full overflow-hidden' : '' }} bg-slate-100/90 border border-slate-200/90 rounded-2xl shadow-xs">
                <!-- Column Header -->
                <div class="px-5 py-3.5 border-b border-slate-200/90 flex items-center justify-between bg-white rounded-t-2xl shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-blue-600 animate-pulse"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">2. Sedang Dikerjakan</h2>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-mono text-xs font-bold">
                        {{ $inProgressOrders->count() }}
                    </span>
                </div>

                <!-- Cards Container with Comfortable Padding and Spacing -->
                <div class="p-4 sm:p-5 space-y-4 {{ $isKiosk ? 'flex-1 overflow-y-auto' : '' }}">
                    @forelse($inProgressOrders as $order)
                        @php
                            $startedAt = \Carbon\Carbon::parse($order->service_started_at ?? $order->created_at);
                            $minsRunning = (int) $startedAt->diffInMinutes();
                            $targetDuration = max(1, (int) $order->items->sum(fn($it) => $it->product?->duration_minutes ?? 30));
                            $progressPct = min(100, round(($minsRunning / $targetDuration) * 100));
                            $isOverdue = $minsRunning > $targetDuration;
                        @endphp
                        <div class="bg-white border-2 {{ $isOverdue ? 'border-amber-400' : 'border-blue-400' }} rounded-2xl p-4 sm:p-5 space-y-4 shadow-sm hover:shadow-md transition relative overflow-hidden">
                            <!-- Header & Stopwatch -->
                            <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                                <div>
                                    <span class="font-mono text-sm font-bold text-slate-900 tracking-tight">{{ $order->invoice_number }}</span>
                                    <div class="text-xs font-bold text-blue-700 mt-1">
                                        {{ $order->customer?->name ?? 'Walk-in Customer' }}
                                    </div>
                                    @if($order->customer?->phone)
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $order->customer->phone }}</div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-mono font-bold {{ $isOverdue ? 'bg-amber-50 text-amber-800 border border-amber-300' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                        <i data-lucide="timer" class="w-3.5 h-3.5 animate-spin text-blue-600"></i>
                                        <span class="service-timer" data-start="{{ $startedAt->toISOString() }}">{{ $minsRunning }}m</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono mt-1">Target: ~{{ $targetDuration }}m</div>
                                </div>
                            </div>

                            <!-- Assigned Staff Chip -->
                            <div class="flex items-center justify-between bg-blue-50/60 border border-blue-100 rounded-xl px-3 py-2 text-xs">
                                <div class="flex items-center gap-2 text-slate-800 font-bold">
                                    <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-[10px]">
                                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span>{{ $order->assignedStaff?->name ?? 'Staf Bertugas' }}</span>
                                </div>
                                <span class="text-[11px] font-mono text-blue-700">Mulai: {{ $startedAt->format('H:i') }}</span>
                            </div>

                            <!-- Items Summary -->
                            <div class="space-y-1.5">
                                @foreach($order->items as $item)
                                    <div class="text-xs text-slate-700 flex items-center justify-between">
                                        <span class="truncate font-medium">&bull; {{ $item->product?->name ?? 'Layanan' }} ({{ rtrim(rtrim(number_format($item->quantity, 2, '.', ''), '0'), '.') }} {{ $item->unit?->short_name ?? $item->product?->baseUnit?->short_name ?? 'x' }})</span>
                                        <span class="text-[11px] text-slate-400 font-mono shrink-0">{{ (int) ($item->product?->duration_minutes ?? 30) }}m</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Action: Complete -->
                            <form action="{{ route('service-queue.complete', $order->id) }}" method="POST" class="pt-3 border-t border-slate-100">
                                @csrf
                                <button type="submit" onclick="return confirm('Tandai selesai untuk pesanan {{ $order->invoice_number }}?')" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Tandai Selesai</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-10 text-center bg-white border border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs">
                            <i data-lucide="play-circle" class="w-7 h-7 mx-auto text-slate-300 mb-2"></i>
                            Tidak ada layanan yang sedang dikerjakan
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 3. COLUMN: SELESAI HARI INI (COMPLETED) -->
            <div class="flex flex-col {{ $isKiosk ? 'h-full overflow-hidden' : '' }} bg-slate-100/90 border border-slate-200/90 rounded-2xl shadow-xs">
                <!-- Column Header -->
                <div class="px-5 py-3.5 border-b border-slate-200/90 flex items-center justify-between bg-white rounded-t-2xl shrink-0">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">3. Selesai Hari Ini</h2>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-xs font-bold">
                        {{ $completedOrders->count() }}
                    </span>
                </div>

                <!-- Cards Container with Comfortable Padding and Spacing -->
                <div class="p-4 sm:p-5 space-y-4 {{ $isKiosk ? 'flex-1 overflow-y-auto' : '' }}">
                    @forelse($completedOrders as $order)
                        @php
                            $completedAt = \Carbon\Carbon::parse($order->service_completed_at ?? $order->updated_at);
                            $startedAt = \Carbon\Carbon::parse($order->service_started_at ?? $order->created_at);
                            $totalDuration = max(1, (int) $startedAt->diffInMinutes($completedAt));
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 space-y-3 shadow-xs hover:shadow-sm transition">
                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                                <div>
                                    <span class="font-mono text-sm font-bold text-slate-900">{{ $order->invoice_number }}</span>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $order->customer?->name ?? 'Walk-in Customer' }}</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Selesai {{ $completedAt->format('H:i') }}
                                </span>
                            </div>

                            <div class="text-xs text-slate-600 flex items-center justify-between font-medium">
                                <span>Staf: <strong class="text-slate-800">{{ $order->assignedStaff?->name ?? '-' }}</strong></span>
                                <span class="font-mono text-slate-500">Durasi: {{ $totalDuration }} mnt</span>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                                <span class="text-slate-400">{{ $order->items->count() }} Layanan</span>
                                <span class="font-mono font-bold text-emerald-600">
                                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center bg-white border border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs">
                            <i data-lucide="check-circle" class="w-7 h-7 mx-auto text-slate-300 mb-2"></i>
                            Belum ada layanan yang selesai hari ini
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Live Digital Clock
    function updateClock() {
        const clockEl = document.getElementById('serviceLiveClock');
        if (!clockEl) return;
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        clockEl.textContent = `${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Live Service Timer Stopwatch for in-progress services
    function updateServiceTimers() {
        document.querySelectorAll('.service-timer').forEach(el => {
            const startIso = el.getAttribute('data-start');
            if (!startIso) return;
            const start = new Date(startIso);
            const now = new Date();
            const diffSecs = Math.max(0, Math.floor((now - start) / 1000));
            const hours = Math.floor(diffSecs / 3600);
            const mins = Math.floor((diffSecs % 3600) / 60);
            const secs = diffSecs % 60;
            if (hours > 0) {
                el.textContent = `${hours}j ${mins}m ${String(secs).padStart(2, '0')}s`;
            } else {
                el.textContent = `${mins}m ${String(secs).padStart(2, '0')}s`;
            }
        });
    }
    setInterval(updateServiceTimers, 1000);
    updateServiceTimers();

    // Fullscreen Toggle Helper
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.warn(`Fullscreen error: ${err.message}`);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    // Auto Refresh every 10 seconds for real-time queue synchronization
    setInterval(() => {
        const activeEl = document.activeElement;
        const isInteracting = activeEl && (activeEl.tagName === 'SELECT' || activeEl.tagName === 'INPUT');
        if (!isInteracting) {
            window.location.reload();
        }
    }, 10000);
</script>
@endpush
