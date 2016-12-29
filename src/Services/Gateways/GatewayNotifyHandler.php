<?php namespace Beansme\Payments\Services\Gateways;

interface GatewayNotifyHandler {

    public function handle($event);

}
