<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Infrastructure\Handler;

use NuonicPluginInstaller\Action\LoadPluginAction;
use NuonicPluginInstaller\Infrastructure\Message\LoadSinglePluginInfoMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: LoadSinglePluginInfoMessage::class)]
readonly class LoadSinglePluginInfoHandler
{
    public function __construct(
        private LoadPluginAction $loadPluginAction,
    ) {
    }

    public function __invoke(LoadSinglePluginInfoMessage $message): void
    {
        $this->loadPluginAction->execute($message->package, $message->now);
    }
}
