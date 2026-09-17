<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Warehouse $warehouse;

    protected Product $product;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->adminUser = User::where('email', 'admin@pospro.com')->first();

        $this->warehouse = Warehouse::create([
            'code' => 'GUD-01',
            'name' => 'Toko Utama',
            'is_active' => true,
        ]);

        $unit = Unit::create(['name' => 'Pcs', 'short_name' => 'pcs', 'is_active' => true]);
        $category = Category::create(['name' => 'Minuman', 'is_active' => true]);

        $this->product = Product::create([
            'code' => 'PRD-001',
            'name' => 'Kopi Latte',
            'barcode' => '8991234567890',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 15000,
            'selling_price' => 25000,
            'min_stock' => 5,
            'track_stock' => true,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 20,
        ]);

        $this->customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'John Doe',
            'phone' => '08123456789',
            'is_active' => true,
        ]);
    }

    public function test_can_export_sales_report_to_excel_xlsx(): void
    {
        $sale = Sale::create([
            'invoice_number' => 'INV-202609-0001',
            'sale_date' => now(),
            'user_id' => $this->adminUser->id,
            'warehouse_id' => $this->warehouse->id,
            'customer_id' => $this->customer->id,
            'subtotal' => 50000,
            'discount_amount' => 5000,
            'tax_amount' => 0,
            'grand_total' => 45000,
            'paid_amount' => 45000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'unit_id' => $this->product->base_unit_id,
            'quantity' => 2,
            'unit_price' => 25000,
            'unit_cost' => 15000,
            'discount_amount' => 5000,
            'tax_amount' => 0,
            'subtotal' => 45000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.sales.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_can_export_purchases_report_to_excel_xlsx(): void
    {
        $supplier = Supplier::create([
            'code' => 'SUP-01',
            'name' => 'Supplier Biji Kopi',
            'is_active' => true,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO-202609-0001',
            'order_date' => now(),
            'expected_date' => now()->addDays(3),
            'supplier_id' => $supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'user_id' => $this->adminUser->id,
            'status' => 'sent',
            'subtotal' => 150000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_cost' => 15000,
            'grand_total' => 165000,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.purchases.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_can_export_stocks_report_to_excel_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.stocks.export-excel', [
                'warehouse_id' => $this->warehouse->id,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_can_preview_and_stream_sales_pdf_in_browser(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.sales.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_preview_and_stream_purchases_pdf_in_browser(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.purchases.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_preview_and_stream_stocks_pdf_in_browser(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.stocks.export-pdf', [
                'warehouse_id' => $this->warehouse->id,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_sales_by_product_report_to_excel_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.sales.products.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_can_export_sales_by_category_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.sales.categories.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.sales.categories.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_sales_by_customer_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.sales.customers.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.sales.customers.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_stock_opnames_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.stock-opnames.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.stock-opnames.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_payables_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.payables.export-excel'));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.payables.export-pdf'));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_receivables_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.receivables.export-excel'));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.receivables.export-pdf'));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_cash_flows_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.cash-flows.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.cash-flows.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_profit_loss_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.profit-loss.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.profit-loss.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_cashier_shifts_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.cashier-shifts.export-excel', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('reports.cashier-shifts.export-pdf', [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_stock_movements_excel_and_pdf(): void
    {
        $excelResponse = $this->actingAs($this->adminUser)
            ->get(route('stocks.movements.export-excel', [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
            ]));
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $pdfResponse = $this->actingAs($this->adminUser)
            ->get(route('stocks.movements.export-pdf', [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
            ]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }
}
