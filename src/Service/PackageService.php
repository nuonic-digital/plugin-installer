<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Service;

use Composer\IO\NullIO;
use NuonicPluginInstaller\Struct\PackageVersion;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\Composer\CommandExecutor;
use Shopware\Core\Framework\Plugin\PluginService;

class PackageService
{
    public function __construct(
        private CommandExecutor $executor,
        private PluginService $pluginService,
    ) {
    }

    public function install(PackageVersion $packageVersion, Context $context): void
    {
        $this->executor->require(
            sprintf('%s:%s', $packageVersion->packageName, $packageVersion->packageVersion),
            'NuonicPluginInstaller'
        );

        $this->pluginService->refreshPlugins($context, new NullIO());
    }

    public function uninstall(string $packageName, Context $context): void
    {
        $this->executor->remove(
            $packageName,
            'NuonicPluginInstaller'
        );

        $this->pluginService->refreshPlugins($context, new NullIO());
    }
}
