<?php

namespace Misery\Component\Akeneo\Client;

use App\Component\Common\Resource\EntityResourceInterface;
use Misery\Component\Reader\ReaderInterface;

class ApiReader implements ReaderInterface
{
    public function __construct(
        private readonly EntityResourceInterface $entityResource,
        private ?\Iterator $cursor = null
    ) {}

    public function read()
    {
        if ($this->cursor === null) {
            $this->cursor = $this->entityResource->getAll();
        }
        if (!$this->cursor->valid()) {
            return null;
        }
        $item = $this->cursor->current();
        $this->cursor->next();

        return $item;
    }

    public function getIterator(): \Iterator
    {
        while ($item = $this->read()) {
            yield $item;
        }
    }

    public function find(array $constraints): ReaderInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    public function filter(callable $callable): ReaderInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    public function map(callable $callable): ReaderInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getItems(): array
    {
        return iterator_to_array($this->getIterator());
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }
}
