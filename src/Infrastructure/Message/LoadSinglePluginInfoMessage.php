<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Infrastructure\Message;

use NuonicPluginInstaller\Struct\PackageIndexEntry;

readonly class LoadSinglePluginInfoMessage
{
    public function __construct(
        public PackageIndexEntry $package,
    ) {
    }
}
