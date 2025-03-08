<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Service;

use NuonicPluginInstaller\Exception\MalformedIndexException;
use NuonicPluginInstaller\Struct\PackageIndexEntry;

interface IndexFileServiceInterface
{
    /** @throws MalformedIndexException */
    public function getPackageInformation(string $packageName): ?PackageIndexEntry;

    /**
     * @return PackageIndexEntry[]
     *
     * @throws MalformedIndexException
     */
    public function listPackages(): array;

    public function getLastModifiedAt(): ?\DateTimeInterface;
}
