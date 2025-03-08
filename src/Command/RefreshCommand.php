<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Command;

use NuonicPluginInstaller\Action\RefreshAction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nuonic:plugin-installer:refresh', description: 'Refresh package index')]
class RefreshCommand extends Command
{
    public function __construct(
        private RefreshAction $refreshAction,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->refreshAction->execute(false);

        return Command::SUCCESS;
    }
}
