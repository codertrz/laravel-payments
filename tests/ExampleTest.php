<?php namespace Beansme\Payments\Test;


class ExampleTest extends TestCase {

    /** @test */
    public function example()
    {
        \DB::table('payments')->get();
    }


}
