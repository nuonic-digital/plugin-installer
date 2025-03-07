<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin;

use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\Aggregate\AvailableOpensourcePluginTranslation\AvailableOpensourcePluginTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Plugin\PluginDefinition;

class AvailableOpensourcePluginDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'nuonic_available_opensource_plugin';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return AvailableOpensourcePluginCollection::class;
    }

    public function getEntityClass(): string
    {
        return AvailableOpensourcePluginEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new TranslatedField('name', 'name'))->addFlags(new Required()),
            (new TranslatedField('description', 'description'))->addFlags(new Required()),
            (new StringField('package_name', 'packageName'))->addFlags(new Required()),
            (new StringField('manufacturer', 'manufacturer'))->addFlags(new Required()),
            (new StringField('manufacturer_link', 'manufacturerLink'))->addFlags(new Required()),
            (new StringField('icon', 'icon'))->addFlags(),
            (new JsonField('images', 'images'))->addFlags(),
            (new StringField('license', 'license'))->addFlags(new Required()),
            (new StringField('link', 'link'))->addFlags(new Required()),
            (new StringField('available_version', 'availableVersion'))->addFlags(new Required()),

            (new TranslationsAssociationField(
                AvailableOpensourcePluginTranslationDefinition::class,
                'available_opensource_plugin_id'
            ))->addFlags(new ApiAware(), new Required()),

            new FkField('plugin_id', 'pluginId', PluginDefinition::class),
            new OneToOneAssociationField('plugin', 'plugin_id', 'id', PluginDefinition::class),
        ]);
    }
}
