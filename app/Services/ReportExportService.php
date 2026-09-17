<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Common color palette for professional reports
     */
    protected const COLOR_HEADER_BG = '1E293B';      // Dark Slate Navy

    protected const COLOR_HEADER_TEXT = 'FFFFFF';    // White

    protected const COLOR_ZEBRA_BG = 'F8FAFC';       // Very light slate/gray

    protected const COLOR_TOTAL_BG = 'F1F5F9';       // Slate 100

    protected const COLOR_BORDER = 'E2E8F0';         // Slate 200

    protected const COLOR_TEXT_MUTED = '64748B';     // Slate 500

    /**
     * Export Sales Transactions to formatted Excel (.xlsx)
     */
    public function exportSales($sales, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Penjualan');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN TRANSAKSI PENJUALAN';

        // 1. Meta / Header info
        $this->writeReportHeader($sheet, $appName, $title, $meta, 'L');

        // 2. Table Headers
        $headers = [
            'A5' => 'No',
            'B5' => 'No. Faktur',
            'C5' => 'Tanggal & Waktu',
            'D5' => 'Kasir',
            'E5' => 'Pelanggan',
            'F5' => 'Cabang / Gudang',
            'G5' => 'Metode Bayar',
            'H5' => 'Status Bayar',
            'I5' => 'Subtotal (Rp)',
            'J5' => 'Diskon (Rp)',
            'K5' => 'Pajak (Rp)',
            'L5' => 'Total Bersih (Rp)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        // 3. Write Data Rows
        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($sales as $sale) {
            $isVoid = ($sale->status === 'void');
            $dateStr = $sale->sale_date ? Carbon::parse($sale->sale_date)->format('d/m/Y H:i') : '-';
            $cashier = $sale->user->name ?? 'Kasir';
            $customer = $sale->customer->name ?? 'Umum (Walk-in)';
            $warehouse = $sale->warehouse->name ?? '-';
            $paymentMethod = strtoupper($sale->payment_method ?? 'CASH');
            $paymentStatus = ucfirst($sale->payment_status ?? 'paid').($isVoid ? ' (VOID)' : '');

            $subtotal = (float) $sale->subtotal;
            $discount = (float) $sale->discount_amount;
            $tax = (float) $sale->tax_amount;
            $grandTotal = (float) $sale->grand_total;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $sale->invoice_number);
            $sheet->setCellValue("C{$row}", $dateStr);
            $sheet->setCellValue("D{$row}", $cashier);
            $sheet->setCellValue("E{$row}", $customer);
            $sheet->setCellValue("F{$row}", $warehouse);
            $sheet->setCellValue("G{$row}", $paymentMethod);
            $sheet->setCellValue("H{$row}", $paymentStatus);
            $sheet->setCellValue("I{$row}", $subtotal);
            $sheet->setCellValue("J{$row}", $discount);
            $sheet->setCellValue("K{$row}", $tax);
            $sheet->setCellValue("L{$row}", $grandTotal);

            $this->styleDataRow($sheet, $row, 'L', $no % 2 === 0);

            // Alignments
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Currency formats
            $sheet->getStyle("I{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            if ($isVoid) {
                $sheet->getStyle("A{$row}:L{$row}")->getFont()->getColor()->setRGB('94A3B8');
            }

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        // 4. Totals / Summary Row
        if ($sales->count() > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("I{$row}", "=SUM(I{$startDataRow}:I{$endDataRow})");
            $sheet->setCellValue("J{$row}", "=SUM(J{$startDataRow}:J{$endDataRow})");
            $sheet->setCellValue("K{$row}", "=SUM(K{$startDataRow}:K{$endDataRow})");
            $sheet->setCellValue("L{$row}", "=SUM(L{$startDataRow}:L{$endDataRow})");

            $this->styleSummaryRow($sheet, $row, 'L');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        // 5. Freeze Pane & Auto Width
        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'L');

        $filename = 'Laporan_Penjualan_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Sales by Product & Margin to formatted Excel (.xlsx)
     */
    public function exportSalesByProduct($products, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penjualan Produk & Margin');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN PENJUALAN PER PRODUK & MARGIN LABA';

        // 1. Meta / Header info
        $this->writeReportHeader($sheet, $appName, $title, $meta, 'I');

        // 2. Table Headers
        $headers = [
            'A5' => 'No',
            'B5' => 'Kode Produk',
            'C5' => 'Nama Produk',
            'D5' => 'Kategori',
            'E5' => 'Qty Terjual',
            'F5' => 'Total Penjualan (Rp)',
            'G5' => 'Total HPP Modal (Rp)',
            'H5' => 'Laba Kotor (Rp)',
            'I5' => 'Margin (%)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        // 3. Write Data Rows
        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($products as $item) {
            $qty = (float) $item->total_qty;
            $revenue = (float) $item->total_revenue;
            $cost = (float) $item->total_cost;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $item->product_code ?? '-');
            $sheet->setCellValue("C{$row}", $item->product_name ?? '-');
            $sheet->setCellValue("D{$row}", $item->category_name ?? 'Tanpa Kategori');
            $sheet->setCellValue("E{$row}", $qty);
            $sheet->setCellValue("F{$row}", $revenue);
            $sheet->setCellValue("G{$row}", $cost);
            $sheet->setCellValue("H{$row}", "=F{$row}-G{$row}");
            $sheet->setCellValue("I{$row}", "=IF(F{$row}>0, H{$row}/F{$row}, 0)");

            $this->styleDataRow($sheet, $row, 'I', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('0.0%');

            $sheet->getStyle("E{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        // 4. Totals / Summary Row
        if (count($products) > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("E{$row}", "=SUM(E{$startDataRow}:E{$endDataRow})");
            $sheet->setCellValue("F{$row}", "=SUM(F{$startDataRow}:F{$endDataRow})");
            $sheet->setCellValue("G{$row}", "=SUM(G{$startDataRow}:G{$endDataRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$startDataRow}:H{$endDataRow})");
            $sheet->setCellValue("I{$row}", "=IF(F{$row}>0, H{$row}/F{$row}, 0)");

            $this->styleSummaryRow($sheet, $row, 'I');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // 5. Freeze Pane & Auto Width
        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'I');

        $filename = 'Laporan_Penjualan_Produk_Margin_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Purchase Orders to formatted Excel (.xlsx)
     */
    public function exportPurchases($purchases, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pembelian');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN PESANAN PEMBELIAN (PURCHASE ORDERS)';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'L');

        $headers = [
            'A5' => 'No',
            'B5' => 'No. PO',
            'C5' => 'Tanggal Pesan',
            'D5' => 'Estimasi Tiba',
            'E5' => 'Supplier',
            'F5' => 'Gudang Tujuan',
            'G5' => 'Status PO',
            'H5' => 'Subtotal (Rp)',
            'I5' => 'Diskon (Rp)',
            'J5' => 'Pajak (Rp)',
            'K5' => 'Ongkir (Rp)',
            'L5' => 'Grand Total (Rp)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($purchases as $po) {
            $orderDate = $po->order_date ? Carbon::parse($po->order_date)->format('d/m/Y') : '-';
            $expectedDate = $po->expected_date ? Carbon::parse($po->expected_date)->format('d/m/Y') : '-';
            $supplier = $po->supplier->name ?? '-';
            $warehouse = $po->warehouse->name ?? '-';
            $status = strtoupper($po->status ?? 'DRAFT');

            $subtotal = (float) $po->subtotal;
            $discount = (float) $po->discount_amount;
            $tax = (float) $po->tax_amount;
            $shipping = (float) $po->shipping_cost;
            $grandTotal = (float) $po->grand_total;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $po->po_number);
            $sheet->setCellValue("C{$row}", $orderDate);
            $sheet->setCellValue("D{$row}", $expectedDate);
            $sheet->setCellValue("E{$row}", $supplier);
            $sheet->setCellValue("F{$row}", $warehouse);
            $sheet->setCellValue("G{$row}", $status);
            $sheet->setCellValue("H{$row}", $subtotal);
            $sheet->setCellValue("I{$row}", $discount);
            $sheet->setCellValue("J{$row}", $tax);
            $sheet->setCellValue("K{$row}", $shipping);
            $sheet->setCellValue("L{$row}", $grandTotal);

            $this->styleDataRow($sheet, $row, 'L', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("H{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}:L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if ($purchases->count() > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->setCellValue("H{$row}", "=SUM(H{$startDataRow}:H{$endDataRow})");
            $sheet->setCellValue("I{$row}", "=SUM(I{$startDataRow}:I{$endDataRow})");
            $sheet->setCellValue("J{$row}", "=SUM(J{$startDataRow}:J{$endDataRow})");
            $sheet->setCellValue("K{$row}", "=SUM(K{$startDataRow}:K{$endDataRow})");
            $sheet->setCellValue("L{$row}", "=SUM(L{$startDataRow}:L{$endDataRow})");

            $this->styleSummaryRow($sheet, $row, 'L');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$row}:L{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'L');

        $filename = 'Laporan_Pembelian_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Stock & Valuation Report to formatted Excel (.xlsx)
     */
    public function exportStocks($stocks, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Stok & Persediaan');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN STOK & NILAI PERSEDIAAN (INVENTORY VALUATION)';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'N');

        $headers = [
            'A5' => 'No',
            'B5' => 'Kode Produk',
            'C5' => 'Barcode',
            'D5' => 'Nama Produk',
            'E5' => 'Kategori',
            'F5' => 'Cabang / Gudang',
            'G5' => 'Sisa Stok',
            'H5' => 'Satuan',
            'I5' => 'Stok Min',
            'J5' => 'HPP Pokok (Rp)',
            'K5' => 'Harga Jual (Rp)',
            'L5' => 'Total Nilai HPP (Rp)',
            'M5' => 'Potensi Omzet (Rp)',
            'N5' => 'Status Stok',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($stocks as $s) {
            $qty = (float) $s->quantity;
            $min = (float) ($s->product->min_stock ?? 0);
            $status = $qty <= 0 ? 'HABIS' : ($qty <= $min ? 'KRITIS' : 'AMAN');
            $cost = (float) ($s->product->purchase_price ?? 0);
            $price = (float) ($s->product->selling_price ?? 0);
            $valuation = $qty * $cost;
            $potential = $qty * $price;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $s->product->code ?? '-');
            $sheet->setCellValue("C{$row}", $s->product->barcode ?? '-');
            $sheet->setCellValue("D{$row}", $s->product->name ?? '-');
            $sheet->setCellValue("E{$row}", $s->product->category->name ?? 'Tanpa Kategori');
            $sheet->setCellValue("F{$row}", $s->warehouse->name ?? '-');
            $sheet->setCellValue("G{$row}", $qty);
            $sheet->setCellValue("H{$row}", $s->product->baseUnit->name ?? 'Pcs');
            $sheet->setCellValue("I{$row}", $min);
            $sheet->setCellValue("J{$row}", $cost);
            $sheet->setCellValue("K{$row}", $price);
            $sheet->setCellValue("L{$row}", "=G{$row}*J{$row}");
            $sheet->setCellValue("M{$row}", "=G{$row}*K{$row}");
            $sheet->setCellValue("N{$row}", $status);

            $this->styleDataRow($sheet, $row, 'N', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Numbers
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("J{$row}:M{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("J{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Conditional status styling
            if ($status === 'HABIS') {
                $sheet->getStyle("N{$row}")->getFont()->getColor()->setRGB('E11D48');
                $sheet->getStyle("N{$row}")->getFont()->setBold(true);
            } elseif ($status === 'KRITIS') {
                $sheet->getStyle("N{$row}")->getFont()->getColor()->setRGB('D97706');
                $sheet->getStyle("N{$row}")->getFont()->setBold(true);
            } else {
                $sheet->getStyle("N{$row}")->getFont()->getColor()->setRGB('059669');
            }

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if ($stocks->count() > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL PERSDIAAN');
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("G{$row}", "=SUM(G{$startDataRow}:G{$endDataRow})");
            $sheet->setCellValue("H{$row}", '');
            $sheet->setCellValue("I{$row}", '');
            $sheet->setCellValue("J{$row}", '');
            $sheet->setCellValue("K{$row}", '');
            $sheet->setCellValue("L{$row}", "=SUM(L{$startDataRow}:L{$endDataRow})");
            $sheet->setCellValue("M{$row}", "=SUM(M{$startDataRow}:M{$endDataRow})");
            $sheet->setCellValue("N{$row}", '');

            $this->styleSummaryRow($sheet, $row, 'N');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("L{$row}:M{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'N');

        $filename = 'Laporan_Stok_Nilai_Persediaan_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Sales by Category to formatted Excel (.xlsx)
     */
    public function exportSalesByCategory($categoriesReport, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penjualan per Kategori');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN PENJUALAN PER KATEGORI PRODUK';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'G');

        $headers = [
            'A5' => 'No',
            'B5' => 'Nama Kategori',
            'C5' => 'Variasi Produk',
            'D5' => 'Total Qty Terjual',
            'E5' => 'Total Pendapatan (Rp)',
            'F5' => 'Total HPP Modal (Rp)',
            'G5' => 'Laba Kotor (Rp)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($categoriesReport as $cat) {
            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $cat->category_name ?? 'Tanpa Kategori');
            $sheet->setCellValue("C{$row}", (int) $cat->unique_products_count);
            $sheet->setCellValue("D{$row}", (float) $cat->total_qty);
            $sheet->setCellValue("E{$row}", (float) $cat->total_revenue);
            $sheet->setCellValue("F{$row}", (float) $cat->total_cost);
            $sheet->setCellValue("G{$row}", "=E{$row}-F{$row}");

            $this->styleDataRow($sheet, $row, 'G', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if (count($categoriesReport) > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("D{$row}", "=SUM(D{$startDataRow}:D{$endDataRow})");
            $sheet->setCellValue("E{$row}", "=SUM(E{$startDataRow}:E{$endDataRow})");
            $sheet->setCellValue("F{$row}", "=SUM(F{$startDataRow}:F{$endDataRow})");
            $sheet->setCellValue("G{$row}", "=SUM(G{$startDataRow}:G{$endDataRow})");

            $this->styleSummaryRow($sheet, $row, 'G');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'G');

        $filename = 'Laporan_Penjualan_Kategori_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Sales by Customer to formatted Excel (.xlsx)
     */
    public function exportSalesByCustomer($customers, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penjualan per Pelanggan');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN PENJUALAN PER PELANGGAN (CUSTOMER)';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'G');

        $headers = [
            'A5' => 'No',
            'B5' => 'Nama Pelanggan',
            'C5' => 'No. Telepon / Kontak',
            'D5' => 'Jumlah Transaksi',
            'E5' => 'Total Belanja (Rp)',
            'F5' => 'Rata-rata Order (Rp)',
            'G5' => 'Transaksi Terakhir',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($customers as $c) {
            $lastOrder = $c->last_order_date ? Carbon::parse($c->last_order_date)->format('d/m/Y H:i') : '-';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $c->customer_name ?? 'Pelanggan Umum');
            $sheet->setCellValue("C{$row}", $c->customer_phone ?? ($c->customer_code ?? '-'));
            $sheet->setCellValue("D{$row}", (int) $c->total_orders);
            $sheet->setCellValue("E{$row}", (float) $c->total_spent);
            $sheet->setCellValue("F{$row}", (float) $c->avg_spent);
            $sheet->setCellValue("G{$row}", $lastOrder);

            $this->styleDataRow($sheet, $row, 'G', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if (count($customers) > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("D{$row}", "=SUM(D{$startDataRow}:D{$endDataRow})");
            $sheet->setCellValue("E{$row}", "=SUM(E{$startDataRow}:E{$endDataRow})");
            $sheet->setCellValue("F{$row}", "=AVERAGE(F{$startDataRow}:F{$endDataRow})");
            $sheet->setCellValue("G{$row}", '');

            $this->styleSummaryRow($sheet, $row, 'G');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'G');

        $filename = 'Laporan_Penjualan_Pelanggan_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Stock Opnames to formatted Excel (.xlsx)
     */
    public function exportStockOpnames($opnames, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Hasil Stok Opname');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN REKAPITULASI HASIL STOK OPNAME';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'G');

        $headers = [
            'A5' => 'No',
            'B5' => 'No. Opname',
            'C5' => 'Tanggal Opname',
            'D5' => 'Gudang / Cabang',
            'E5' => 'Pelaksana / Petugas',
            'F5' => 'Approver',
            'G5' => 'Status',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;

        foreach ($opnames as $op) {
            $opnameDate = $op->opname_date ? Carbon::parse($op->opname_date)->format('d/m/Y') : '-';
            $status = strtoupper($op->status ?? 'DRAFT');

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $op->opname_number);
            $sheet->setCellValue("C{$row}", $opnameDate);
            $sheet->setCellValue("D{$row}", $op->warehouse->name ?? '-');
            $sheet->setCellValue("E{$row}", $op->conductor->name ?? '-');
            $sheet->setCellValue("F{$row}", $op->approver->name ?? '-');
            $sheet->setCellValue("G{$row}", $status);

            $this->styleDataRow($sheet, $row, 'G', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($status === 'APPROVED') {
                $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle("G{$row}")->getFont()->setBold(true);
            } elseif ($status === 'CANCELLED') {
                $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB('E11D48');
            }

            $row++;
            $no++;
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'G');

        $filename = 'Laporan_Stok_Opname_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Profit and Loss Statement to formatted Excel (.xlsx)
     */
    public function exportProfitLoss(array $data, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laba Rugi');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN LABA RUGI SEDERHANA (PROFIT & LOSS)';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'D');

        // Table Header
        $headers = [
            'A5' => 'No',
            'B5' => 'Komponen Finansial',
            'C5' => 'Keterangan',
            'D5' => 'Nominal (Rp)',
        ];
        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;

        // 1. Pendapatan Penjualan
        $sheet->setCellValue("A{$row}", '1');
        $sheet->setCellValue("B{$row}", 'PENDAPATAN PENJUALAN BERSIH');
        $sheet->setCellValue("C{$row}", 'Gross Sales dikurangi Diskon');
        $sheet->setCellValue("D{$row}", (float) ($data['netSales'] ?? 0));
        $this->styleDataRow($sheet, $row, 'D', false);
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        // Detail Penjualan Kotor & Diskon
        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", '   - Penjualan Kotor (Gross Sales)');
        $sheet->setCellValue("C{$row}", 'Total subtotal penjualan');
        $sheet->setCellValue("D{$row}", (float) ($data['grossSales'] ?? 0));
        $this->styleDataRow($sheet, $row, 'D', true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", '   - Potongan Diskon Penjualan');
        $sheet->setCellValue("C{$row}", 'Total diskon transaksi & item');
        $sheet->setCellValue("D{$row}", (float) ($data['salesDiscounts'] ?? 0));
        $this->styleDataRow($sheet, $row, 'D', false);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        // 2. HPP (Cost of Goods Sold)
        $sheet->setCellValue("A{$row}", '2');
        $sheet->setCellValue("B{$row}", 'BEBAN POKOK PENJUALAN (HPP FIFO)');
        $sheet->setCellValue("C{$row}", 'Total modal harga pokok barang terjual');
        $sheet->setCellValue("D{$row}", (float) ($data['totalHpp'] ?? 0));
        $this->styleDataRow($sheet, $row, 'D', true);
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        // Subtotal Laba Kotor (Gross Profit)
        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", 'LABA KOTOR (GROSS PROFIT)');
        $sheet->setCellValue("C{$row}", 'Pendapatan Bersih - Beban HPP');
        $sheet->setCellValue("D{$row}", (float) ($data['grossProfit'] ?? 0));
        $this->styleSummaryRow($sheet, $row, 'D');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        // 3. Beban Operasional Kas Keluar
        $sheet->setCellValue("A{$row}", '3');
        $sheet->setCellValue("B{$row}", 'TOTAL BIAYA OPERASIONAL');
        $sheet->setCellValue("C{$row}", 'Total kas keluar operasional');
        $sheet->setCellValue("D{$row}", (float) ($data['totalExpenses'] ?? 0));
        $this->styleDataRow($sheet, $row, 'D', true);
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        if (! empty($data['expensesByCategory'])) {
            foreach ($data['expensesByCategory'] as $exp) {
                $sheet->setCellValue("A{$row}", '');
                $sheet->setCellValue("B{$row}", '   - Beban: '.ucfirst($exp->category ?: 'Operasional Umum'));
                $sheet->setCellValue("C{$row}", 'Pengeluaran operasional');
                $sheet->setCellValue("D{$row}", (float) $exp->total_expense);
                $this->styleDataRow($sheet, $row, 'D', false);
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $row++;
            }
        }

        // 4. Laba Bersih
        $sheet->setCellValue("A{$row}", '4');
        $sheet->setCellValue("B{$row}", 'LABA BERSIH (NET PROFIT)');
        $sheet->setCellValue("C{$row}", 'Laba Kotor - Total Biaya Operasional (Margin: '.number_format($data['netProfitMargin'] ?? 0, 1).'%)');
        $sheet->setCellValue("D{$row}", (float) ($data['netProfit'] ?? 0));
        $this->styleSummaryRow($sheet, $row, 'D');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("D{$row}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'D');

        $filename = 'Laporan_Laba_Rugi_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Payables (Hutang Supplier) to formatted Excel (.xlsx)
     */
    public function exportPayables($payablesData, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hutang Supplier (AP)');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN HUTANG USAHA SUPPLIER (ACCOUNTS PAYABLE)';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'H');

        $headers = [
            'A5' => 'No',
            'B5' => 'No. Penerimaan',
            'C5' => 'No. PO',
            'D5' => 'Supplier',
            'E5' => 'Tanggal Masuk',
            'F5' => 'Umur Hutang (Hari)',
            'G5' => 'Nilai Tagihan (Rp)',
            'H5' => 'Sisa Hutang (Rp)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($payablesData as $p) {
            $dateStr = $p->receipt_date ? Carbon::parse($p->receipt_date)->format('d/m/Y') : '-';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $p->receipt_number);
            $sheet->setCellValue("C{$row}", $p->po_number ?? '-');
            $sheet->setCellValue("D{$row}", $p->supplier_name);
            $sheet->setCellValue("E{$row}", $dateStr);
            $sheet->setCellValue("F{$row}", "{$p->days_outstanding} ({$p->aging_group})");
            $sheet->setCellValue("G{$row}", (float) $p->total_amount);
            $sheet->setCellValue("H{$row}", (float) $p->outstanding_amount);

            $this->styleDataRow($sheet, $row, 'H', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if (count($payablesData) > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("G{$row}", "=SUM(G{$startDataRow}:G{$endDataRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$startDataRow}:H{$endDataRow})");

            $this->styleSummaryRow($sheet, $row, 'H');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'H');

        $filename = 'Laporan_Hutang_Supplier_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Receivables (Piutang Customer) to formatted Excel (.xlsx)
     */
    public function exportReceivables($receivablesData, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Piutang Pelanggan (AR)');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN PIUTANG USAHA PELANGGAN (ACCOUNTS RECEIVABLE)';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'H');

        $headers = [
            'A5' => 'No',
            'B5' => 'No. Faktur',
            'C5' => 'Pelanggan',
            'D5' => 'Kontak / HP',
            'E5' => 'Tanggal Transaksi',
            'F5' => 'Umur Piutang (Hari)',
            'G5' => 'Total Nilai (Rp)',
            'H5' => 'Sisa Piutang (Rp)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($receivablesData as $r) {
            $dateStr = $r->sale_date ? Carbon::parse($r->sale_date)->format('d/m/Y') : '-';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $r->invoice_number);
            $sheet->setCellValue("C{$row}", $r->customer_name);
            $sheet->setCellValue("D{$row}", $r->customer_phone ?? '-');
            $sheet->setCellValue("E{$row}", $dateStr);
            $sheet->setCellValue("F{$row}", "{$r->days_outstanding} ({$r->aging_group})");
            $sheet->setCellValue("G{$row}", (float) $r->total_amount);
            $sheet->setCellValue("H{$row}", (float) $r->outstanding_amount);

            $this->styleDataRow($sheet, $row, 'H', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("G{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if (count($receivablesData) > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("G{$row}", "=SUM(G{$startDataRow}:G{$endDataRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$startDataRow}:H{$endDataRow})");

            $this->styleSummaryRow($sheet, $row, 'H');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'H');

        $filename = 'Laporan_Piutang_Pelanggan_'.now()->format('Ymd_His').'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Cash Flows to formatted Excel (.xlsx)
     */
    public function exportCashFlows($cashFlows, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Arus Kas');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN MUTASI ARUS KAS & BANK';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'G');

        $headers = [
            'A5' => 'No',
            'B5' => 'No. Bukti Mutasi',
            'C5' => 'Tanggal',
            'D5' => 'Akun Kas / Bank',
            'E5' => 'Tipe Mutasi',
            'F5' => 'Kategori / Keterangan',
            'G5' => 'Nominal (Rp)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;

        foreach ($cashFlows as $cf) {
            $dateStr = $cf->transaction_date ? Carbon::parse($cf->transaction_date)->format('d/m/Y') : '-';
            $typeLabel = $cf->type === 'in' ? 'MASUK (IN)' : 'KELUAR (OUT)';
            $desc = ($cf->category ? '['.ucfirst($cf->category).'] ' : '').($cf->description ?: '-');
            $nominal = (float) $cf->amount;

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $cf->cash_flow_number);
            $sheet->setCellValue("C{$row}", $dateStr);
            $sheet->setCellValue("D{$row}", $cf->account->name ?? '-');
            $sheet->setCellValue("E{$row}", $typeLabel);
            $sheet->setCellValue("F{$row}", $desc);
            $sheet->setCellValue("G{$row}", $cf->type === 'in' ? $nominal : -$nominal);

            $this->styleDataRow($sheet, $row, 'G', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($cf->type === 'in') {
                $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB('059669');
            } else {
                $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB('E11D48');
                $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB('E11D48');
            }

            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'G');

        $filename = 'Laporan_Mutasi_Arus_Kas_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Cashier Shifts to formatted Excel (.xlsx)
     */
    public function exportCashierShifts($shifts, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Shift Kasir');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'LAPORAN REKAPITULASI SESI SHIFT KASIR';

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'J');

        $headers = [
            'A5' => 'No',
            'B5' => 'Kasir',
            'C5' => 'Cabang / Gudang',
            'D5' => 'Waktu Buka',
            'E5' => 'Waktu Tutup',
            'F5' => 'Modal Awal (Rp)',
            'G5' => 'Total Penjualan (Rp)',
            'H5' => 'Biaya Kasir (Rp)',
            'I5' => 'Fisik Kas Tutup (Rp)',
            'J5' => 'Selisih Kas (Rp)',
        ];

        $this->writeTableHeaders($sheet, $headers, 5);

        $row = 6;
        $no = 1;
        $startDataRow = $row;

        foreach ($shifts as $sh) {
            $openedStr = $sh->opened_at ? Carbon::parse($sh->opened_at)->format('d/m/Y H:i') : '-';
            $closedStr = $sh->closed_at ? Carbon::parse($sh->closed_at)->format('d/m/Y H:i') : 'Masih Terbuka';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $sh->user->name ?? 'Kasir');
            $sheet->setCellValue("C{$row}", $sh->warehouse->name ?? '-');
            $sheet->setCellValue("D{$row}", $openedStr);
            $sheet->setCellValue("E{$row}", $closedStr);
            $sheet->setCellValue("F{$row}", (float) $sh->starting_cash);
            $sheet->setCellValue("G{$row}", (float) $sh->total_sales);
            $sheet->setCellValue("H{$row}", (float) ($sh->total_expenses ?? 0));
            $sheet->setCellValue("I{$row}", $sh->closing_cash !== null ? (float) $sh->closing_cash : '-');
            $sheet->setCellValue("J{$row}", $sh->cash_difference !== null ? (float) $sh->cash_difference : 0);

            $this->styleDataRow($sheet, $row, 'J', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("F{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
            $sheet->getStyle("F{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if ($shifts->count() > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL KESELURUHAN');
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("F{$row}", "=SUM(F{$startDataRow}:F{$endDataRow})");
            $sheet->setCellValue("G{$row}", "=SUM(G{$startDataRow}:G{$endDataRow})");
            $sheet->setCellValue("H{$row}", "=SUM(H{$startDataRow}:H{$endDataRow})");
            $sheet->setCellValue("I{$row}", "=SUM(I{$startDataRow}:I{$endDataRow})");
            $sheet->setCellValue("J{$row}", "=SUM(J{$startDataRow}:J{$endDataRow})");

            $this->styleSummaryRow($sheet, $row, 'J');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$row}:J{$row}")->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
        }

        $sheet->freezePane('A6');
        $this->autoFitColumns($sheet, 'A', 'J');

        $filename = 'Laporan_Rekap_Shift_Kasir_'.($meta['start_date'] ?? now()->format('Y-m-d')).'_sampai_'.($meta['end_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Export Stock Movements (Kartu Mutasi Stok) to formatted Excel (.xlsx)
     */
    public function exportStockMovements($movements, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kartu Stok');
        $sheet->setShowGridLines(true);

        $appName = Setting::get('app_name', 'POS Retail Pro');
        $title = 'KARTU STOK & RIWAYAT MUTASI BARANG (STOCK CARD)';
        $product = $meta['product'] ?? null;

        $this->writeReportHeader($sheet, $appName, $title, $meta, 'I');

        $tableStartRow = 5;

        // If single product is selected, render a structured Product Header block
        if ($product) {
            // Row 5: Product Details
            $sheet->setCellValue('A5', 'Kode SKU / Item:');
            $sheet->setCellValue('B5', $product->code ?? '-');
            $sheet->setCellValue('D5', 'Kategori:');
            $sheet->setCellValue('E5', $product->category->name ?? 'Tanpa Kategori');
            $sheet->setCellValue('G5', 'Stok Awal:');
            $sheet->setCellValue('H5', (float) ($meta['initial_stock'] ?? 0));

            // Row 6
            $sheet->setCellValue('A6', 'Nama Produk:');
            $sheet->setCellValue('B6', $product->name ?? '-');
            $sheet->setCellValue('D6', 'Satuan Dasar:');
            $sheet->setCellValue('E6', ($product->baseUnit->name ?? 'Pcs').' ('.($product->baseUnit->symbol ?? 'pcs').')');
            $sheet->setCellValue('G6', 'Total Masuk (+):');
            $sheet->setCellValue('H6', (float) ($meta['total_in'] ?? 0));

            // Row 7
            $sheet->setCellValue('A7', 'Barcode:');
            $sheet->setCellValue('B7', $product->barcode ?: '-');
            $sheet->setCellValue('D7', 'Harga Beli / Jual:');
            $sheet->setCellValue('E7', 'Rp '.number_format($product->purchase_price, 0, ',', '.').' / Rp '.number_format($product->selling_price, 0, ',', '.'));
            $sheet->setCellValue('G7', 'Total Keluar (-):');
            $sheet->setCellValue('H7', (float) ($meta['total_out'] ?? 0));

            // Row 8
            $sheet->setCellValue('A8', 'Gudang / Cabang:');
            $sheet->setCellValue('B8', $meta['warehouse'] ?? 'Semua Cabang / Gudang');
            $sheet->setCellValue('D8', 'Stok Minimum:');
            $sheet->setCellValue('E8', (float) ($product->min_stock ?? 0));
            $sheet->setCellValue('G8', 'Stok Akhir:');
            $sheet->setCellValue('H8', (float) ($meta['final_stock'] ?? 0));

            // Style Product Header box (Rows 5-8)
            $cardRange = 'A5:I8';
            $sheet->getStyle($cardRange)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8FAFC'],
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '94A3B8'],
                    ],
                    'inside' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Bold labels
            $sheet->getStyle('A5:A8')->getFont()->setBold(true)->getColor()->setRGB('475569');
            $sheet->getStyle('D5:D8')->getFont()->setBold(true)->getColor()->setRGB('475569');
            $sheet->getStyle('G5:G8')->getFont()->setBold(true)->getColor()->setRGB('475569');
            $sheet->getStyle('B5:B8')->getFont()->setBold(true);
            $sheet->getStyle('E5:E8')->getFont()->setBold(true);

            $sheet->getStyle('H5:H8')->getFont()->setBold(true);
            $sheet->getStyle('H5')->getFont()->getColor()->setRGB('0F172A');
            $sheet->getStyle('H6')->getFont()->getColor()->setRGB('059669');
            $sheet->getStyle('H7')->getFont()->getColor()->setRGB('E11D48');
            $sheet->getStyle('H8')->getFont()->getColor()->setRGB('0284C7');
            $sheet->getStyle('H5:H8')->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H5:H8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $tableStartRow = 10;
        }

        $headers = [
            "A{$tableStartRow}" => 'No',
            "B{$tableStartRow}" => 'Tanggal & Waktu',
            "C{$tableStartRow}" => 'Dokumen / Ref',
            "D{$tableStartRow}" => 'Gudang / Cabang',
            "E{$tableStartRow}" => 'Tipe Mutasi',
            "F{$tableStartRow}" => 'Masuk (+)',
            "G{$tableStartRow}" => 'Keluar (-)',
            "H{$tableStartRow}" => 'Saldo Akhir',
            "I{$tableStartRow}" => 'Keterangan',
        ];

        $this->writeTableHeaders($sheet, $headers, $tableStartRow);

        $row = $tableStartRow + 1;
        $no = 1;
        $startDataRow = $row;

        foreach ($movements as $m) {
            $dateStr = $m->created_at ? Carbon::parse($m->created_at)->format('d/m/Y H:i') : '-';
            $warehouse = $m->warehouse->name ?? 'Gudang Utama';
            $typeLabel = $m->type === 'in' ? 'MASUK' : 'KELUAR';
            $qty = (float) $m->quantity;
            $ref = $m->reference_type ? class_basename($m->reference_type).($m->reference_id ? ' #'.$m->reference_id : '') : '-';
            $desc = $m->description ?: '-';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $dateStr);
            $sheet->setCellValue("C{$row}", $ref);
            $sheet->setCellValue("D{$row}", $warehouse);
            $sheet->setCellValue("E{$row}", $typeLabel);
            $sheet->setCellValue("F{$row}", $m->type === 'in' ? $qty : 0);
            $sheet->setCellValue("G{$row}", $m->type === 'out' ? $qty : 0);
            $sheet->setCellValue("H{$row}", (float) $m->after_stock);
            $sheet->setCellValue("I{$row}", $desc);

            $this->styleDataRow($sheet, $row, 'I', $no % 2 === 0);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($m->type === 'in') {
                $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('059669');
                $sheet->getStyle("F{$row}")->getFont()->setBold(true);
            } else {
                $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB('E11D48');
                $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB('E11D48');
                $sheet->getStyle("G{$row}")->getFont()->setBold(true);
            }

            $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"-"');
            $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$row}")->getFont()->setBold(true);

            $row++;
            $no++;
        }

        $endDataRow = max($startDataRow, $row - 1);

        if (count($movements) > 0) {
            $sheet->setCellValue("A{$row}", 'TOTAL MUTASI:');
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("F{$row}", "=SUM(F{$startDataRow}:F{$endDataRow})");
            $sheet->setCellValue("G{$row}", "=SUM(G{$startDataRow}:G{$endDataRow})");
            $sheet->setCellValue("H{$row}", "=H{$endDataRow}");
            $sheet->setCellValue("I{$row}", '');

            $this->styleSummaryRow($sheet, $row, 'I');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('059669');
            $sheet->getStyle("G{$row}")->getFont()->getColor()->setRGB('E11D48');
            $sheet->getStyle("H{$row}")->getFont()->getColor()->setRGB('0284C7');
        }

        $sheet->freezePane('A'.($tableStartRow + 1));
        $this->autoFitColumns($sheet, 'A', 'I');

        $productSuffix = $product ? '_'.str_replace(' ', '_', $product->name) : '';
        $filename = 'Kartu_Stok_Mutasi'.$productSuffix.'_'.($meta['start_date'] ?? now()->format('Y-m-d')).'.xlsx';

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Helper to write structured report header metadata
     */
    protected function writeReportHeader(Worksheet $sheet, string $appName, string $title, array $meta, string $maxCol): void
    {
        // Line 1: Store / Company Name from Settings
        $companyName = Setting::get('company_name', Setting::get('app_name', $appName));
        $sheet->setCellValue('A1', strtoupper($companyName));
        $sheet->mergeCells("A1:{$maxCol}1");
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('0F172A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Line 2: Report Title
        $sheet->setCellValue('A2', $title);
        $sheet->mergeCells("A2:{$maxCol}2");
        $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('EA580C');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Line 3: Meta details
        $metaParts = [];
        if (! empty($meta['period'])) {
            $metaParts[] = "Periode: {$meta['period']}";
        } elseif (! empty($meta['start_date']) && ! empty($meta['end_date'])) {
            $s = Carbon::parse($meta['start_date'])->format('d/m/Y');
            $e = Carbon::parse($meta['end_date'])->format('d/m/Y');
            $metaParts[] = "Periode: {$s} s/d {$e}";
        }

        if (! empty($meta['warehouse'])) {
            $metaParts[] = "Cabang/Gudang: {$meta['warehouse']}";
        }

        $metaParts[] = 'Dicetak: '.now()->format('d/m/Y H:i');
        $metaParts[] = 'Oleh: '.(auth()->user()->name ?? 'Administrator');

        $metaText = implode('  |  ', $metaParts);
        $sheet->setCellValue('A3', $metaText);
        $sheet->mergeCells("A3:{$maxCol}3");
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB(self::COLOR_TEXT_MUTED);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Row 4 is spacer
        $sheet->getRowDimension(4)->setRowHeight(8);
    }

    /**
     * Helper to write and format Table Header row
     */
    protected function writeTableHeaders(Worksheet $sheet, array $headers, int $headerRow): void
    {
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $firstCol = array_key_first($headers);
        $lastCol = array_key_last($headers);
        $range = "{$firstCol}:{$lastCol}";

        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => self::COLOR_HEADER_TEXT],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_HEADER_BG],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'wrapText' => false,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '334155'],
                ],
            ],
        ]);
    }

    /**
     * Helper to style a single data row
     */
    protected function styleDataRow(Worksheet $sheet, int $row, string $maxCol, bool $isEven): void
    {
        $sheet->getRowDimension($row)->setRowHeight(20);
        $range = "A{$row}:{$maxCol}{$row}";

        $style = [
            'font' => [
                'size' => 9.5,
                'color' => ['rgb' => '1E293B'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::COLOR_BORDER],
                ],
                'left' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::COLOR_BORDER],
                ],
                'right' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::COLOR_BORDER],
                ],
            ],
        ];

        if ($isEven) {
            $style['fill'] = [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_ZEBRA_BG],
            ];
        }

        $sheet->getStyle($range)->applyFromArray($style);
    }

    /**
     * Helper to style summary / total row
     */
    protected function styleSummaryRow(Worksheet $sheet, int $row, string $maxCol): void
    {
        $sheet->getRowDimension($row)->setRowHeight(24);
        $range = "A{$row}:{$maxCol}{$row}";

        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_TOTAL_BG],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '94A3B8'],
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_DOUBLE,
                    'color' => ['rgb' => '0F172A'],
                ],
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);
    }

    /**
     * Auto fit column widths with padding while keeping Column A (No) compact
     */
    protected function autoFitColumns(Worksheet $sheet, string $startCol, string $endCol): void
    {
        // Fix Column A (No) width to a neat, compact size (does not expand with Kop)
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth(7);

        $startIdx = Coordinate::columnIndexFromString($startCol === 'A' ? 'B' : $startCol);
        $endIdx = Coordinate::columnIndexFromString($endCol);

        for ($i = $startIdx; $i <= $endIdx; $i++) {
            $colStr = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colStr)->setAutoSize(true);
        }
    }

    /**
     * Stream binary Excel .xlsx response to browser
     */
    protected function streamSpreadsheet(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
            'Pragma' => 'public',
        ];

        return response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headers);
    }
}
