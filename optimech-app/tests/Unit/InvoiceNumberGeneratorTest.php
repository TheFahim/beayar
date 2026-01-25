<?php

namespace Tests\Unit;

use App\Models\Bill;
use App\Models\Quotation;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new InvoiceNumberGenerator();
    }

    /** @test */
    public function it_generates_first_invoice_number_with_suffix_a_if_no_bills_exist()
    {
        $quotation = Quotation::factory()->create(['quotation_no' => 'Q-100']);

        $invoiceNo = $this->generator->generate($quotation);

        $this->assertEquals('Q-100A', $invoiceNo);
    }

    /** @test */
    public function it_generates_sequential_invoice_numbers()
    {
        $quotation = Quotation::factory()->create(['quotation_no' => 'Q-100']);

        // First bill
        Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'invoice_no' => 'Q-100A',
        ]);
        $this->assertEquals('Q-100B', $this->generator->generate($quotation));

        // Second bill
        Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'invoice_no' => 'Q-100B',
        ]);
        $this->assertEquals('Q-100C', $this->generator->generate($quotation));
    }

    /** @test */
    public function it_handles_legacy_base_number_correctly()
    {
        $quotation = Quotation::factory()->create(['quotation_no' => 'Q-100']);

        // Legacy bill without suffix
        Bill::factory()->create([
            'quotation_id' => $quotation->id,
            'invoice_no' => 'Q-100',
        ]);

        // Should start with A
        $this->assertEquals('Q-100A', $this->generator->generate($quotation));
    }

    /** @test */
    public function it_handles_z_to_za_transition()
    {
        $quotation = Quotation::factory()->create(['quotation_no' => 'Q-100']);

        // Create A-Z bills (indices 1 to 26)
        for ($i = 1; $i <= 26; $i++) {
             $suffix = $this->getSuffix($i);
             Bill::factory()->create([
                'quotation_id' => $quotation->id,
                'invoice_no' => 'Q-100' . $suffix,
            ]);
        }

        // Should generate ZA (index 27)
        $this->assertEquals('Q-100ZA', $this->generator->generate($quotation));
    }

    /** @test */
    public function it_handles_za_to_zb_transition()
    {
        $quotation = Quotation::factory()->create(['quotation_no' => 'Q-100']);

        // Create A-Z (1-26)
        for ($i = 1; $i <= 26; $i++) {
             $suffix = $this->getSuffix($i);
             Bill::factory()->create([
                'quotation_id' => $quotation->id,
                'invoice_no' => 'Q-100' . $suffix,
            ]);
        }
        // Create ZA (27)
        Bill::factory()->create(['quotation_id' => $quotation->id, 'invoice_no' => 'Q-100ZA']);

        // Next should be ZB
        $this->assertEquals('Q-100ZB', $this->generator->generate($quotation));
    }

    /** @test */
    public function it_skips_existing_invoice_numbers_if_gaps_exist()
    {
        $quotation = Quotation::factory()->create(['quotation_no' => 'Q-100']);

        Bill::factory()->create(['quotation_id' => $quotation->id, 'invoice_no' => 'Q-100A']);
        Bill::factory()->create(['quotation_id' => $quotation->id, 'invoice_no' => 'Q-100C']);

        // Should fill the gap B
        $this->assertEquals('Q-100B', $this->generator->generate($quotation));
    }

    private function getSuffix(int $n): string
    {
        if ($n === 0) {
            return '';
        }

        if ($n <= 26) {
            return chr(65 + $n - 1); // A is 65
        }

        return 'Z' . $this->getSuffix($n - 26);
    }
}
