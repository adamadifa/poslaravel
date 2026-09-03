<!-- MODAL MANAGE CUSTOMER GROUPS -->
<div id="manageGroupsModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="badge-percent" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Kelola Grup & Diskon Member</h3>
                    <p class="text-[11px] text-slate-400">Atur kategori keanggotaan dan persentase potongan harga kasir</p>
                </div>
            </div>
            <button onclick="closeManageGroupsModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Form Add/Edit Group -->
            <form id="groupForm" action="{{ route('customer-groups.store') }}" method="POST" class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3.5">
                @csrf
                <input type="hidden" name="_method" id="group_form_method" value="POST">
                <input type="hidden" name="is_group_modal" value="1">
                
                <h4 id="group_form_title" class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-brand-500"></i>
                    <span>Tambah Kategori Grup Baru</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Nama Grup -->
                    <div class="sm:col-span-2">
                        <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-2.5 pb-1.5">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-slate-700">
                                Nama Grup Member <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="group_input_name" required placeholder="Contoh: VIP Gold / Mitra Reseller" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                        </div>
                    </div>

                    <!-- Diskon Persen -->
                    <div>
                        <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-2.5 pb-1.5">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-slate-700">
                                Diskon (%)
                            </label>
                            <div class="flex items-center gap-1">
                                <input type="number" step="0.1" name="discount_percent" id="group_input_discount" value="0" placeholder="0" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none">
                                <span class="text-xs font-bold text-slate-400">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-3.5 pt-2.5 pb-1.5">
                    <label class="absolute -top-2 left-3 bg-white px-1 text-[10px] font-bold text-slate-700">
                        Deskripsi / Syarat Member (Opsional)
                    </label>
                    <input type="text" name="description" id="group_input_description" placeholder="Minimal belanja Rp 100.000 / pendaftaran member" class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" onclick="resetGroupForm()" id="group_btn_cancel" class="hidden px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">Batal Edit</button>
                    <button type="submit" id="group_btn_submit" class="px-4 py-1.5 rounded-lg bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span id="group_btn_text">Simpan Grup</span>
                    </button>
                </div>
            </form>

            <!-- Table List of Groups -->
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                            <th class="py-2.5 px-4">Nama Grup</th>
                            <th class="py-2.5 px-4 text-center">Diskon Kasir</th>
                            <th class="py-2.5 px-4 text-center">Jumlah Member</th>
                            <th class="py-2.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($customerGroups as $grp)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-4">
                                    <div class="font-bold text-slate-800 text-xs">{{ $grp->name }}</div>
                                    @if($grp->description)
                                        <div class="text-[10px] text-slate-400">{{ $grp->description }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    @if($grp->discount_percent > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ floatval($grp->discount_percent) }}% OFF
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">0% (Normal)</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-center font-bold text-slate-700">
                                    {{ $grp->customers()->count() }} Member
                                </td>
                                <td class="py-2.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" onclick="editGroupRow({{ json_encode($grp) }})" class="p-1 rounded text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" title="Edit Grup">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </button>
                                        @if($grp->customers()->count() === 0)
                                            <form id="delete-group-{{ $grp->id }}" action="{{ route('customer-groups.destroy', $grp) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDelete('delete-group-{{ $grp->id }}', 'Hapus Grup?', 'Grup {{ $grp->name }} akan dihapus!')" class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Grup">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
