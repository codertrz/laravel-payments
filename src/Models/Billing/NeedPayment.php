<?php

trait NeedPayment {

    /**
     * payment info
     */
    public abstract function getBillingNo();

    public abstract function getAmount();

    /**
     * relations
     */
    public function payment()
    {

    }

    /**
     * pay info
     */
    public abstract function isPaid();

    public abstract function setAsPaid($payment_id, $channel = '');


}
