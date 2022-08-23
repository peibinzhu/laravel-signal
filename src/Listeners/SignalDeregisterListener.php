<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal\Listeners;

use Illuminate\Contracts\Container\Container;
use PeibinLaravel\Signal\SignalManager;

class SignalDeregisterListener
{
    public function __construct(protected Container $container)
    {
    }

    public function handle(): void
    {
        $this->container->make(SignalManager::class)->setStopped(true);
    }
}
