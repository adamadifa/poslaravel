<!-- MODAL CREATE CATEGORY -->
<div id="createCategoryModal" class="fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-xs {{ $errors->hasBag('default') && !old('_method') ? 'flex' : 'hidden' }} items-center justify-center p-4">
    <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-7 max-w-lg w-full shadow-2xl space-y-6 transition-all">
        <!-- Header -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-lg text-slate-900 tracking-tight">Tambah Kategori</h3>
                <p class="text-xs text-slate-400 mt-0.5">Lengkapi formulir di bawah untuk membuat kategori produk baru</p>
            </div>
            <button onclick="closeCreateCategoryModal()" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="createCategoryForm" action="{{ route('categories.store') }}" method="POST" novalidate class="space-y-5">
            @csrf
            
            <!-- Nama Kategori Input -->
            <div id="create_group_name">
                <div id="create_box_name" class="relative rounded-xl border {{ $errors->has('name') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2.5">
                    <label id="create_label_name" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <i id="create_icon_name" data-lucide="tag" class="w-4 h-4 {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <input type="text" 
                               id="create_input_name"
                               name="name" 
                               value="{{ old('_method') ? '' : old('name') }}" 
                               required 
                               placeholder="Masukkan nama kategori" 
                               class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none">
                    </div>
                </div>
                <p id="create_error_name" class="mt-1 text-[11px] font-medium {{ $errors->has('name') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('name') && !old('_method') ? $errors->first('name') : 'Nama lengkap wajib diisi' }}
                </p>
            </div>

            <!-- Induk Kategori Selector -->
            <div id="create_group_parent_id">
                <div id="create_box_parent_id" class="relative rounded-xl border {{ $errors->has('parent_id') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2.5">
                    <label id="create_label_parent_id" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('parent_id') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                        Induk Kategori (Opsional)
                    </label>
                    <div class="flex items-center gap-3">
                        <i id="create_icon_parent_id" data-lucide="layers" class="w-4 h-4 {{ $errors->has('parent_id') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0"></i>
                        <select name="parent_id" class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Kategori Utama (Tanpa Induk)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ (old('_method') ? '' : old('parent_id')) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p id="create_error_parent_id" class="mt-1 text-[11px] font-medium {{ $errors->has('parent_id') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} px-1">
                    {{ $errors->has('parent_id') && !old('_method') ? $errors->first('parent_id') : 'Pilih induk jika kategori ini merupakan sub-kategori' }}
                </p>
            </div>

            <!-- Deskripsi Textarea -->
            <div id="create_group_description">
                <div id="create_box_description" class="relative rounded-xl border {{ $errors->has('description') && !old('_method') ? 'border-rose-500 ring-2 ring-rose-500/10' : 'border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20' }} bg-white transition px-4 pt-3 pb-2.5">
                    <label id="create_label_description" class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold {{ $errors->has('description') && !old('_method') ? 'text-rose-500' : 'text-slate-700' }}">
                        Deskripsi (Opsional)
                    </label>
                    <div class="flex items-start gap-3 pt-0.5">
                        <i id="create_icon_description" data-lucide="align-left" class="w-4 h-4 {{ $errors->has('description') && !old('_method') ? 'text-rose-500' : 'text-slate-400' }} shrink-0 mt-0.5"></i>
                        <textarea name="description" rows="2" placeholder="Contoh: Kategori untuk produk minuman segar dan jus" class="w-full bg-transparent border-0 p-0 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none resize-none">{{ old('_method') ? '' : old('description') }}</textarea>
                    </div>
                </div>
                @if($errors->has('description') && !old('_method'))
                    <p id="create_error_description" class="mt-1 text-[11px] font-medium text-rose-500 px-1">
                        {{ $errors->first('description') }}
                    </p>
                @endif
            </div>

            <!-- Modal Footer / Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCreateCategoryModal()" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Kategori</span>
                </button>
            </div>
        </form>
    </div>
</div>
