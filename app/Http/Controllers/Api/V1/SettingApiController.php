<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingApiController extends BaseApiController
{
    /**
     * Get all store settings grouped and structured for mobile consumption.
     */
    public function getSettings(): JsonResponse
    {
        $companyLogo = Setting::get('company_logo', '');
        $companyLogoUrl = ! empty($companyLogo) ? asset('storage/'.$companyLogo) : null;

        $transferTiers = json_decode(Setting::get('agent_transfer_fee_tiers', '[]'), true) ?: [];
        $withdrawTiers = json_decode(Setting::get('agent_withdraw_fee_tiers', '[]'), true) ?: [];

        $settings = [
            'profile' => [
                'company_name' => Setting::get('company_name', 'WarungPro'),
                'company_tagline' => Setting::get('company_tagline', 'Solusi Kasir & Manajemen Ritel Modern'),
                'company_address' => Setting::get('company_address', 'Jl. Sudirman No. 45, Jakarta Pusat'),
                'company_phone' => Setting::get('company_phone', '0812-3456-7890'),
                'company_email' => Setting::get('company_email', 'support@pospro.com'),
                'company_npwp' => Setting::get('company_npwp', '01.234.567.8-901.000'),
                'company_logo' => $companyLogo,
                'company_logo_url' => $companyLogoUrl,
            ],
            'business_type' => [
                'business_type' => Setting::get('business_type', 'retail'),
                'pos_allow_manual_price_edit' => Setting::get('pos_allow_manual_price_edit', '1') === '1',
                // FNB
                'fnb_enable_table_management' => Setting::get('fnb_enable_table_management', '1') === '1',
                'fnb_enable_kitchen_display' => Setting::get('fnb_enable_kitchen_display', '1') === '1',
                'fnb_enable_modifiers' => Setting::get('fnb_enable_modifiers', '1') === '1',
                'fnb_enable_reservation' => Setting::get('fnb_enable_reservation', '1') === '1',
                'fnb_default_service_type' => Setting::get('fnb_default_service_type', 'dine_in'),
                'fnb_service_charge_percent' => (float) Setting::get('fnb_service_charge_percent', '0'),
                'fnb_auto_print_kitchen_ticket' => Setting::get('fnb_auto_print_kitchen_ticket', '0') === '1',
                'fnb_enable_queue_number' => Setting::get('fnb_enable_queue_number', '1') === '1',
                // Service
                'service_enable_booking' => Setting::get('service_enable_booking', '1') === '1',
                'service_enable_technician_assignment' => Setting::get('service_enable_technician_assignment', '1') === '1',
                'service_enable_duration_tracking' => Setting::get('service_enable_duration_tracking', '1') === '1',
                'service_booking_slot_minutes' => (int) Setting::get('service_booking_slot_minutes', '30'),
                'service_auto_queue' => Setting::get('service_auto_queue', '1') === '1',
                'service_enable_material_usage' => Setting::get('service_enable_material_usage', '0') === '1',
            ],
            'prefixes' => [
                'prefix_invoice' => Setting::get('prefix_invoice', 'INV'),
                'prefix_po' => Setting::get('prefix_po', 'PO'),
                'prefix_grn' => Setting::get('prefix_grn', 'GRN'),
                'prefix_return_sale' => Setting::get('prefix_return_sale', 'SR'),
                'prefix_return_purchase' => Setting::get('prefix_return_purchase', 'PR'),
                'prefix_opname' => Setting::get('prefix_opname', 'SO'),
                'prefix_transfer' => Setting::get('prefix_transfer', 'TF'),
            ],
            'tax_currency' => [
                'default_tax_rate' => (float) Setting::get('default_tax_rate', '11'),
                'currency_symbol' => Setting::get('currency_symbol', 'Rp'),
                'currency_code' => Setting::get('currency_code', 'IDR'),
            ],
            'receipt' => [
                'receipt_header' => Setting::get('receipt_header', "Terima Kasih Telah Berbelanja!\nBarang yang sudah dibeli tidak dapat ditukar."),
                'receipt_footer' => Setting::get('receipt_footer', "Simpan struk ini sebagai bukti pembayaran yang sah.\nInstagram: @pospro.id"),
                'receipt_paper_size' => Setting::get('receipt_paper_size', '58mm'),
                'receipt_show_logo' => Setting::get('receipt_show_logo', '1') === '1',
            ],
            'agent' => [
                'agent_transfer_admin_fee' => (float) Setting::get('agent_transfer_admin_fee', '5000'),
                'agent_withdraw_admin_fee' => (float) Setting::get('agent_withdraw_admin_fee', '5000'),
                'transfer_tiers' => $transferTiers,
                'withdraw_tiers' => $withdrawTiers,
            ],
        ];

        return $this->sendResponse($settings, 'Pengaturan toko berhasil dimuat.');
    }

    /**
     * Update store profile settings.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_name' => ['required', 'string', 'max:150'],
            'company_tagline' => ['nullable', 'string', 'max:200'],
            'company_address' => ['nullable', 'string', 'max:300'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'company_npwp' => ['nullable', 'string', 'max:50'],
            'company_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

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

        return $this->getSettings();
    }

    /**
     * Update business type & hybrid options.
     */
    public function updateBusinessType(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_type' => ['required', 'in:retail,fnb,service,hybrid'],
            'pos_allow_manual_price_edit' => ['nullable', 'boolean'],
            'fnb_enable_table_management' => ['nullable', 'boolean'],
            'fnb_enable_kitchen_display' => ['nullable', 'boolean'],
            'fnb_enable_modifiers' => ['nullable', 'boolean'],
            'fnb_enable_reservation' => ['nullable', 'boolean'],
            'fnb_default_service_type' => ['nullable', 'in:dine_in,take_away'],
            'fnb_service_charge_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fnb_auto_print_kitchen_ticket' => ['nullable', 'boolean'],
            'fnb_enable_queue_number' => ['nullable', 'boolean'],
            'service_enable_booking' => ['nullable', 'boolean'],
            'service_enable_technician_assignment' => ['nullable', 'boolean'],
            'service_enable_duration_tracking' => ['nullable', 'boolean'],
            'service_booking_slot_minutes' => ['nullable', 'integer', 'in:15,30,45,60,90,120'],
            'service_auto_queue' => ['nullable', 'boolean'],
            'service_enable_material_usage' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        Setting::set('business_type', $validated['business_type'], 'business_type', 'string', 'Jenis Model Usaha');
        Setting::set('pos_allow_manual_price_edit', $request->boolean('pos_allow_manual_price_edit', true) ? '1' : '0', 'pos', 'boolean', 'Izinkan Edit Harga Manual di POS');

        // FNB
        Setting::set('fnb_enable_table_management', $request->boolean('fnb_enable_table_management', true) ? '1' : '0', 'fnb', 'boolean', 'Manajemen Meja');
        Setting::set('fnb_enable_kitchen_display', $request->boolean('fnb_enable_kitchen_display', true) ? '1' : '0', 'fnb', 'boolean', 'Kitchen Display System');
        Setting::set('fnb_enable_modifiers', $request->boolean('fnb_enable_modifiers', true) ? '1' : '0', 'fnb', 'boolean', 'Menu Modifiers / Topping');
        Setting::set('fnb_enable_reservation', $request->boolean('fnb_enable_reservation', true) ? '1' : '0', 'fnb', 'boolean', 'Reservasi Meja');
        Setting::set('fnb_default_service_type', $request->get('fnb_default_service_type', 'dine_in'), 'fnb', 'string', 'Default Tipe Layanan FNB');
        Setting::set('fnb_service_charge_percent', (string) $request->get('fnb_service_charge_percent', '0'), 'fnb', 'numeric', 'Service Charge (%)');
        Setting::set('fnb_auto_print_kitchen_ticket', $request->boolean('fnb_auto_print_kitchen_ticket', false) ? '1' : '0', 'fnb', 'boolean', 'Auto Print Tiket Dapur');
        Setting::set('fnb_enable_queue_number', $request->boolean('fnb_enable_queue_number', true) ? '1' : '0', 'fnb', 'boolean', 'Nomor Antrian Take Away');

        // Service
        Setting::set('service_enable_booking', $request->boolean('service_enable_booking', true) ? '1' : '0', 'service', 'boolean', 'Booking & Appointment Jasa');
        Setting::set('service_enable_technician_assignment', $request->boolean('service_enable_technician_assignment', true) ? '1' : '0', 'service', 'boolean', 'Assignment Teknisi / Terapis');
        Setting::set('service_enable_duration_tracking', $request->boolean('service_enable_duration_tracking', true) ? '1' : '0', 'service', 'boolean', 'Tracking Durasi Layanan');
        Setting::set('service_booking_slot_minutes', (string) $request->get('service_booking_slot_minutes', '30'), 'service', 'integer', 'Durasi Slot Booking (menit)');
        Setting::set('service_auto_queue', $request->boolean('service_auto_queue', true) ? '1' : '0', 'service', 'boolean', 'Antrian Layanan Otomatis');
        Setting::set('service_enable_material_usage', $request->boolean('service_enable_material_usage', false) ? '1' : '0', 'service', 'boolean', 'Tracking Pemakaian Bahan / Material');

        return $this->getSettings();
    }

    /**
     * Update transaction code prefixes.
     */
    public function updatePrefixes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prefix_invoice' => ['required', 'string', 'max:10'],
            'prefix_po' => ['required', 'string', 'max:10'],
            'prefix_grn' => ['required', 'string', 'max:10'],
            'prefix_return_sale' => ['required', 'string', 'max:10'],
            'prefix_return_purchase' => ['required', 'string', 'max:10'],
            'prefix_opname' => ['required', 'string', 'max:10'],
            'prefix_transfer' => ['required', 'string', 'max:10'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        foreach ($validated as $key => $val) {
            Setting::set($key, strtoupper($val), 'prefixes', 'string', "Prefix {$key}");
        }

        return $this->getSettings();
    }

    /**
     * Update tax and currency settings.
     */
    public function updateTaxCurrency(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_code' => ['required', 'string', 'max:10'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        Setting::set('default_tax_rate', (string) $validated['default_tax_rate'], 'tax', 'numeric', 'Default Pajak PPN (%)');
        Setting::set('currency_symbol', $validated['currency_symbol'], 'currency', 'string', 'Simbol Mata Uang');
        Setting::set('currency_code', strtoupper($validated['currency_code']), 'currency', 'string', 'Kode Mata Uang');

        return $this->getSettings();
    }

    /**
     * Update receipt template settings.
     */
    public function updateReceipt(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receipt_header' => ['nullable', 'string', 'max:500'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'receipt_paper_size' => ['required', 'in:58mm,80mm'],
            'receipt_show_logo' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        Setting::set('receipt_header', $validated['receipt_header'] ?? '', 'receipt', 'string', 'Header Struk');
        Setting::set('receipt_footer', $validated['receipt_footer'] ?? '', 'receipt', 'string', 'Footer Struk');
        Setting::set('receipt_paper_size', $validated['receipt_paper_size'], 'receipt', 'string', 'Ukuran Kertas Struk');
        Setting::set('receipt_show_logo', $request->boolean('receipt_show_logo', true) ? '1' : '0', 'receipt', 'boolean', 'Tampilkan Logo di Struk');

        return $this->getSettings();
    }

    /**
     * Update Agent & PPOB fee settings.
     */
    public function updateAgent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'agent_transfer_admin_fee' => ['required', 'numeric', 'min:0'],
            'agent_withdraw_admin_fee' => ['required', 'numeric', 'min:0'],
            'transfer_tiers' => ['nullable', 'array'],
            'transfer_tiers.*.min' => ['required', 'numeric', 'min:0'],
            'transfer_tiers.*.max' => ['required', 'numeric', 'min:0'],
            'transfer_tiers.*.fee' => ['required', 'numeric', 'min:0'],
            'withdraw_tiers' => ['nullable', 'array'],
            'withdraw_tiers.*.min' => ['required', 'numeric', 'min:0'],
            'withdraw_tiers.*.max' => ['required', 'numeric', 'min:0'],
            'withdraw_tiers.*.fee' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        Setting::set('agent_transfer_admin_fee', (string) $validated['agent_transfer_admin_fee'], 'agent', 'numeric', 'Biaya Admin Transfer Uang (Default)');
        Setting::set('agent_withdraw_admin_fee', (string) $validated['agent_withdraw_admin_fee'], 'agent', 'numeric', 'Biaya Admin Tarik Tunai (Default)');

        $transferTiers = collect($request->input('transfer_tiers', []))
            ->filter(fn ($item) => isset($item['min'], $item['max'], $item['fee']) && is_numeric($item['fee']))
            ->map(fn ($item) => [
                'min' => (float) $item['min'],
                'max' => (float) $item['max'],
                'fee' => (float) $item['fee'],
            ])
            ->sortBy('min')
            ->values()
            ->all();

        $withdrawTiers = collect($request->input('withdraw_tiers', []))
            ->filter(fn ($item) => isset($item['min'], $item['max'], $item['fee']) && is_numeric($item['fee']))
            ->map(fn ($item) => [
                'min' => (float) $item['min'],
                'max' => (float) $item['max'],
                'fee' => (float) $item['fee'],
            ])
            ->sortBy('min')
            ->values()
            ->all();

        Setting::set('agent_transfer_fee_tiers', json_encode($transferTiers), 'agent', 'json', 'Tingkatan Biaya Admin Transfer Berdasarkan Range');
        Setting::set('agent_withdraw_fee_tiers', json_encode($withdrawTiers), 'agent', 'json', 'Tingkatan Biaya Admin Tarik Tunai Berdasarkan Range');

        return $this->getSettings();
    }
}
