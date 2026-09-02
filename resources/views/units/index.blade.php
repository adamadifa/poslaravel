@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
        <form method="GET" action="{{ route('units.index') }}" class="flex items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[220px]">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari satuan..." class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
            </div>
            @if(request('search'))
                <a href="{{ route('units.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">Reset</a>
            @endif
        </form>

        <button onclick="openCreateUnitModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Satuan</span>
        </button>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-xs font-semibold text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-xs font-semibold text-rose-800 dark:text-rose-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
        </div>
    @endif

    <!-- Units Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-850 text-slate-400 uppercase font-bold tracking-wider text-[10px]">
                        <th class="py-3.5 px-5">Nama Satuan</th>
                        <th class="py-3.5 px-5">Simbol / Singkatan</th>
                        <th class="py-3.5 px-5 text-center">Jumlah Produk</th>
                        <th class="py-3.5 px-5 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium text-slate-700 dark:text-slate-200">
                    @forelse($units as $unit)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-5 font-bold text-slate-900 dark:text-white">
                                {{ $unit->name }}
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold">
                                    {{ $unit->short_name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $unit->products_count }}
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                @if($unit->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="openEditUnitModal({{ json_encode($unit) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Edit Satuan">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @if($unit->products_count === 0)
                                        <form action="{{ route('units.destroy', $unit) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus satuan ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Hapus Satuan">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada data satuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($units->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850">
                {{ $units->links() }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL CREATE UNIT -->
<div id="createUnitModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-xl space-y-5 transition-colors">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Tambah Satuan</h3>
            <button onclick="closeCreateUnitModal()" class="text-slate-400 hover:text-slate-600 p-1"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <form action="{{ route('units.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Nama Satuan</label>
                <input type="text" name="name" required placeholder="Contoh: Karton" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:border-brand-500">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Simbol / Singkatan</label>
                <input type="text" name="short_name" required placeholder="Contoh: krt" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:border-brand-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateUnitModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 text-white text-xs font-bold shadow-md shadow-brand-500/25 hover:from-brand-600 hover:to-amber-600 transition">Simpan Satuan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT UNIT -->
<div id="editUnitModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-xl space-y-5 transition-colors">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Edit Satuan</h3>
            <button onclick="closeEditUnitModal()" class="text-slate-400 hover:text-slate-600 p-1"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <form id="editUnitForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Nama Satuan</label>
                <input type="text" id="edit_unit_name" name="name" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:border-brand-500">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Simbol / Singkatan</label>
                <input type="text" id="edit_unit_short_name" name="short_name" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:border-brand-500">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit_unit_is_active" name="is_active" value="1" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-500 border-slate-300">
                <label for="edit_unit_is_active" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">Satuan Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeEditUnitModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 text-white text-xs font-bold shadow-md shadow-brand-500/25 hover:from-brand-600 hover:to-amber-600 transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateUnitModal() {
        const modal = document.getElementById('createUnitModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeCreateUnitModal() {
        const modal = document.getElementById('createUnitModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    function openEditUnitModal(unit) {
        document.getElementById('editUnitForm').action = `/units/${unit.id}`;
        document.getElementById('edit_unit_name').value = unit.name || '';
        document.getElementById('edit_unit_short_name').value = unit.short_name || '';
        document.getElementById('edit_unit_is_active').checked = unit.is_active ? true : false;

        const modal = document.getElementById('editUnitModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeEditUnitModal() {
        const modal = document.getElementById('editUnitModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
