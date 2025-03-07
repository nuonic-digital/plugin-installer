<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\Action;

use Composer\Semver\Semver;
use NuonicPluginInstaller\Service\IndexFileServiceInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class LoadPluginAction
{
	public function __construct(
		private readonly EntityRepository $availableOpensourcePluginRepository,
		private readonly IndexFileServiceInterface $indexFileService
	) {
		// This is a constructor
	}

	public function execute(string $packageName)
	{
		$packageInformation = $this->indexFileService->getPackageInformation($packageName);

		if ($packageInformation === null) {
			return;
		}

		$repositoryUrl = $packageInformation->repositoryUrl;
		$ref = $packageInformation->ref;

		$packagistData = $this->getPackagistData($ref);

		$version = $this->findSuitableVersion($packagistData);


		$plugin = $this->availableOpensourcePluginRepository->search(
			(new Criteria())->addFilter(new EqualsFilter('packageName', $packageName)),
			\Shopware\Core\Framework\Context::createDefaultContext()
		)->first();

		$id = $plugin?->getId() ?? Uuid::randomHex();
	}

	private function findSuitableVersion(array $packagistData): string
	{
		$test = 1;
		return "";
	}

	private function getPackagistData(string $ref)
	{
		$packagistData = file_get_contents($ref);
		$packagistData = json_decode($packagistData, true);
		return $packagistData;
	}

	private function checkVersionConstraint(string $constraint, string $version): bool
	{
		return Semver::satisfies($version, $constraint);
	}
}
