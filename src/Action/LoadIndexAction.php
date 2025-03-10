<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Action;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use NuonicPluginInstaller\Config\ConfigValue;
use NuonicPluginInstaller\Config\PluginConfigService;
use NuonicPluginInstaller\Exception\IndexLoadFailedException;
use NuonicPluginInstaller\Exception\LoadIndexFailedException;
use NuonicPluginInstaller\Service\IndexFileService;
use Shopware\Core\System\SystemConfig\Exception\InvalidSettingValueException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class LoadIndexAction
{
    private const FALLBACK_PACKAGE_SOURCE = 'https://plugin-data.nuonic.dev/shopware_extensions_v1.json';

    public function __construct(
        private PluginConfigService $config,
        private HttpClientInterface $httpClient,
        private FilesystemOperator $filesystem,
    ) {
    }

    public function execute(): void
    {
        try {
            $url = $this->config->getString(ConfigValue::PACKAGE_SOURCE);
        } catch (InvalidSettingValueException $e) {
            $url = null;
        }
        if (is_null($url) || '' === trim($url)) {
            $url = self::FALLBACK_PACKAGE_SOURCE;
        }

        try {
            $response = $this->httpClient->request('GET', $url);

            if (200 !== $response->getStatusCode()) {
                throw new IndexLoadFailedException();
            }

            $this->filesystem->write(IndexFileService::FILE_NAME, $response->getContent());
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|FilesystemException $e) {
            throw new LoadIndexFailedException(sprintf('Failed to load index file: %s', $e->getMessage()), $e->getCode(), $e);
        }
    }
}
