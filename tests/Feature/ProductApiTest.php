<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_a_product_via_open_api(): void
    {
        $payload = [
            'name' => 'Wireless Gaming Mouse',
            'description' => 'High precision RGB wireless gaming mouse',
            'price' => 49.99,
            'stock' => 100,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Product created successfully',
                     'data' => [
                         'name' => 'Wireless Gaming Mouse',
                         'price' => '49.99',
                         'stock' => 100,
                     ]
                 ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Wireless Gaming Mouse',
        ]);
    }

    public function test_can_edit_a_product_via_open_api(): void
    {
        $product = Product::create([
            'name' => 'Old Keyboard',
            'description' => 'Mechanical Keyboard',
            'price' => 29.99,
            'stock' => 10,
        ]);

        $updatePayload = [
            'name' => 'Updated Mechanical Keyboard',
            'price' => 39.99,
            'stock' => 15,
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updatePayload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Product updated successfully',
                     'data' => [
                         'name' => 'Updated Mechanical Keyboard',
                         'price' => '39.99',
                         'stock' => 15,
                     ]
                 ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Mechanical Keyboard',
        ]);
    }

    public function test_can_delete_a_product_via_open_api(): void
    {
        $product = Product::create([
            'name' => 'Temporary Item',
            'description' => 'Item to be deleted',
            'price' => 9.99,
            'stock' => 5,
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Product deleted successfully',
                 ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_can_fetch_all_products_via_open_api(): void
    {
        Product::create([
            'name' => 'Product A',
            'price' => 10.00,
            'stock' => 5,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Products retrieved successfully',
                 ]);
    }
}
