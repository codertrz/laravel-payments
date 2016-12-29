<?php namespace Beansme\Payments;
class Facade extends \Illuminate\Support\Facades\Facade {

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'payments';
    }

}
