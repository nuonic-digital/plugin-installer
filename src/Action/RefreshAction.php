<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Action;

use NuonicPluginInstaller\Infrastructure\Message\LoadSinglePluginInfoMessage;
use NuonicPluginInstaller\Service\IndexFileServiceInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class RefreshAction
{
    public function __construct(
        private LoadIndexAction $loadIndexAction,
        private LoadPluginAction $loadPluginAction,
        private CleanupPluginsTask $cleanupPluginsTask,
        private IndexFileServiceInterface $indexFileService,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function execute(Context $context, bool $async = true): void
    {
        $now = new \DateTime();
        $this->loadIndexAction->execute();

        $this->cleanupPluginsTask->execute(
            $context,
            $now,
        );

        foreach ($this->indexFileService->listPackages() as $package) {
            if ($async) {
                $this->messageBus->dispatch(new LoadSinglePluginInfoMessage(
                    $package,
                    $now,
                ));
                continue;
            }

            $this->loadPluginAction->execute($package, new \DateTime());
        }
    }
}
