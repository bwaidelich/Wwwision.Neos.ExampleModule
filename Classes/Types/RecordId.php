<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use JsonSerializable;
use Neos\Flow\Annotations\Proxy;
use Neos\Flow\Utility\Algorithms;

#[Proxy(false)]
final class RecordId implements JsonSerializable
{
    private function __construct(
        public readonly string $value,
    ) {
    }

    public static function create(): self
    {
        return new self(Algorithms::generateUUID());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
