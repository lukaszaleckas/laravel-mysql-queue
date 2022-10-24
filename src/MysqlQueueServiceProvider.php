<?php

namespace LaravelMysqlQueue;

use Illuminate\Support\ServiceProvider;

class MysqlQueueServiceProvider extends ServiceProvider
{
    public const CONNECTOR = 'mysql';

    /**
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        $this->app['queue']->addConnector(self::CONNECTOR, function () {
            return new MysqlQueueConnector();
        });
    }
}
