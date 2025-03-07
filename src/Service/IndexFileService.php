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

    /**
     * @return void
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

        foreach ($data as $package => $packageInfo) {
            if (!isset($packageInfo['repositoryUrl'], $packageInfo['ref'])) {
                throw new MalformedIndexException(sprintf('Package information is missing required fields for package: %s', $package));
            }

            $this->packageInformation[$package] = new PackageIndexEntry(
                repositoryUrl: $packageInfo['repositoryUrl'],
                ref: $packageInfo['ref'],
            );
        }
    }
}
