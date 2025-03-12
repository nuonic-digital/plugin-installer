<?php

declare(strict_types=1);

namespace NuonicPluginInstaller;

use Doctrine\DBAL\Connection;
use NuonicPluginInstaller\Action\RefreshAction;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class NuonicPluginInstaller extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $locator = new FileLocator('Resources/config');

        $resolver = new LoaderResolver([
            new YamlFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
        ]);

        $configLoader = new DelegatingLoader($resolver);

        $confDir = rtrim($this->getPath(), '/').'/Resources/config';

        $configLoader->load($confDir.'/{packages}/*.yaml', 'glob');
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $connection = $this->container->get(Connection::class);

        $connection->executeStatement('DROP TABLE IF EXISTS nuonic_available_opensource_plugin_translation');
        $connection->executeStatement('DROP TABLE IF EXISTS nuonic_available_opensource_plugin');
    }

    public function postInstall(InstallContext $installContext): void
    {
        $this->container->get(RefreshAction::class)->execute($installContext->getContext());
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        $this->container->get(RefreshAction::class)->execute($updateContext->getContext());
    }
}
