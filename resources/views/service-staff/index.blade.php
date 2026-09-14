@extends('layouts.admin')

@section('title', 'Staff & Komisi Layanan Jasa')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2.5">
                <div class="p-2.5 rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
                Staff & Komisi Layanan Jasa
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Atur penugasan teknisi/terapis untuk setiap jenis layanan jasa serta skema bagi hasil/komisi.</p>
        </div>

        <button type="button" onclick="openCreateModal()" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition flex items-center gap-2 shadow-md shadow-emerald-500/20 cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Penugasan Staff</span>
        </button>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3 shadow-xs">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Assignments Table Card (Solid Green Header Theme) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-emerald-600 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="award" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Matriks Penugasan & Komisi Staff</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $assignments->count() }} Penugasan
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-emerald-600 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Nama Staff / Teknisi</th>
                        <th class="py-3 px-4 border-b border-white/10">Layanan Jasa</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Durasi Layanan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Tipe Komisi</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Nilai Komisi</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                    @forelse($assignments as $assign)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-black text-xs flex items-center justify-center">
                                        {{ strtoupper(substr($assign->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <span>{{ $assign->user->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $assign->product->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">Rp {{ number_format($assign->product->selling_price ?? 0, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-600 dark:text-slate-400">
                                {{ $assign->product->duration_minutes ?? 30 }} Menit
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $assign->commission_type === 'percent' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($assign->commission_type === 'fixed' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $assign->commission_type === 'percent' ? 'Persentase (%)' : ($assign->commission_type === 'fixed' ? 'Nominal Tetap (Rp)' : 'Tanpa Komisi') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                                @if($assign->commission_type === 'percent')
                                    {{ $assign->commission_value }}%
                                @elseif($assign->commission_type === 'fixed')
                                    Rp {{ number_format($assign->commission_value, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <form action="{{ route('service-staff.destroy', $assign->id) }}" method="POST" onsubmit="return confirm('Hapus penugasan ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Hapus Penugasan">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="users" class="w-10 h-10 text-slate-300 mx-auto mb-2"></i>
                                <p class="text-sm font-semibold text-slate-600">Belum ada data penugasan staff layanan</p>
                                <p class="text-xs text-slate-400 mt-1">Tugaskan teknisi/terapis ke layanan jasa untuk mengaktifkan perhitungan komisi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tambah Penugasan Staff -->
<div id="createAssignModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Tugaskan Staff ke Layanan</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('service-staff.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Staff / Teknisi</label>
                <select name="user_id" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white">
                    @foreach($staffMembers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Layanan Jasa</label>
                <select name="product_id" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white">
                    @foreach($serviceProducts as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }} (Rp {{ number_format($prod->selling_price, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tipe Komisi</label>
                    <select name="commission_type" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-semibold">
                        <option value="percent">Persentase (%)</option>
                        <option value="fixed">Nominal Tetap (Rp)</option>
                        <option value="none">Tanpa Komisi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nilai Komisi</label>
                    <input type="number" step="0.1" name="commission_value" value="10" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 shadow-md shadow-emerald-600/20">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createAssignModal').classList.remove('hidden');
        document.getElementById('createAssignModal').classList.add('flex');
    }
    function closeCreateModal() {
        document.getElementById('createAssignModal').classList.add('hidden');
        document.getElementById('createAssignModal').classList.remove('flex');
    }
</script>
@endsection
