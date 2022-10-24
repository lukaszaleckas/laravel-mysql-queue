<?php

namespace LaravelMysqlQueue\Tests\Contracts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

abstract class AbstractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(realpath(__DIR__ . '/../../src/migrations'));
    }

    /**
     * @param mixed $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver'   => 'mysql',
            'host'     => 'mysql',
            'port'     => '3306',
            'database' => 'database',
            'username' => 'root',
            'password' =>  'secret',
        ]);
    }
}
