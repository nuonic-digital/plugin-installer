<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Struct;

use Shopware\Core\Framework\Struct\Struct;

class PackageIndexEntry extends Struct
{
    public function __construct(
        public string $packageName,
        public string $repositoryUrl,
        public string $ref,
    ) {
    }
}
