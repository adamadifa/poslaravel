<script>
    // =========================================================================
    // BARCODE PRINT CONTROLLER (CUSTOM MM DIMENSIONS & REALTIME SVG RENDERING)
    // =========================================================================
    let barcodeQueue = [];
    let currentPreviewZoom = 100;

    const barcodePresets = {
        '40x30': { width: 40, height: 30, cols: 1, gap: 2 },
        '50x30': { width: 50, height: 30, cols: 1, gap: 2 },
        '33x15_3col': { width: 33, height: 15, cols: 3, gap: 2 },
        '100x50': { width: 100, height: 50, cols: 1, gap: 3 },
        'a4_108': { width: 38, height: 18, cols: 5, gap: 2 },
        'a4_103': { width: 64, height: 32, cols: 3, gap: 3 },
    };

    function handleBarcodePresetChange(key) {
        if (key !== 'custom' && barcodePresets[key]) {
            const p = barcodePresets[key];
            document.getElementById('barcode_param_width').value = p.width;
            document.getElementById('barcode_param_height').value = p.height;
            document.getElementById('barcode_param_cols').value = p.cols;
            document.getElementById('barcode_param_gap').value = p.gap;
        }
        renderBarcodePreview();
    }

    function openBarcodePrintModal(initialItems = []) {
        const modal = document.getElementById('barcodePrintModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        if (initialItems && initialItems.length > 0) {
            initialItems.forEach(item => {
                const existing = barcodeQueue.find(q => q.id === item.id);
                if (existing) {
                    existing.qty += (item.qty || 10);
                } else {
                    barcodeQueue.push({ ...item, qty: item.qty || 10 });
                }
            });
        } else {
            // Check if any row checkboxes were checked
            const checkedBoxes = document.querySelectorAll('.product-row-checkbox:checked');
            if (checkedBoxes.length > 0) {
                checkedBoxes.forEach(cb => {
                    const prod = {
                        id: cb.value + '_base',
                        product_id: cb.value,
                        name: cb.dataset.name,
                        code: cb.dataset.code,
                        barcode: cb.dataset.barcode,
                        unit_name: cb.dataset.unit,
                        price: parseFloat(cb.dataset.price) || 0
                    };
                    if (!barcodeQueue.some(item => item.id === prod.id)) {
                        barcodeQueue.push({ ...prod, qty: 10 });
                    }
                });
            }
        }

        // Initialize Select2 AJAX for product search inside barcode modal
        $('#barcode_product_search_select').select2({
            dropdownParent: $('#barcodePrintModal'),
            placeholder: 'Cari & tambah produk lain ke antrean...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("products.barcode-search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: (data.results || []).map(r => ({
                            id: r.id,
                            text: r.label,
                            itemData: r
                        }))
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        }).off('select2:select').on('select2:select', function (e) {
            const data = e.params.data.itemData;
            if (data) {
                const existing = barcodeQueue.find(q => q.id === data.id);
                if (existing) {
                    existing.qty += 10;
                } else {
                    barcodeQueue.push({ ...data, qty: 10 });
                }
                renderBarcodeQueueList();
                renderBarcodePreview();
            }
            $(this).val(null).trigger('change');
        });

        renderBarcodeQueueList();
        renderBarcodePreview();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }


    function openProductBarcodeFromRow(product) {
        const items = [];
        const baseBarcode = product.barcode || product.code || '';
        const baseUnitName = product.base_unit ? formatUnitDisplay(product.base_unit) : 'Pcs';
        const baseSellingPrice = parseFloat(product.selling_price) || 0;

        // 1. Base unit item
        items.push({
            id: product.id + '_base',
            product_id: product.id,
            name: product.name,
            code: product.code,
            barcode: baseBarcode,
            unit_name: baseUnitName,
            price: baseSellingPrice,
            is_base: true,
            qty: 10
        });

        // 2. Barcodes / Multi-satuan
        const processedUnits = new Set([product.base_unit_id]);

        if (product.barcodes && product.barcodes.length > 0) {
            product.barcodes.forEach(b => {
                if (b.unit_id && !processedUnits.has(b.unit_id)) {
                    processedUnits.add(b.unit_id);
                    const uName = b.unit ? formatUnitDisplay(b.unit) : 'Satuan';
                    let convRatio = 1;
                    if (product.conversions) {
                        const conv = product.conversions.find(c => c.from_unit_id == b.unit_id);
                        if (conv && conv.conversion_value > 0) convRatio = parseFloat(conv.conversion_value);
                    }
                    const pList = product.price_lists ? product.price_lists.find(p => p.unit_id == b.unit_id) : null;
                    const unitPrice = pList && pList.selling_price > 0 ? parseFloat(pList.selling_price) : (baseSellingPrice * convRatio);

                    items.push({
                        id: product.id + '_barcode_' + b.id,
                        product_id: product.id,
                        name: product.name,
                        code: product.code,
                        barcode: b.barcode || baseBarcode,
                        unit_name: uName,
                        price: unitPrice,
                        is_base: false,
                        qty: 10
                    });
                }
            });
        }

        if (product.conversions && product.conversions.length > 0) {
            product.conversions.forEach(c => {
                if (c.from_unit_id && !processedUnits.has(c.from_unit_id)) {
                    processedUnits.add(c.from_unit_id);
                    const uName = c.from_unit ? formatUnitDisplay(c.from_unit) : 'Satuan';
                    const convRatio = parseFloat(c.conversion_value) || 1;
                    const pList = product.price_lists ? product.price_lists.find(p => p.unit_id == c.from_unit_id) : null;
                    const unitPrice = pList && pList.selling_price > 0 ? parseFloat(pList.selling_price) : (baseSellingPrice * convRatio);

                    items.push({
                        id: product.id + '_conv_' + c.id,
                        product_id: product.id,
                        name: product.name,
                        code: product.code,
                        barcode: baseBarcode,
                        unit_name: uName,
                        price: unitPrice,
                        is_base: false,
                        qty: 10
                    });
                }
            });
        }

        openBarcodePrintModal(items);
    }


    function openSingleProductBarcode(productData) {
        openBarcodePrintModal([productData]);
    }

    function closeBarcodeModal() {
        const modal = document.getElementById('barcodePrintModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function renderBarcodeQueueList() {
        const container = document.getElementById('barcode_queue_container');
        const badge = document.getElementById('barcode_queue_badge');
        if (badge) badge.innerText = `${barcodeQueue.length} Produk`;

        if (!container) return;

        if (barcodeQueue.length === 0) {
            container.innerHTML = `
                <div class="py-6 text-center text-slate-400 text-xs">
                    <p class="font-semibold">Antrean cetak masih kosong.</p>
                    <p class="text-[10px] mt-1">Cari produk di atas atau pilih dari tabel produk.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = barcodeQueue.map((item, idx) => `
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold text-slate-900 dark:text-white truncate max-w-[170px]" title="${item.name}">${item.name}</span>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-teal-50 text-teal-700 border border-teal-200">${item.unit_name || 'Pcs'}</span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1 mt-0.5">
                        <span class="text-brand-600 font-semibold">${item.barcode || item.code}</span>
                        <span>•</span>
                        <span class="text-slate-700 dark:text-slate-300 font-bold">Rp ${Math.round(item.price).toLocaleString('id-ID')}</span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="updateBarcodeQueueQty(${idx}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-white font-bold text-sm flex items-center justify-center transition active:scale-95 shadow-2xs">-</button>
                    <input type="number" min="1" max="1000" value="${item.qty}" oninput="setBarcodeQueueQtyDirect(${idx}, this.value)" class="w-14 h-7 text-center text-xs font-extrabold rounded-lg border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-1 focus:ring-brand-500 focus:border-brand-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none p-0">
                    <button type="button" onclick="updateBarcodeQueueQty(${idx}, 1)" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-white font-bold text-sm flex items-center justify-center transition active:scale-95 shadow-2xs">+</button>
                    <button type="button" onclick="removeBarcodeQueueItem(${idx})" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition ml-0.5" title="Hapus dari antrean">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        `).join('');

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }


    function updateBarcodeQueueQty(index, delta) {
        if (!barcodeQueue[index]) return;
        barcodeQueue[index].qty = Math.max(1, barcodeQueue[index].qty + delta);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function setBarcodeQueueQtyDirect(index, value) {
        if (!barcodeQueue[index]) return;
        barcodeQueue[index].qty = Math.max(1, parseInt(value) || 1);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function removeBarcodeQueueItem(index) {
        barcodeQueue.splice(index, 1);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function clearBarcodeQueue() {
        barcodeQueue = [];
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function setAllBarcodeQty(qty) {
        barcodeQueue.forEach(item => item.qty = qty);
        renderBarcodeQueueList();
        renderBarcodePreview();
    }

    function setPreviewZoom(zoom) {
        currentPreviewZoom = zoom;
        const viewport = document.getElementById('barcode_preview_viewport');
        if (viewport) {
            viewport.style.transform = `scale(${zoom / 100})`;
        }
        ['75', '100', '125'].forEach(z => {
            const btn = document.getElementById(`btn_zoom_${z}`);
            if (btn) {
                if (parseInt(z) === zoom) {
                    btn.className = 'px-1.5 py-0.5 rounded bg-brand-500 text-white shadow-2xs';
                } else {
                    btn.className = 'px-1.5 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700';
                }
            }
        });
    }

    function toggleSelectAllProducts(master) {
        const checkboxes = document.querySelectorAll('.product-row-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
        handleProductCheckboxChange();
    }

    function handleProductCheckboxChange() {
        const checked = document.querySelectorAll('.product-row-checkbox:checked');
        const badge = document.getElementById('bulk_barcode_badge');
        if (badge) {
            if (checked.length > 0) {
                badge.innerText = checked.length;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }


    function renderBarcodePreview() {
        const widthEl = document.getElementById('barcode_param_width');
        const heightEl = document.getElementById('barcode_param_height');
        const colsEl = document.getElementById('barcode_param_cols');
        const gapEl = document.getElementById('barcode_param_gap');

        if (!widthEl || !heightEl) return;

        const width = parseFloat(widthEl.value) || 40;
        const height = parseFloat(heightEl.value) || 30;
        const cols = parseInt(colsEl ? colsEl.value : '1') || 1;
        const gap = parseFloat(gapEl ? gapEl.value : '2') || 2;

        const showStore = document.getElementById('barcode_toggle_store')?.checked ?? false;
        const storeName = document.getElementById('barcode_store_name')?.value.trim() ?? '';
        const showName = document.getElementById('barcode_toggle_name')?.checked ?? true;
        const showPrice = document.getElementById('barcode_toggle_price')?.checked ?? true;
        const showUnit = document.getElementById('barcode_toggle_unit')?.checked ?? true;
        const showCodeText = document.getElementById('barcode_toggle_code_text')?.checked ?? true;

        const fontSizeScale = document.getElementById('barcode_font_size')?.value || 'md';
        const barHeight = parseInt(document.getElementById('barcode_bar_height')?.value || '35') || 35;

        // Font sizes mapping
        const fontMap = {
            sm: { store: 8, name: 9, price: 9, code: 8 },
            md: { store: 9, name: 10, price: 11, code: 9 },
            lg: { store: 10, name: 11, price: 12, code: 10 },
        }[fontSizeScale] || { store: 9, name: 10, price: 11, code: 9 };

        // Flatten all labels according to item.qty
        const labels = [];
        barcodeQueue.forEach(item => {
            const count = Math.max(1, parseInt(item.qty) || 1);
            for (let i = 0; i < count; i++) {
                labels.push(item);
            }
        });

        const totalBadge = document.getElementById('barcode_total_labels_badge');
        if (totalBadge) totalBadge.innerText = `Total: ${labels.length} Label (${cols} Kolom)`;

        const sheetContainer = document.getElementById('barcode_render_sheet');
        if (!sheetContainer) return;

        if (labels.length === 0) {
            sheetContainer.innerHTML = `
                <div class="p-8 text-center text-slate-400 text-xs w-full">
                    <p class="font-bold text-slate-500">Belum ada label untuk ditampilkan.</p>
                    <p class="text-[11px] mt-1">Tambahkan produk ke antrean di panel sebelah kiri.</p>
                </div>
            `;
            sheetContainer.style.width = 'auto';
            sheetContainer.style.padding = '10px';
            return;
        }

        // Calculate sheet container width in mm
        const sheetWidthMm = cols * width + (cols - 1) * gap + 4;
        sheetContainer.style.width = `${sheetWidthMm}mm`;
        sheetContainer.style.padding = '2mm';
        sheetContainer.style.gap = `${gap}mm`;

        let html = '';
        labels.forEach((item, idx) => {
            const priceFormatted = Math.round(item.price).toLocaleString('id-ID');
            const unitText = showUnit && item.unit_name ? ` / ${item.unit_name}` : '';

            html += `
                <div class="barcode-preview-card" style="
                    width: ${width}mm; 
                    height: ${height}mm; 
                    box-sizing: border-box; 
                    border: 1px dashed #cbd5e1; 
                    padding: 1.5mm; 
                    background: #ffffff; 
                    display: flex; 
                    flex-direction: column; 
                    align-items: center; 
                    justify-content: space-between; 
                    text-align: center; 
                    overflow: hidden; 
                    position: relative;
                ">
                    ${showStore && storeName ? `<div style="font-size: ${fontMap.store}px; font-weight: 800; text-transform: uppercase; line-height: 1.1; letter-spacing: -0.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; color: #0f172a;">${storeName}</div>` : ''}
                    
                    ${showName ? `<div style="font-size: ${fontMap.name}px; font-weight: 700; line-height: 1.1; max-height: 2.2em; overflow: hidden; color: #1e293b; width: 100%; word-break: break-word;">${item.name}</div>` : ''}
                    
                    <div style="width: 100%; flex: 1 1 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; min-height: 0;">
                        <svg class="barcode-svg-${idx}" style="max-width: 98%; max-height: 100%; display: block; margin: 0 auto;"></svg>
                    </div>

                    ${showPrice ? `<div style="font-size: ${fontMap.price}px; font-weight: 900; line-height: 1; color: #000000; width: 100%;"><span style="font-size: 0.85em; font-weight: 700;">Rp</span> ${priceFormatted}<span style="font-size: 0.8em; font-weight: normal; color: #475569;">${unitText}</span></div>` : ''}
                </div>
            `;
        });

        sheetContainer.innerHTML = html;

        // Render SVG barcodes with JsBarcode
        labels.forEach((item, idx) => {
            const codeVal = item.barcode || item.code || '12345678';
            try {
                if (typeof JsBarcode !== 'undefined') {
                    JsBarcode(`.barcode-svg-${idx}`, codeVal, {
                        format: "CODE128",
                        width: width < 35 ? 1.0 : 1.3,
                        height: barHeight,
                        displayValue: showCodeText,
                        fontSize: fontMap.code,
                        fontOptions: "bold",
                        margin: 0,
                        textMargin: 1
                    });
                }
            } catch (err) {
                console.warn('JsBarcode render fallback for code:', codeVal, err);
            }
        });
    }

    function executeBarcodePrint() {
        const width = parseFloat(document.getElementById('barcode_param_width')?.value) || 40;
        const height = parseFloat(document.getElementById('barcode_param_height')?.value) || 30;
        const cols = parseInt(document.getElementById('barcode_param_cols')?.value) || 1;
        const gap = parseFloat(document.getElementById('barcode_param_gap')?.value) || 2;

        const showStore = document.getElementById('barcode_toggle_store')?.checked ?? false;
        const storeName = (document.getElementById('barcode_store_name')?.value || '').trim();
        const showName = document.getElementById('barcode_toggle_name')?.checked ?? true;
        const showPrice = document.getElementById('barcode_toggle_price')?.checked ?? true;
        const showUnit = document.getElementById('barcode_toggle_unit')?.checked ?? true;
        const showCodeText = document.getElementById('barcode_toggle_code_text')?.checked ?? true;

        const fontSizeScale = document.getElementById('barcode_font_size')?.value || 'md';
        const barHeight = parseInt(document.getElementById('barcode_bar_height')?.value) || 35;

        const fontMap = {
            sm: { store: '7pt', name: '8pt', price: '8.5pt', code: '7pt' },
            md: { store: '8pt', name: '9pt', price: '9.5pt', code: '8pt' },
            lg: { store: '9pt', name: '10pt', price: '11pt', code: '9pt' },
        }[fontSizeScale] || { store: '8pt', name: '9pt', price: '9.5pt', code: '8pt' };

        const labels = [];
        barcodeQueue.forEach(function (item) {
            const count = Math.max(1, parseInt(item.qty) || 1);
            for (let i = 0; i < count; i++) {
                labels.push(item);
            }
        });

        if (labels.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Antrean Kosong',
                    text: 'Silakan pilih atau tambahkan produk sebelum mencetak barcode.',
                    confirmButtonColor: '#f97316'
                });
            } else {
                alert('Silakan pilih atau tambahkan produk sebelum mencetak barcode.');
            }
            return;
        }

        const printFrame = document.getElementById('barcode_print_frame');
        if (!printFrame) return;

        const printWin = printFrame.contentWindow;
        const printDoc = printWin.document;

        const pageWidth = cols > 1 ? (cols * width + (cols - 1) * gap) : width;
        const pageHeight = height;

        // Reset iframe document cleanly via DOM without literal head or body closing strings
        if (printDoc.head) printDoc.head.innerHTML = '';
        if (printDoc.body) printDoc.body.innerHTML = '';
        printDoc.title = 'Print Barcode';

        // Inject print styling using DOM element with \x40 instead of literal @ to avoid Blade directive traps
        const styleEl = printDoc.createElement('style');
        styleEl.textContent = [
            '\\x40page { size: ' + pageWidth + 'mm ' + pageHeight + 'mm; margin: 0; }',
            '\\x40media print { html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; color: #000 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; } }',
            '* { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; }',
            'body { margin: 0; padding: 0; background: #fff; display: flex; flex-wrap: wrap; align-content: flex-start; width: ' + pageWidth + 'mm; }',
            '.print-label-item { width: ' + width + 'mm; height: ' + height + 'mm; padding: 1mm 1.5mm; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: space-between; text-align: center; overflow: hidden; page-break-inside: avoid; break-inside: avoid; ' + (cols > 1 ? ('margin-right: ' + gap + 'mm; margin-bottom: ' + gap + 'mm;') : '') + ' }',
            cols > 1 ? ('.print-label-item:nth-child(' + cols + 'n) { margin-right: 0 !important; }') : '',
            '.lbl-store { font-size: ' + fontMap.store + '; font-weight: 800; text-transform: uppercase; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; }',
            '.lbl-name { font-size: ' + fontMap.name + '; font-weight: 700; line-height: 1.1; max-height: 2.2em; overflow: hidden; width: 100%; word-break: break-word; }',
            '.lbl-svg-wrap { width: 100%; flex: 1 1 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; min-height: 0; }',
            '.lbl-svg-wrap svg { max-width: 98%; max-height: 100%; display: block; margin: 0 auto; }',
            '.lbl-price { font-size: ' + fontMap.price + '; font-weight: 900; line-height: 1; width: 100%; }'
        ].filter(Boolean).join('\n');
        printDoc.head.appendChild(styleEl);

        // Build label elements cleanly via DOM
        const container = printDoc.body;
        container.innerHTML = '';

        labels.forEach(function (item, idx) {
            const labelDiv = printDoc.createElement('div');
            labelDiv.className = 'print-label-item';

            if (showStore && storeName) {
                const storeEl = printDoc.createElement('div');
                storeEl.className = 'lbl-store';
                storeEl.textContent = storeName;
                labelDiv.appendChild(storeEl);
            }

            if (showName) {
                const nameEl = printDoc.createElement('div');
                nameEl.className = 'lbl-name';
                nameEl.textContent = item.name || '';
                labelDiv.appendChild(nameEl);
            }

            const svgWrap = printDoc.createElement('div');
            svgWrap.className = 'lbl-svg-wrap';
            const svgEl = printDoc.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgEl.id = 'p-svg-' + idx;
            svgWrap.appendChild(svgEl);
            labelDiv.appendChild(svgWrap);

            if (showPrice) {
                const priceEl = printDoc.createElement('div');
                priceEl.className = 'lbl-price';
                const priceFormatted = Math.round(item.price || 0).toLocaleString('id-ID');
                const unitText = (showUnit && item.unit_name) ? (' / ' + item.unit_name) : '';
                priceEl.textContent = 'Rp ' + priceFormatted + unitText;
                labelDiv.appendChild(priceEl);
            }

            container.appendChild(labelDiv);
        });

        // Use parent window's JsBarcode to render each SVG inside iframe
        labels.forEach(function (item, i) {
            try {
                const code = item.barcode || item.code || '12345678';
                const targetSvg = printDoc.getElementById('p-svg-' + i);
                if (targetSvg && typeof JsBarcode !== 'undefined') {
                    JsBarcode(targetSvg, code, {
                        format: "CODE128",
                        width: width < 35 ? 1.0 : 1.3,
                        height: barHeight,
                        displayValue: showCodeText,
                        fontSize: fontSizeScale === 'sm' ? 8 : (fontSizeScale === 'lg' ? 11 : 9),
                        fontOptions: "bold",
                        margin: 0,
                        textMargin: 1
                    });
                }
            } catch (e) {
                console.warn('Barcode print render error:', e);
            }
        });

        setTimeout(function () {
            printWin.focus();
            printWin.print();
        }, 300);
    }

    window.handleBarcodePresetChange = handleBarcodePresetChange;
    window.openBarcodePrintModal = openBarcodePrintModal;
    window.openProductBarcodeFromRow = openProductBarcodeFromRow;
    window.openSingleProductBarcode = openSingleProductBarcode;
    window.closeBarcodeModal = closeBarcodeModal;
    window.renderBarcodeQueueList = renderBarcodeQueueList;
    window.updateBarcodeQueueQty = updateBarcodeQueueQty;
    window.setBarcodeQueueQtyDirect = setBarcodeQueueQtyDirect;
    window.removeBarcodeQueueItem = removeBarcodeQueueItem;
    window.clearBarcodeQueue = clearBarcodeQueue;
    window.setAllBarcodeQty = setAllBarcodeQty;
    window.setPreviewZoom = setPreviewZoom;
    window.toggleSelectAllProducts = toggleSelectAllProducts;
    window.handleProductCheckboxChange = handleProductCheckboxChange;
    window.renderBarcodePreview = renderBarcodePreview;
    window.executeBarcodePrint = executeBarcodePrint;
</script>