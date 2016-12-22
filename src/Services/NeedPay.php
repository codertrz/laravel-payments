<?php namespace Beansme\Payments\Services;

use Beansme\Payments\Models\Receipt;

trait NeedPay {

    public abstract function getAmount();

    /**
     * pay info
     */
    public abstract function isPaid();

    public abstract function setAsPaid($payment_id, $channel = '');

    /**
     * relations
     */
    /**
     * @return Receipt
     */
    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'order_no', $this->getKeyName());
    }


}
