<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\ScheduledTask\LoadPluginInfo;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class LoadPluginInfoScheduledTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'nuonic_plugin_installer.load_plugin_info';
    }

    public static function getDefaultInterval(): int
    {
        return 1_800;
    }
}
