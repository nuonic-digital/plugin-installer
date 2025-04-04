<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1741375493AddPackageName extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1741375493;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `nuonic_available_opensource_plugin` ADD COLUMN `package_name` VARCHAR(255) NULL AFTER `id`');
    }
}
