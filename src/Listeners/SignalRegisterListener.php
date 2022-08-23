<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal\Listeners;

use Illuminate\Contracts\Container\Container;
use Laravel\Octane\Events\WorkerStarting;
use PeibinLaravel\Process\Events\BeforeProcessHandle;
use PeibinLaravel\Signal\SignalHandlerInterface as SignalHandler;
use PeibinLaravel\Signal\SignalManager;
use Swoole\Http\Server;

class SignalRegisterListener
{
    public function __construct(protected Container $container)
    {
    }

    public function handle(object $event): void
    {
        if ($event instanceof WorkerStarting && $event->app->get(Server::class)->taskworker) {
            return;
        }

        $manager = $this->container->make(SignalManager::class);
        $manager->init();
        $manager->listen(
            value(function () use ($event) {
                if ($event instanceof WorkerStarting) {
                    return SignalHandler::WORKER;
                }

                if ($event instanceof BeforeProcessHandle) {
                    return SignalHandler::PROCESS;
                }

                return null;
            })
        );
    }
}
