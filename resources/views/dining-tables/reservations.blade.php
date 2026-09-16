@extends('layouts.admin')

@section('title', 'Jadwal Reservasi Meja F&B')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
                Reservasi Meja Resto
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Pencatatan dan pengelolaan jadwal booking meja tamu restoran/kafe.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('tables.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 text-xs font-bold transition flex items-center gap-2 shadow-xs">
                <i data-lucide="layout-grid" class="w-4 h-4 text-amber-600"></i>
                <span>Denah Meja</span>
            </a>
            <button type="button" onclick="openReservationModal()" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition flex items-center gap-2 shadow-md shadow-amber-500/20 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Reservasi Baru</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('tables.reservations') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-3 items-center">
            <!-- Outlet -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Outlet</label>
                <div class="flex items-center gap-2">
                    <i data-lucide="warehouse" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="warehouse_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 cursor-pointer">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Tanggal Booking</label>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 cursor-pointer">
                </div>
            </div>

            <!-- Status -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Status</label>
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="status" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                        <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="seated" {{ $status == 'seated' ? 'selected' : '' }}>Sudah Duduk (Seated)</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
            </div>

            @if(request()->hasAny(['date', 'status']))
                <a href="{{ route('tables.reservations') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition w-fit" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Reservations Table Card (Solid Orange Header Theme) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-amber-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="calendar" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Reservasi Meja</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $reservations->total() }} Reservasi
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Tanggal: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-amber-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Jam Booking</th>
                        <th class="py-3 px-4 border-b border-white/10">Meja & Area</th>
                        <th class="py-3 px-4 border-b border-white/10">Nama Tamu & Kontak</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Jumlah Tamu</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Catatan / Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                    @forelse($reservations as $res)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($res->reservation_time)->format('H:i') }} WIB
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-black text-amber-600">{{ $res->diningTable->table_number ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $res->diningTable->area ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $res->guest_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $res->guest_phone ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ $res->guest_count }} Orang
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase
                                    {{ $res->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $res->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $res->status === 'seated' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                    {{ $res->status === 'completed' ? 'bg-slate-100 text-slate-600' : '' }}
                                    {{ $res->status === 'cancelled' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                ">
                                    {{ $res->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($res->status === 'confirmed' || $res->status === 'pending')
                                        <form action="{{ route('tables.reservations.status', $res->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="seated" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition" title="Tamu Sudah Duduk">
                                                Seated
                                            </button>
                                        </form>
                                        <form action="{{ route('tables.reservations.status', $res->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="cancelled" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 transition" title="Batalkan Reservasi">
                                                Batal
                                            </button>
                                        </form>
                                    @elseif($res->status === 'seated')
                                        <form action="{{ route('tables.reservations.status', $res->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="completed" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-600 text-white hover:bg-blue-700 transition" title="Selesai">
                                                Selesai
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="calendar-x" class="w-10 h-10 text-slate-300 mx-auto mb-2"></i>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada data reservasi untuk tanggal ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reservations->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Tambah Reservasi -->
<div id="createReservationModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Buat Reservasi Meja</h3>
            <button type="button" onclick="closeReservationModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('tables.reservations.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Meja Resto</label>
                <select name="dining_table_id" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white">
                    @foreach($tables as $tbl)
                        <option value="{{ $tbl->id }}">{{ $tbl->table_number }} - {{ $tbl->area }} (Kapasitas {{ $tbl->capacity }} orang)</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Tamu</label>
                    <input type="text" name="guest_name" required placeholder="Bpk. John" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. WhatsApp / HP</label>
                    <input type="text" name="guest_phone" placeholder="081234..." class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                    <input type="date" name="reservation_date" value="{{ today()->toDateString() }}" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jam</label>
                    <input type="time" name="reservation_time" value="19:00" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jumlah Tamu</label>
                    <input type="number" name="guest_count" value="2" min="1" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Dekat jendela / perayaan ulang tahun..." class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeReservationModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 shadow-md shadow-amber-500/20">Simpan Reservasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReservationModal() {
        document.getElementById('createReservationModal').classList.remove('hidden');
        document.getElementById('createReservationModal').classList.add('flex');
    }
    function closeReservationModal() {
        document.getElementById('createReservationModal').classList.add('hidden');
        document.getElementById('createReservationModal').classList.remove('flex');
    }
</script>
@endsection
