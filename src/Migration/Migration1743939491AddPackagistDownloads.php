<?php declare(strict_types=1);

namespace NuonicPluginInstaller\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1743939491AddPackagistDownloads extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743939491;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('ALTER TABLE `nuonic_available_opensource_plugin` ADD COLUMN `packagist_downloads` INT(11) NOT NULL AFTER `available_version`');
    }
}
