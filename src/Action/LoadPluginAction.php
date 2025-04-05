<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Action;

use Composer\Semver\Semver;
use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginEntity;
use NuonicPluginInstaller\Service\IndexFileServiceInterface;
use NuonicPluginInstaller\Struct\PackageIndexEntry;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
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
    public function __construct(
        private EntityRepository $availableOpensourcePluginRepository,
        private IndexFileServiceInterface $indexFileService,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private EntityRepository $languageRepository,
        private string $shopwareVersion,
    ) {
    }

    public function execute(string|PackageIndexEntry $packageInformation, \DateTimeInterface $now): void
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
        ];

        if ('' === trim($pluginData['manufacturer'])) {
            $pluginData['manufacturer'] = 'UNKNOWN';
        }

        if (!isset($pluginData['description']['en-GB'])) {
            $pluginData['description']['en-GB'] = !empty($version['extra']['description']['en-GB'])
                ? $version['extra']['description']['en-GB']
                : $packageInformation->packageName;
        }

        if (!isset($pluginData['description']['de-DE'])) {
            $pluginData['description']['de-DE'] = !empty($version['extra']['description']['de-DE'])
                ? $version['extra']['description']['de-DE']
                : $packageInformation->packageName;
        }

        $pluginData['name']['de-DE'] = $version['extra']['label']['de-DE'] ?? $packageInformation->packageName;
        $pluginData['name']['en-GB'] = $version['extra']['label']['en-GB'] ?? $packageInformation->packageName;

        $langIdDe = $this->loadLanguageId('de-DE');
        $langIdEn = $this->loadLanguageId('en-GB');

        $pluginData['translations'] = [
            ['languageId' => $langIdDe, 'name' => $pluginData['name']['de-DE'], 'description' => $pluginData['description']['de-DE']],
            ['languageId' => $langIdEn, 'name' => $pluginData['name']['en-GB'], 'description' => $pluginData['description']['en-GB']],
        ];

        unset($pluginData['description'], $pluginData['name']);

        /** @var AvailableOpensourcePluginEntity|null $plugin */
        $plugin = $this->availableOpensourcePluginRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('packageName', $packageInformation->packageName)),
            Context::createDefaultContext()
        )->first();

        $id = $plugin?->getId() ?? Uuid::randomHex();

        $pluginData['id'] = $id;
        $pluginData['lastSeenAt'] = $now;
        $pluginData['lastCommitTime'] = $packageInformation->latestCommitTime;

        $this->availableOpensourcePluginRepository->upsert([$pluginData], Context::createDefaultContext());
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
                $pluginData['description']['en-GB'] = $extensionYmlData['store']['description']['en'];
            }
            if (isset($extensionYmlData['store']['description']['de'])
                && !str_starts_with($extensionYmlData['store']['description']['de'], 'file:')
            ) {
                $pluginData['description']['de-DE'] = $extensionYmlData['store']['description']['de'];
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

    private function loadLanguageId(string $locale): string
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
}
