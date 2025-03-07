<?php

declare(strict_types=1);

namespace NuonicPluginInstaller;

use Doctrine\DBAL\Connection;
use NuonicPluginInstaller\Action\LoadIndexAction;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class NuonicPluginInstaller extends Plugin
{
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);
        if ($context->keepUserData()) {
            return;
        }
        $connection = $this->container->get(Connection::class);

        $connection->executeStatement('DROP TABLE IF EXISTS nuonic_available_opensource_plugin_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS nuonic_available_opensource_plugin');
    }

    public function postInstall(InstallContext $installContext): void
    {
        if ($action = $this->container->get(LoadIndexAction::class)) {
            $action->execute();
        }
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        if ($action = $this->container->get(LoadIndexAction::class)) {
            $action->execute();
        }
    }
}
