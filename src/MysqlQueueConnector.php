<?php

namespace LaravelMysqlQueue;

use Illuminate\Queue\Connectors\ConnectorInterface;
use LaravelMysqlQueue\Repositories\JobsRepository;
use LaravelMysqlQueue\Repositories\LockRepository;

class MysqlQueueConnector implements ConnectorInterface
{
    public const CONFIG_DEFAULT_QUEUE    = 'default_queue';
    public const CONFIG_CONNECTION       = 'connection';
    public const CONFIG_LOCK_NAME_PREFIX = 'lock_name_prefix';
    public const CONFIG_LOCK_TIMEOUT     = 'lock_timeout';

    /**
     * @param array $config
     * @return MysqlQueue
     */
    public function connect(array $config): MysqlQueue
    {
        return new MysqlQueue(
            new JobsRepository(
                $config[self::CONFIG_CONNECTION],
                $config[self::CONFIG_LOCK_NAME_PREFIX],
                new LockRepository(
                    $config[self::CONFIG_LOCK_TIMEOUT]
                )
            ),
            $config[self::CONFIG_DEFAULT_QUEUE]
        );
    }
}
