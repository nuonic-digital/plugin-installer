<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\Aggregate\AvailableOpensourcePluginTranslation;

use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class AvailableOpensourcePluginTranslationEntity extends TranslationEntity
{
	protected string $availableOpensourcePluginId;

	protected string $name;

	protected string $description;

	protected AvailableOpensourcePluginEntity $availableOpensourcePlugin;

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

	public function getAvailableOpensourcePluginId(): string
	{
		return $this->availableOpensourcePluginId;
	}

	public function setAvailableOpensourcePluginId(string $availableOpensourcePluginId): void
	{
		$this->availableOpensourcePluginId = $availableOpensourcePluginId;
	}

	public function getAvailableOpensourcePlugin(): AvailableOpensourcePluginEntity
	{
		return $this->availableOpensourcePlugin;
	}

	public function setAvailableOpensourcePlugin(AvailableOpensourcePluginEntity $availableOpensourcePlugin): void
	{
		$this->availableOpensourcePlugin = $availableOpensourcePlugin;
	}
}
