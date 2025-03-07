<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class AvailableOpensourcePluginCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AvailableOpensourcePluginEntity::class;
    }
}
