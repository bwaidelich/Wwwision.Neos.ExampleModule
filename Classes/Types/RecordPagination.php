<?php

declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

use Neos\Flow\Annotations as Flow;
use Webmozart\Assert\Assert;

#[Flow\Proxy(false)]
final class RecordPagination
{
    private const DEFAULT_RESULTS_PER_PAGE = 10;

    private function __construct(
        public readonly int $offset,
        public readonly int $resultsPerPage,
    ) {
    }

    public static function firstPage(): self
    {
        return new self(0, self::DEFAULT_RESULTS_PER_PAGE);
    }

    public static function forPage(int $pageNumber): self
    {
        Assert::positiveInteger($pageNumber);
        return new self(self::DEFAULT_RESULTS_PER_PAGE * ($pageNumber - 1), self::DEFAULT_RESULTS_PER_PAGE);
    }
}
