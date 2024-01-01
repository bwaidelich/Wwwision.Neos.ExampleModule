<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Controller;

use DateTimeImmutable;
use Neos\Error\Messages\Message;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Wwwision\Neos\ExampleModule\RecordRepository;
use Wwwision\Neos\ExampleModule\Types\RecordCreationDate;
use Wwwision\Neos\ExampleModule\Types\RecordFilter;
use Wwwision\Neos\ExampleModule\Types\Record;
use Wwwision\Neos\ExampleModule\Types\RecordId;
use Wwwision\Neos\ExampleModule\Types\RecordPagination;
use Wwwision\Neos\ExampleModule\Types\Records;
use Wwwision\Neos\ExampleModule\Types\RecordTitle;
use Wwwision\Neos\ExampleModule\Types\RecordState;

final class ExampleModuleController extends AbstractModuleController
{
    protected $defaultViewObjectName = FusionView::class;
    private RecordFilter $filter;

    public function __construct(
        private readonly RecordRepository $recordRepository,
    ) {
        $this->filter = RecordFilter::create();
    }

    protected function initializeAction(): void
    {
        parent::initializeAction();
        if ($this->request->hasArgument('filter')) {
            /** @var array<string, string> $filterArray */
            /** @noinspection PhpUnhandledExceptionInspection */
            $filterArray = $this->request->getArgument('filter');
            $this->filter = RecordFilter::fromArray($filterArray);
        }
    }

    protected function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);
        $view->assign('filter', $this->filter);
    }

    public function indexAction(): void
    {
        $pagination = RecordPagination::forPage((int)($this->request->getHttpRequest()->getQueryParams()['page'] ?? 1));
        $this->view->assign('pagination', $pagination);
        $this->view->assign('records', $this->recordRepository->findByFilter($this->filter, $pagination));
    }

    public function addRecordAction(array $form): void
    {
        $recordId = RecordId::create();
        $this->recordRepository->add(new Record(
            $recordId,
            RecordTitle::fromString($form['title']),
            RecordState::from($form['state']),
            RecordCreationDate::fromDateTime(new DateTimeImmutable()),
        ));
        $this->addFlashMessage('LLL:flashMessage.recordAdded');
        $this->redirect('index');
    }

    public function seedRecordsAction(): void
    {
        $recordsToCreate = 100;
        $records = [];
        for ($i = 0; $i < $recordsToCreate; $i ++) {
            $records[] = new Record(
                RecordId::create(),
                RecordTitle::fromString('Dummy record #' . ($i + 1)),
                random_int(0, 100) > 30 ? RecordState::ACTIVE : RecordState::INACTIVE,
                RecordCreationDate::fromDateTime((new DateTimeImmutable('-' . random_int(0, 40) . 'days')))
            );
        }
        $this->recordRepository->addMany(Records::create(...$records));
        $this->addFlashMessage('LLL:flashMessage.dummyRecordsAdded');
        $this->redirect('index');
    }

    public function showRecordAction(string $id): void
    {
        $record = $this->getRecordOrRedirect($id);
        $this->view->assign('record', $record);
    }

    public function disableRecordAction(string $id): void
    {
        $record = $this->getRecordOrRedirect($id);
        if ($record->state === RecordState::INACTIVE) {
            $this->addFlashMessage('LLL:flashMessage.recordAlreadyDisabled', severity: Message::SEVERITY_NOTICE);
        } else {
            $this->recordRepository->update($record->with(state: RecordState::INACTIVE));
            $this->addFlashMessage('LLL:flashMessage.recordDisabled');
        }
        $this->redirect('showRecord', arguments: ['id' => $record->id->value]);
    }

    public function enableRecordAction(string $id): void
    {
        $record = $this->getRecordOrRedirect($id);
        if ($record->state === RecordState::ACTIVE) {
            $this->addFlashMessage('LLL:flashMessage.recordAlreadyEnabled', severity: Message::SEVERITY_NOTICE);
        } else {
            $this->recordRepository->update($record->with(state: RecordState::ACTIVE));
            $this->addFlashMessage('LLL:flashMessage.recordEnabled');
        }
        $this->redirect('showRecord', arguments: ['id' => $record->id->value]);
    }

    public function renameRecordAction(string $id, array $form): void
    {
        $record = $this->getRecordOrRedirect($id);
        $this->recordRepository->update($record->with(title: RecordTitle::fromString($form['newTitle'])));
        $this->addFlashMessage('LLL:flashMessage.recordRenamed');
        $this->redirect('showRecord', arguments: ['id' => $record->id->value]);
    }

    public function deleteRecordAction(string $id): void
    {
        $this->recordRepository->delete(RecordId::fromString($id));
        $this->addFlashMessage('LLL:flashMessage.recordDeleted', severity: Message::SEVERITY_NOTICE);
        $this->redirect('index');
    }

    public function deleteAllRecordsAction(): void
    {
        $this->recordRepository->deleteAll();
        $this->addFlashMessage('LLL:flashMessage.allRecordsDeleted', severity: Message::SEVERITY_NOTICE);
        $this->redirect('index');
    }


    /**
     * @throws StopActionException
     */
    private function getRecordOrRedirect(string $id): Record
    {
        $record = $this->recordRepository->findOneById(RecordId::fromString($id));
        if ($record === null) {
            $this->addFlashMessage('LLL:flashMessage.recordNotFound', severity: Message::SEVERITY_ERROR);
            $this->redirect('index');
        }
        return $record;
    }
}
