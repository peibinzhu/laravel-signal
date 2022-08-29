<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal;

use Illuminate\Support\ServiceProvider;
use PeibinLaravel\Process\Events\AfterProcessHandle;
use PeibinLaravel\Process\Events\BeforeProcessHandle;
use PeibinLaravel\Signal\Listeners\SignalDeregisterListener;
use PeibinLaravel\Signal\Listeners\SignalRegisterListener;
use PeibinLaravel\SwooleEvent\Events\BeforeWorkerStart;
use PeibinLaravel\SwooleEvent\Events\OnWorkerStop;
use PeibinLaravel\Utils\Providers\RegisterProviderConfig;

class SignalServiceProvider extends ServiceProvider
{
    use RegisterProviderConfig;

    public function __invoke(): array
    {
        return [
            'listeners' => [
                BeforeWorkerStart::class   => SignalRegisterListener::class,
                BeforeProcessHandle::class => SignalRegisterListener::class,
                OnWorkerStop::class        => SignalDeregisterListener::class,
                AfterProcessHandle::class  => SignalDeregisterListener::class,
            ],
            'publish'   => [
                [
                    'id'          => 'signal',
                    'source'      => __DIR__ . '/../config/signal.php',
                    'destination' => config_path('signal.php'),
                ],
            ],
        ];
    }
}
