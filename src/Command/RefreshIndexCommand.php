<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Command;

use NuonicPluginInstaller\Action\LoadIndexAction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nuonic:plugin-installer:index:refresh', description: 'Refresh package index')]
class RefreshIndexCommand extends Command
{
    public function __construct(
        private LoadIndexAction $loadIndexAction,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loadIndexAction->execute();

        return Command::SUCCESS;
    }
}
