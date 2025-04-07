<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Action;

use Composer\Semver\Semver;
use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginCollection;
use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginEntity;
use NuonicPluginInstaller\Service\IndexFileServiceInterface;
use NuonicPluginInstaller\Struct\PackageIndexEntry;
use Shopware\Administration\Notification\NotificationCollection;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Language\LanguageCollection;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class LoadPluginAction
{
    private const LANG_EN_GB = 'en-GB';
    private const LANG_DE_DE = 'de-DE';

    /**
     * @param EntityRepository<AvailableOpensourcePluginCollection> $availableOpensourcePluginRepository
     * @param EntityRepository<LanguageCollection>                  $languageRepository
     * @param EntityRepository<NotificationCollection>              $notificationRepository
     * @param EntityRepository<PluginCollection>                    $pluginRepository
     */
    public function __construct(
        private EntityRepository $availableOpensourcePluginRepository,
        private IndexFileServiceInterface $indexFileService,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private EntityRepository $languageRepository,
        private EntityRepository $notificationRepository,
        private EntityRepository $pluginRepository,
        private string $shopwareVersion,
    ) {
    }

    public function execute(string|PackageIndexEntry $packageInformation, \DateTimeInterface $now, Context $context): void
    {
        if (!$packageInformation instanceof PackageIndexEntry) {
            $packageInformation = $this->indexFileService->getPackageInformation($packageInformation);
        }

        if (is_null($packageInformation)) {
            return;
        }

        $repositoryUrl = $packageInformation->repositoryUrl;
        $ref = $packageInformation->ref;

        $packagistData = $this->getPackagistData($ref);

        $version = $this->findSuitableVersion($packagistData);

        if (is_null($version)) {
            return;
        }

        $githubUrl = str_replace('.git', '', $version['source']['url']);
        $githubUrl = str_replace('//refs', '/refs', str_replace('https://github.com/', 'https://raw.githubusercontent.com/', $githubUrl.'/refs/heads/main/'));
        $mainExtensionYmlUrl = $githubUrl.'.shopware-extension.yml';

        $pluginData = [
            'icon' => $this->checkIconUrl(
                $githubUrl.'/'.($version['extra']['plugin-icon'] ?? 'src/Resources/config/plugin.png')
            ),
            ...$packageInformation->additionalMetadataExists ? $this->fetchExtensionYmlData($mainExtensionYmlUrl, $githubUrl) : [],
            'packageName' => $packageInformation->packageName,
            'manufacturer' => implode(', ', array_column($version['authors'], 'name')),
            'manufacturerLink' => array_values($version['extra']['manufacturerLink'] ?? ['https://github.com'])[0], // get first translation - TODO: fix
            'license' => $version['license'][0] ?? 'UNKNOWN',
            'link' => $repositoryUrl,
            'availableVersion' => $version['version'],
            'packagistDownloads' => $packagistData['package']['downloads']['total'] ?? 0,
        ];

        if ('' === trim($pluginData['manufacturer'])) {
            $pluginData['manufacturer'] = 'UNKNOWN';
        }

        if (!isset($pluginData['description'][self::LANG_EN_GB])) {
            $pluginData['description'][self::LANG_EN_GB] = !empty($version['extra']['description'][self::LANG_EN_GB])
                ? $version['extra']['description'][self::LANG_EN_GB]
                : $packageInformation->packageName;
        }

        if (!isset($pluginData['description'][self::LANG_DE_DE])) {
            $pluginData['description'][self::LANG_DE_DE] = !empty($version['extra']['description'][self::LANG_DE_DE])
                ? $version['extra']['description'][self::LANG_DE_DE]
                : $packageInformation->packageName;
        }

        $pluginData['name'][self::LANG_DE_DE] = $version['extra']['label'][self::LANG_DE_DE] ?? $packageInformation->packageName;
        $pluginData['name'][self::LANG_EN_GB] = $version['extra']['label'][self::LANG_EN_GB] ?? $packageInformation->packageName;

        $pluginData['translations'] = [];

        if ($langIdDe = $this->loadLanguageId(self::LANG_DE_DE)) {
            $pluginData['translations'][] = [
                'languageId' => $langIdDe,
                'name' => $pluginData['name'][self::LANG_DE_DE],
                'description' => $pluginData['description'][self::LANG_DE_DE],
            ];
        }

        if ($langIdEn = $this->loadLanguageId(self::LANG_EN_GB)) {
            $pluginData['translations'][] = [
                'languageId' => $langIdEn,
                'name' => $pluginData['name'][self::LANG_EN_GB],
                'description' => $pluginData['description'][self::LANG_EN_GB],
            ];
        }

        unset($pluginData['description'], $pluginData['name']);

        /** @var AvailableOpensourcePluginEntity|null $plugin */
        $plugin = $this->availableOpensourcePluginRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('packageName', $packageInformation->packageName))
                ->addAssociation('plugin'),
            $context
        )->first();

        $id = $plugin?->getId() ?? Uuid::randomHex();

        $pluginData['id'] = $id;
        $pluginData['lastSeenAt'] = $now;
        $pluginData['lastCommitTime'] = $packageInformation->latestCommitTime;

        if (is_null($plugin) || is_null($plugin->getPluginId())) {
            $pluginData['pluginId'] = $this->determineExistingPluginId($pluginData, $context);
        }

        $this->availableOpensourcePluginRepository->upsert([$pluginData], $context);

        if (!is_null($plugin)) {
            $this->handleUpdateNotification(
                $plugin,
                $pluginData,
                $context
            );
        }
    }

    private function fetchExtensionYmlData(string $mainExtensionYmlUrl, string $githubUrl): array
    {
        $extensionYmlResponse = $this->httpClient->request('GET', $mainExtensionYmlUrl);
        if (200 !== $extensionYmlResponse->getStatusCode()) {
            return [];
        }

        $extensionYmlData = Yaml::parse($extensionYmlResponse->getContent());

        $pluginData = [];
        if (isset($extensionYmlData['store'])) {
            if (isset($extensionYmlData['store']['icon'])) {
                $pluginData['icon'] = $githubUrl.'/'.$extensionYmlData['store']['icon'];
            }
            if (isset($extensionYmlData['store']['images'])) {
                foreach ($extensionYmlData['store']['images'] as $images) {
                    $pluginData['images'][] = $githubUrl.'/'.$images['file'];
                }
            }
            if (isset($extensionYmlData['store']['description']['en'])
                && !str_starts_with($extensionYmlData['store']['description']['en'], 'file:')
            ) {
                $pluginData['description'][self::LANG_EN_GB] = $extensionYmlData['store']['description']['en'];
            }
            if (isset($extensionYmlData['store']['description']['de'])
                && !str_starts_with($extensionYmlData['store']['description']['de'], 'file:')
            ) {
                $pluginData['description'][self::LANG_DE_DE] = $extensionYmlData['store']['description']['de'];
            }
        }

        return $pluginData;
    }

    private function checkIconUrl(string $iconUrl): ?string
    {
        $response = $this->httpClient->request('GET', $iconUrl);

        return 200 === $response->getStatusCode() && strlen($response->getContent()) > 10 ? $iconUrl : null;
    }

    private function findSuitableVersion(array $packagistData): ?array
    {
        $versions = $packagistData['package']['versions'];
        \ksort($versions, SORT_NATURAL);

        $versions = \array_reverse($versions, true);

        foreach ($versions as $version => $versionData) {
            if (\str_contains($version, 'dev') || \str_contains($version, 'alpha') || \str_contains($version, 'beta') || \str_contains($version, 'rc') || \str_contains($version, 'main')) {
                continue;
            }

            if (!isset($versionData['require']['shopware/core'])) {
                continue;
            }

            if ($this->checkVersionConstraint($versionData['require']['shopware/core'], $this->shopwareVersion)) {
                if (isset($versionData['require']['php']) && !$this->checkVersionConstraint($versionData['require']['php'], PHP_VERSION)) {
                    continue;
                }

                return $versionData;
            }
        }

        return null;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getPackagistData(string $ref)
    {
        $response = $this->httpClient->request('GET', $ref);

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $packagistData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $packagistData;
    }

    private function checkVersionConstraint(string $constraint, string $version): bool
    {
        return Semver::satisfies($version, $constraint);
    }

    private function loadLanguageId(string $locale): ?string
    {
        $key = 'nuonic-plugin-locale-'.$locale;
        $context = Context::createDefaultContext();

        $value = $this->cache->get($key, function (ItemInterface $item) use ($locale, $context) {
            $languageCriteria = new Criteria();
            $languageCriteria->addFilter(new EqualsFilter('locale.code', $locale));
            $languageCriteria->setLimit(1);

            $languageId = $this->languageRepository->searchIds($languageCriteria, $context)->firstId();

            return CacheValueCompressor::compress($languageId);
        });

        return CacheValueCompressor::uncompress($value);
    }

    private function handleUpdateNotification(
        AvailableOpensourcePluginEntity $plugin,
        array $pluginData,
        Context $context,
    ): void {
        $swPlugin = $plugin->getPlugin() ?? (isset($pluginData['pluginId']) ? $this->pluginRepository->search(
            new Criteria([$pluginData['pluginId']]),
            $context
        )->first() : null);

        if (is_null($swPlugin) || is_null($swPlugin->getInstalledAt())) {
            return;
        }

        if (1 !== version_compare($pluginData['availableVersion'], $swPlugin->getVersion())) {
            return;
        }

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($swPlugin): void {
            $this->notificationRepository->create([[
                'id' => Uuid::randomHex(),
                'status' => 'info',
                'message' => 'Update available for plugin '.$swPlugin->getName(),
                'requiredPrivileges' => [],
            ]], $context);
        });
    }

    private function determineExistingPluginId(array $pluginData, Context $context): ?string
    {
        return $this->pluginRepository->searchIds(
            (new Criteria())->addFilter(new EqualsFilter('composerName', $pluginData['packageName'])),
            $context
        )->firstId();
    }
}
