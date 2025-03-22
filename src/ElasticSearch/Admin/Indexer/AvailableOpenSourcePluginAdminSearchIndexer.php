<?php

declare(strict_types=1);

namespace NuonicPluginInstaller\ElasticSearch\Admin\Indexer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use NuonicPluginInstaller\Core\Framework\Plugin\AvailableOpensourcePlugin\AvailableOpensourcePluginDefinition;
use OpenSearchDSL\Query\Compound\BoolQuery;
use OpenSearchDSL\Query\FullText\SimpleQueryStringQuery;
use OpenSearchDSL\Search;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IterableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Elasticsearch\Admin\Indexer\AbstractAdminIndexer;

class AvailableOpenSourcePluginAdminSearchIndexer extends AbstractAdminIndexer
{

    public function __construct(
        private readonly Connection $connection,
        private readonly IteratorFactory $factory,
        private readonly EntityRepository $repository,
        private readonly int $indexingBatchSize
    ) {
    }

    public function getDecorated(): AbstractAdminIndexer
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return 'nuonic.available-opensource-plugin';
    }

    public function getEntity(): string
    {
        return AvailableOpensourcePluginDefinition::ENTITY_NAME;
    }

    public function getIterator(): IterableQuery
    {
        return $this->factory->createIterator($this->getEntity(), null, $this->indexingBatchSize);
    }

    public function fetch(array $ids): array
    {
        $data = $this->connection->fetchAllAssociative(<<<SQL
                SELECT 
                    LOWER(HEX(p.id)) as id,
                    GROUP_CONCAT(DISTINCT pt.name SEPARATOR " ") as name,
                    p.package_name as packageName
                FROM 
                    nuonic_available_opensource_plugin AS p
                INNER JOIN 
                    nuonic_available_opensource_plugin_translation AS pt
                    ON p.id = pt.nuonic_available_opensource_plugin_id 
                WHERE 
                    p.id IN (:ids)
                GROUP BY 
                    p.id
            SQL,
            [
                'ids' => Uuid::fromHexToBytesList($ids),
            ],
            [
                'ids' => ArrayParameterType::BINARY,
            ]
        );

        $mapped = [];
        foreach ($data as $row) {
            $textBoosted = $row['name'];

            $id = (string) $row['id'];
            unset($row['name']);
            $text = \implode(' ', array_filter(array_unique(array_values($row))));

            $mapped[$id] = [
                'id' => $id,
                'textBoosted' => \strtolower($textBoosted),
                'text' => \strtolower($text)
            ];
        }

        return $mapped;
    }

    public function globalData(array $result, Context $context): array
    {
        $ids = array_column($result['hits'], 'id');
        $criteria = new Criteria($ids);

        return [
            'total' => (int) $result['total'],
            'data' => $this->repository->search($criteria, $context)->getEntities(),
        ];
    }

    public function globalCriteria(string $term, Search $criteria): Search
    {
        $splitTerms = explode(' ', $term);
        $lastPart = end($splitTerms);

        // If the end of the search term is not a symbol, apply the prefix search query
        if (preg_match('/^[\p{L}0-9]+$/u', $lastPart)) {
            $term .= '*';
        }

        $query = new SimpleQueryStringQuery($term, [
            'fields' => ['textBoosted'],
            'boost' => 10,
        ]);

        $criteria->addQuery($query, BoolQuery::SHOULD);

        return $criteria;
    }
}
