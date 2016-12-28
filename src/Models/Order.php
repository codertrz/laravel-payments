<?php namespace Beansme\Payments\Models;
use Beansme\Payments\Services\Contracts\NeedPay;
use Hafael\LaraFlake\Traits\LaraFlakeTrait;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    use NeedPay, LaraFlakeTrait;

}
