<?php namespace Beansme\Payments\Test;

use Beansme\Payments\Services\PayFactory;
use Beansme\Payments\Services\Receipts\ReceiptService;

class PayFactoryTest extends TestCase {

    /** @test */
    public function it_can_load_payments_config()
    {
        $config = config('payments');

        $this->assertArrayHasKey('default', $config);
        $this->assertArrayHasKey($config['default'], $config['gateways']);
    }

    /** @test */
    public function it_can_init_a_pingxx_pay_service()
    {
        $factory = $this->app->make(PayFactory::class);
        $gateway = $factory->init('pingxx');

        $this->assertTrue($gateway instanceof ReceiptService);
    }

    /** @test */
    public function it_can_init_a_pingxx_pay_service_by_facade()
    {
        $gateway = \Pay::init();
        $this->assertTrue($gateway instanceof ReceiptService);
    }

}
