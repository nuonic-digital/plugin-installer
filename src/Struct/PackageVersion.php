<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Struct;

use Shopware\Core\Framework\Struct\Struct;

class PackageVersion extends Struct
{
    public function __construct(
        public string $packageName,
        public string $packageVersion,
    ) {
    }
}
