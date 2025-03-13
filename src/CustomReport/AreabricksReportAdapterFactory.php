<?php

namespace Basilicom\AreabrickReport\CustomReport;

use Doctrine\DBAL\Connection;
use Pimcore\Bundle\CustomReportsBundle\Tool\Adapter\CustomReportAdapterFactoryInterface;
use Pimcore\Bundle\CustomReportsBundle\Tool\Adapter\CustomReportAdapterInterface;
use Pimcore\Bundle\CustomReportsBundle\Tool\Config;
use Pimcore\Extension\Document\Areabrick\AreabrickManager;
use stdClass;

readonly class AreabricksReportAdapterFactory implements CustomReportAdapterFactoryInterface
{
    public function __construct(
        private string $className,
        private AreabrickManager $areabrickManager,
        private Connection $connection,
    ) {
    }

    public function create(stdClass $config, Config $fullConfig = null): CustomReportAdapterInterface
    {
        /** @var CustomReportAdapterInterface $object */
        $object = new $this->className($config, $fullConfig, $this->areabrickManager, $this->connection);
        return $object;
    }
}
