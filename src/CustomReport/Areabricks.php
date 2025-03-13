<?php

namespace Basilicom\AreabrickReport\CustomReport;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Pimcore\Bundle\CustomReportsBundle\Tool\Adapter\AbstractAdapter;
use Pimcore\Bundle\CustomReportsBundle\Tool\Config;
use Pimcore\Db;
use Pimcore\Extension\Document\Areabrick\AreabrickManager;
use stdClass;
use Symfony\Component\DependencyInjection\Container;

class Areabricks extends AbstractAdapter
{
    private const COLUMN_AREABRICK_ID = 'areabrickId';
    private const COLUMN_DOCUMENT_ID = 'documentId';
    private const EMPTY_VALUE = null;
    private const EMPTY_NAME = '---';
    private const QUERY_AREABRICKS = 'select * from documents_editables
         join documents on documents.id = documents_editables.documentId
         where documents_editables.type = \'areablock\' and data <> \'a:0:{}\'';

    public function __construct(
        stdClass $config,
        Config $fullConfig = null,
        private readonly AreabrickManager $areabrickManager,
        private readonly Connection $connection,
    ) {
        parent::__construct($config, $fullConfig);
    }
    /**
     * @param array|null $filters not used
     * @param string|null $sort disabled by configuration
     * @param string|null $dir disabled by configuration
     * @param int|null $offset not implemented, because too complex
     * @param int|null $limit not implemented, because too complex
     * @param array|null $fields not used
     * @param array|null $drillDownFilters filters over the table
     * @return array
     *
     * @throws Exception
     */
    public function getData(?array $filters, ?string $sort, ?string $dir, ?int $offset, ?int $limit, array $fields = null, array $drillDownFilters = null): array // @phpstan-ignore-line
    {
        $areabrickIds = $this->getAreabrickIds();
        $documentId = null;
        if (!empty($drillDownFilters)) {
            if (!empty($drillDownFilters[self::COLUMN_AREABRICK_ID])) {
                $areabrickIds = [$drillDownFilters[self::COLUMN_AREABRICK_ID]];
            }
            if (!empty($drillDownFilters[self::COLUMN_DOCUMENT_ID])) {
                $documentId = $drillDownFilters[self::COLUMN_DOCUMENT_ID];
            }
        }
        $generalAreabrickInfo = [];
        foreach ($areabrickIds as $areabrickId) {
            $query = self::QUERY_AREABRICKS;
            $query .= ' and data like \'%';
            $query .= sprintf('s:4:"type";s:%s:"%s";', strlen($areabrickId), $areabrickId);
            $query .= '%\'';
            if (!empty($documentId)) {
                $query .= ' and documents_editables.documentId = ' . $documentId;
            }
            $query .= ' limit ' . $offset . ', ' . $limit;
            $result = $this->connection->executeQuery($query)->fetchAllAssociative();
            if (!empty($result)) {
                foreach ($result as $item) {
                    $generalAreabrickInfo[] = [
                        self::COLUMN_AREABRICK_ID => $this->getAreabrickName($areabrickId),
                        self::COLUMN_DOCUMENT_ID => $this->getDocumentKey($item['documentId']),
                    ];
                }
            }
        }
        return ['data' => $generalAreabrickInfo, 'total' => count($generalAreabrickInfo)];
    }
    /**
     * @return string[]
     */
    public function getColumns(?stdClass $configuration): array
    {
        return [
            self::COLUMN_AREABRICK_ID,
            self::COLUMN_DOCUMENT_ID
        ];
    }
    /**
     * @throws Exception
     */
    public function getAvailableOptions(array $filters, string $field, array $drillDownFilters): array // @phpstan-ignore-line
    {
        $data =  [
            ['name' => self::EMPTY_NAME, 'value' => self::EMPTY_VALUE],
        ];
        if ($field === self::COLUMN_AREABRICK_ID) {
            foreach ($this->getAreabricks() as $id => $brick) {
                $data[] = [
                    'name' => $brick,
                    'value' => $id,
                ];
            }
        } elseif ($field === self::COLUMN_DOCUMENT_ID) {
            foreach ($this->getDocuments() as $id => $key) {
                $data[] = [
                    'name' => $key,
                    'value' => $id,
                ];
            }
        }
        return ['data' => $data];
    }
    /**
     * @return string[]
     */
    private function getAreabricks(): array
    {
        $data = [];
        foreach ($this->areabrickManager->getBricks() as $brick) {
            $data[$brick->getId()] = $brick->getName();
        }

        return $data;
    }
    /**
     * @return string[]
     */
    private function getAreabrickIds(): array
    {
        return array_keys($this->getAreabricks());
    }
    private function getAreabrickName(string $id): string
    {
        $areabricks = $this->getAreabricks();
        return $areabricks[$id] ?? $id;
    }
    /**
     * @return string[]
     * @throws Exception
     */
    private function getDocuments(): array
    {
        $query = self::QUERY_AREABRICKS;
        $result = $this->connection->executeQuery($query)->fetchAllAssociative();
        $data = [];
        if (!empty($result)) {
            foreach ($result as $item) {
                $data[$item['documentId']] = $item['path'] . $item['key'];
            }
        }
        return $data;
    }
    /**
     * @throws Exception
     */
    private function getDocumentKey(mixed $id): mixed
    {
        $documents = $this->getDocuments();
        return $documents[$id] ?? $id;
    }
}
