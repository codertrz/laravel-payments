<?php namespace BTWay\Payments\Test\Gateway;

use BTWay\Payments\Models\Payment;
use BTWay\Payments\Models\Receipt;
use BTWay\Payments\Protocol;
use BTWay\Payments\Services\Gateways\PingxxGateway;
use BTWay\Payments\Services\Receipts\ReceiptService;
use BTWay\Payments\Test\TestCase;
use Pingpp\Charge;

class ThirdPartyReceiptTest extends TestCase {

    // api_key 获取方式：登录 [Dashboard](https://dashboard.pingxx.com)->点击管理平台右上角公司名称->企业设置->开发设置->Live/Test Secret Key
    const APP_KEY = 'sk_test_ibbTe5jLGCi5rzfH4OqPW9KC';

    /**
     * @var ReceiptService
     */
    public $pay;
    public $user_id;
    public $order_no;
    public $amount = 10000;
    public $subject = 'payments test';
    public $body = 'payments test';

    public $gateway = 'pingxx';

    public function setUp()
    {
        parent::setUp();

        $this->pay = \Pay::init($this->gateway);
        $this->user_id = Protocol::generateId();
        $this->order_no = Protocol::generateId();

//        $this->app['config']->set('payments.gateways.pingxx.api_key', self::APP_KEY);
    }

    /** @test */
    public function it_can_accept_purchase_request_and_create_a_receipt_record()
    {
        $this->assertTrue($this->pay->gateway() instanceof PingxxGateway);

        $receipt = $this->pay->initReceipt($this->user_id, $this->order_no, $this->amount, $this->subject, $this->body);
        $this->assertTrue($receipt instanceof Receipt);
        $this->assertEquals($receipt['amount'], $this->amount);
        $this->assertEquals($receipt['order_no'], $this->order_no);
        $this->assertEquals($receipt['user_id'], $this->user_id);
        $this->assertEquals($receipt['payment_type'], config('payments.gateways.' . $this->gateway . '.payment_type'));
    }

    /** @test */
    public function it_can_request_a_pingxx_purchase()
    {
        $channel = PingxxGateway::PINGXX_SPECIAL_CHANNEL_WECHAT_QR;
        $charge = $this->pay->purchase($this->user_id, $this->order_no, $this->amount, $this->subject, $this->body, $channel);

        $receipt = Receipt::query()->where('order_no', $this->order_no)->firstOrFail();

        $this->assertTrue($charge instanceof Charge);

        $this->seeInDatabase('payments', ['id' => $charge['id']]);
        $this->seeInDatabase('payments', ['id' => $charge['id'], 'user_id' => $receipt['user_id'], 'receipt_id' => $receipt->getKey()]);

        return $charge;
    }


    /** @test */
    public function it_can_pay_a_pingxx_payment()
    {
        $charge = $this->it_can_request_a_pingxx_purchase();

        $pay_url = "http://sissi.pingxx.com/notify.php?ch_id=" . $charge['id'];

//        $pay_url = $charge['credential'][$charge['channel']];

        $result = $this->visitUrl($pay_url);

        return $charge;
    }

    /** @test */
    public function it_can_finish_purchase_process_by_check()
    {
        $charge = $this->it_can_pay_a_pingxx_payment();

        $payment_id = $charge['id'];

        $charge_paid = $this->pay->gateway()->transactionIsPaid($payment_id);
        $this->assertTrue($charge_paid);

        $this->checkPaid($payment_id);
    }

    /** @test */
    public function it_can_finish_purchase_process_by_notify()
    {
        $charge = $this->it_can_pay_a_pingxx_payment();
        $payment_id = $charge['id'];

        $charge = $this->pay->gateway()->fetchTransaction($payment_id, false);

//        $charge_array = json_decode((string)$charge, true);

        $event = [
            'id' => 'event_' . $payment_id,
            'livemode' => true,
            'type' => 1,
            'data' => [
                'object' => $charge
            ]
        ];

        $this->json('post', config('payments.routeAttributes.prefix') . '/pingxx/paid', $event);
        $this->assertResponseStatus(403);
        $event['livemode'] = false;


        $this->json('post', config('payments.routeAttributes.prefix') . '/pingxx/paid', $event);
        $this->assertResponseStatus(422);
        $event['type'] = PingxxGateway::PINGXX_EVENT_PAID_SUCCEED;

        $this->json('post', config('payments.routeAttributes.prefix') . '/pingxx/paid', $event);
        $this->assertResponseOk();

        $this->checkPaid($payment_id);

    }

    protected function checkPaid($payment_id)
    {
        $payment = Payment::query()->findOrFail($payment_id);
        $this->assertTrue($payment->isPaid());

        $receipt = Receipt::query()->where('payment_id', $payment_id)->firstOrFail();
        $this->assertTrue($receipt->isPaid());
    }

}
