<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Subscriber;

use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginCollection;
use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Event\PluginPostInstallEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class MarkInstalledPluginSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PluginPostInstallEvent::class => [
                ['onPluginPostInstall', -5000],
            ],
        ];
    }

    /**
     * @param EntityRepository<AvailableOpensourcePluginCollection> $openSourcePluginRepository
     */
    public function __construct(
        private EntityRepository $openSourcePluginRepository,
    ) {
    }

    public function onPluginPostInstall(PluginPostInstallEvent $event): void
    {
        $context = $event->getContext()->getContext();

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('packageName', $event->getPlugin()->getComposerName()));

        /** @var AvailableOpensourcePluginEntity|null $openSourcePlugin */
        $openSourcePlugin = $this->openSourcePluginRepository->search(
            $criteria,
            $context
        )->first();

        if (is_null($openSourcePlugin)) {
            return;
        }

        $this->openSourcePluginRepository->update([[
            'id' => $openSourcePlugin->getId(),
            'pluginId' => $event->getPlugin()->getId(),
        ]], $context);
    }
}
