<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the settings management page with tabs.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'profile');

        // Store profile settings
        $companyName = Setting::get('company_name', 'POS Pro Enterprise');
        $companyTagline = Setting::get('company_tagline', 'Solusi Kasir & Manajemen Ritel Modern');
        $companyAddress = Setting::get('company_address', 'Jl. Sudirman No. 45, Jakarta Pusat');
        $companyPhone = Setting::get('company_phone', '0812-3456-7890');
        $companyEmail = Setting::get('company_email', 'support@pospro.com');
        $companyTaxNumber = Setting::get('company_npwp', '01.234.567.8-901.000');
        $companyLogo = Setting::get('company_logo', null);

        // Transaction prefix settings
        $prefixInvoice = Setting::get('prefix_invoice', 'INV');
        $prefixPo = Setting::get('prefix_po', 'PO');
        $prefixGrn = Setting::get('prefix_grn', 'GRN');
        $prefixReturnSale = Setting::get('prefix_return_sale', 'SR');
        $prefixReturnPurchase = Setting::get('prefix_return_purchase', 'PR');
        $prefixOpname = Setting::get('prefix_opname', 'SO');
        $prefixTransfer = Setting::get('prefix_transfer', 'TF');

        // Tax and currency settings
        $defaultTaxRate = Setting::get('default_tax_rate', '11');
        $currencySymbol = Setting::get('currency_symbol', 'Rp');
        $currencyCode = Setting::get('currency_code', 'IDR');

        // Receipt template settings
        $receiptHeader = Setting::get('receipt_header', "Terima Kasih Telah Berbelanja!\nBarang yang sudah dibeli tidak dapat ditukar.");
        $receiptFooter = Setting::get('receipt_footer', "Simpan struk ini sebagai bukti pembayaran yang sah.\nInstagram: @pospro.id");
        $receiptPaperSize = Setting::get('receipt_paper_size', '58mm');
        $receiptShowLogo = Setting::get('receipt_show_logo', '1');

        return view('settings.index', compact(
            'tab',
            'companyName',
            'companyTagline',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxNumber',
            'companyLogo',
            'prefixInvoice',
            'prefixPo',
            'prefixGrn',
            'prefixReturnSale',
            'prefixReturnPurchase',
            'prefixOpname',
            'prefixTransfer',
            'defaultTaxRate',
            'currencySymbol',
            'currencyCode',
            'receiptHeader',
            'receiptFooter',
            'receiptPaperSize',
            'receiptShowLogo'
        ));
    }

    /**
     * Save/Update Store Profile Settings.
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:150',
            'company_tagline' => 'nullable|string|max:200',
            'company_address' => 'nullable|string|max:300',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:100',
            'company_npwp' => 'nullable|string|max:50',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        Setting::set('company_name', $validated['company_name'], 'profile', 'string', 'Nama Toko / Perusahaan');
        Setting::set('company_tagline', $validated['company_tagline'] ?? '', 'profile', 'string', 'Tagline Toko');
        Setting::set('company_address', $validated['company_address'] ?? '', 'profile', 'string', 'Alamat Toko');
        Setting::set('company_phone', $validated['company_phone'] ?? '', 'profile', 'string', 'No. Telepon / WhatsApp');
        Setting::set('company_email', $validated['company_email'] ?? '', 'profile', 'string', 'Email Kontak');
        Setting::set('company_npwp', $validated['company_npwp'] ?? '', 'profile', 'string', 'NPWP Perusahaan');

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('settings', 'public');
            Setting::set('company_logo', $path, 'profile', 'file', 'Logo Toko');
        }

        return redirect()->route('settings.index', ['tab' => 'profile'])->with('success', 'Profil toko berhasil diperbarui.');
    }

    /**
     * Save/Update Transaction Format Settings.
     */
    public function updatePrefixes(Request $request)
    {
        $validated = $request->validate([
            'prefix_invoice' => 'required|string|max:10',
            'prefix_po' => 'required|string|max:10',
            'prefix_grn' => 'required|string|max:10',
            'prefix_return_sale' => 'required|string|max:10',
            'prefix_return_purchase' => 'required|string|max:10',
            'prefix_opname' => 'required|string|max:10',
            'prefix_transfer' => 'required|string|max:10',
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, strtoupper($val), 'prefixes', 'string', "Prefix {$key}");
        }

        return redirect()->route('settings.index', ['tab' => 'prefixes'])->with('success', 'Format nomor transaksi berhasil diperbarui.');
    }

    /**
     * Save/Update Tax and Currency Settings.
     */
    public function updateTaxCurrency(Request $request)
    {
        $validated = $request->validate([
            'default_tax_rate' => 'required|numeric|min:0|max:100',
            'currency_symbol' => 'required|string|max:10',
            'currency_code' => 'required|string|max:10',
        ]);

        Setting::set('default_tax_rate', $validated['default_tax_rate'], 'tax', 'numeric', 'Default Pajak PPN (%)');
        Setting::set('currency_symbol', $validated['currency_symbol'], 'currency', 'string', 'Simbol Mata Uang');
        Setting::set('currency_code', strtoupper($validated['currency_code']), 'currency', 'string', 'Kode Mata Uang');

        return redirect()->route('settings.index', ['tab' => 'tax'])->with('success', 'Pengaturan pajak dan mata uang berhasil diperbarui.');
    }

    /**
     * Save/Update Receipt Template Settings.
     */
    public function updateReceipt(Request $request)
    {
        $validated = $request->validate([
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
            'receipt_paper_size' => 'required|in:58mm,80mm',
            'receipt_show_logo' => 'nullable|boolean',
        ]);

        Setting::set('receipt_header', $validated['receipt_header'] ?? '', 'receipt', 'string', 'Header Struk');
        Setting::set('receipt_footer', $validated['receipt_footer'] ?? '', 'receipt', 'string', 'Footer Struk');
        Setting::set('receipt_paper_size', $validated['receipt_paper_size'], 'receipt', 'string', 'Ukuran Kertas Struk');
        Setting::set('receipt_show_logo', $request->has('receipt_show_logo') ? '1' : '0', 'receipt', 'boolean', 'Tampilkan Logo di Struk');

        return redirect()->route('settings.index', ['tab' => 'receipt'])->with('success', 'Template struk kasir berhasil diperbarui.');
    }
}
