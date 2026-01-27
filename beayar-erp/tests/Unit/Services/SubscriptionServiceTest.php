<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionUsage;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_check_limit_returns_false_if_no_subscription()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('subscription')->andReturn(null);

        $result = $this->service->checkLimit($user, 'quotations');

        $this->assertFalse($result);
    }

    public function test_check_limit_delegates_to_subscription_model()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);
        
        $user->shouldReceive('getAttribute')->with('subscription')->andReturn($subscription);
        $subscription->shouldReceive('checkLimit')->with('quotations')->once()->andReturn(true);

        $result = $this->service->checkLimit($user, 'quotations');

        $this->assertTrue($result);
    }

    public function test_record_usage_delegates_to_subscription_model()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);
        
        $user->shouldReceive('getAttribute')->with('subscription')->andReturn($subscription);
        $subscription->shouldReceive('recordUsage')->with('quotations', 1)->once();

        $this->service->recordUsage($user, 'quotations');
        
        // Mockery assertion handled by tearDown/mock expectation
        $this->assertTrue(true);
    }

    public function test_get_usage_returns_zero_if_no_subscription()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('subscription')->andReturn(null);

        $result = $this->service->getUsage($user, 'quotations');

        $this->assertEquals(0, $result);
    }
}
