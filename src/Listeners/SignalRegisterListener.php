<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal\Listeners;

use Illuminate\Contracts\Container\Container;
use PeibinLaravel\Process\Events\BeforeProcessHandle;
use PeibinLaravel\Signal\SignalHandlerInterface as SignalHandler;
use PeibinLaravel\Signal\SignalManager;
use PeibinLaravel\SwooleEvent\Events\BeforeWorkerStart;

class SignalRegisterListener
{
    public function __construct(protected Container $container)
    {
    }

    public function handle(object $event): void
    {
        $manager = $this->container->get(SignalManager::class);
        $manager->init();
        $manager->listen(
            value(function () use ($event) {
                if ($event instanceof BeforeWorkerStart) {
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
