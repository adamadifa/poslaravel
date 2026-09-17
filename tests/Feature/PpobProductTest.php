<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\PpobProduct;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PpobProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Account $ppobAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->user = User::where('email', 'admin@pospro.com')->first();

        $this->ppobAccount = Account::create([
            'account_code' => 'PPOB-DIGI',
            'name' => 'Deposit Digiflazz',
            'type' => 'ppob_provider',
            'opening_balance' => 1000000,
            'current_balance' => 1000000,
            'is_active' => true,
        ]);
    }

    public function test_can_view_ppob_products_index_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('ppob-products.index'));
        $response->assertOk();
        $response->assertSee('Katalog Produk PPOB');
    }

    public function test_can_create_new_ppob_product(): void
    {
        $response = $this->actingAs($this->user)->post(route('ppob-products.store'), [
            'name' => 'Telkomsel 25.000',
            'category' => 'pulsa',
            'provider' => 'Telkomsel',
            'code' => 'TSEL25_TEST',
            'cost_price' => 24800,
            'selling_price' => 27000,
            'default_account_id' => $this->ppobAccount->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('ppob-products.index'));
        $this->assertDatabaseHas('ppob_products', [
            'code' => 'TSEL25_TEST',
            'name' => 'Telkomsel 25.000',
            'cost_price' => 24800,
            'selling_price' => 27000,
        ]);
    }

    public function test_can_update_ppob_product(): void
    {
        $product = PpobProduct::create([
            'name' => 'Token PLN 20.000',
            'category' => 'token_pln',
            'provider' => 'PLN',
            'code' => 'PLN20_TEST',
            'cost_price' => 20100,
            'selling_price' => 22500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->put(route('ppob-products.update', $product), [
            'name' => 'Token PLN 20.000 (Promo)',
            'category' => 'token_pln',
            'provider' => 'PLN',
            'code' => 'PLN20_TEST',
            'cost_price' => 20000,
            'selling_price' => 22000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('ppob-products.index'));
        $this->assertDatabaseHas('ppob_products', [
            'id' => $product->id,
            'name' => 'Token PLN 20.000 (Promo)',
            'cost_price' => 20000,
            'selling_price' => 22000,
        ]);
    }

    public function test_can_delete_ppob_product(): void
    {
        $product = PpobProduct::create([
            'name' => 'Hapus Saya 10k',
            'category' => 'pulsa',
            'code' => 'DEL10',
            'cost_price' => 10000,
            'selling_price' => 12000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->delete(route('ppob-products.destroy', $product));
        $response->assertRedirect(route('ppob-products.index'));
        $this->assertDatabaseMissing('ppob_products', [
            'id' => $product->id,
        ]);
    }

    public function test_api_returns_active_products_for_pos(): void
    {
        PpobProduct::create([
            'name' => 'Pulsa Tsel 10k',
            'category' => 'pulsa',
            'provider' => 'Telkomsel',
            'code' => 'T10',
            'cost_price' => 10200,
            'selling_price' => 12500,
            'is_active' => true,
        ]);

        PpobProduct::create([
            'name' => 'Produk Nonaktif',
            'category' => 'pulsa',
            'code' => 'INACT',
            'cost_price' => 10000,
            'selling_price' => 12000,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('api.ppob-products'));
        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Pulsa Tsel 10k']);
        $response->assertJsonMissing(['name' => 'Produk Nonaktif']);
    }
}
