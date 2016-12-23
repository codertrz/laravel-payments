<?php namespace Beansme\Payments\Services\Gateways;

use Beansme\Payments\Models\Receipt;
use Beansme\Payments\Protocol;
use Beansme\Payments\Services\Contracts\CanRefund;
use Beansme\Payments\Services\Contracts\NeedPay;
use Hafael\LaraFlake\LaraFlake;
use Pingpp\Charge;
use Pingpp\Pingpp;
use Pingpp\Refund;

class PingxxGateway extends GatewayAbstract {

    public function __construct()
    {
        parent::__construct();
        Pingpp::setApiKey(self::$config['api_key']);
    }

    public function configName()
    {
        return 'pingxx';
    }

    /**
     * @param NeedPay $order
     * @param null $channel
     * @return Charge
     */
    public function purchase(NeedPay $order, $channel = null)
    {
        $charge = Charge::create([
            "amount" => $order->getPaymentAmount(),
            "channel" => $channel,
            "order_no" => LaraFlake::generateID(),
            "currency" => Protocol::CURRENCY_OF_CNY,
            "client_ip" => \Request::ip(),
            "app" => ["id" => self::$config['app_id']],
            "subject" => $order->getPaymentSubject(),
            "body" => $order->getPaymentBody(),
            "extra" => self::getExtraData($channel, $order),
        ]);

        return $charge;
    }

    /**
     * @param $payment_id
     * @return Charge
     */
    public function fetchTransaction($payment_id)
    {
        return Charge::retrieve($payment_id);
    }

    public function transfer()
    {
        // TODO: Implement transfer() method.
    }

    /**
     * @param CanRefund $payment
     * @param $desc
     * @param null $amount
     * @return Refund
     */
    public function refund(CanRefund $payment, $desc, $amount = null)
    {
        $charge = $this->fetchTransaction($payment->getRefundNo());
        return $charge->refunds->create([
            'amount' => $amount ?: $payment->getRefundAmount(),
            'description' => $desc,
        ]);
    }

    public function fetchTransactionLists($parameters)
    {
        return Charge::all($parameters);
    }

    /**
     * @param CanRefund $payment
     * @param null $refund_id
     * @return Refund
     */
    public function fetchRefund(CanRefund $payment, $refund_id = null)
    {
        $charge = $this->fetchTransaction($payment->getRefundNo());
        if (!is_null($refund_id)) {
            if($refund_id instanceof Refund) {
                return $refund_id;
            }
            return $charge->refunds->retrieve($refund_id);
        }

        return $charge->refunds->all(['limit' => 1]);
    }

    protected static function getExtraData($channel, NeedPay $order)
    {
        $mobile_success = self::$config['mobile_success'];
        $mobile_cancel = self::$config['mobile_cancel'];
        $pc_success = self::$config['pc_success'];
        $pc_cancel = self::$config['pc_cancel'];

        switch ($channel) {
            case 'alipay_wap':
                $extra = [
                    'success_url' => $mobile_success,
                    'cancel_url' => $mobile_cancel
                ];
                break;
            case 'upmp_wap':
                $extra = [
                    'result_url' => $mobile_success
                ];
                break;
            case 'bfb_wap':
                $extra = [
                    'result_url' => $mobile_success,
                    'bfb_login' => true
                ];
                break;
            case 'upacp_wap':
                $extra = [
                    'result_url' => $mobile_success
                ];
                break;
            case 'upacp_pc':
                $extra = [
                    'result_url' => $pc_success
                ];
                break;
            case 'wx_pub':
                $extra = [
                    'open_id' => $order->getPaymentPayerID(true)
                ];
                break;
            case 'wx_pub_qr':
                $extra = [
                    'product_id' => $order->getPaymentOrderNo()
                ];
                break;
            case 'yeepay_wap':
                $extra = [
                    'product_category' => '1',
                    'identity_id' => 'your identity_id',
                    'identity_type' => 1,
                    'terminal_type' => 1,
                    'terminal_id' => 'your terminal_id',
                    'user_ua' => 'your user_ua',
                    'result_url' => $mobile_success
                ];
                break;
            case 'jdpay_wap':
                $extra = [
                    'success_url' => $mobile_success,
                    'fail_url' => 'http://www.yourdomain.com',
                    'token' => 'dsafadsfasdfadsjuyhfnhujkijunhaf'
                ];
                break;
            case 'alipay_pc_direct':
                $extra = [
                    'success_url' => $pc_success,
                ];
                break;
            default:
                $extra = [];
                break;
        }

        return $extra;
    }


    public function persistTransaction($charge, Receipt $receipt)
    {
        // TODO: Implement persistTransaction() method.
    }

    public function persistRefund()
    {
        // TODO: Implement persistRefund() method.
    }
}
