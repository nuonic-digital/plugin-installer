<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\Aggregate\AvailableOpensourcePluginTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<AvailableOpensourcePluginTranslationEntity>
 */
class AvailableOpensourcePluginTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AvailableOpensourcePluginTranslationEntity::class;
    }
}
