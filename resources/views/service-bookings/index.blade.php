@extends('layouts.admin')

@section('title', 'Booking & Jadwal Layanan Jasa')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                    <i data-lucide="calendar-check-2" class="w-6 h-6"></i>
                </div>
                Booking & Janji Temu (Appointment)
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Kelola jadwal reservasi layanan jasa (Salon, Barbershop, Bengkel, Spa, Klinik).</p>
        </div>

        <button type="button" onclick="openCreateBookingModal()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Buat Booking Baru</span>
        </button>
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
        <form method="GET" action="{{ route('service-bookings.index') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-3 items-center">
            <!-- Outlet -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Outlet</label>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Tanggal Booking</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0">
            </div>

            <!-- Status -->
            <div class="relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-white focus:ring-0">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
                    <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            @if(request()->hasAny(['date', 'status']))
                <a href="{{ route('service-bookings.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition w-fit" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Bookings Table Card (Solid Green Header Theme) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-emerald-600 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="calendar" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Janji Temu (Bookings)</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $bookings->total() }} Booking
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Tanggal: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-emerald-600 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Booking & Jam</th>
                        <th class="py-3 px-4 border-b border-white/10">Nama Pelanggan & Kontak</th>
                        <th class="py-3 px-4 border-b border-white/10">Layanan yang Dipesan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Durasi Est.</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <td class="py-3.5 px-5">
                                <div class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $booking->booking_number }}</div>
                                <div class="text-[11px] text-slate-400 font-bold">
                                    {{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }} WIB
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $booking->guest_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $booking->guest_phone ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @foreach($booking->items as $item)
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item->product->name ?? 'Layanan' }}</span>
                                            @if($item->staff)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] bg-slate-100 text-slate-600">({{ $item->staff->name }})</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-600 dark:text-slate-400">
                                {{ $booking->estimated_duration_minutes }} Menit
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase
                                    {{ $booking->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $booking->status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $booking->status === 'in_progress' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                    {{ $booking->status === 'completed' ? 'bg-slate-100 text-slate-600' : '' }}
                                    {{ $booking->status === 'cancelled' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                ">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($booking->status === 'confirmed')
                                        <form action="{{ route('service-bookings.status', $booking->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="in_progress" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-600 text-white hover:bg-blue-700 transition" title="Mulai Pengerjaan">
                                                Mulai
                                            </button>
                                        </form>
                                        <form action="{{ route('service-bookings.status', $booking->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="cancelled" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 transition" title="Batalkan">
                                                Batal
                                            </button>
                                        </form>
                                    @elseif($booking->status === 'in_progress')
                                        <form action="{{ route('service-bookings.status', $booking->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" name="status" value="completed" class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition" title="Selesai">
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
                                <p class="text-sm font-semibold text-slate-600">Tidak ada data janji temu untuk tanggal ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Buat Booking Baru -->
<div id="createBookingModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4 shrink-0">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Buat Jadwal Booking Layanan</h3>
            <button type="button" onclick="closeCreateBookingModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('service-bookings.store') }}" method="POST" class="space-y-4 overflow-y-auto flex-1 pr-1">
            @csrf
            <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Pelanggan</label>
                    <input type="text" name="guest_name" required placeholder="Nama lengkap" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">No. HP / WhatsApp</label>
                    <input type="text" name="guest_phone" placeholder="081234..." class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                    <input type="date" name="booking_date" value="{{ today()->toDateString() }}" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jam Booking</label>
                    <input type="time" name="booking_time" value="10:00" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Layanan Utama</label>
                <select name="items[0][product_id]" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold">
                    @foreach($serviceProducts as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} ({{ $prod->duration_minutes ?? 30 }} menit - Rp {{ number_format($prod->selling_price, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilihan Teknisi / Terapis (Opsional)</label>
                <select name="items[0][staff_user_id]" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold">
                    <option value="">-- Siapapun yang Tersedia --</option>
                    @foreach($staffMembers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan</label>
                <textarea name="notes" rows="2" placeholder="Permintaan khusus pelanggan..." class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800 shrink-0">
                <button type="button" onclick="closeCreateBookingModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">Simpan Booking</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateBookingModal() {
        document.getElementById('createBookingModal').classList.remove('hidden');
        document.getElementById('createBookingModal').classList.add('flex');
    }
    function closeCreateBookingModal() {
        document.getElementById('createBookingModal').classList.add('hidden');
        document.getElementById('createBookingModal').classList.remove('flex');
    }
</script>
@endsection
