<?php

namespace Tests\Feature\Dashboard;

use App\Http\Controllers\DashboradController;
use App\Models\Bill;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_summary_calculations_match_backend_logic(): void
    {
        $q1 = Quotation::factory()->create();
        $q2 = Quotation::factory()->create();

        Bill::factory()->regular()->create([
            'quotation_id' => $q1->id,
            'invoice_no' => 'INV-Q1-OLD',
            'bill_date' => '2025-01-10',
            'total_amount' => 100.00,
            'due' => 20.00,
        ]);

        Bill::factory()->regular()->create([
            'quotation_id' => $q1->id,
            'invoice_no' => 'INV-Q1-NEW',
            'bill_date' => '2025-02-15',
            'total_amount' => 150.00,
            'due' => 30.00,
        ]);

        Bill::factory()->advance()->create([
            'quotation_id' => $q2->id,
            'invoice_no' => 'INV-Q2-ONLY',
            'bill_date' => '2025-03-05',
            'total_amount' => 200.00,
            'due' => 50.00,
        ]);

        $controller = new DashboradController;
        $summary = $controller->getFinancialSummary();

        $this->assertSame(3, $summary['total_bills']);
        $this->assertEquals(450.00, $summary['total_amount']);
        $this->assertEquals(100.00, $summary['total_due']);
        $this->assertEquals(350.00, $summary['total_amount_unique_by_quotation']);
        $this->assertEquals(80.00, $summary['total_due_unique_by_quotation']);
    }
}
