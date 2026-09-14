@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        <form method="GET" action="{{ route('raw-materials.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-3 flex-1 w-full">
            
            <!-- Outset Floating-label Search Input -->
            <div class="relative flex-1 min-w-[200px] w-full rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Cari Bahan Baku
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Ketik nama bahan, SKU, atau merk..." 
                           class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white placeholder-slate-400 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Outset Floating-label Category Filter -->
            <div class="relative min-w-[170px] rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Kategori Bahan
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="tag" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="category_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-white focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(request('search') || request('category_id'))
                <a href="{{ route('raw-materials.index') }}" class="text-xs font-bold text-rose-500 hover:text-rose-600 px-2 shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <button onclick="openCreateRawMaterialModal()" class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-brand-500 hover:from-amber-600 hover:to-brand-600 text-white font-bold text-xs shadow-md shadow-amber-500/25 transition shrink-0 whitespace-nowrap">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Bahan Baku</span>
        </button>
    </div>

    <!-- Table Card (Solid Amber Theme) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Table Card Header -->
        <div class="px-6 pt-5 pb-3 bg-gradient-to-r from-amber-500 to-amber-600 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-white">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center shadow-xs">
                    <i data-lucide="boxes" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white tracking-tight flex items-center gap-2">
                        <span>Katalog Master Bahan Baku (Raw Materials)</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white">
                            {{ $rawMaterials->total() }} Total
                        </span>
                    </h3>
                    <p class="text-[11px] text-white/80">Kelola bahan mentah, takaran racikan, dan stok fisik untuk resep F&B</p>
                </div>
            </div>

            @if(request('search') || request('category_id'))
                <div class="text-[11px] text-white flex items-center gap-1.5 bg-black/15 px-3 py-1.5 rounded-xl self-start sm:self-auto">
                    <i data-lucide="filter" class="w-3.5 h-3.5 text-white/90"></i>
                    <span>Filter Aktif</span>
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-amber-500/10 dark:bg-amber-500/20 text-slate-700 dark:text-slate-300 uppercase font-extrabold tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2.5 px-6">Nama Bahan & SKU</th>
                        <th class="py-2.5 px-5">Kategori</th>
                        <th class="py-2.5 px-5">Satuan Takaran</th>
                        <th class="py-2.5 px-5 text-right">Harga Modal (HPP)</th>
                        <th class="py-2.5 px-5 text-right">Stok Fisik Outlet</th>
                        <th class="py-2.5 px-5">Dipakai di Menu Resep</th>
                        <th class="py-2.5 px-5 text-center">Status</th>
                        <th class="py-2.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium text-slate-700 dark:text-slate-300">
                    @forelse($rawMaterials as $item)
                        @php
                            $totalStock = $item->stocks->sum('quantity');
                            $isLow = $item->min_stock > 0 && $totalStock <= $item->min_stock;
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <!-- Info Bahan -->
                            <td class="py-3 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60 shrink-0 font-bold">
                                        <i data-lucide="box" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white leading-snug">{{ $item->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1.5">
                                            <span class="text-amber-600 font-semibold">{{ $item->code }}</span>
                                            @if($item->brand)
                                                <span>•</span>
                                                <span>{{ $item->brand }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="py-3 px-5">
                                @if($item->category)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $item->category->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Satuan Dasar -->
                            <td class="py-3 px-5">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item->baseUnit->name ?? '-' }} ({{ $item->baseUnit->short_name ?? '-' }})</span>
                            </td>

                            <!-- HPP Modal -->
                            <td class="py-3 px-5 text-right font-mono font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($item->purchase_price, 0, ',', '.') }}
                                <span class="text-[10px] font-normal text-slate-400">/{{ $item->baseUnit->short_name ?? '' }}</span>
                            </td>

                            <!-- Stok Fisik -->
                            <td class="py-3 px-5 text-right">
                                <div class="font-mono font-bold {{ $isLow ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
                                    {{ number_format($totalStock, 0, ',', '.') }} {{ $item->baseUnit->short_name ?? '' }}
                                </div>
                                @if($isLow)
                                    <span class="inline-flex items-center text-[9px] font-bold text-rose-500">
                                        <i data-lucide="alert-triangle" class="w-2.5 h-2.5 mr-0.5"></i> Menipis (Min {{ number_format($item->min_stock, 0, ',', '.') }})
                                    </span>
                                @endif
                            </td>

                            <!-- Dipakai di Menu -->
                            <td class="py-3 px-5">
                                @if($item->usedInRecipes && $item->usedInRecipes->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($item->usedInRecipes->take(3) as $recipe)
                                            @if($recipe->product)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    {{ $recipe->product->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if($item->usedInRecipes->count() > 3)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-100 text-slate-600">
                                                +{{ $item->usedInRecipes->count() - 3 }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[10px] italic">Belum terpasang di resep</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-5 text-center">
                                @if($item->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-6 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="openEditRawMaterialModal({{ json_encode($item) }})" class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-slate-100 transition" title="Edit Bahan Baku">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form id="delete-raw-{{ $item->id }}" action="{{ route('raw-materials.destroy', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('delete-raw-{{ $item->id }}', 'Hapus Bahan Baku {{ addslashes($item->name) }}?', 'Data bahan baku ini akan dihapus.')" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Bahan Baku">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada data bahan baku. Klik "Tambah Bahan Baku" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rawMaterials->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $rawMaterials->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
@include('raw_materials._create_modal')
@include('raw_materials._edit_modal')
@endpush

@push('scripts')
<script>
    function openCreateRawMaterialModal() {
        const modal = document.getElementById('createRawMaterialModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeCreateRawMaterialModal() {
        const modal = document.getElementById('createRawMaterialModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditRawMaterialModal(item) {
        document.getElementById('editRawMaterialForm').action = `/raw-materials/${item.id}`;
        document.getElementById('edit_raw_id').value = item.id;
        document.getElementById('edit_raw_name').value = item.name || '';
        document.getElementById('edit_raw_code').value = item.code || '';
        document.getElementById('edit_raw_category_id').value = item.category_id || '';
        document.getElementById('edit_raw_base_unit_id').value = item.base_unit_id || '';
        document.getElementById('edit_raw_purchase_price').value = item.purchase_price || '0';
        document.getElementById('edit_raw_min_stock').value = item.min_stock || '0';
        document.getElementById('edit_raw_brand').value = item.brand || '';
        document.getElementById('edit_raw_description').value = item.description || '';
        document.getElementById('edit_raw_is_active').checked = item.is_active ? true : false;

        const modal = document.getElementById('editRawMaterialModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeEditRawMaterialModal() {
        const modal = document.getElementById('editRawMaterialModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
