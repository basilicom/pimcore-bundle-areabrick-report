<?php

namespace Basilicom\AreabrickReport\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Exception;
use Pimcore\Console\AbstractCommand;
use Pimcore\Extension\Document\Areabrick\AreabrickManager;
use Pimcore\Model\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'basilicom:report:areabrick',
    description: 'Creates a the database table and the content for the report over all used arebricks.',
    hidden: false
)]
class AreabrickReportDataCommand extends AbstractCommand
{
    private const TABLE_NAME = 'areabrick_report';

    /**
     * @var string[]
     */
    private array $areabricks;

    public function __construct(
        private readonly AreabrickManager $areabrickManager,
        private readonly Connection $connection,
    ) {
        parent::__construct();

        $this->areabricks = $this->getAreabricks();
    }

    public function configure(): void
    {
        // bin/console basilicom:report:areabrick -f true
        $this->setDefinition(
            [
                new InputOption('force', 'f', InputOption::VALUE_OPTIONAL, 'drops table and recreates table and data.'),
            ]
        );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $forceRecreate = $input->getOption('force') === 'true';

            $tableExists = $this->checkIfTableExists();
            if ($tableExists) {
                if ($forceRecreate) {
                    $this->dropTable();
                    $this->createTable();
                } else {
                    $this->truncateTable();
                }
            } else {
                $this->createTable();
            }

            $this->indexAreabricks();
        } catch (DbalException $exception) {
            $this->writeError('Error: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
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

    private function getAreabrickName(string $id): string
    {
        return $this->areabricks[$id] ?? '';
    }

    /**
     * @throws DbalException
     */
    private function checkIfTableExists(): bool
    {
        $checkTableExistsQuery = "SHOW TABLES LIKE '" . self::TABLE_NAME . "'";
        $result = $this->connection->executeQuery($checkTableExistsQuery);

        return !empty($result->fetchOne());
    }

    /**
     * @throws DbalException
     */
    private function dropTable(): void
    {
        $dropTableQuery = 'DROP TABLE ' . self::TABLE_NAME;
        $this->connection->executeQuery($dropTableQuery);

        $this->writeComment('Database table "' . self::TABLE_NAME . '" was dropped.');
    }

    /**
     * @throws DbalException
     */
    private function truncateTable(): void
    {
        $dropTableQuery = 'TRUNCATE TABLE ' . self::TABLE_NAME;
        $this->connection->executeQuery($dropTableQuery);

        $this->writeComment('Database table "' . self::TABLE_NAME . '" was truncated.');
    }

    /**
     * @throws DbalException
     */
    private function createTable(): void
    {
        $createTableQuery = [];
        $createTableQuery[] = 'CREATE TABLE ' . self::TABLE_NAME;
        $createTableQuery[] = '(';
        $createTableQuery[] = 'id int NOT NULL AUTO_INCREMENT,';
        $createTableQuery[] = 'areabrickName varchar(255) NOT NULL,';
        $createTableQuery[] = 'areabrickId varchar(255) NOT NULL,';
        $createTableQuery[] = 'areabrickIndex int NOT NULL,';
        $createTableQuery[] = 'documentPathKey varchar(255) NOT NULL,';
        $createTableQuery[] = 'documentType varchar(255) NOT NULL,';
        $createTableQuery[] = 'documentTitle varchar(255) NOT NULL,';
        $createTableQuery[] = 'documentLanguage varchar(10) NOT NULL,';
        $createTableQuery[] = 'documentId int NOT NULL,';
        $createTableQuery[] = 'PRIMARY KEY (id),';
        $createTableQuery[] = 'INDEX index_areabrickName (areabrickName),';
        $createTableQuery[] = 'INDEX index_documentPathKey (documentPathKey)';
        $createTableQuery[] = ')';

        $this->connection->executeQuery(implode(' ', $createTableQuery));

        $this->writeComment('Database table "' . self::TABLE_NAME . '" was be created.');
    }

    /**
     * @throws Exception
     */
    private function indexAreabricks(): void
    {
        $listing = Document::getList([
            'unpublished' => true,
            'condition' => "type IN ('page', 'snippet', 'email')",
            'orderKey' => ['key'],
            'order' => 'desc',
        ]);

        foreach ($listing as $document) {
            $title = $document->getKey();
            if (method_exists($document, 'getTitle')) {
                $title = $document->getTitle();
            }
            $data = [
                'documentId' => $document->getId(),
                'documentType' => $document->getType(),
                'documentTitle' => $title,
                'documentLanguage' => $document->getProperty('language'),
                'documentPathKey' => $document->getPath() . $document->getKey(),
            ];

            foreach ($document->getEditables() as $editable) {
                if (!$editable instanceof Document\Editable\Areablock) {
                    continue;
                }

                $count = 1;
                foreach ($editable->getIndices() as $areabrick) {
                    $name = $this->getAreabrickName($areabrick['type']);
                    if (empty($name)) {
                        continue;
                    }

                    $rowData = $data;
                    $rowData['areabrickIndex'] = $count;
                    $rowData['areabrickId'] = $areabrick['type'];
                    $rowData['areabrickName'] = $this->getAreabrickName($areabrick['type']);

                    $this->connection->insert(self::TABLE_NAME, $rowData);

                    $count++;
                }
            }
        }
    }
}
