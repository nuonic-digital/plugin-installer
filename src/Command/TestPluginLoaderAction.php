<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Command;

use NuonicPluginInstaller\Action\LoadPluginAction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Command name
#[AsCommand(name: 'nuonic:plugin-installer:plugin:load')]
class TestPluginLoaderAction extends Command
{
    public function __construct(
        private LoadPluginAction $loadPluginAction,
    ) {
        parent::__construct();
    }

    // Provides a description, printed out in bin/console
    protected function configure(): void
    {
        $this->setDescription('Tests LoadPluginAction');
        $this->addOption('packageName', 'p', InputOption::VALUE_REQUIRED, 'Package name to load');
    }

    // Actual code executed in the command
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loadPluginAction->execute($input->getOption('packageName'), new \DateTime());

        return Command::SUCCESS;
    }
}
