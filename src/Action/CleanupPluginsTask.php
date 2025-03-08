<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Action;

use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

readonly class CleanupPluginsTask
{
    /**
     * @param EntityRepository<AvailableOpensourcePluginCollection> $openSourcePluginRepository
     */
    public function __construct(
        private EntityRepository $openSourcePluginRepository,
    ) {
    }

    public function execute(Context $context, \DateTimeInterface $now): void
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('pluginId', null))
            ->addFilter(new RangeFilter('lastSeenAt', [
                RangeFilter::LT => \DateTime::createFromInterface($now)
                    ->sub(new \DateInterval('PT2H'))
                    ->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]));

        $searchResult = $this->openSourcePluginRepository->search($criteria, $context);
        if (0 === $searchResult->getTotal()) {
            return;
        }

        $this->openSourcePluginRepository->delete(
            array_map(
                static fn (string $id) => ['id' => $id],
                array_values($searchResult->getIds())
            ),
            $context,
        );
    }
}
