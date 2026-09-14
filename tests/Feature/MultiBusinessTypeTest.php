<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\ModifierGroup;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiBusinessTypeTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Warehouse $warehouse;

    protected Unit $unit;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Super Admin role and user
        Role::create(['name' => 'super_admin']);
        $this->adminUser = User::factory()->create(['email' => 'admin@pos.test']);
        $this->adminUser->assignRole('super_admin');

        $this->warehouse = Warehouse::create([
            'code' => 'WH01',
            'name' => 'Outlet Pusat',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'name' => 'Porsi',
            'short_name' => 'prs',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Makanan & Minuman',
            'is_active' => true,
        ]);
    }

    public function test_can_update_business_type_settings(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('settings.business-type'), [
                'business_type' => 'hybrid',
                'fnb_enable_table_management' => '1',
                'fnb_enable_kitchen_display' => '1',
                'fnb_enable_modifiers' => '1',
                'fnb_service_charge_percent' => '5',
                'service_enable_booking' => '1',
                'service_booking_slot_minutes' => '30',
            ]);

        $response->assertRedirect(route('settings.index', ['tab' => 'business_type']));
        $this->assertEquals('hybrid', Setting::get('business_type'));
        $this->assertEquals('5', Setting::get('fnb_service_charge_percent'));
    }

    public function test_dining_table_crud_and_status(): void
    {
        // 1. Create table
        $response = $this->actingAs($this->adminUser)
            ->post(route('tables.store'), [
                'warehouse_id' => $this->warehouse->id,
                'table_number' => 'M-01',
                'area' => 'Indoor',
                'capacity' => 4,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dining_tables', [
            'table_number' => 'M-01',
            'status' => 'available',
        ]);

        $table = DiningTable::where('table_number', 'M-01')->first();

        // 2. Change status
        $this->actingAs($this->adminUser)
            ->post(route('tables.status', $table->id), ['status' => 'occupied']);

        $this->assertEquals('occupied', $table->fresh()->status);

        // 3. Update table
        $this->actingAs($this->adminUser)
            ->put(route('tables.update', $table->id), [
                'warehouse_id' => $this->warehouse->id,
                'table_number' => 'M-01-VIP',
                'area' => 'VIP Room',
                'capacity' => 6,
                'status' => 'available',
            ]);

        $this->assertEquals('M-01-VIP', $table->fresh()->table_number);
    }

    public function test_table_reservation_workflow(): void
    {
        $table = DiningTable::create([
            'warehouse_id' => $this->warehouse->id,
            'table_number' => 'T-05',
            'area' => 'Outdoor',
            'capacity' => 4,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('tables.reservations.store'), [
                'warehouse_id' => $this->warehouse->id,
                'dining_table_id' => $table->id,
                'guest_name' => 'Bpk. Ahmad',
                'guest_phone' => '08123456789',
                'guest_count' => 4,
                'reservation_date' => today()->toDateString(),
                'reservation_time' => '19:00',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('table_reservations', [
            'guest_name' => 'Bpk. Ahmad',
            'status' => 'confirmed',
        ]);
    }

    public function test_fnb_modifiers_and_product_attachment(): void
    {
        // 1. Create modifier group
        $group = ModifierGroup::create([
            'name' => 'Level Pedas',
            'selection_type' => 'single',
            'is_required' => true,
        ]);

        // 2. Add modifier options
        $this->actingAs($this->adminUser)
            ->post(route('modifiers.store'), [
                'modifier_group_id' => $group->id,
                'name' => 'Level 1 (Sedang)',
                'price_adjustment' => 0,
            ]);

        $this->actingAs($this->adminUser)
            ->post(route('modifiers.store'), [
                'modifier_group_id' => $group->id,
                'name' => 'Level 3 (Ekstra Pedas)',
                'price_adjustment' => 3000,
            ]);

        $this->assertDatabaseHas('modifiers', ['name' => 'Level 3 (Ekstra Pedas)', 'price_adjustment' => 3000]);

        // 3. Attach to food product
        $product = Product::create([
            'name' => 'Ayam Geprek Sambal Korek',
            'code' => 'MENU-01',
            'product_type' => 'food',
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 10000,
            'selling_price' => 20000,
        ]);

        $this->actingAs($this->adminUser)
            ->post(route('modifiers.groups.sync-products', $group->id), [
                'product_ids' => [$product->id],
            ]);

        $this->assertTrue($product->modifierGroups->contains($group));
    }

    public function test_service_staff_and_booking_workflow(): void
    {
        $techUser = User::factory()->create(['name' => 'Rudi Barber']);

        $serviceProduct = Product::create([
            'name' => 'Gentlemen Haircut & Wash',
            'code' => 'SRV-01',
            'product_type' => 'service',
            'duration_minutes' => 45,
            'is_bookable' => true,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 0,
            'selling_price' => 50000,
        ]);

        // Assign staff with 20% commission
        $this->actingAs($this->adminUser)
            ->post(route('service-staff.store'), [
                'user_id' => $techUser->id,
                'product_id' => $serviceProduct->id,
                'commission_type' => 'percent',
                'commission_value' => 20,
            ]);

        $this->assertDatabaseHas('service_staff', [
            'user_id' => $techUser->id,
            'product_id' => $serviceProduct->id,
            'commission_value' => 20,
        ]);

        // Create booking
        $this->actingAs($this->adminUser)
            ->post(route('service-bookings.store'), [
                'warehouse_id' => $this->warehouse->id,
                'guest_name' => 'Denny',
                'guest_phone' => '085551234',
                'booking_date' => today()->toDateString(),
                'booking_time' => '14:00',
                'items' => [
                    [
                        'product_id' => $serviceProduct->id,
                        'staff_user_id' => $techUser->id,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('service_bookings', ['guest_name' => 'Denny']);
    }

    public function test_order_service_queue_number_and_kds_flow(): void
    {
        $orderService = app(OrderService::class);

        $q1 = $orderService->generateQueueNumber($this->warehouse->id, 'A');
        $q2 = $orderService->generateQueueNumber($this->warehouse->id, 'A');

        $this->assertEquals('A-001', $q1);
        $this->assertEquals('A-002', $q2);
    }

    public function test_pos_checkout_with_table_service_type_and_item_modifiers_and_notes(): void
    {
        // 1. Setup table
        $table = DiningTable::create([
            'warehouse_id' => $this->warehouse->id,
            'table_number' => 'T-05',
            'area' => 'VIP Room',
            'capacity' => 6,
            'status' => 'available',
            'is_active' => true,
        ]);

        // 2. Setup menu product and modifier
        $beverage = Product::create([
            'name' => 'Signature Aren Latte',
            'code' => 'MENU-AREN-01',
            'product_type' => 'beverage',
            'category_id' => $this->category->id,
            'base_unit_id' => $this->unit->id,
            'purchase_price' => 8000,
            'selling_price' => 25000,
            'is_active' => true,
        ]);

        $modGroup = ModifierGroup::create([
            'name' => 'Extra Topping',
            'selection_type' => 'multiple',
            'is_required' => false,
            'is_active' => true,
        ]);

        $topping = $modGroup->modifiers()->create([
            'name' => 'Grass Jelly',
            'price_adjustment' => 4000,
            'is_active' => true,
        ]);

        // 3. Perform POS checkout
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('pos.checkout'), [
                'warehouse_id' => $this->warehouse->id,
                'service_type' => 'dine_in',
                'dining_table_id' => $table->id,
                'guest_count' => 4,
                'payment_method' => 'cash',
                'paid_amount' => 58000,
                'notes' => 'Tamu VIP meja 5',
                'items' => [
                    [
                        'product_id' => $beverage->id,
                        'unit_id' => $this->unit->id,
                        'quantity' => 2,
                        'price' => 29000, // 25000 + 4000
                        'notes' => 'Less ice, sedotan kertas',
                        'modifiers' => [
                            [
                                'id' => $topping->id,
                                'name' => $topping->name,
                                'price_adjustment' => 4000,
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        // 4. Verify Sale database records
        $this->assertDatabaseHas('sales', [
            'service_type' => 'dine_in',
            'dining_table_id' => $table->id,
            'guest_count' => 4,
            'notes' => 'Tamu VIP meja 5',
        ]);

        $this->assertDatabaseHas('sale_items', [
            'product_id' => $beverage->id,
            'quantity' => 2,
            'unit_price' => 29000,
            'notes' => 'Less ice, sedotan kertas',
        ]);

        $this->assertDatabaseHas('sale_item_modifiers', [
            'modifier_id' => $topping->id,
            'modifier_name' => 'Grass Jelly',
            'price_adjustment' => 4000,
        ]);
    }
}
