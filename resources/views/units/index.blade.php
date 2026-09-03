@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        <form method="GET" action="{{ route('units.index') }}" class="flex items-center gap-3 flex-1 w-full">
            <div class="relative w-full rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Satuan
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama satuan atau simbol (Pcs, Box, Kg)..." class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                    @if(request('search'))
                        <a href="{{ route('units.index') }}" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 shrink-0">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <button onclick="openCreateUnitModal()" class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Satuan</span>
        </button>
    </div>

    <!-- Units Table Card -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Integrated Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="scale" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Satuan (Units)</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $units->total() }} Satuan
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-2.5 px-6">Nama Satuan</th>
                        <th class="py-2.5 px-5">Simbol / Singkatan</th>
                        <th class="py-2.5 px-5 text-center">Jumlah Produk</th>
                        <th class="py-2.5 px-5 text-center">Status</th>
                        <th class="py-2.5 px-6 text-right">Aksi</th>
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
                            <!-- Aksi -->
                            <td class="py-2.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="openEditUnitModal({{ json_encode($unit) }})" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" title="Edit Satuan">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    
                                    @if($unit->products_count === 0)
                                        <form id="delete-unit-{{ $unit->id }}" action="{{ route('units.destroy', $unit) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="button" 
                                                onclick="confirmDelete('delete-unit-{{ $unit->id }}', 'Hapus Satuan?', 'Satuan {{ $unit->name }} akan dihapus dari data master!')" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" 
                                                title="Hapus Satuan"
                                            >
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i data-lucide="scale" class="w-6 h-6"></i>
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">Belum Ada Data Satuan</span>
                                    <span class="text-xs text-slate-400 max-w-xs">Data satuan barang belum ditambahkan atau tidak sesuai kata kunci pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($units->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $units->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<!-- CREATE & EDIT MODALS -->
@include('units._create_modal')
@include('units._edit_modal')
@endpush

@push('scripts')
<script>
    function openCreateUnitModal() {
        document.getElementById('createUnitModal').classList.remove('hidden');
        document.getElementById('createUnitModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeCreateUnitModal() {
        document.getElementById('createUnitModal').classList.add('hidden');
        document.getElementById('createUnitModal').classList.remove('flex');
    }

    function openEditUnitModal(unit) {
        const form = document.getElementById('editUnitForm');
        form.action = `/units/${unit.id}`;
        document.getElementById('edit_form_action').value = `/units/${unit.id}`;
        document.getElementById('edit_unit_id').value = unit.id;

        document.getElementById('edit_input_name').value = unit.name || '';
        document.getElementById('edit_input_short_name').value = unit.short_name || '';
        document.getElementById('edit_input_is_active').checked = unit.is_active ? true : false;

        document.getElementById('editUnitModal').classList.remove('hidden');
        document.getElementById('editUnitModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeEditUnitModal() {
        document.getElementById('editUnitModal').classList.add('hidden');
        document.getElementById('editUnitModal').classList.remove('flex');
    }

    // Close on Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateUnitModal();
            closeEditUnitModal();
        }
    });
</script>
@endpush
