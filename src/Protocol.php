<?php namespace BTWay\Payments;

use BTWay\Payments\Models\Payment;

class Protocol {

    //支付用户信息标识
    const PAYER_ID_USER_ID = 'system';
    const PAYER_ID_OPEN_ID = 'openid';


    //支付状态
    const STATUS_PAY_UNPAID = 'unpaid';
    const STATUS_PAY_AUDIT = 'audit';
    const STATUS_PAY_PAID = 'paid';

    //退款状态
    const STATUS_REFUND_NONE = 'none';
    const STATUS_REFUND_APPLY = 'apply';
    const STATUS_REFUND_REFUNDING = 'pending';
    const STATUS_REFUND_REFUNDED = 'succeeded';
    const STATUS_REFUND_FAIL = 'fail';

    //结算
    const STATUS_INVOICE_NONE = 'none';
    const STATUS_INVOICE_SUCCEED = 'succeed';

    //currency
    const CURRENCY_OF_CNY = 'cny';

    //gateway
    const PAY_GATEWAY_OF_PINGXX = 'pingxx';


    public static function display_price($price)
    {
        return bcdiv($price, 100, 2);
    }

    public static function generateId()
    {
        return mt_rand(1, 9) . substr(date('Y'), -2) . date('md') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', mt_rand(0, 99));
    }



    public static function getUserOpenId($user_id)
    {
        return $user_id;
    }


}
