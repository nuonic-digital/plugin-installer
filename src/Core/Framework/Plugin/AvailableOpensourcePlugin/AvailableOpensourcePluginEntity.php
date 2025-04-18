<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Plugin\PluginEntity;

class AvailableOpensourcePluginEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected string $description;

    protected string $packageName;

    protected string $manufacturer;

    protected string $manufacturerLink;

    protected ?string $icon;

    /** @var MediaEntity[]|null */
    protected ?array $images;

    protected string $license;

    protected string $link;

    protected string $availableVersion;

    protected int $packagistDownloads;

    protected \DateTimeInterface $lastSeenAt;

    protected \DateTimeInterface $lastCommitTime;

    protected ?string $pluginId;

    protected ?PluginEntity $plugin;

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

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function setPackageName(string $packageName): void
    {
        $this->packageName = $packageName;
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

    /**
     * @return MediaEntity[]|null
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    /**
     * @param MediaEntity[]|null $images
     */
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

    public function getPackagistDownloads(): int
    {
        return $this->packagistDownloads;
    }

    public function setPackagistDownloads(int $packagistDownloads): void
    {
        $this->packagistDownloads = $packagistDownloads;
    }

    public function getPluginId(): ?string
    {
        return $this->pluginId;
    }

    public function setPluginId(?string $pluginId): void
    {
        $this->pluginId = $pluginId;
    }

    public function getPlugin(): ?PluginEntity
    {
        return $this->plugin;
    }

    public function setPlugin(?PluginEntity $plugin): void
    {
        $this->plugin = $plugin;
    }

    public function getLastSeenAt(): \DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(\DateTimeInterface $lastSeenAt): void
    {
        $this->lastSeenAt = $lastSeenAt;
    }

    public function getLastCommitTime(): \DateTimeInterface
    {
        return $this->lastCommitTime;
    }

    public function setLastCommitTime(\DateTimeInterface $lastCommitTime): void
    {
        $this->lastCommitTime = $lastCommitTime;
    }

    public function getApiAlias(): string
    {
        return 'available_opensource_plugin';
    }
}
