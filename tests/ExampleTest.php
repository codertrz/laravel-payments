<?php namespace Beansme\Payments\Test;
class ExampleTest extends TestCase {

    /** @test */
    public function example()
    {
        $this->json('post', 'api/gateway/payments/pingxx/paid');
        
        $this->assertResponseStatus(403);
    }


}
