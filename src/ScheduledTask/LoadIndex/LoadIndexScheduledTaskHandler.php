<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\ScheduledTask\LoadIndex;

use NuonicPluginInstaller\Action\LoadIndexAction;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: LoadIndexScheduledTask::class)]
class LoadIndexScheduledTaskHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $exceptionLogger,
        private readonly LoadIndexAction $loadIndexAction,
    ) {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public function run(): void
    {
        $this->loadIndexAction->execute();
    }
}
