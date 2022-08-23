<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal;

use App\Events\OnWorkerStop;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\WorkerStarting;
use PeibinLaravel\Process\Events\BeforeProcessHandle;
use PeibinLaravel\Signal\Listeners\SignalDeregisterListener;
use PeibinLaravel\Signal\Listeners\SignalRegisterListener;
use PeibinLaravel\Utils\Providers\RegisterProviderConfig;

class SignalServiceProvider extends ServiceProvider
{
    use RegisterProviderConfig;

    public function __invoke(): array
    {
        return [
            'listeners' => [
                WorkerStarting::class      => SignalRegisterListener::class,
                BeforeProcessHandle::class => SignalRegisterListener::class,
                OnWorkerStop::class        => SignalDeregisterListener::class,
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
