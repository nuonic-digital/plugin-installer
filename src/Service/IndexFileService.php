<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use NuonicPluginInstaller\Exception\MalformedIndexException;
use NuonicPluginInstaller\Struct\PackageIndexEntry;

class IndexFileService implements IndexFileServiceInterface
{
    public const FILE_NAME = 'shopware-extension-index.json';

    /** @var array<string, PackageIndexEntry> */
    private array $packageInformation = [];

    private \DateTimeInterface $generatedAt;

    public function __construct(
        private readonly FilesystemOperator $filesystem,
    ) {
    }

    public function getPackageInformation(string $packageName): ?PackageIndexEntry
    {
        if (!isset($this->packageInformation[$packageName])) {
            $this->loadPluginInformationFromFile();
        }

        return $this->packageInformation[$packageName];
    }

    public function listPackages(): array
    {
        if (empty($this->packageInformation)) {
            $this->loadPluginInformationFromFile();
        }

        return $this->packageInformation;
    }

    public function getGeneratedAt(): \DateTimeInterface
    {
        if (empty($this->generatedAt)) {
            $this->loadPluginInformationFromFile();
        }

        return $this->generatedAt;
    }

    public function getLastModifiedAt(): ?\DateTimeInterface
    {
        try {
            return new \DateTime('@'.$this->filesystem->lastModified(self::FILE_NAME));
        } catch (FilesystemException|\DateMalformedStringException $e) {
            return null;
        }
    }

    /**
     * @throws MalformedIndexException
     */
    private function loadPluginInformationFromFile(): void
    {
        try {
            $jsonData = $this->filesystem->read(self::FILE_NAME);
            $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (FilesystemException $e) {
            // if the file does not exist, do not build an index
            return;
        } catch (\JsonException $e) {
            throw new MalformedIndexException(sprintf('JSON decoding failed: %s', $e->getMessage()), $e->getCode(), $e);
        }

        if (!isset($data['extensions']) || !is_array($data['extensions'])) {
            throw new MalformedIndexException('Package information is missing.');
        }

        if (!isset($data['generatedAt'])) {
            throw new MalformedIndexException('Metadata is missing.');
        }

        $this->generatedAt = new \DateTime('@'.$data['generatedAt']);

        foreach ($data['extensions'] as $package => $packageInfo) {
            if (!isset($packageInfo['repositoryUrl'], $packageInfo['ref'], $packageInfo['latestCommitTime'])) {
                throw new MalformedIndexException(sprintf('Package information is missing required fields for package: %s', $package));
            }

            $this->packageInformation[$package] = new PackageIndexEntry(
                packageName: $package,
                repositoryUrl: $packageInfo['repositoryUrl'],
                ref: $packageInfo['ref'],
                additionalMetadataExists: $packageInfo['additionalMetadataExists'] ?? false,
                latestCommitTime: new \DateTime('@'.$packageInfo['latestCommitTime']),
            );
        }
    }
}
