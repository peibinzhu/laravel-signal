<?php

declare(strict_types=1);

namespace PeibinLaravel\Signal\Annotations;

use PeibinLaravel\Di\Annotation\AbstractAnnotation;

class Signal extends AbstractAnnotation
{
    public function __construct(public ?int $priority = null)
    {
    }
}
