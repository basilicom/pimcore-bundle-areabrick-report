<?php

namespace Basilicom\AreabrickReport\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Pimcore\Console\AbstractCommand;
use Pimcore\Extension\Document\Areabrick\AreabrickManager;
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

    public function __construct(
        private readonly AreabrickManager $areabrickManager,
        private readonly Connection $connection,
    ) {
        parent::__construct();
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
        } catch (Exception $exception) {
            $this->writeError('Error: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function indexAreabricks(): void
    {
        $areabricks = $this->getAreabricks();

        foreach ($areabricks as $areabrickId => $areabrickName) {
            $query = [];
            $query[] = 'SELECT * FROM documents_editables';
            $query[] = 'JOIN documents ON documents.id = documents_editables.documentId';
            $query[] = 'WHERE documents_editables.type = \'areablock\'';
            $query[] = 'and data like \'%' . sprintf('s:4:"type";s:%s:"%s";', strlen($areabrickId), $areabrickId) . '%\'';

            $this->writeComment(sprintf('Index areabrick %s (%s).', $areabrickName, $areabrickId));

            $result = $this->connection->executeQuery(implode(' ', $query))->fetchAllAssociative();
            if (empty($result)) {
                $data = [
                    'areabrickName' => $areabrickName,
                    'areabrickId' => $areabrickId,
                    'documentPath' => 'not in use',
                    'documentId' => 1,
                ];
                $this->connection->insert(self::TABLE_NAME, $data);

                continue;
            }

            foreach ($result as $item) {
                $data = [
                    'areabrickName' => $areabrickName,
                    'areabrickId' => $areabrickId,
                    'documentPath' => $item['path'] . $item['key'],
                    'documentId' => $item['documentId'],
                ];
                $this->connection->insert(self::TABLE_NAME, $data);
            }
        }

        $this->writeInfo(sprintf('Index %s areabrick types.', count($areabricks)));
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
     * @throws Exception
     */
    private function checkIfTableExists(): bool
    {
        $checkTableExistsQuery = "SHOW TABLES LIKE '" . self::TABLE_NAME . "'";
        $result = $this->connection->executeQuery($checkTableExistsQuery);

        return !empty($result->fetchOne());
    }

    /**
     * @throws Exception
     */
    private function dropTable(): void
    {
        $dropTableQuery = 'DROP TABLE ' . self::TABLE_NAME;
        $this->connection->executeQuery($dropTableQuery);

        $this->writeComment('Database table "' . self::TABLE_NAME . '" was dropped.');
    }

    /**
     * @throws Exception
     */
    private function truncateTable(): void
    {
        $dropTableQuery = 'TRUNCATE TABLE ' . self::TABLE_NAME;
        $this->connection->executeQuery($dropTableQuery);

        $this->writeComment('Database table "' . self::TABLE_NAME . '" was truncated.');
    }

    /**
     * @throws Exception
     */
    private function createTable(): void
    {
        $createTableQuery = [];
        $createTableQuery[] = 'CREATE TABLE ' . self::TABLE_NAME;
        $createTableQuery[] = '(';
        $createTableQuery[] = 'id int NOT NULL AUTO_INCREMENT,';
        $createTableQuery[] = 'areabrickName varchar(255) NOT NULL,';
        $createTableQuery[] = 'areabrickId varchar(255) NOT NULL,';
        $createTableQuery[] = 'documentPath varchar(255) NOT NULL,';
        $createTableQuery[] = 'documentId int NOT NULL,';
        $createTableQuery[] = 'PRIMARY KEY (id),';
        $createTableQuery[] = 'INDEX index_areabrickName (areabrickName),';
        $createTableQuery[] = 'INDEX index_documentPath (documentPath)';
        $createTableQuery[] = ')';

        $this->connection->executeQuery(implode(' ', $createTableQuery));

        $this->writeComment('Database table "' . self::TABLE_NAME . '" was be created.');
    }
}
