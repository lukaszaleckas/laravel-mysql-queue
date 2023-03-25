<?php

namespace LaravelMysqlQueue\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LockRepository
{
    /** @var int */
    private int $lockTimeout;

    /**
     * @param int $lockTimeout
     */
    public function __construct(int $lockTimeout)
    {
        $this->lockTimeout = $lockTimeout;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function acquireLock(string $name): bool
    {
        return $this->getLockResult(
            DB::query()->selectRaw(
                'GET_LOCK(:name, :timeout)',
                [
                    'name'    => $name,
                    'timeout' => $this->lockTimeout
                ]
            )->get()->toArray()
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    public function releaseLock(string $name): bool
    {
        return $this->getLockResult(
            DB::query()->selectRaw('RELEASE_LOCK(?)', [$name])->get()->toArray()
        );
    }

    /**
     * @param array $result
     * @return bool
     */
    private function getLockResult(array $result): bool
    {
        return (bool)Arr::first(
            Arr::first($result)
        );
    }
}
