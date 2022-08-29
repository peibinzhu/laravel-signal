<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use PeibinLaravel\Di\Annotation\AnnotationCollector;
use PeibinLaravel\Signal\Annotations\Signal;
use PeibinLaravel\Signal\SignalHandlerInterface as SignalHandler;
use SplPriorityQueue;
use Swoole\Coroutine;
use Swoole\Coroutine\System;

class SignalManager
{
    /**
     * @var SignalHandlerInterface[][][]
     */
    protected array $handlers = [];

    protected Repository $config;

    protected bool $stopped = false;

    public function __construct(protected Container $container)
    {
        $this->config = $container->get(Repository::class);
    }

    public function init(): void
    {
        foreach ($this->getQueue() as $class) {
            /** @var SignalHandlerInterface $handler */
            $handler = $this->container->get($class);
            foreach ($handler->listen() as [$process, $signal]) {
                if ($process & SignalHandler::WORKER) {
                    $this->handlers[SignalHandler::WORKER][$signal][] = $handler;
                } elseif ($process & SignalHandler::PROCESS) {
                    $this->handlers[SignalHandler::PROCESS][$signal][] = $handler;
                }
            }
        }
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }

    public function listen(?int $process): void
    {
        if ($this->isInvalidProcess($process)) {
            return;
        }

        foreach ($this->handlers[$process] ?? [] as $signal => $handlers) {
            Coroutine::create(function () use ($signal, $handlers) {
                while (true) {
                    $ret = System::waitSignal($signal, $this->config->get('signal.timeout', 5.0));
                    if ($ret) {
                        foreach ($handlers as $handler) {
                            $handler->handle($signal);
                        }
                    }

                    if ($this->isStopped()) {
                        break;
                    }
                }
            });
        }
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    public function setStopped(bool $stopped): self
    {
        $this->stopped = $stopped;
        return $this;
    }

    protected function isInvalidProcess(?int $process): bool
    {
        return !in_array($process, [
            SignalHandler::PROCESS,
            SignalHandler::WORKER,
        ]);
    }

    protected function getQueue(): SplPriorityQueue
    {
        $handlers = $this->config->get('signal.handlers', []);

        $queue = new SplPriorityQueue();
        foreach ($handlers as $handler => $priority) {
            if (!is_numeric($priority)) {
                $handler = $priority;
                $priority = 0;
            }
            $queue->insert($handler, $priority);
        }

        $handlers = AnnotationCollector::getClassesByAnnotation(Signal::class);
        /**
         * @var string $handler
         * @var Signal $annotation
         */
        foreach ($handlers as $handler => $annotation) {
            $queue->insert($handler, $annotation->priority ?? 0);
        }

        return $queue;
    }
}
