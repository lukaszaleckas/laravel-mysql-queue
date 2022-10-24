<?php

namespace LaravelMysqlQueue;

use Illuminate\Contracts\Queue\Queue as QueueInterface;
use Illuminate\Queue\Queue;
use LaravelMysqlQueue\Repositories\JobsRepository;
use Throwable;

class MysqlQueue extends Queue implements QueueInterface
{
    public const OPTIONS_DELAY = 'delay';

    /** @var JobsRepository */
    private JobsRepository $jobsRepository;

    /** @var string */
    private string $defaultQueue;

    /**
     * @param JobsRepository $jobsRepository
     * @param string         $defaultQueue
     */
    public function __construct(JobsRepository $jobsRepository, string $defaultQueue)
    {
        $this->jobsRepository = $jobsRepository;
        $this->defaultQueue   = $defaultQueue;
    }

    /**
     * @param string|null $queue
     * @return int
     */
    public function size($queue = null): int
    {
        return $this->jobsRepository->getQueueSize(
            $this->getQueue($queue)
        );
    }

    /**
     * @param string|object $job
     * @param mixed         $data
     * @param string|null   $queue
     * @return string|null
     * @throws Throwable
     */
    public function push($job, $data = '', $queue = null): ?string
    {
        $queue = $this->getQueue($queue);

        return $this->pushRaw(
            $this->createPayload($job, $queue, $data),
            $queue
        );
    }

    /**
     * @param mixed       $payload
     * @param string|null $queue
     * @param array       $options
     * @return string|null
     * @throws Throwable
     */
    public function pushRaw($payload, $queue = null, array $options = []): ?string
    {
        $this->jobsRepository->createJob(
            $this->getQueue($queue),
            $payload,
            $options[self::OPTIONS_DELAY] ?? null
        );

        return data_get(json_decode($payload), 'uuid') ?? null;
    }

    /**
     * @param mixed       $delay
     * @param mixed       $job
     * @param mixed       $data
     * @param string|null $queue
     * @return string|null
     * @throws Throwable
     */
    public function later($delay, $job, $data = '', $queue = null): ?string
    {
        $queue = $this->getQueue($queue);

        return $this->pushRaw(
            $this->createPayload($job, $queue, $data),
            $queue,
            [self::OPTIONS_DELAY => $delay]
        );
    }

    /**
     * @param string|null $queue
     * @return MysqlJob|null
     * @throws Throwable
     */
    public function pop($queue = null): ?MysqlJob
    {
        $result = $this->jobsRepository->getJob(
            $this->getQueue($queue)
        );

        if ($result === null) {
            return null;
        }

        return new MysqlJob($this->container, $result->getPayload(), $result->getQueue());
    }

    /**
     * @param string|null $queue
     * @return string
     */
    private function getQueue(?string $queue): string
    {
        return $queue ?? $this->defaultQueue;
    }
}
