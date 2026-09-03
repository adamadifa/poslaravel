@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        
        <!-- Search Filter Form (Full Width) -->
        <form action="{{ route('suppliers.index') }}" method="GET" class="flex items-center gap-3 flex-1 w-full">
            <div class="relative w-full rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Pemasok
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ketik nama pemasok, kode, PIC, nomor telepon, atau kota..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                    @if(request('search'))
                        <a href="{{ route('suppliers.index') }}" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 shrink-0">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Action Buttons -->
        <button 
            onclick="openCreateSupplierModal()" 
            type="button" 
            class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
        >
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Pemasok</span>
        </button>
    </div>

    <!-- SUPPLIERS TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Integrated Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="truck" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Pemasok (Supplier)</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $suppliers->total() }} Pemasok
                </span>
            </div>
        </div>

        <!-- Table Responsive Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-2.5 px-6">Pemasok & PIC</th>
                        <th class="py-2.5 px-5">Kontak & Email</th>
                        <th class="py-2.5 px-5">Kota & Alamat</th>
                        <th class="py-2.5 px-5">Tempo Bayar</th>
                        <th class="py-2.5 px-5">Status</th>
                        <th class="py-2.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- Pemasok & PIC -->
                            <td class="py-2.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-200/60 flex items-center justify-center text-brand-600 font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                            <span>{{ $supplier->name }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $supplier->code }}
                                            </span>
                                        </div>
                                        @if($supplier->contact_person)
                                            <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                                <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                                <span>PIC: {{ $supplier->contact_person }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kontak & Email -->
                            <td class="py-2.5 px-5">
                                <div class="space-y-0.5">
                                    @if($supplier->phone)
                                        <div class="font-semibold text-slate-700 flex items-center gap-1 text-[11px]">
                                            <i data-lucide="phone" class="w-3 h-3 text-brand-500"></i>
                                            <span>{{ $supplier->phone }}</span>
                                        </div>
                                    @endif
                                    @if($supplier->email)
                                        <div class="text-[11px] text-slate-400 flex items-center gap-1">
                                            <i data-lucide="mail" class="w-3 h-3 text-slate-400"></i>
                                            <span>{{ $supplier->email }}</span>
                                        </div>
                                    @endif
                                    @if(!$supplier->phone && !$supplier->email)
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kota & Alamat -->
                            <td class="py-2.5 px-5">
                                <div class="max-w-[200px]">
                                    @if($supplier->city)
                                        <div class="font-semibold text-slate-800 text-[11px] flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                            <span class="truncate">{{ $supplier->city }}</span>
                                        </div>
                                    @endif
                                    @if($supplier->address)
                                        <div class="text-[11px] text-slate-400 truncate" title="{{ $supplier->address }}">
                                            {{ $supplier->address }}
                                        </div>
                                    @endif
                                    @if(!$supplier->city && !$supplier->address)
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tempo Bayar -->
                            <td class="py-2.5 px-5">
                                @if($supplier->payment_term_days > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>{{ $supplier->payment_term_days }} Hari</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span>Cash / Tunai</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-2.5 px-5">
                                @if($supplier->is_active)
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
                                        onclick="openEditSupplierModal({{ json_encode($supplier) }})" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" 
                                        title="Edit Pemasok"
                                    >
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    
                                    <form id="delete-supplier-{{ $supplier->id }}" action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="button" 
                                            onclick="confirmDelete('delete-supplier-{{ $supplier->id }}', 'Hapus Pemasok?', 'Pemasok {{ $supplier->name }} akan dihapus dari data master!')" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" 
                                            title="Hapus Pemasok"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i data-lucide="truck" class="w-6 h-6"></i>
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">Belum Ada Data Pemasok</span>
                                    <span class="text-xs text-slate-400 max-w-xs">Data pemasok barang belum ditambahkan atau tidak sesuai kata kunci pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<!-- CREATE & EDIT MODALS -->
@include('suppliers._create_modal')
@include('suppliers._edit_modal')
@endpush

@push('scripts')
<script>
    // Create Modal Logic
    function openCreateSupplierModal() {
        document.getElementById('createSupplierModal').classList.remove('hidden');
        document.getElementById('createSupplierModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeCreateSupplierModal() {
        document.getElementById('createSupplierModal').classList.add('hidden');
        document.getElementById('createSupplierModal').classList.remove('flex');
    }

    // Realtime Form Validation for Create Modal
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
                    err.innerText = 'Nama pemasok / supplier wajib diisi.';
                } else {
                    box.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/10');
                    box.classList.add('border-slate-200', 'hover:border-slate-300');
                    label.classList.remove('text-rose-500');
                    label.classList.add('text-slate-700');
                    icon.classList.remove('text-rose-500');
                    icon.classList.add('text-slate-400');
                    err.classList.remove('text-rose-500');
                    err.classList.add('text-slate-400');
                    err.innerText = 'Nama lengkap badan usaha atau vendor';
                }
            });
        }
    });

    // Edit Modal Logic
    function openEditSupplierModal(supplier) {
        const form = document.getElementById('editSupplierForm');
        form.action = `/suppliers/${supplier.id}`;
        document.getElementById('edit_form_action').value = `/suppliers/${supplier.id}`;
        document.getElementById('edit_supplier_id').value = supplier.id;

        document.getElementById('edit_input_name').value = supplier.name || '';
        document.getElementById('edit_input_code').value = supplier.code || '';
        document.getElementById('edit_input_contact_person').value = supplier.contact_person || '';
        document.getElementById('edit_input_phone').value = supplier.phone || '';
        document.getElementById('edit_input_email').value = supplier.email || '';
        document.getElementById('edit_input_city').value = supplier.city || '';
        document.getElementById('edit_input_payment_term_days').value = supplier.payment_term_days || 0;
        document.getElementById('edit_input_address').value = supplier.address || '';
        document.getElementById('edit_input_is_active').checked = supplier.is_active ? true : false;

        document.getElementById('editSupplierModal').classList.remove('hidden');
        document.getElementById('editSupplierModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeEditSupplierModal() {
        document.getElementById('editSupplierModal').classList.add('hidden');
        document.getElementById('editSupplierModal').classList.remove('flex');
    }

    // Close on backdrop click or Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateSupplierModal();
            closeEditSupplierModal();
        }
    });
</script>
@endpush
