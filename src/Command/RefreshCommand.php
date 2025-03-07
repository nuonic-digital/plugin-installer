<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Command;

use NuonicPluginInstaller\Action\LoadIndexAction;
use NuonicPluginInstaller\Action\LoadPluginAction;
use NuonicPluginInstaller\Service\IndexFileServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nuonic:plugin-installer:refresh', description: 'Refresh package index')]
class RefreshCommand extends Command
{
    public function __construct(
        private LoadIndexAction $loadIndexAction,
        private LoadPluginAction $loadPluginAction,
        private IndexFileServiceInterface $indexFileService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loadIndexAction->execute();

        foreach ($this->indexFileService->listPackages() as $package) {
            $this->loadPluginAction->execute($package);
        }

        return Command::SUCCESS;
    }
}
