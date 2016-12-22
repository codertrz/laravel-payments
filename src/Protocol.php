<?php namespace Beansme\Payments;
class Protocol {

    //支付状态
    const STATUS_PAY_UNPAID = 'unpaid';
    const STATUS_PAY_AUDIT = 'audit';
    const STATUS_PAY_PAID = 'paid';

    //退款状态
    const STATUS_REFUND_NONE = 'none';
    const STATUS_REFUND_APPLY = 'apply';
    const STATUS_REFUND_REFUNDING = 'refunding';
    const STATUS_REFUND_REFUNDED = 'refunded';


}
