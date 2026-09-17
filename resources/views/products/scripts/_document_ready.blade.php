<script>
    // Document Ready Initializers
    $(document).ready(function () {
        // Filter Kategori on index
        $('#filter_category_id').select2({
            placeholder: 'Semua Kategori',
            allowClear: true,
            width: '100%'
        }).on('change', function () {
            $(this).closest('form').submit();
        });

        // Initialize if Create Modal is rendered open on load (e.g. error bag)
        if ($('#createProductModal').length && !$('#createProductModal').hasClass('hidden')) {
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
            });
            $('#create_input_product_type').select2({
                dropdownParent: $('#createProductModal'),
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        }

        // Initialize if Edit Modal is rendered open on load
        if ($('#editProductModal').length && !$('#editProductModal').hasClass('hidden')) {
            $('#edit_input_category_id').select2({
                dropdownParent: $('#editProductModal'),
                placeholder: 'Pilih Kategori',
                allowClear: true,
                width: '100%'
            });
            $('#edit_input_base_unit_id').select2({
                dropdownParent: $('#editProductModal'),
                placeholder: 'Pilih Satuan',
                allowClear: true,
                width: '100%'
            });
            $('#edit_input_product_type').select2({
                dropdownParent: $('#editProductModal'),
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        }

        // Realtime validation listeners for Select2
        ['create', 'edit'].forEach(prefix => {
            $(`#${prefix}_input_base_unit_id`).on('change', function () {
                if ($(this).val() === '' || !$(this).val()) {
                    if (typeof setFieldStatus === 'function') setFieldStatus(prefix, 'base_unit_id', 'Satuan dasar wajib dipilih.');
                } else {
                    if (typeof setFieldStatus === 'function') setFieldStatus(prefix, 'base_unit_id', null);
                }
            });
        });
    });



</script>