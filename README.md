# Laravel Mysql Queue Driver

## Installation

1. Run:

```
composer require lukaszaleckas/laravel-mysql-queue
```

Service provider should be automatically registered, if not add

```php
LaravelMysqlQueue\MysqlQueueServiceProvider::class
```

to application's your `app.php`.

2. Add Mysql's connection to your `queue.php` config:

```php
'mysql'      => [
    'driver'          => 'mysql',
    'default_queue'   => 'default',
    'connection'      => 'mysql',
    'lock_name_prefix' => 'mysql_queue_',
    'lock_timeout'    => 60
]
```
