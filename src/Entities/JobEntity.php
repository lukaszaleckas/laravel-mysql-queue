<?php

namespace LaravelMysqlQueue\Entities;

use LaravelMysqlQueue\Repositories\JobsRepository;

class JobEntity
{
    /** @var string */
    private string $queue;

    /** @var string */
    private string $payload;

    /**
     * @param string $queue
     * @param string $payload
     */
    public function __construct(string $queue, string $payload)
    {
        $this->queue   = $queue;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * @param object $data
     * @return self
     */
    public static function buildFromObject(object $data): self
    {
        return new self(
            $data->{JobsRepository::COLUMN_QUEUE},
            $data->{JobsRepository::COLUMN_PAYLOAD}
        );
    }
}
