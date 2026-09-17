<script>
    // Modal Create Handlers
    function openCreateProductModal() {
        const modal = document.getElementById('createProductModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        if (typeof switchProductTab === 'function') {
            switchProductTab('create', 'basic');
        }

        // Initialize Select2 inside Create Modal safely
        try {
            if (typeof $ !== 'undefined' && $('#create_input_category_id').length) {
                $('#create_input_category_id').select2({
                    dropdownParent: $('#createProductModal'),
                    placeholder: 'Pilih Kategori',
                    allowClear: true,
                    width: '100%'
                });

                $('#create_input_base_unit_id').select2({
                    dropdownParent: $('#createProductModal'),
                    placeholder: 'Pilih Satuan',
                    allowClear: true,
                    width: '100%'
                }).off('change.unitSync').on('change.unitSync', function() {
                    if (typeof updateProductUnitDropdowns === 'function') {
                        updateProductUnitDropdowns('create');
                    }
                });

                $('#create_input_product_type').select2({
                    dropdownParent: $('#createProductModal'),
                    minimumResultsForSearch: Infinity,
                    width: '100%'
                });
            }
        } catch(e) {
            console.error('Select2 init error in modal:', e);
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeCreateProductModal() {
        const modal = document.getElementById('createProductModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Modal Edit Handlers
    function openEditProductModal(product) {
        const form = document.getElementById('editProductForm');
        form.action = `/products/${product.id}`;
        document.getElementById('edit_form_action').value = form.action;
        document.getElementById('edit_product_id').value = product.id;

        document.getElementById('edit_input_name').value = product.name || '';
        document.getElementById('edit_input_code').value = product.code || '';
        document.getElementById('edit_input_barcode').value = product.barcode || '';
        document.getElementById('edit_input_purchase_price').value = product.purchase_price || '0';
        document.getElementById('edit_input_selling_price').value = product.selling_price || '0';
        document.getElementById('edit_input_min_stock').value = product.min_stock || '5';
        document.getElementById('edit_input_brand').value = product.brand || '';
        document.getElementById('edit_input_is_active').checked = product.is_active ? true : false;

        // Populate and initialize Select2 for Edit Modal
        $('#edit_input_category_id').val(product.category_id || '').select2({
            dropdownParent: $('#editProductModal'),
            placeholder: 'Pilih Kategori',
            allowClear: true,
            width: '100%'
        });

        $('#edit_input_base_unit_id').val(product.base_unit_id || '').select2({
            dropdownParent: $('#editProductModal'),
            placeholder: 'Pilih Satuan',
            allowClear: true,
            width: '100%'
        }).off('change.unitSync').on('change.unitSync', function() {
            if (typeof updateProductUnitDropdowns === 'function') {
                updateProductUnitDropdowns('edit');
            }
        });

        $('#edit_input_product_type').val(product.product_type || 'standard').select2({
            dropdownParent: $('#editProductModal'),
            minimumResultsForSearch: Infinity,
            width: '100%'
        });

        // Image preview
        const img = document.getElementById('edit_image_preview_img');
        const icon = document.getElementById('edit_image_preview_icon');
        if (product.image_path) {
            img.src = `/storage/${product.image_path}`;
            img.classList.remove('hidden');
            icon.classList.add('hidden');
        } else {
            img.classList.add('hidden');
            icon.classList.remove('hidden');
        }

        // Render Barcodes
        const barcodeContainer = document.getElementById('edit_barcodes_container');
        barcodeContainer.innerHTML = '';
        if (product.barcodes && product.barcodes.length > 0) {
            product.barcodes.forEach(b => {
                if (typeof addBarcodeRow === 'function') addBarcodeRow('edit', b.barcode, b.unit_id);
            });
        }

        // Render Conversions
        const convContainer = document.getElementById('edit_conversions_container');
        convContainer.innerHTML = '';
        if (product.conversions && product.conversions.length > 0) {
            product.conversions.forEach(c => {
                if (typeof addConversionRow === 'function') addConversionRow('edit', c.from_unit_id, c.to_unit_id, c.conversion_value);
            });
        }

        // Render Tiered Prices (Harga Berjenjang)
        const tieredContainer = document.getElementById('edit_tiered_container');
        tieredContainer.innerHTML = '';
        if (product.tiered_prices && product.tiered_prices.length > 0) {
            product.tiered_prices.forEach(t => {
                if (typeof addTieredRow === 'function') addTieredRow('edit', t.unit_id, t.customer_group_id, t.min_qty, t.max_qty || '', t.price);
            });
        }

        document.getElementById('editProductModal').classList.remove('hidden');
        document.getElementById('editProductModal').classList.add('flex');
        if (typeof switchProductTab === 'function') {
            switchProductTab('edit', 'basic');
        }
    }

    function closeEditProductModal() {
        const modal = document.getElementById('editProductModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Realtime field validation feedback
    function setFieldStatus(prefix, field, errorMsg) {
        const box = document.getElementById(`${prefix}_box_${field}`);
        const label = document.getElementById(`${prefix}_label_${field}`);
        const icon = document.getElementById(`${prefix}_icon_${field}`);
        const err = document.getElementById(`${prefix}_error_${field}`);

        if (!box) return;

        if (errorMsg) {
            box.classList.add('border-rose-500', 'ring-2', 'ring-rose-500/10');
            box.classList.remove('border-slate-200', 'focus-within:border-brand-500');
            if (label) { label.classList.add('text-rose-500'); label.classList.remove('text-slate-700'); }
            if (icon) { icon.classList.add('text-rose-500'); icon.classList.remove('text-slate-400'); }
            if (err) { err.textContent = errorMsg; err.classList.add('text-rose-500'); err.classList.remove('text-slate-400'); }
        } else {
            box.classList.remove('border-rose-500', 'ring-2', 'ring-rose-500/10');
            box.classList.add('border-slate-200');
            if (label) { label.classList.remove('text-rose-500'); label.classList.add('text-slate-700'); }
            if (icon) { icon.classList.remove('text-rose-500'); icon.classList.add('text-slate-400'); }
            if (err) {
                err.classList.remove('text-rose-500');
                err.classList.add('text-slate-400');
                if (field === 'name') err.textContent = 'Nama lengkap barang dagangan';
                else if (field === 'base_unit_id') err.textContent = 'Satuan eceran terendah';
                else if (field === 'purchase_price') err.textContent = 'Harga modal per 1 satuan dasar';
                else if (field === 'selling_price') err.textContent = 'Harga jual standar di kasir';
            }
        }
    }

    ['create', 'edit'].forEach(prefix => {
        const nameInput = document.getElementById(`${prefix}_input_name`);
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                if (this.value.trim() === '') setFieldStatus(prefix, 'name', 'Nama produk wajib diisi.');
                else setFieldStatus(prefix, 'name', null);
            });
        }
    });

    window.openCreateProductModal = openCreateProductModal;
    window.closeCreateProductModal = closeCreateProductModal;
    window.openEditProductModal = openEditProductModal;
    window.closeEditProductModal = closeEditProductModal;
    window.setFieldStatus = setFieldStatus;
</script>
