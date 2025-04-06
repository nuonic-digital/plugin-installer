<?php declare(strict_types=1);

namespace NuonicPluginInstaller\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1743926883UniqueIndex extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1743926883;
    }

    public function update(Connection $connection): void
    {
        $query = <<<'SQL'
            CREATE UNIQUE INDEX nuonic_available_opensource_plugin_package_name_uindex ON nuonic_available_opensource_plugin (package_name);
            CREATE UNIQUE INDEX nuonic_available_opensource_plugin_link_uindex ON nuonic_available_opensource_plugin (link);
SQL;

        $connection->executeStatement($query);
    }
}
