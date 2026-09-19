<!-- MODAL EDIT CATEGORY -->
<div id="editCategoryModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs {{ $errors->hasBag('default') && old('_method') === 'PUT' ? 'flex' : 'hidden' }} items-center justify-center p-4">
    <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-6 transition-all">
        <!-- Header -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-lg text-slate-900 tracking-tight">Edit Kategori</h3>
                <p class="text-xs text-slate-400 mt-0.5">Perbarui informasi dan struktur kategori produk</p>
            </div>
            <button onclick="closeEditCategoryModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editCategoryForm" action="{{ old('edit_form_action', '#') }}" method="POST" novalidate class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_form_action" id="edit_form_action" value="{{ old('edit_form_action') }}">
            
            <!-- Nama Kategori Input -->
            <div id="edit_group_name">
                <div id="edit_box_name" class="relative rounded-xl border {{ $errors->has('name') && old('_method') === 'PUT' ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2.5">
                    <label id="edit_label_name" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-700' }}">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <i id="edit_icon_name" data-lucide="tag" class="w-4 h-4 {{ $errors->has('name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <input type="text" 
                               id="edit_cat_name" 
                               name="name" 
                               value="{{ old('_method') === 'PUT' ? old('name') : '' }}" 
                               required 
                               placeholder="Masukkan nama kategori" 
                               class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <p id="edit_error_name" class="mt-1 text-[11px] font-medium {{ $errors->has('name') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('name') && old('_method') === 'PUT' ? $errors->first('name') : 'Nama lengkap wajib diisi' }}
                </p>
            </div>

            <!-- Induk Kategori Selector -->
            <div id="edit_group_parent_id">
                <div id="edit_box_parent_id" class="relative rounded-xl border {{ $errors->has('parent_id') && old('_method') === 'PUT' ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2.5">
                    <label id="edit_label_parent_id" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('parent_id') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-700' }}">
                        Induk Kategori (Opsional)
                    </label>
                    <div class="flex items-center gap-3">
                        <i id="edit_icon_parent_id" data-lucide="layers" class="w-4 h-4 {{ $errors->has('parent_id') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <select id="edit_cat_parent_id" name="parent_id" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Kategori Utama (Tanpa Induk)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ (old('_method') === 'PUT' && old('parent_id') == $parent->id) ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p id="edit_error_parent_id" class="mt-1 text-[11px] font-medium {{ $errors->has('parent_id') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('parent_id') && old('_method') === 'PUT' ? $errors->first('parent_id') : 'Atur relasi sub-kategori jika diperlukan' }}
                </p>
            </div>

            <!-- Deskripsi Textarea -->
            <div id="edit_group_description">
                <div id="edit_box_description" class="relative rounded-xl border {{ $errors->has('description') && old('_method') === 'PUT' ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2.5">
                    <label id="edit_label_description" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('description') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-700' }}">
                        Deskripsi (Opsional)
                    </label>
                    <div class="flex items-start gap-3 pt-0.5">
                        <i id="edit_icon_description" data-lucide="align-left" class="w-4 h-4 {{ $errors->has('description') && old('_method') === 'PUT' ? 'text-rose-500' : 'text-slate-400' }} shrink-0 mt-0.5"></i>
                        <textarea id="edit_cat_description" name="description" rows="2" placeholder="Keterangan kategori..." class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none resize-none">{{ old('_method') === 'PUT' ? old('description') : '' }}</textarea>
                    </div>
                </div>
                @if($errors->has('description') && old('_method') === 'PUT')
                    <p id="edit_error_description" class="mt-1 text-[11px] font-medium text-rose-500 px-1">
                        {{ $errors->first('description') }}
                    </p>
                @endif
            </div>

            <!-- Catatan Default POS (Preset Racikan / Notes Dinamis) -->
            <div>
                <div class="relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2.5">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700 flex items-center gap-1">
                        <i data-lucide="message-square" class="w-3 h-3 text-brand-500"></i>
                        <span>Pilihan Catatan Default POS (Pisahkan dengan koma)</span>
                    </label>
                    <div class="flex items-start gap-3 pt-0.5">
                        <i data-lucide="list-plus" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                        <textarea id="edit_cat_default_notes" name="default_notes_raw" rows="2" placeholder="Contoh: Less Sugar, Less Ice, Ekstra Pedas, Tanpa Bawang..." class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none resize-none">{{ old('_method') === 'PUT' ? old('default_notes_raw') : '' }}</textarea>
                    </div>
                </div>
                <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[10px] text-slate-400 px-1">
                    <span class="font-semibold text-slate-500">Rekomendasi Cepat:</span>
                    <button type="button" onclick="appendEditNotePreset('Less Sugar, Less Ice, Normal Sugar, Panas')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition">+ Minuman</button>
                    <button type="button" onclick="appendEditNotePreset('Pedas Sedang, Ekstra Pedas, Tidak Pedas, Tanpa Bawang, Pisah Sambal')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition">+ Makanan</button>
                    <button type="button" onclick="appendEditNotePreset('Bungkus Terpisah, Kado, Cek Kondisi')" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-600 transition">+ Retail</button>
                </div>
            </div>

            <!-- Status Aktif Checkbox Styled -->
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="toggle-right" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Status Kategori</span>
                        <span class="block text-[11px] text-slate-400">Aktifkan agar dapat dipilih saat transaksi & produk</span>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="edit_cat_is_active" name="is_active" value="1" {{ (old('_method') === 'PUT' ? old('is_active') : true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-10 h-5.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4.5 after:w-4.5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <!-- Modal Footer / Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditCategoryModal()" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
