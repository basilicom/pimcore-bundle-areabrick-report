<?php

namespace Basilicom\AreabrickReport;

use Composer\InstalledVersions;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class PimcorePluginAreabrickReportBundle extends AbstractPimcoreBundle
{
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Custom Report to list Areabricks on Documents.';
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return InstalledVersions::getVersion('basilicom/pimcore-plugin-migration-toolkit');
    }
}
