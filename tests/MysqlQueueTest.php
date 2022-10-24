<?php

namespace LaravelMysqlQueue\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use LaravelMysqlQueue\Exceptions\MysqlQueueException;
use LaravelMysqlQueue\MysqlJob;
use LaravelMysqlQueue\MysqlQueue;
use LaravelMysqlQueue\MysqlQueueConnector;
use LaravelMysqlQueue\Repositories\JobsRepository;
use LaravelMysqlQueue\Repositories\LockRepository;
use LaravelMysqlQueue\Tests\Contracts\AbstractTest;
use Mockery;
use Throwable;

class MysqlQueueTest extends AbstractTest
{
    use WithFaker;

    /** @var MysqlQueue */
    private MysqlQueue $queue;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = (new MysqlQueueConnector())->connect([
            MysqlQueueConnector::CONFIG_CONNECTION       => 'mysql',
            MysqlQueueConnector::CONFIG_LOCK_NAME_PREFIX => '',
            MysqlQueueConnector::CONFIG_LOCK_TIMEOUT     => 10,
            MysqlQueueConnector::CONFIG_DEFAULT_QUEUE    => 'default',
        ]);

        $this->queue->setContainer($this->app);
    }

    /**
     * @dataProvider queueTestDataProvider
     *
     * @param string|null $queue
     * @return void
     * @throws Throwable
     */
    public function testCanGetQueueSize(?string $queue): void
    {
        //Initially 0
        self::assertEquals(0, $this->queue->size($queue));

        //Push a job - expected 1
        $this->queue->push('', '', $queue);

        self::assertEquals(1, $this->queue->size($queue));

        //Pop job - expected 0 again
        $this->queue->pop($queue);

        self::assertEquals(0, $this->queue->size($queue));
    }

    /**
     * @dataProvider queueTestDataProvider
     *
     * @param string|null $queue
     * @return void
     * @throws Throwable
     */
    public function testCanQueueAJob(?string $queue): void
    {
        $this->queue->push('', '', $queue);

        $result = $this->queue->pop($queue);

        self::assertInstanceOf(MysqlJob::class, $result);
        self::assertEquals(1, $result->attempts());
        self::assertNull($result->getJobId());
    }

    /**
     * @dataProvider queueTestDataProvider
     *
     * @param string|null $queue
     * @return void
     * @throws Throwable
     */
    public function testCanQueueWithDelay(?string $queue): void
    {
        $this->queue->later(1234, '', '', $queue);

        self::assertNull(
            $this->queue->pop($queue)
        );
    }

    /**
     * @return array
     */
    public function queueTestDataProvider(): array
    {
        return [
            [null],
            [$this->faker->word]
        ];
    }

    /**
     * @dataProvider lockOperationTestDataProvider
     *
     * @param array $returnValues
     * @return void
     * @throws Throwable
     */
    public function testThrowsExceptionIfLockOperationFailed(array $returnValues): void
    {
        $this->expectException(MysqlQueueException::class);

        /** @var LockRepository $lockRepositoryMock */
        $lockRepositoryMock = Mockery::mock(LockRepository::class)
            ->shouldReceive($returnValues)->getMock();

        $queue = new MysqlQueue(
            new JobsRepository('mysql', '', $lockRepositoryMock),
            'default'
        );

        $queue->pop();
    }

    /**
     * @return array
     */
    public function lockOperationTestDataProvider(): array
    {
        return [
            [
                ['acquireLock' => false]
            ],
            [
                [
                    'acquireLock' => true,
                    'releaseLock' => false
                ]
            ]
        ];
    }
}
