<?php

namespace Tests\Unit;

use App\Services\BillingService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BillingServicePercentageTest extends TestCase
{
    public function test_calculates_correct_percentage(): void
    {
        $service = new BillingService;
        $result = $service->calculateBillPercentage(250, 1000);
        $this->assertEquals(25.00, $result);
    }

    public function test_zero_total_throws_exception(): void
    {
        $this->expectException(ValidationException::class);
        $service = new BillingService;
        $service->calculateBillPercentage(100, 0);
    }

    public function test_non_numeric_inputs_throw_exception(): void
    {
        $service = new BillingService;
        $this->expectException(ValidationException::class);
        $service->calculateBillPercentage('abc', 100);
    }

    public function test_negative_values_are_calculated(): void
    {
        $service = new BillingService;
        $this->assertEquals(-10.00, $service->calculateBillPercentage(-100, 1000));
        $this->assertEquals(-10.00, $service->calculateBillPercentage(100, -1000));
        $this->assertEquals(10.00, $service->calculateBillPercentage(-100, -1000));
    }
}
