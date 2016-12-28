<?php namespace Beansme\Payments\Services\Receipts;

use Beansme\Payments\Models\Payment;
use Beansme\Payments\Services\Gateways\ThirdPartyGatewayContract;

class ThirdPartyReceiptService extends ReceiptServiceAbstract {

    public function __construct(ThirdPartyGatewayContract $gateway)
    {
        parent::__construct($gateway);
    }

    protected function getPaymentType()
    {
        return Payment::class;
    }
}
