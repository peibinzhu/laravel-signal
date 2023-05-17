<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use PeibinLaravel\Process\Events\AfterProcessHandle;
use PeibinLaravel\Process\Events\BeforeProcessHandle;
use PeibinLaravel\Signal\Listeners\SignalDeregisterListener;
use PeibinLaravel\Signal\Listeners\SignalRegisterListener;
use PeibinLaravel\SwooleEvent\Events\BeforeWorkerStart;
use PeibinLaravel\SwooleEvent\Events\OnWorkerStop;

class SignalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $dependencies = [
            SignalManager::class => SignalManager::class,
        ];
        $this->registerDependencies($dependencies);

        $listeners = [
            BeforeWorkerStart::class   => SignalRegisterListener::class,
            BeforeProcessHandle::class => SignalRegisterListener::class,
            OnWorkerStop::class        => SignalDeregisterListener::class,
            AfterProcessHandle::class  => SignalDeregisterListener::class,
        ];
        $this->registerListeners($listeners);

        $this->registerPublishing();
    }

    private function registerDependencies(array $dependencies)
    {
        $config = $this->app->get(Repository::class);
        foreach ($dependencies as $abstract => $concrete) {
            $concreteStr = is_string($concrete) ? $concrete : gettype($concrete);
            if (is_string($concrete) && method_exists($concrete, '__invoke')) {
                $concrete = function () use ($concrete) {
                    return $this->app->call($concrete . '@__invoke');
                };
            }
            $this->app->singleton($abstract, $concrete);
            $config->set(sprintf('dependencies.%s', $abstract), $concreteStr);
        }
    }

    private function registerListeners(array $listeners)
    {
        $dispatcher = $this->app->get(Dispatcher::class);
        foreach ($listeners as $event => $_listeners) {
            foreach ((array)$_listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }

    public function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/signal.php' => config_path('signal.php'),
            ], 'signal');
        }
    }
}
