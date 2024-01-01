<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use Neos\Cache\Frontend\StringFrontend;
use Neos\Flow\Annotations\Scope;
use RuntimeException;
use Wwwision\Neos\ExampleModule\Types\RecordCreationDate;
use Wwwision\Neos\ExampleModule\Types\RecordFilter;
use Wwwision\Neos\ExampleModule\Types\Record;
use Wwwision\Neos\ExampleModule\Types\RecordFilterResult;
use Wwwision\Neos\ExampleModule\Types\RecordId;
use Wwwision\Neos\ExampleModule\Types\RecordPagination;
use Wwwision\Neos\ExampleModule\Types\Records;
use Wwwision\Neos\ExampleModule\Types\RecordTitle;
use Wwwision\Neos\ExampleModule\Types\RecordState;

#[Scope('singleton')]
final class RecordRepository
{
    private const RECORDS_CACHE_ENTRY_ID = 'records';

    public function __construct(
        private readonly StringFrontend $recordsCache,
    )
    {
    }

    public function findAll(): Records
    {
        return $this->loadRecordsFromCache();
    }

    public function findByFilter(RecordFilter $filter, RecordPagination $pagination): RecordFilterResult
    {
        $matchingRecords = $this->loadRecordsFromCache()->filter(static function (Record $record) use ($filter) {
            if ($filter->state !== null && $record->state !== $filter->state) {
                return false;
            }
            if ($filter->searchTerm !== null && !str_contains($record->title->value, $filter->searchTerm->value)) {
                return false;
            }
            return true;
        });
        return new RecordFilterResult(
            $matchingRecords->count(),
            $matchingRecords->slice($pagination->offset, $pagination->resultsPerPage),
        );
    }

    public function findOneById(RecordId $id): ?Record
    {
        return $this->loadRecordsFromCache()
            ->filter(static fn (Record $record) => $record->id->equals($id))
            ->first();
    }

    public function add(Record $record): void
    {
        $mergedRecords = $this->findAll();
        $mergedRecords = $mergedRecords->merge(Records::create($record));
        $this->storeRecordsInCache($mergedRecords);
    }

    public function addMany(Records $records): void
    {
        $mergedRecords = $this->findAll();
        $mergedRecords = $mergedRecords->merge($records);
        $this->storeRecordsInCache($mergedRecords);
    }

    public function update(Record $recordToUpdate): void
    {
        $updatedRecords = [];
        foreach ($this->findAll() as $existingRecord) {
            $updatedRecords[] = $existingRecord->id->equals($recordToUpdate->id) ? $recordToUpdate : $existingRecord;
        }
        $this->storeRecordsInCache(Records::create(...$updatedRecords));
    }

    public function delete(RecordId $id): void
    {
        $filteredRecords = $this->loadRecordsFromCache()->filter(static fn (Record $record) => !$record->id->equals($id));
        $this->storeRecordsInCache($filteredRecords);
    }

    public function deleteAll(): void
    {
        $this->storeRecordsInCache(Records::none());
    }

    /** ------------------------- */

    private function getRecordOrThrow(RecordId $id): Record
    {
        $record = $this->findOneById($id);
        if ($record === null) {
            throw new InvalidArgumentException(sprintf('Failed to find record with id "%s"', $id->value), 1703510408);
        }
        return $record;
    }

    private function loadRecordsFromCache(): Records
    {
        $recordsEncoded = $this->recordsCache->get(self::RECORDS_CACHE_ENTRY_ID);
        if ($recordsEncoded === false) {
            return Records::none();
        }
        try {
            $recordsDecoded = json_decode($recordsEncoded, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException(sprintf('Failed to load and decode records from cache: %s', $e->getMessage()), 1703509508, $e);
        }
        return Records::create(...array_map(static fn (array $recordDecoded) => new Record(
            RecordId::fromString($recordDecoded['id']),
            RecordTitle::fromString($recordDecoded['title']),
            RecordState::from($recordDecoded['state']),
            RecordCreationDate::fromString($recordDecoded['creationDate']),
        ), $recordsDecoded));
    }

    private function storeRecordsInCache(Records $records): void
    {
        try {
            $recordsEncoded = json_encode($records, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException(sprintf('Failed to encode records: %s', $e->getMessage()), 1703509568, $e);
        }
        $this->recordsCache->set(self::RECORDS_CACHE_ENTRY_ID, $recordsEncoded);
    }
}
