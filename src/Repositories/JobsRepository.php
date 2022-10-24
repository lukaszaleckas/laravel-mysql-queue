<?php

namespace LaravelMysqlQueue\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use LaravelMysqlQueue\Entities\JobEntity;
use LaravelMysqlQueue\Exceptions\MysqlQueueException;

class JobsRepository
{
    public const TABLE = 'jobs';

    public const COLUMN_ID           = 'id';
    public const COLUMN_QUEUE        = 'queue';
    public const COLUMN_PAYLOAD      = 'payload';
    public const COLUMN_AVAILABLE_AT = 'available_at';

    /** @var string */
    private string $connection;

    /** @var string */
    private string $lockNamePrefix;

    /** @var LockRepository */
    private LockRepository $lockRepository;

    /**
     * @param string         $connection
     * @param string         $lockNamePrefix
     * @param LockRepository $lockRepository
     */
    public function __construct(string $connection, string $lockNamePrefix, LockRepository $lockRepository)
    {
        $this->connection     = $connection;
        $this->lockNamePrefix = $lockNamePrefix;
        $this->lockRepository = $lockRepository;
    }

    /**
     * @param string   $queue
     * @param string   $payload
     * @param int|null $delay
     * @return void
     */
    public function createJob(string $queue, string $payload, int $delay = null): void
    {
        $this->getQuery()->insert([
            self::COLUMN_QUEUE        => $queue,
            self::COLUMN_PAYLOAD      => $payload,
            self::COLUMN_AVAILABLE_AT => now()->addSeconds($delay ?? 0)
        ]);
    }

    /**
     * @param string $queue
     * @return JobEntity|null
     * @throws MysqlQueueException
     */
    public function getJob(string $queue): ?JobEntity
    {
        $this->acquireLock($queue);

        $result = $this->getQuery()
            ->where(self::COLUMN_QUEUE, $queue)
            ->where(self::COLUMN_AVAILABLE_AT, '<=', now())
            ->first();

        if ($result !== null) {
            $this->getQuery()->where(self::COLUMN_ID, $result->id)->delete();
        }

        $this->releaseLock($queue);

        return $result !== null ? JobEntity::buildFromObject($result) : null;
    }

    /**
     * @param string $queue
     * @return void
     * @throws MysqlQueueException
     */
    public function acquireLock(string $queue): void
    {
        $result   = $this->lockRepository->acquireLock(
            $name = $this->getLockName($queue)
        );

        if ($result === false) {
            throw new MysqlQueueException("Failed acquiring $name lock");
        }
    }

    /**
     * @param string $queue
     * @return void
     * @throws MysqlQueueException
     */
    public function releaseLock(string $queue): void
    {
        $result   = $this->lockRepository->releaseLock(
            $name = $this->getLockName($queue)
        );

        if ($result === false) {
            throw new MysqlQueueException("Failed releasing $name lock");
        }
    }

    /**
     * @param string $queue
     * @return int
     */
    public function getQueueSize(string $queue): int
    {
        return $this->getQuery()->where(self::COLUMN_QUEUE, $queue)->count();
    }

    /**
     * @param string $queue
     * @return string
     */
    private function getLockName(string $queue): string
    {
        return $this->lockNamePrefix . $queue;
    }

    /**
     * @return Builder
     */
    private function getQuery(): Builder
    {
        return DB::connection($this->connection)->table(self::TABLE);
    }
}
