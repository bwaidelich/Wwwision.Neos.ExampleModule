<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use Neos\Flow\Annotations\Proxy;

#[Proxy(false)]
final class RecordFilter
{
    private function __construct(
        public readonly ?RecordState $state,
        public readonly ?SearchTerm $searchTerm,
    ) {
    }

    public static function create(): self
    {
        return new self(null, null);
    }

    public static function fromArray(array $array): self
    {
        return new self(
            !empty($array['state']) ? RecordState::from($array['state']) : null,
            !empty($array['searchTerm']) ? SearchTerm::fromString($array['searchTerm']) : null,
        );
    }

    public function with(
        ?RecordState $state = null,
        ?SearchTerm $searchTerm = null,
    ): self
    {
        return new self(
            $state ?? $this->state,
            $searchTerm ?? $this->searchTerm,
        );
    }
}
