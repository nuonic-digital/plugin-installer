<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Action;

use League\Flysystem\FilesystemOperator;
use NuonicPluginInstaller\Config\ConfigValue;
use NuonicPluginInstaller\Config\PluginConfigService;
use NuonicPluginInstaller\Exception\IndexLoadFailedException;
use NuonicPluginInstaller\Service\IndexFileService;
use Shopware\Core\System\SystemConfig\Exception\InvalidSettingValueException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoadIndexAction
{
    private const FALLBACK_PACKAGE_SOURCE = 'https://raw.githubusercontent.com/nuonic-digital/plugin-data/refs/heads/dev/shopware_extensions_v1.json';

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

        $response = $this->httpClient->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            throw new IndexLoadFailedException();
        }


        $this->filesystem->writeStream(IndexFileService::FILE_NAME, $this->httpClient->stream($response));
    }
}
