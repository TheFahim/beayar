<?php

namespace Tests\Feature\Quotations;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\Specification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationProductSpecificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function it_stores_product_specification_correctly()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $specification = Specification::factory()->create();

        $quotationData = [
            'quotation' => [
                'customer_id' => $customer->id,
                'quotation_no' => 'QT-TEST-001',
                'ship_to' => 'Test Address',
            ],
            'quotation_revision' => [
                'type' => 'normal',
                'date' => '01/01/2023',
                'validity' => '15/01/2023',
                'currency' => 'BDT',
                'exchange_rate' => 1,
                'subtotal' => 100,
                'total' => 100,
                'saved_as' => 'draft',
            ],
            'quotation_products' => [
                [
                    'product_id' => $product->id,
                    'specification_id' => $specification->id,
                    'quantity' => 1,
                    'unit_price' => 100,
                ]
            ]
        ];

        // Act
        $response = $this->post(route('quotations.store'), $quotationData);

        // Assert
        $response->assertRedirect(route('quotations.index'));

        $this->assertDatabaseHas('quotation_products', [
            'product_id' => $product->id,
            'specification_id' => $specification->id,
        ]);
    }

    /** @test */
    public function it_passes_specification_id_to_edit_view()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $specification = Specification::factory()->create();

        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $quotationProduct = QuotationProduct::create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'specification_id' => $specification->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        // Act
        $response = $this->get(route('quotations.edit', $quotation));

        // Assert
        $response->assertStatus(200);

        // We check if the configuration object in the view contains the specification_id
        // This confirms the backend is sending the data correctly
        $response->assertViewHas('loadRevision');

        // Check the view content for the specification_id in the JS config
        $response->assertSee('"specification_id":' . $specification->id, false);
    }

    /** @test */
    public function it_updates_product_specification_correctly()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();
        $specification1 = Specification::factory()->create();
        $specification2 = Specification::factory()->create();

        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        $quotationProduct = QuotationProduct::create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'specification_id' => $specification1->id, // Initial spec
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $updateData = [
            'quotation' => [
                'customer_id' => $customer->id,
                'quotation_no' => $quotation->quotation_no,
            ],
            'quotation_revision' => [
                'id' => $revision->id,
                'type' => 'normal',
                'date' => '01/01/2023',
                'validity' => '15/01/2023',
                'currency' => 'BDT',
                'exchange_rate' => 1,
                'subtotal' => 100,
                'total' => 100,
                'saved_as' => 'draft',
            ],
            'quotation_products' => [
                [
                    'id' => $quotationProduct->id,
                    'product_id' => $product->id,
                    'specification_id' => $specification2->id, // New spec
                    'quantity' => 1,
                    'unit_price' => 100,
                ]
            ]
        ];

        // Act
        $response = $this->put(route('quotations.update', $quotation), $updateData);

        // Assert
        $response->assertRedirect();

        $this->assertDatabaseHas('quotation_products', [
            'product_id' => $product->id,
            'specification_id' => $specification2->id,
        ]);
    }
}
