<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Service;

use NuonicPluginInstaller\Struct\PackageIndexEntry;

interface IndexFileServiceInterface
{
    public function getPackageInformation(string $packageName): PackageIndexEntry;

    /**
     * @return PackageIndexEntry[]
     */
    public function listPackages(): array;
}
