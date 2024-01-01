<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use JsonSerializable;
use Neos\Flow\Annotations\Proxy;

#[Proxy(false)]
final class SearchTerm implements JsonSerializable
{
    private function __construct(
        public readonly string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
