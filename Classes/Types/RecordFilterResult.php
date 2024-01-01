<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use Countable;
use IteratorAggregate;
use Neos\Flow\Annotations\Proxy;
use Traversable;

/**
 * @implements IteratorAggregate<Record>
 */
#[Proxy(false)]
final class RecordFilterResult implements IteratorAggregate, Countable
{
    public function __construct(
        public readonly int $numberOfResults,
        public readonly Records $records,
    ) {
    }

    /**
     * @return Traversable<Record>
     */
    public function getIterator(): Traversable
    {
        return $this->records;
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function isEmpty(): bool
    {
        return $this->numberOfResults === 0;
    }
}
