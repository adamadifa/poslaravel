<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Warehouse $warehouse;

    protected Unit $unit;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Toko Utama',
            'code' => 'WH01',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Pcs',
            'short_name' => 'pcs',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);
    }

    public function test_can_search_products_via_api(): void
    {
        $product = Product::create([
            'name' => 'Kopi Susu Gula Aren',
            'code' => 'PRD-001',
            'barcode' => '8991234567890',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 8000,
            'selling_price' => 15000,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 50,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/pos/products?q=Kopi');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.0.name', 'Kopi Susu Gula Aren');
    }

    public function test_can_calculate_cart_and_checkout_via_api(): void
    {
        $product = Product::create([
            'name' => 'Teh Botol Kotak',
            'code' => 'PRD-002',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 3000,
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $customer = Customer::create([
            'code' => 'CUST-0001',
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        // 1. Calculate Cart
        $calcResponse = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/pos/calculate-cart', [
                'customer_id' => $customer->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'unit_id' => $this->unit->id,
                        'quantity' => 2,
                        'price' => 5000,
                    ],
                ],
                'manual_discount' => 1000,
            ]);

        $calcResponse->assertStatus(200)
            ->assertJsonPath('data.subtotal', 10000)
            ->assertJsonPath('data.total_discount', 1000)
            ->assertJsonPath('data.grand_total', 9000);

        // 2. Checkout
        $checkoutResponse = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/pos/checkout', [
                'warehouse_id' => $this->warehouse->id,
                'customer_id' => $customer->id,
                'service_type' => 'takeaway',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'unit_id' => $this->unit->id,
                        'quantity' => 2,
                        'price' => 5000,
                    ],
                ],
                'paid_amount' => 10000,
                'payment_method' => 'cash',
                'manual_discount' => 1000,
            ]);

        $checkoutResponse->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertEquals(9000, (float) $checkoutResponse->json('data.grand_total'));
        $this->assertEquals(1000, (float) $checkoutResponse->json('data.change_amount'));
    }
}
