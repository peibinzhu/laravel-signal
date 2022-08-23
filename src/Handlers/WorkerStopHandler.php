<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal\Handlers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use PeibinLaravel\Signal\SignalHandlerInterface;
use Swoole\Server;

class WorkerStopHandler implements SignalHandlerInterface
{
    public function __construct(protected Application $app, protected Repository $config)
    {
    }

    public function listen(): array
    {
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        if ($signal !== SIGINT) {
            $time = $this->config->get('server.settings.max_wait_time', 3);
            sleep($time);
        }

        $this->app->has(Server::class) && $this->app->get(Server::class)->stop();
    }
}
