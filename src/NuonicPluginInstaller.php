<?php

declare(strict_types=1);

namespace NuonicPluginInstaller;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

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
}
