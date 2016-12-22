<?php namespace Beansme\Payments\Services;

trait NeedPay {

    /**
     * payment info
     */
    public abstract function getBillingNo();

    public abstract function getAmount();

    /**
     * relations
     */
    public function receipt()
    {

    }

    /**
     * pay info
     */
    public abstract function isPaid();

    public abstract function setAsPaid($payment_id, $channel = '');


}
