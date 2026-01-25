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

class QuotationColumnSpanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function it_calculates_rowspan_correctly_for_interleaved_specifications()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $specA = Specification::factory()->create(['description' => 'Spec A']);
        $specB = Specification::factory()->create(['description' => 'Spec B']);
        $product = Product::factory()->create();

        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        // Create 4 products: A, A, B, A
        $qp1 = QuotationProduct::create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'specification_id' => $specA->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $qp2 = QuotationProduct::create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'specification_id' => $specA->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $qp3 = QuotationProduct::create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'specification_id' => $specB->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $qp4 = QuotationProduct::create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'specification_id' => $specA->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        // Act
        $response = $this->get(route('quotations.show', $quotation));

        // Assert
        $response->assertStatus(200);
        $content = $response->getContent();

        // We need to check the HTML structure.
        // Row 1 (Spec A): Should have rowspan="2"
        // Row 2 (Spec A): Should NOT have specification td
        // Row 3 (Spec B): Should have rowspan="1"
        // Row 4 (Spec A): Should have rowspan="1"

        // Since it's hard to parse exact table rows with regex, we can look for the pattern of rowspans.
        // Or we can use a DOM crawler if available, but regex is usually enough for simple checks.
        
        // Find the specification cells
        // Pattern: <td ... rowspan="N">...Spec Description...</td>
        
        // Let's verify presence of rowspan="2" for Spec A
        // And ensure we don't see rowspan="3" which was the bug (total count of A is 3)
        
        $this->assertStringContainsString('rowspan="2"', $content);
        $this->assertStringNotContainsString('rowspan="3"', $content);
        
        // More precise check using simple string matching logic on the rendered content
        // We expect Spec A to appear twice in the source code as rendered text (once for first group, once for second group)
        // Wait, Spec A description appears inside the <td>.
        // First group: <td ... rowspan="2">...Spec A...</td>
        // Second group: <td ... rowspan="1">...Spec A...</td>
        
        $specACount = substr_count($content, 'Spec A');
        // Note: Spec A might appear elsewhere? No, created unique description.
        // However, if we skip the cell, the description is NOT rendered.
        // So Spec A should appear exactly 2 times in the HTML.
        $this->assertEquals(2, $specACount, "Spec A should appear exactly twice (once for first group, once for last item).");
        
        $specBCount = substr_count($content, 'Spec B');
        $this->assertEquals(1, $specBCount, "Spec B should appear exactly once.");
    }
    
    /** @test */
    public function it_calculates_rowspan_correctly_for_single_items()
    {
         // Arrange
        $customer = Customer::factory()->create();
        $specA = Specification::factory()->create(['description' => 'Spec A']);
        $product = Product::factory()->create();

        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
        ]);

        // Create 1 product
        QuotationProduct::create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'specification_id' => $specA->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);
        
        // Act
        $response = $this->get(route('quotations.show', $quotation));
        
        // Assert
        $response->assertStatus(200);
        $content = $response->getContent();
        
        $this->assertStringContainsString('rowspan="1"', $content);
        $this->assertEquals(1, substr_count($content, 'Spec A'));
    }
}
