<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('core')]
class Migration1741421700NuonicAvailableOpensourcePlugin extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1741421700;
    }

    public function update(Connection $connection): void
    {
        $query = <<<'SQL'
            ALTER TABLE nuonic_available_opensource_plugin ADD last_seen_at DATE NOT NULL, ADD last_commit_time DATE NOT NULL;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // Add destructive update if necessary
    }
}
