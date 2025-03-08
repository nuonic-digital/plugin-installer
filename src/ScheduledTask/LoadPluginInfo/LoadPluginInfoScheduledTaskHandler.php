<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\ScheduledTask\LoadPluginInfo;

use NuonicPluginInstaller\Action\CleanupPluginsTask;
use NuonicPluginInstaller\Infrastructure\Message\LoadSinglePluginInfoMessage;
use NuonicPluginInstaller\Service\IndexFileServiceInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(handles: LoadPluginInfoScheduledTask::class)]
class LoadPluginInfoScheduledTaskHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $exceptionLogger,
        private readonly IndexFileServiceInterface $indexFileService,
        private readonly MessageBusInterface $messageBus,
        private readonly CleanupPluginsTask $cleanupPluginsTask,
    ) {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public function run(): void
    {

        $lastModified = $this->indexFileService->getLastModifiedAt();
        assert($lastModified instanceof \DateTimeInterface);

        $this->cleanupPluginsTask->execute(
            Context::createDefaultContext(),
            $lastModified,
        );

        foreach ($this->indexFileService->listPackages() as $package) {
            $this->messageBus->dispatch(new LoadSinglePluginInfoMessage(
                $package,
                $lastModified,
            ));
        }
    }
}
