<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class AvailableOpensourcePluginEntity extends Entity
{
	use EntityIdTrait;

	/**
	 * @var string
	 */
	protected string $name;

	/**
	 * @var string
	 */
	protected string $description;

	/**
	 * @var string
	 */
	protected string $manufacturer;

	/**
	 * @var string
	 */
	protected string $manufacturerLink;

	/**
	 * @var string|null
	 */
	protected ?string $icon;

	/**
	 * @var array|null
	 */
	protected ?array $images;

	/**
	 * @var string
	 */
	protected string $license;

	/**
	 * @var string
	 */
	protected string $link;

	/**
	 * @var string
	 */
	protected string $availableVersion;

	/**
	 * @var bool
	 */
	protected bool $isInstalled;

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

	public function getManufacturer(): string
	{
		return $this->manufacturer;
	}

	public function setManufacturer(string $manufacturer): void
	{
		$this->manufacturer = $manufacturer;
	}

	public function getManufacturerLink(): string
	{
		return $this->manufacturerLink;
	}

	public function setManufacturerLink(string $manufacturerLink): void
	{
		$this->manufacturerLink = $manufacturerLink;
	}

	public function getIcon(): ?string
	{
		return $this->icon;
	}

	public function setIcon(?string $icon): void
	{
		$this->icon = $icon;
	}

	public function getImages(): ?array
	{
		return $this->images;
	}

	public function setImages(?array $images): void
	{
		$this->images = $images;
	}

	public function getLicense(): string
	{
		return $this->license;
	}

	public function setLicense(string $license): void
	{
		$this->license = $license;
	}

	public function getLink(): string
	{
		return $this->link;
	}

	public function setLink(string $link): void
	{
		$this->link = $link;
	}

	public function getAvailableVersion(): string
	{
		return $this->availableVersion;
	}

	public function setAvailableVersion(string $availableVersion): void
	{
		$this->availableVersion = $availableVersion;
	}

	public function isInstalled(): bool
	{
		return $this->isInstalled;
	}

	public function setIsInstalled(bool $isInstalled): void
	{
		$this->isInstalled = $isInstalled;
	}

	public function getApiAlias(): string
	{
		return 'available_opensource_plugin';
	}
}
