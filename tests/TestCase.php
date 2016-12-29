<?php namespace Beansme\Payments\Test;

use Beansme\Payments\Providers\PaymentServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends \Orchestra\Testbench\TestCase {

    use DatabaseMigrations;

    protected $baseUrl = 'http://payments.dev';

    public function setUp()
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    protected function getPackageProviders($app)
    {
        return [PaymentServiceProvider::class];
    }


    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
//        $app['config']->set('database.default', 'testing');

        //test by mysql

        $app['config']->set('database.connections.mysql.testing', [
            'driver' => 'mysql',
            'host' => env('DB_TEST_HOST', 'localhost'),
            'database' => env('DB_TEST_DATABASE', 'homestead_test'),
            'username' => env('DB_TEST_USERNAME', 'homestead'),
            'password' => env('DB_TEST_PASSWORD', 'secret'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]);

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'test');
        $app['config']->set('database.connections.mysql.username', 'root');
        $app['config']->set('database.connections.mysql.password', '');
    }

    protected function dumpResponse()
    {
        print_r($this->getResponseData());
    }

    public function echoJson()
    {
        echo $this->response->getContent();
    }

    protected function getResponseData($key = null)
    {
        $content = json_decode($this->response->getContent(), true);

        if ($key) {
            return array_get($content, $key);
        }
        return $content;
    }

    protected function getFaker()
    {
        return \Faker\Factory::create();
    }

    public function visitUrl($url)
    {
        //模拟付款
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $content;
    }

    /**
     * Test running migration.
     *
     * @test
     */
    public function testRunningMigration()
    {

    }


}
