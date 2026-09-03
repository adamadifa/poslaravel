@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (Frameless & Full Width matching /categories) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 w-full">
        
        <!-- Search & Filter Form (Full Width) -->
        <form action="{{ route('discounts.index') }}" method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-3 flex-1 w-full">
            
            <!-- Outset Floating-label Search Input -->
            <div class="relative flex-1 min-w-[200px] w-full rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Promo Diskon
                </label>
                <div class="flex items-center gap-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ketik nama promo, kode voucher, atau deskripsi..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Type Filter -->
            <div class="relative min-w-[180px] rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Filter Tipe Promo
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="type" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Tipe Diskon</option>
                        <option value="percentage_item" {{ request('type') == 'percentage_item' ? 'selected' : '' }}>Diskon % Item</option>
                        <option value="fixed_item" {{ request('type') == 'fixed_item' ? 'selected' : '' }}>Potongan Rp Item</option>
                        <option value="percentage_invoice" {{ request('type') == 'percentage_invoice' ? 'selected' : '' }}>Diskon % Transaksi</option>
                        <option value="fixed_invoice" {{ request('type') == 'fixed_invoice' ? 'selected' : '' }}>Potongan Rp Transaksi</option>
                        <option value="buy_x_get_y" {{ request('type') == 'buy_x_get_y' ? 'selected' : '' }}>Buy X Get Y</option>
                    </select>
                </div>
            </div>

            <!-- Outset Floating-label Status Filter -->
            <div class="relative min-w-[140px] rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status Promo
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="toggle-left" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
            </div>

            @if(request('search') || request('type') || request('status') !== null)
                <a href="{{ route('discounts.index') }}" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 px-2 shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <!-- Top Action Button -->
        <button 
            onclick="openCreateDiscountModal()" 
            type="button" 
            class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
        >
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Buat Promo Baru</span>
        </button>
    </div>

    <!-- DISCOUNTS TABLE CARD -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Integrated Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="badge-percent" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Promo & Aturan Diskon Kasir</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $discounts->total() }} Promo
                </span>
            </div>
        </div>

        <!-- Table Responsive Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-2.5 px-6">Promo & Voucher</th>
                        <th class="py-2.5 px-5">Skema Diskon</th>
                        <th class="py-2.5 px-5">Target & Syarat</th>
                        <th class="py-2.5 px-5">Periode Berlaku</th>
                        <th class="py-2.5 px-5 text-center">Status</th>
                        <th class="py-2.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($discounts as $discount)
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- Promo & Voucher -->
                            <td class="py-2.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-orange-50 border border-orange-200/60 flex items-center justify-center text-brand-600 font-bold text-xs shrink-0">
                                        <i data-lucide="ticket" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                            <span>{{ $discount->name }}</span>
                                            @if($discount->code)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                                    {{ $discount->code }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($discount->description)
                                            <div class="text-[11px] text-slate-400 truncate max-w-[200px]" title="{{ $discount->description }}">
                                                {{ $discount->description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Skema Diskon -->
                            <td class="py-2.5 px-5">
                                @if($discount->type === 'percentage_item')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ floatval($discount->value) }}% OFF / Item
                                    </span>
                                @elseif($discount->type === 'fixed_item')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Potongan Rp {{ number_format($discount->value, 0, ',', '.') }} / Item
                                    </span>
                                @elseif($discount->type === 'percentage_invoice')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ floatval($discount->value) }}% OFF Total Nota
                                    </span>
                                @elseif($discount->type === 'fixed_invoice')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Potongan Rp {{ number_format($discount->value, 0, ',', '.') }} Total Nota
                                    </span>
                                @elseif($discount->type === 'buy_x_get_y')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Beli {{ floatval($discount->buy_qty) }} Gratis {{ floatval($discount->get_qty) }}
                                    </span>
                                @endif

                                @if($discount->is_combinable)
                                    <span class="block text-[10px] text-emerald-600 font-semibold mt-0.5">Dapat Digabung Promo Lain</span>
                                @endif
                            </td>

                            <!-- Target & Syarat -->
                            <td class="py-2.5 px-5">
                                <div class="space-y-0.5">
                                    <div class="font-semibold text-slate-700 text-[11px]">
                                        {{ $discount->customerGroup ? $discount->customerGroup->name : 'Semua Pelanggan' }}
                                    </div>
                                    @if($discount->min_order_amount > 0)
                                        <div class="text-[10px] text-slate-400">
                                            Min. belanja Rp {{ number_format($discount->min_order_amount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    @if($discount->items->count() > 0)
                                        <div class="text-[10px] text-brand-600 font-bold">
                                            {{ $discount->items->count() }} Produk terpilih
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Periode Berlaku -->
                            <td class="py-2.5 px-5">
                                @if($discount->start_date || $discount->end_date)
                                    <div class="text-[11px] font-medium text-slate-700">
                                        {{ $discount->start_date ? \Carbon\Carbon::parse($discount->start_date)->format('d/m/Y') : 'Sekarang' }}
                                        -
                                        {{ $discount->end_date ? \Carbon\Carbon::parse($discount->end_date)->format('d/m/Y') : 'Selamanya' }}
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Tidak Terbatas</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-2.5 px-5 text-center">
                                @if($discount->is_active)
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
                                        onclick="openEditDiscountModal({{ json_encode($discount) }})" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" 
                                        title="Edit Promo"
                                    >
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    
                                    <form id="delete-discount-{{ $discount->id }}" action="{{ route('discounts.destroy', $discount) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="button" 
                                            onclick="confirmDelete('delete-discount-{{ $discount->id }}', 'Hapus Promo?', 'Promo {{ $discount->name }} akan dihapus!')" 
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" 
                                            title="Hapus Promo"
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
                                        <i data-lucide="badge-percent" class="w-6 h-6"></i>
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">Belum Ada Promo Diskon</span>
                                    <span class="text-xs text-slate-400 max-w-xs">Data diskon atau promosi belum ditambahkan.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($discounts->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $discounts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
<!-- CREATE & EDIT MODALS -->
@include('discounts._create_modal')
@include('discounts._edit_modal')
@endpush

@push('scripts')
<script>
    function toggleDiscountFields(prefix) {
        const type = document.getElementById(`${prefix}_discount_type`).value;
        const bogoBox = document.getElementById(`${prefix}_bogo_fields`);
        const valWrapper = document.getElementById(`${prefix}_value_wrapper`);
        const valLabel = document.getElementById(`${prefix}_value_label`);
        const valPrefix = document.getElementById(`${prefix}_value_prefix`);
        const valSuffix = document.getElementById(`${prefix}_value_suffix`);
        const prodSelector = document.getElementById(`${prefix}_products_selector_wrapper`);

        if (type === 'buy_x_get_y') {
            bogoBox.classList.remove('hidden');
            valWrapper.classList.add('hidden');
            prodSelector.classList.remove('hidden');
        } else {
            bogoBox.classList.add('hidden');
            valWrapper.classList.remove('hidden');

            if (type === 'percentage_item' || type === 'percentage_invoice') {
                valLabel.innerHTML = 'Nilai Diskon (%) <span class="text-rose-500">*</span>';
                valPrefix.classList.add('hidden');
                valSuffix.classList.remove('hidden');
            } else {
                valLabel.innerHTML = 'Nominal Potongan (Rp) <span class="text-rose-500">*</span>';
                valPrefix.classList.remove('hidden');
                valSuffix.classList.add('hidden');
            }

            if (type === 'percentage_invoice' || type === 'fixed_invoice') {
                prodSelector.classList.add('hidden');
            } else {
                prodSelector.classList.remove('hidden');
            }
        }
    }

    function openCreateDiscountModal() {
        document.getElementById('createDiscountModal').classList.remove('hidden');
        document.getElementById('createDiscountModal').classList.add('flex');
        toggleDiscountFields('create');
        lucide.createIcons();
    }

    function closeCreateDiscountModal() {
        document.getElementById('createDiscountModal').classList.add('hidden');
        document.getElementById('createDiscountModal').classList.remove('flex');
    }

    function openEditDiscountModal(discount) {
        const form = document.getElementById('editDiscountForm');
        form.action = `/discounts/${discount.id}`;
        document.getElementById('edit_form_action').value = `/discounts/${discount.id}`;
        document.getElementById('edit_discount_id').value = discount.id;

        document.getElementById('edit_input_name').value = discount.name || '';
        document.getElementById('edit_input_code').value = discount.code || '';
        document.getElementById('edit_discount_type').value = discount.type || 'percentage_item';
        document.getElementById('edit_input_value').value = discount.value || '0';
        document.getElementById('edit_input_buy_qty').value = discount.buy_qty || '2';
        document.getElementById('edit_input_get_qty').value = discount.get_qty || '1';
        document.getElementById('edit_input_reward_product_id').value = discount.reward_product_id || '';
        document.getElementById('edit_input_min_order_amount').value = discount.min_order_amount || '';
        document.getElementById('edit_input_max_discount_amount').value = discount.max_discount_amount || '';
        document.getElementById('edit_input_customer_group_id').value = discount.customer_group_id || '';
        document.getElementById('edit_input_start_date').value = discount.start_date ? discount.start_date.substring(0, 10) : '';
        document.getElementById('edit_input_end_date').value = discount.end_date ? discount.end_date.substring(0, 10) : '';
        document.getElementById('edit_input_is_active').checked = discount.is_active ? true : false;
        document.getElementById('edit_input_is_combinable').checked = discount.is_combinable ? true : false;

        // Check products
        const selectedProductIds = (discount.items || []).map(i => i.product_id);
        const checkboxes = document.querySelectorAll('.edit-product-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectedProductIds.includes(parseInt(cb.value));
        });

        toggleDiscountFields('edit');
        updateSelectedCount('edit');
        document.getElementById('editDiscountModal').classList.remove('hidden');
        document.getElementById('editDiscountModal').classList.add('flex');
        lucide.createIcons();
    }

    function closeEditDiscountModal() {
        document.getElementById('editDiscountModal').classList.add('hidden');
        document.getElementById('editDiscountModal').classList.remove('flex');
    }

    // Live Product Search Filter
    function filterProductList(prefix) {
        const query = document.getElementById(`${prefix}_product_search_input`).value.toLowerCase().trim();
        const items = document.querySelectorAll(`.${prefix}-product-item`);
        
        items.forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const code = item.getAttribute('data-code') || '';
            const barcode = item.getAttribute('data-barcode') || '';

            if (name.includes(query) || code.includes(query) || barcode.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Toggle Select All Visible Products
    function toggleSelectAllProducts(prefix, selectAll) {
        const items = document.querySelectorAll(`.${prefix}-product-item`);
        items.forEach(item => {
            if (item.style.display !== 'none') {
                const cb = item.querySelector(`.${prefix}-product-checkbox`);
                if (cb) cb.checked = selectAll;
            }
        });
        updateSelectedCount(prefix);
    }

    // Update Counter of Selected Products
    function updateSelectedCount(prefix) {
        const checkedCount = document.querySelectorAll(`.${prefix}-product-checkbox:checked`).length;
        const countBadge = document.getElementById(`${prefix}_selected_products_count`);
        if (countBadge) {
            countBadge.innerText = `${checkedCount} Dipilih`;
        }
    }

    // Close on Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateDiscountModal();
            closeEditDiscountModal();
        }
    });
</script>
@endpush
