<!-- MODAL EDIT UNIT -->
<div id="editUnitModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs {{ $errors->hasBag('default') && old('_method') === 'PUT' ? 'flex' : 'hidden' }} items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight">Edit Satuan</h3>
                    <p class="text-[11px] text-slate-400">Perbarui nama takaran, singkatan, dan status</p>
                </div>
            </div>
            <button onclick="closeEditUnitModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editUnitForm" action="{{ old('edit_form_action', '#') }}" method="POST" novalidate class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_form_action" id="edit_form_action" value="{{ old('edit_form_action') }}">
            <input type="hidden" name="unit_id" id="edit_unit_id" value="{{ old('unit_id') }}">

            <!-- Nama Satuan -->
            <div id="edit_group_name">
                <div id="edit_box_name" class="relative rounded-xl border {{ $errors->has('name') && old('_method') === 'PUT' ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                    <label id="edit_label_name" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-700' }}">
                        Nama Satuan <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i id="edit_icon_name" data-lucide="scale" class="w-4 h-4 {{ $errors->has('name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <input type="text" name="name" id="edit_input_name" value="{{ old('_method') === 'PUT' ? old('name') : '' }}" required placeholder="Contoh: Karton, Lusin" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <p id="edit_error_name" class="mt-1 text-[11px] font-medium {{ $errors->has('name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('name') && old('_method') === 'PUT' ? $errors->first('name') : 'Nama lengkap satuan' }}
                </p>
            </div>

            <!-- Simbol / Singkatan -->
            <div id="edit_group_short_name">
                <div id="edit_box_short_name" class="relative rounded-xl border {{ $errors->has('short_name') && old('_method') === 'PUT' ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2">
                    <label id="edit_label_short_name" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('short_name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-700' }}">
                        Simbol / Singkatan <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2.5">
                        <i id="edit_icon_short_name" data-lucide="hash" class="w-4 h-4 {{ $errors->has('short_name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <input type="text" name="short_name" id="edit_input_short_name" value="{{ old('_method') === 'PUT' ? old('short_name') : '' }}" required placeholder="Contoh: krt, lsn" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <p id="edit_error_short_name" class="mt-1 text-[11px] font-medium {{ $errors->has('short_name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('short_name') && old('_method') === 'PUT' ? $errors->first('short_name') : 'Simbol singkatan pada nota kasir' }}
                </p>
            </div>

            <!-- Status Aktif Switch -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/50 border border-slate-200/90">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="toggle-right" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Status Satuan Aktif</span>
                        <span class="block text-[10px] text-slate-400">Dapat dipilih pada opsi satuan produk</span>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" id="edit_input_is_active" value="1" {{ (old('_method') === 'PUT' ? old('is_active') : true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Modal Footer / Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditUnitModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
