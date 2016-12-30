<?php namespace BTWay\Payments\Services\Gateways;

interface GatewayNotifyHandler {

    public function handle($event);

}
