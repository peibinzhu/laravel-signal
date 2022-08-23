<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal;

interface SignalHandlerInterface
{
    public const WORKER = 1;

    public const PROCESS = 2;

    /**
     * @return array [[ WOKKER, SIGNAL ]]
     */
    public function listen(): array;

    public function handle(int $signal): void;
}
