<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Action\ItemActionProcessor;

class ActionPipe implements PipeInterface
{
    public function __construct(private readonly ItemActionProcessor $actionProcessor) {}

    public function pipe(array $item): array
    {
        return $this->actionProcessor->process($item);
    }
}