<?php namespace Beansme\Payments\Services\Contracts;

use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Protocol;

trait NeedPay {

    public abstract function getPaymentAmount();

    public function getCurrency()
    {
        return Protocol::CURRENCY_OF_CNY;
    }

    public abstract function getPaymentPayerID($openid = false);

    public function getPaymentOrderNo()
    {
        return $this->getKey();
    }

    public function getPaymentSubject()
    {
        return $this->getPaymentOrderNo();
    }

    public function getPaymentBody()
    {
        return $this->getPaymentOrderNo();
    }

    /**
     * pay info
     */
    public abstract function isPaid();

    public abstract function setAsPaid($payment_id, $channel = '');

    /**
     * relations
     */
    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'order_no', $this->getKeyName());
    }


}
