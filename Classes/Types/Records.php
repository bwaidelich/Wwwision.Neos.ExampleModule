<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Neos\Flow\Annotations\Proxy;
use Traversable;

/**
 * @implements IteratorAggregate<Record>
 */
#[Proxy(false)]
final class Records implements IteratorAggregate, Countable, JsonSerializable
{
    public function __construct(
        private readonly array $records,
    )
    {
    }

    public static function create(Record ...$records): self
    {
        return new self($records);
    }

    public static function none(): self
    {
        return new self([]);
    }

    public function isEmpty(): bool
    {
        return $this->records === [];
    }

    public function first(): ?Record
    {
        return $this->records[array_key_first($this->records)] ?? null;
    }

    /**
     * @param Closure(Record): bool $filter
     */
    public function filter(Closure $filter): Records
    {
        return new self(array_filter($this->records, $filter));
    }


    public function slice(int $offset, int $length): self
    {
        return new self(array_slice($this->records, $offset, $length));
    }

    /**
     * @param Closure(Record): mixed $callback
     */
    public function map(Closure $callback): array
    {
        return array_map($callback, $this->records);
    }

    public function merge(self $newRecords): self
    {
        return new self([...$this->records, ...$newRecords->records]);
    }

    /**
     * @return Traversable<Record>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->records);
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function jsonSerialize(): array
    {
        return $this->records;
    }
}
