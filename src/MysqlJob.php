<?php

namespace LaravelMysqlQueue;

use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobInterface;

class MysqlJob extends Job implements JobInterface
{
    /** @var Container */
    protected $container;

    /** @var string */
    protected string $job;

    /** @var array */
    protected array $decoded;

    /**
     * @param Container $container
     * @param string    $job
     * @param string    $queue
     */
    public function __construct(Container $container, string $job, string $queue)
    {
        $this->container = $container;
        $this->job       = $job;
        $this->queue     = $queue;

        $this->decoded = $this->payload();
    }

    /**
     * @return string
     */
    public function getRawBody(): string
    {
        return $this->job;
    }

    /**
     * @return int
     */
    public function attempts(): int
    {
        return ($this->decoded['attempts'] ?? null) + 1;
    }

    /**
     * @return string|null
     */
    public function getJobId(): ?string
    {
        return $this->decoded['id'] ?? null;
    }
}
