<?php

namespace Basilicom\AreabrickReport\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

class PimcoreAdminEventListener
{
    public function addAdminJSFiles(PathsEvent $event): void
    {
        $event->setPaths(
            array_merge(
                $event->getPaths(),
                [
                    '/bundles/pimcorepluginareabrickreport/js/custom-report/areabricks.js',
                ]
            )
        );
    }
}
