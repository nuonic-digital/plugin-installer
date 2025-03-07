<?php declare(strict_types=1);

namespace NuonicPluginInstaller\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1741388682NuonicAvailableOpensourcePlugin extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1741388682;
    }

    public function update(Connection $connection): void
    {
        $query = <<<'SQL'
            ALTER TABLE nuonic_available_opensource_plugin ADD plugin_id BINARY(16) DEFAULT NULL, DROP name, DROP description, DROP is_installed, CHANGE package_name package_name VARCHAR(255) NOT NULL;

ALTER TABLE nuonic_available_opensource_plugin ADD CONSTRAINT fk.nuonic_available_opensource_plugin.plugin_id FOREIGN KEY (plugin_id) REFERENCES plugin (id) ON UPDATE CASCADE ON DELETE SET NULL;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
