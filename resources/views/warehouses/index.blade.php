@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        
        <!-- Search Filter Form (Full Width) -->
        <form action="{{ route('warehouses.index') }}" method="GET" class="flex items-center gap-3 flex-1 w-full">
            <div class="relative w-full rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Gudang / Cabang
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ketik nama gudang, kode cabang, telepon, atau alamat..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                    @if(request('search'))
                        <a href="{{ route('warehouses.index') }}" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 shrink-0">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Action Buttons -->
        <button 
            onclick="openCreateWarehouseModal()" 
            type="button" 
            class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
        >
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Gudang</span>
        </button>
    </div>

    <!-- WAREHOUSES TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Integrated Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="warehouse" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Gudang & Cabang Outlet</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $warehouses->total() }} Lokasi
                </span>
            </div>
        </div>

        <!-- Table Responsive Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-2.5 px-6">Gudang & Kode</th>
                        <th class="py-2.5 px-5">Telepon & Kontak</th>
                        <th class="py-2.5 px-5">Alamat Lokasi</th>
                        <th class="py-2.5 px-5 text-center">Gudang Utama</th>
                        <th class="py-2.5 px-5 text-center">Status</th>
                        <th class="py-2.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($warehouses as $warehouse)
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- Gudang & Kode -->
                            <td class="py-2.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-200/60 flex items-center justify-center text-brand-600 font-bold text-xs shrink-0">
                                        <i data-lucide="warehouse" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                            <span>{{ $warehouse->name }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $warehouse->code }}
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5 font-medium">
                                            {{ $warehouse->stocks_count }} item SKU terdaftar
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Telepon & Kontak -->
                            <td class="py-2.5 px-5">
                                @if($warehouse->phone)
                                    <div class="font-semibold text-slate-700 flex items-center gap-1 text-[11px]">
                                        <i data-lucide="phone" class="w-3 h-3 text-brand-500"></i>
                                        <span>{{ $warehouse->phone }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Alamat Lokasi -->
                            <td class="py-2.5 px-5">
                                @if($warehouse->address)
                                    <div class="text-[11px] text-slate-600 max-w-[220px] truncate" title="{{ $warehouse->address }}">
                                        {{ $warehouse->address }}
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Gudang Utama (Default) -->
                            <td class="py-2.5 px-5 text-center">
                                @if($warehouse->is_default)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-300 shadow-2xs">
                                        <i data-lucide="star" class="w-3 h-3 fill-amber-500 text-amber-500"></i>
                                        <span>Default Utama</span>
                                    </span>
                                @else
                                    <form action="{{ route('warehouses.set-default', $warehouse) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-0.5 rounded-md text-[10px] font-semibold text-slate-400 hover:text-amber-600 hover:bg-amber-50 border border-slate-200 transition">
                                            Set Default
                                        </button>
                                    </form>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-2.5 px-5 text-center">
                                @if($warehouse->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Non-Aktif</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-2.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        type="button" 
                                        onclick="openEditWarehouseModal({{ json_encode($warehouse) }})" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" 
                                        title="Edit Gudang"
                                    >
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    
                                    @if(!$warehouse->is_default)
                                        <form id="delete-warehouse-{{ $warehouse->id }}" action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="button" 
                                                onclick="confirmDelete('delete-warehouse-{{ $warehouse->id }}', 'Hapus Gudang?', 'Gudang {{ $warehouse->name }} akan dihapus!')" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" 
                                                title="Hapus Gudang"
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
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i data-lucide="warehouse" class="w-6 h-6"></i>
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">Belum Ada Data Gudang</span>
                                    <span class="text-xs text-slate-400 max-w-xs">Data gudang cabang belum ditambahkan atau tidak sesuai filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($warehouses->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<!-- CREATE & EDIT MODALS -->
@include('warehouses._create_modal')
@include('warehouses._edit_modal')
@endpush

@push('scripts')
<script>
    // Create Modal Logic
    function openCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.remove('hidden');
        document.getElementById('createWarehouseModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.add('hidden');
        document.getElementById('createWarehouseModal').classList.remove('flex');
    }

    // Edit Modal Logic
    function openEditWarehouseModal(warehouse) {
        const form = document.getElementById('editWarehouseForm');
        form.action = `/warehouses/${warehouse.id}`;
        document.getElementById('edit_form_action').value = `/warehouses/${warehouse.id}`;
        document.getElementById('edit_warehouse_id').value = warehouse.id;

        document.getElementById('edit_input_name').value = warehouse.name || '';
        document.getElementById('edit_input_code').value = warehouse.code || '';
        document.getElementById('edit_input_phone').value = warehouse.phone || '';
        document.getElementById('edit_input_address').value = warehouse.address || '';
        document.getElementById('edit_input_is_default').checked = warehouse.is_default ? true : false;
        document.getElementById('edit_input_is_active').checked = warehouse.is_active ? true : false;

        document.getElementById('editWarehouseModal').classList.remove('hidden');
        document.getElementById('editWarehouseModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeEditWarehouseModal() {
        document.getElementById('editWarehouseModal').classList.add('hidden');
        document.getElementById('editWarehouseModal').classList.remove('flex');
    }

    // Realtime form feedback for create warehouse
    document.addEventListener('DOMContentLoaded', () => {
        const createNameInput = document.getElementById('create_input_name');
        if (createNameInput) {
            createNameInput.addEventListener('input', () => {
                const val = createNameInput.value.trim();
                const box = document.getElementById('create_box_name');
                const label = document.getElementById('create_label_name');
                const icon = document.getElementById('create_icon_name');
                const err = document.getElementById('create_error_name');

                if (val === '') {
                    box.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/10');
                    box.classList.remove('border-slate-200', 'hover:border-slate-300');
                    label.classList.add('text-rose-500');
                    label.classList.remove('text-slate-700');
                    icon.classList.add('text-rose-500');
                    icon.classList.remove('text-slate-400');
                    err.classList.add('text-rose-500');
                    err.classList.remove('text-slate-400');
                    err.innerText = 'Nama gudang / cabang outlet wajib diisi.';
                } else {
                    box.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/10');
                    box.classList.add('border-slate-200', 'hover:border-slate-300');
                    label.classList.remove('text-rose-500');
                    label.classList.add('text-slate-700');
                    icon.classList.remove('text-rose-500');
                    icon.classList.add('text-slate-400');
                    err.classList.remove('text-rose-500');
                    err.classList.add('text-slate-400');
                    err.innerText = 'Nama identitas lokasi fisik gudang atau toko';
                }
            });
        }
    });

    // Close on Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateWarehouseModal();
            closeEditWarehouseModal();
        }
    });
</script>
@endpush
