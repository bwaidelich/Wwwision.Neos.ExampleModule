<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Neos\Flow\Annotations\Proxy;

#[Proxy(false)]
final class RecordCreationDate implements JsonSerializable
{
    private function __construct(
        public readonly DateTimeImmutable $dateTime,
    ) {
    }

    public static function fromDateTime(DateTimeImmutable $dateTime): self
    {
        return new self($dateTime);
    }

    public static function fromString(string $value): self
    {
        return new self(DateTimeImmutable::createFromFormat(DATE_W3C, $value));
    }

    public function jsonSerialize(): string
    {
        return $this->dateTime->format(DATE_W3C);
    }
}
