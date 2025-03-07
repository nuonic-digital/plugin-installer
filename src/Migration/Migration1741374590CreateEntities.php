<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1741374590CreateEntities extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1741374590;
    }

    public function update(Connection $connection): void
    {

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `nuonic_available_opensource_plugin` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NOT NULL,
                `manufacturer` VARCHAR(255) NOT NULL,
                `manufacturer_link` VARCHAR(255) NOT NULL,
                `icon` VARCHAR(255),
                `images` JSON,
                `license` VARCHAR(255) NOT NULL,
                `link` VARCHAR(255) NOT NULL,
                `available_version` VARCHAR(255) NOT NULL,
                `is_installed` TINYINT(1) DEFAULT(0) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3),
                PRIMARY KEY (`id`)
            )
        ');

        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `nuonic_available_opensource_plugin_translation` (
                `nuonic_available_opensource_plugin_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` LONGTEXT NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3),
                PRIMARY KEY (`nuonic_available_opensource_plugin_id`, `language_id`),
                CONSTRAINT `fk.aop_translation.available_opensource_plugin_id` FOREIGN KEY (`nuonic_available_opensource_plugin_id`)
                    REFERENCES `nuonic_available_opensource_plugin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.aop_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
        ');
    }
}
