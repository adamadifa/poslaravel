@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        
        <!-- Search & Filter Form (Full Width) -->
        <form action="{{ route('customers.index') }}" method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-3 flex-1 w-full">
            
            <!-- Outset Floating-label Search Input -->
            <div class="relative flex-1 min-w-[200px] w-full rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Pelanggan
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ketik nama pelanggan, kode, nomor HP, email, atau kota..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Group Filter -->
            <div class="relative min-w-[170px] rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Filter Grup Member
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="customer_group_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Grup</option>
                        @foreach($customerGroups as $grp)
                            <option value="{{ $grp->id }}" {{ request('customer_group_id') == $grp->id ? 'selected' : '' }}>
                                {{ $grp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(request('search') || request('customer_group_id'))
                <a href="{{ route('customers.index') }}" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 px-2 shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <!-- Top Action Buttons -->
        <div class="flex items-center gap-2.5 shrink-0">
            <button 
                onclick="openManageGroupsModal()" 
                type="button" 
                class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
            >
                <i data-lucide="badge-percent" class="w-4 h-4 text-brand-500"></i>
                <span>Grup & Diskon</span>
            </button>

            <button 
                onclick="openCreateCustomerModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Pelanggan</span>
            </button>
        </div>
    </div>

    <!-- CUSTOMERS TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Integrated Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="user-check" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Pelanggan & Member</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $customers->total() }} Pelanggan
                </span>
            </div>
        </div>

        <!-- Table Responsive Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-2.5 px-6">Pelanggan & Kode</th>
                        <th class="py-2.5 px-5">Grup & Diskon</th>
                        <th class="py-2.5 px-5">Kontak & Kota</th>
                        <th class="py-2.5 px-5">Batas Kredit</th>
                        <th class="py-2.5 px-5 text-center">Status</th>
                        <th class="py-2.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- Pelanggan & Kode -->
                            <td class="py-2.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-200/60 flex items-center justify-center text-brand-600 font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                            <span>{{ $customer->name }}</span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $customer->code }}
                                            </span>
                                        </div>
                                        @if($customer->address)
                                            <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $customer->address }}">
                                                {{ $customer->address }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Grup & Diskon Member -->
                            <td class="py-2.5 px-5">
                                @if($customer->group)
                                    <div class="space-y-0.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i data-lucide="badge-check" class="w-3 h-3"></i>
                                            <span>{{ $customer->group->name }}</span>
                                        </span>
                                        @if($customer->group->discount_percent > 0)
                                            <div class="text-[10px] font-bold text-amber-600">
                                                Diskon {{ floatval($customer->group->discount_percent) }}% Kasir
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Kontak & Kota -->
                            <td class="py-2.5 px-5">
                                <div class="space-y-0.5">
                                    @if($customer->phone)
                                        <div class="font-semibold text-slate-700 flex items-center gap-1 text-[11px]">
                                            <i data-lucide="phone" class="w-3 h-3 text-brand-500"></i>
                                            <span>{{ $customer->phone }}</span>
                                        </div>
                                    @endif
                                    @if($customer->city)
                                        <div class="text-[11px] text-slate-400 flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                            <span>{{ $customer->city }}</span>
                                        </div>
                                    @endif
                                    @if(!$customer->phone && !$customer->city)
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Batas Kredit / Piutang -->
                            <td class="py-2.5 px-5">
                                @if($customer->credit_limit > 0)
                                    <span class="font-bold text-slate-800 text-xs">
                                        Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Tidak Ada (Cash)</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-2.5 px-5 text-center">
                                @if($customer->is_active)
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
                                        onclick="openEditCustomerModal({{ json_encode($customer) }})" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" 
                                        title="Edit Pelanggan"
                                    >
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    
                                    <form id="delete-customer-{{ $customer->id }}" action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="button" 
                                            onclick="confirmDelete('delete-customer-{{ $customer->id }}', 'Hapus Pelanggan?', 'Pelanggan {{ $customer->name }} akan dihapus dari data master!')" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" 
                                            title="Hapus Pelanggan"
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
                                        <i data-lucide="user-x" class="w-6 h-6"></i>
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">Belum Ada Data Pelanggan</span>
                                    <span class="text-xs text-slate-400 max-w-xs">Data pelanggan atau member belum ditambahkan atau tidak sesuai filter.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<!-- CREATE, EDIT, & GROUP MODALS -->
@include('customers._create_modal')
@include('customers._edit_modal')
@include('customers._groups_modal')
@endpush

@push('scripts')
<script>
    // Create Customer Modal Logic
    function openCreateCustomerModal() {
        document.getElementById('createCustomerModal').classList.remove('hidden');
        document.getElementById('createCustomerModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeCreateCustomerModal() {
        document.getElementById('createCustomerModal').classList.add('hidden');
        document.getElementById('createCustomerModal').classList.remove('flex');
    }

    // Edit Customer Modal Logic
    function openEditCustomerModal(customer) {
        const form = document.getElementById('editCustomerForm');
        form.action = `/customers/${customer.id}`;
        document.getElementById('edit_form_action').value = `/customers/${customer.id}`;
        document.getElementById('edit_customer_id').value = customer.id;

        document.getElementById('edit_input_name').value = customer.name || '';
        document.getElementById('edit_input_code').value = customer.code || '';
        document.getElementById('edit_input_customer_group_id').value = customer.customer_group_id || '';
        document.getElementById('edit_input_phone').value = customer.phone || '';
        document.getElementById('edit_input_email').value = customer.email || '';
        document.getElementById('edit_input_city').value = customer.city || '';
        document.getElementById('edit_input_credit_limit').value = customer.credit_limit || 0;
        document.getElementById('edit_input_address').value = customer.address || '';
        document.getElementById('edit_input_is_active').checked = customer.is_active ? true : false;

        document.getElementById('editCustomerModal').classList.remove('hidden');
        document.getElementById('editCustomerModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeEditCustomerModal() {
        document.getElementById('editCustomerModal').classList.add('hidden');
        document.getElementById('editCustomerModal').classList.remove('flex');
    }

    // Manage Groups Modal Logic
    function openManageGroupsModal() {
        document.getElementById('manageGroupsModal').classList.remove('hidden');
        document.getElementById('manageGroupsModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeManageGroupsModal() {
        document.getElementById('manageGroupsModal').classList.add('hidden');
        document.getElementById('manageGroupsModal').classList.remove('flex');
    }

    function editGroupRow(group) {
        document.getElementById('groupForm').action = `/customer-groups/${group.id}`;
        document.getElementById('group_form_method').value = 'PUT';
        document.getElementById('group_input_name').value = group.name || '';
        document.getElementById('group_input_discount').value = parseFloat(group.discount_percent) || 0;
        document.getElementById('group_input_description').value = group.description || '';
        
        document.getElementById('group_form_title').innerHTML = `<i data-lucide="edit-3" class="w-3.5 h-3.5 text-brand-500"></i><span>Edit Kategori Grup: ${group.name}</span>`;
        document.getElementById('group_btn_text').innerText = 'Simpan Perubahan';
        document.getElementById('group_btn_cancel').classList.remove('hidden');
        lucide.createIcons();
    }

    function resetGroupForm() {
        document.getElementById('groupForm').action = `{{ route('customer-groups.store') }}`;
        document.getElementById('group_form_method').value = 'POST';
        document.getElementById('group_input_name').value = '';
        document.getElementById('group_input_discount').value = 0;
        document.getElementById('group_input_description').value = '';
        
        document.getElementById('group_form_title').innerHTML = `<i data-lucide="plus-circle" class="w-3.5 h-3.5 text-brand-500"></i><span>Tambah Kategori Grup Baru</span>`;
        document.getElementById('group_btn_text').innerText = 'Simpan Grup';
        document.getElementById('group_btn_cancel').classList.add('hidden');
        lucide.createIcons();
    }

    // Realtime form feedback for create customer
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
                    err.innerText = 'Nama pelanggan wajib diisi.';
                } else {
                    box.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/10');
                    box.classList.add('border-slate-200', 'hover:border-slate-300');
                    label.classList.remove('text-rose-500');
                    label.classList.add('text-slate-700');
                    icon.classList.remove('text-rose-500');
                    icon.classList.add('text-slate-400');
                    err.classList.remove('text-rose-500');
                    err.classList.add('text-slate-400');
                    err.innerText = 'Nama lengkap pelanggan atau toko';
                }
            });
        }
    });

    // Close on Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateCustomerModal();
            closeEditCustomerModal();
            closeManageGroupsModal();
        }
    });
</script>
@endpush
