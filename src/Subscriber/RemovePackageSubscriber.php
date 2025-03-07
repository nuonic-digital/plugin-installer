<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Subscriber;

use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginCollection;
use NuonicPluginInstaller\Service\PackageService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Event\PluginPostUninstallEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class RemovePackageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PluginPostUninstallEvent::class => [
                ['onPluginUninstall', -5000],
            ],
        ];
    }

    /**
     * @param EntityRepository<AvailableOpensourcePluginCollection> $openSourcePluginRepository
     */
    public function __construct(
        private PackageService $packageService,
        private EntityRepository $openSourcePluginRepository,
    ) {
    }

    public function onPluginUninstall(PluginPostUninstallEvent $event): void
    {
        $context = $event->getContext()->getContext();

        $this->packageService->uninstall(
            $event->getPlugin()->getComposerName(),
            $context
        );

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('packageName', $event->getPlugin()->getComposerName()));

        $openSourcePlugin = $this->openSourcePluginRepository->search(
            $criteria,
            $context
        )->first();

        if (is_null($openSourcePlugin)) {
            return;
        }

        $this->openSourcePluginRepository->update([[
            'id' => $openSourcePlugin->getId(),
            'pluginId' => null,
        ]], $context);
    }
}
