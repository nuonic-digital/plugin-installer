<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\Aggregate\AvailableOpensourcePluginTranslation;

use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class AvailableOpensourcePluginTranslationEntity extends TranslationEntity
{
    protected string $nuonicAvailableOpensourcePluginId;

    protected string $name;

    protected string $description;

    protected AvailableOpensourcePluginEntity $nuonicAvailableOpensourcePlugin;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getNuonicAvailableOpensourcePluginId(): string
    {
        return $this->nuonicAvailableOpensourcePluginId;
    }

    public function setNuonicAvailableOpensourcePluginId(string $nuonicAvailableOpensourcePluginId): void
    {
        $this->nuonicAvailableOpensourcePluginId = $nuonicAvailableOpensourcePluginId;
    }

    public function getNuonicAvailableOpensourcePlugin(): AvailableOpensourcePluginEntity
    {
        return $this->nuonicAvailableOpensourcePlugin;
    }

    public function setNuonicAvailableOpensourcePlugin(AvailableOpensourcePluginEntity $nuonicAvailableOpensourcePlugin): void
    {
        $this->nuonicAvailableOpensourcePlugin = $nuonicAvailableOpensourcePlugin;
    }
}
