<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\ScheduledTask\LoadIndex;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class LoadIndexScheduledTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'nuonic_plugin_installer.load_index';
    }

    public static function getDefaultInterval(): int
    {
        return 1_800;
    }
}
