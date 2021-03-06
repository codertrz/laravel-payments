<?php namespace BTWay\Payments\Services\Gateways;

use BTWay\Payments\Events\Payments\PaymentRefundApply;
use BTWay\Payments\Models\Payment;
use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Models\RefundPayment;
use BTWay\Payments\Models\RefundReceipt;
use BTWay\Payments\Protocol;
use BTWay\Payments\Services\Contracts\CanRefund;
use BTWay\Payments\Services\Gateways\Exceptions\ChargeNotPayException;
use Illuminate\Database\Eloquent\Model;
use Pingpp\Charge;
use Pingpp\Error\Base;
use Pingpp\Pingpp;
use Pingpp\Refund;

class PingxxGateway extends GatewayAbstract implements GatewayNotifyHandler {

    const PINGXX_APP_CHANNEL_ALIPAY = 'alipay';
    const PINGXX_APP_CHANNEL_WECHAT = 'wx';
    const PINGXX_APP_CHANNEL_UNIONPAY_NEW = 'upacp';
    const PINGXX_APP_CHANNEL_UNIONPAY_OLD = 'upmp';
    const PINGXX_APP_CHANNEL_BAIDU = 'bfb';
    const PINGXX_APP_CHANNEL_APPLE_PAY = 'apple_pay';

    const PINGXX_WAP_CHANNEL_ALIPAY = 'alipay_wap';
    const PINGXX_WAP_CHANNEL_WECHAT = 'wx_pub';
    const PINGXX_WAP_CHANNEL_UNIONPAY_NEW = 'upacp_wap';
    const PINGXX_WAP_CHANNEL_UNIONPAY_OLD = 'upmp_wap';
    const PINGXX_WAP_CHANNEL_BAIDU = 'bfb_wap';
    const PINGXX_WAP_CHANNEL_YEEPAY = 'yeepay_wap';
    const PINGXX_WAP_CHANNEL_JINGDONG = 'jdpay_wap';


    const PINGXX_PC_CHANNEL_ALIPAY = 'alipay_pc_direct';
    const PINGXX_PC_CHANNEL_UNIONPAY = 'upacp_pc';

    const PINGXX_SPECIAL_CHANNEL_ALIPAY_QR = 'alipay_qr';
    const PINGXX_SPECIAL_CHANNEL_WECHAT_QR = 'wx_pub_qr';

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
     * @param Receipt $receipt
     * @param null $channel
     * @return Charge
     */
    public function purchase(Receipt $receipt, $channel = null)
    {
        $charge = Charge::create([
            "amount" => $receipt->getPaymentAmount(),
            "channel" => $channel ?: self::PINGXX_WAP_CHANNEL_WECHAT,
            "order_no" => Protocol::generateId(),
            "currency" => Protocol::CURRENCY_OF_CNY,
            "client_ip" => \Request::ip(),
            "app" => ["id" => self::$config['app_id']],
            "subject" => $receipt->getPaymentSubject(),
            "body" => $receipt->getPaymentBody(),
            "extra" => self::getExtraData($channel, $receipt),
        ]);

        $local_payment = $this->persistTransaction($charge, $receipt);

        return $charge;
    }

    /**
     * @param $payment_id
     * @param bool $local
     * @return Charge|Payment|Model
     */
    public function fetchTransaction($payment_id, $local = true)
    {
        if ($local) {
            return ($payment_id instanceof Payment) ? $payment_id : Payment::query()->findOrFail($payment_id);
        }

        try {
            return $payment_id instanceof Charge ? $payment_id : Charge::retrieve($payment_id);
        } catch (\Pingpp\Error\Base $e) {
            \Log::error('Pingxx 请求失败 ' . $e->getMessage());
            throw new \Exception('Pingxx 请求失败');
        }
    }

    /**
     * @param $parameters
     * @param bool $local
     * @return array|mixed
     */
    public function fetchTransactionLists($parameters, $local = false)
    {
        if ($local) {
            $parameters = array_only($parameters, ['app', 'channel', 'paid', 'refunded']);
            return Payment::query()->where($parameters)->paginate(array_get($parameters, 'limit', 10));
        }

        return Charge::all($parameters);
    }


    public function persistTransaction($charge, Receipt $receipt)
    {
        return Payment::query()->updateOrCreate(
            ['id' => $charge['id']],
            [
                'id' => $charge['id'],
                'order_no' => $receipt->getOrderNo(),
                'payment_no' => $charge['order_no'],
                'transaction_no' => $charge['transaction_no'],
                'receipt_id' => $receipt->getKey(),
                'user_id' => $receipt['user_id'],
                'gateway' => $this->configName(),
                'livemode' => $charge['livemode'],
                'app' => $charge['app'],
                'channel' => $charge['channel'],
                'client_ip' => $charge['client_ip'],
                'currency' => $charge['currency'],
                'amount' => $charge['amount'],
                'amount_settle' => $charge['amount_settle'],
                'amount_refunded' => $charge['amount_refunded'],
                'time_paid' => $charge['time_paid'],
                'time_expire' => $charge['time_expire'],
                'time_settle' => $charge['time_settle'],
                'paid' => $charge['paid'],
                'refunded' => $charge['refunded'],
                'failure_code' => $charge['failure_code'],
                'failure_msg' => $charge['failure_msg'],
                'credential' => $charge['credential'],
            ]
        );
    }

    /**
     * @param $charge
     * @return Payment
     * @throws ChargeNotPayException
     */
    public function finishPurchase($charge)
    {
        if (!$this->isPaid($charge)) {
            throw new ChargeNotPayException();
        }

        $payment = $this->fetchTransaction($charge['id'], $local = true);

        $this->processPurchase($payment, $charge);

        return $payment;
    }

    protected function processPurchase(Payment $payment, $charge)
    {
        $payment->setAsPaid($charge['transaction_no'], $charge['time_paid']);
    }

    public function isPaid($charge)
    {
        if (self::$config['live']) {
            return $charge['paid'] && $charge['livemode'];
        }

        return $charge['paid'];
    }

    public function transactionIsPaid($payment_id)
    {
        try {
            $payment = $this->fetchTransaction($payment_id, $local = true);
            return $payment->isPaid() ?: call_user_func(function () use ($payment) {
                $charge_paid = $this->isPaid($charge = $this->fetchTransaction($payment->getKey(), $local = false));
                if ($charge_paid) {
                    $this->processPurchase($payment, $charge);
                }
                return $charge_paid;
            });
        } catch (\Exception $e) {
            \Log::error($e);
            return false;
        }
    }

    /**
     * @param $receipt_id
     * @return Payment|null
     */
    public function receiptIsPaid($receipt_id)
    {
        $payments = Payment::query()->where('receipt_id', $receipt_id)->get();

        foreach ($payments as $payment) {
            if ($this->transactionIsPaid($payment)) {
                return $payment;
            }
        }

        return null;
    }

    protected static function getExtraData($channel, Receipt $receipt)
    {
        $mobile_success = self::$config['url_mobile_success'];
        $mobile_cancel = self::$config['url_mobile_cancel'];
        $pc_success = self::$config['url_pc_success'];
        $pc_cancel = self::$config['url_pc_cancel'];

        switch ($channel) {
            case self::PINGXX_WAP_CHANNEL_ALIPAY:
                $extra = [
                    'success_url' => $mobile_success,
                    'cancel_url' => $mobile_cancel
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_UNIONPAY_OLD:
                $extra = [
                    'result_url' => $mobile_success
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_BAIDU:
                $extra = [
                    'result_url' => $mobile_success,
                    'bfb_login' => true
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_UNIONPAY_NEW:
                $extra = [
                    'result_url' => $mobile_success
                ];
                break;
            case self::PINGXX_PC_CHANNEL_UNIONPAY:
                $extra = [
                    'result_url' => $pc_success
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_WECHAT:
                $extra = [
                    'open_id' => $receipt->getPaymentPayerID(Protocol::PAYER_ID_OPEN_ID)
                ];
                break;
            case self::PINGXX_SPECIAL_CHANNEL_WECHAT_QR:
                $extra = [
                    'product_id' => $receipt->getKey()
                ];
                break;
            case self::PINGXX_WAP_CHANNEL_YEEPAY:
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
            case self::PINGXX_WAP_CHANNEL_JINGDONG:
                $extra = [
                    'success_url' => $mobile_success,
                    'fail_url' => 'http://www.yourdomain.com',
                    'token' => 'dsafadsfasdfadsjuyhfnhujkijunhaf'
                ];
                break;
            case self::PINGXX_PC_CHANNEL_ALIPAY:
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


    /**
     * Refund
     */


    /**
     * @param RefundReceipt $refund
     * @return Refund
     * @internal param CanRefund $payment
     * @internal param $desc
     * @internal param null $amount
     */
    public function refund(RefundReceipt $refund)
    {
        $charge = $this->fetchTransaction($refund->getPaidPaymentId(), $local = false);

        try {
            $refund_charge = $charge->refunds->create([
                'amount' => $refund->getAmount(),
                'description' => $refund->getDesc(),
            ]);

            $refund_payment = $this->persistRefund($refund_charge);

            //request success
            if (!$refund_charge['failure_code']) {
                $refund->setAsApprove($refund_payment->getKey());
            }

            event(new PaymentRefundApply($refund_payment));

            return $refund_charge;
        } catch (Base $e) {
            $refund->setAsFail($e->getJsonBody());

            throw $e;
        }
    }

    /**
     * @param null $refund_id
     * @param null $payment_id
     * @param bool $local
     * @return mixed|RefundPayment|Refund
     * @throws \Exception
     */
    public function fetchRefundTransaction($refund_id = null, $payment_id = null, $local = false)
    {
        if (is_null($refund_id) && is_null($payment_id)) {
            throw new \Exception('wrong query refund transaction params', 422);
        }

        $local_payment = function () use ($refund_id, $payment_id) {
            if ($refund_id) {
                return RefundPayment::query()->findOrFail($refund_id);
            }
            return RefundPayment::query()->where('payment_id', $payment_id)->get();
        };

        if ($local) {
            return $local_payment();
        }

        if (is_null($payment_id)) {
            $payment_id = $local_payment()['payment_id'];
        }

        $charge = $this->fetchTransaction($payment_id, false);
        if (!is_null($refund_id)) {
            if ($refund_id instanceof Refund) {
                return $refund_id;
            }
            return $charge->refunds->retrieve($refund_id);
        }

        return $charge->refunds->all();
    }


    public function persistRefund($refund_charge)
    {
        return RefundPayment::query()->updateOrCreate(
            ['id' => $refund_charge['id']],
            [
                'id' => $refund_charge['id'],
                'refund_order_no' => $refund_charge['order_no'],
                'payment_order_no' => $refund_charge['charge_order_no'],
                'transaction_no' => $refund_charge['transaction_no'],
                'payment_id' => $refund_charge['charge'],
                'amount' => $refund_charge['amount'],
                'time_succeed' => $refund_charge['time_succeed'],
                'succeed' => $refund_charge['succeed'],
                'status' => $refund_charge['status'],
                'failure_code' => $refund_charge['failure_code'],
                'failure_msg' => $refund_charge['failure_msg'],
                'description' => $refund_charge['description'],
            ]
        );
    }

    public function finishRefund($refund_charge)
    {
        $refund_payment = $this->fetchRefundTransaction($refund_charge['id'], null, $local = true);

        if ($refund_charge['succeed']) {
            $refund_payment->setAsSucceed($refund_charge['transaction_no'], $refund_charge['time_succeed']);
            return $refund_payment;
        } else if ($refund_charge['failure_code']) {
            $refund_payment->setAsFail($refund_charge['failure_code'], $refund_charge['failure_msg']);
        }

        return false;
    }

    public function transactionIsRefunded($refund_charge_id)
    {
        $refund_payment = $this->fetchRefundTransaction($refund_charge_id, null, $local = true);

        return $refund_payment->isSucceed() ?: call_user_func(function () use ($refund_charge_id) {
            $refund_charge = $this->fetchRefundTransaction($refund_charge_id, null, $local = false);
            if ($refund_succeed = $refund_charge['succeed']) {
                $this->finishRefund($refund_charge);
            }
            return $refund_succeed;
        });
    }

    /**
     * Event Handler
     */
    const PINGXX_EVENT_SUMMARY_DAILY = 'summary.daily.available'; //上一天 0 点到 23 点 59 分 59 秒的交易金额和交易量统计，在每日 02:00 点左右触发。
    const PINGXX_EVENT_SUMMARY_WEEKLY = 'summary.weekly.available'; //上周一 0 点至上周日 23 点 59 分 59 秒的交易金额和交易量统计，在每周一 02:00 点左右触发。
    const PINGXX_EVENT_SUMMARY_MONTHLY = 'summary.monthly.available'; //上月一日 0 点至上月末 23 点 59 分 59 秒的交易金额和交易量统计，在每月一日 02:00 点左右触发。
    const PINGXX_EVENT_PAID_SUCCEED = 'charge.succeeded';
    const PINGXX_EVENT_REFUND_SUCCEED = 'refund.succeeded';
    const PINGXX_EVENT_TRANSFER_SUCCEED = 'transfer.succeeded'; //企业支付对象，支付成功时触发。
    const PINGXX_EVENT_RED_SENT = 'red_envelope.sent'; //红包对象，红包发送成功时触发。
    const PINGXX_EVENT_RED_RECEIVED = 'red_envelope.received'; //红包对象，红包接收成功时触发。
    const PINGXX_EVENT_BATCH_TRANSFER_SUCCEED = 'batch_transfer.succeeded'; //批量企业付款对象，批量企业付款成功时触发。

    /**
     * Transfer
     */

    public function transfer()
    {
        // TODO: Implement transfer() method.
    }

    public function handleNotify($event)
    {
        $charge = $event['data']['object'];
        switch ($event['type']) {
            case self::PINGXX_EVENT_PAID_SUCCEED:
                return $this->finishPurchase($charge);
            case self::PINGXX_EVENT_REFUND_SUCCEED:
                return $this->finishRefund($charge);
            default:
                throw new \Exception('event type not exist', 422);
        }
    }

}


