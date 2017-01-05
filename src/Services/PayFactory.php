<?php namespace BTWay\Payments\Services;

use BTWay\Payments\Protocol;
use BTWay\Payments\Repositories\Receipts\EloquentReceiptRepo;
use BTWay\Payments\Services\Gateways\PingxxGateway;
use BTWay\Payments\Services\Receipts\ReceiptService;
use BTWay\Payments\Services\Receipts\ReceiptServiceContract;

class PayFactory {

    protected $gateways;

    public function __construct()
    {
        $this->initConfig();
    }

    /**
     * @param null $gateway_name
     * @return ReceiptServiceContract
     * @throws \Exception
     */
    public function init($gateway_name = null)
    {
        $gateway_name = $gateway_name ?: config('payments.default');

        if (array_key_exists($gateway_name, $this->gateways)) {
            return call_user_func($this->gateways[$gateway_name]);
        }

        throw new \Exception('gateway ' . $gateway_name . ' not exits');
    }

    public function extend($gateway, callable $initFunction)
    {
        $this->gateways[$gateway] = call_user_func($initFunction);
    }

    protected function initConfig()
    {
        $this->gateways[Protocol::PAY_GATEWAY_OF_PINGXX] = function () {
            return new ReceiptService(new PingxxGateway(), new EloquentReceiptRepo);
        };
    }

}
