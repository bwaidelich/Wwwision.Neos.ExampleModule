<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use Neos\Flow\Annotations\Proxy;

#[Proxy(false)]
final class Record
{
    public function __construct(
        public readonly RecordId $id,
        public readonly RecordTitle $title,
        public readonly RecordState $state,
        public readonly RecordCreationDate $creationDate,
    ) {
    }

    public function with(
        RecordTitle $title = null,
        RecordState $state = null,
    ): self
    {
        return new self(
            $this->id,
            $title ?? $this->title,
            $state ?? $this->state,
            $this->creationDate,
        );
    }

    public function isDisabled(): bool
    {
        return $this->state === RecordState::INACTIVE;
    }
}
