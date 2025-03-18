
# Installation
```
composer require basilicom/pimcore-bundle-areabrick-report
```

add to bundles.php

```php
return [
    // ...
    PimcorePluginAreabrickReportBundle::class => ['all' => true],
];
```

## Command

Add the command to run every x minutes, depending how often you want to update the data in the report.
```
bin/console basilicom:report:areabrick
```

For updating we suggest to use the force flag, because it will drop and create the database table. 
So it there are changes to the database table it will be updated.
```
bin/console basilicom:report:areabrick -f true
```

# Info

Shows a report with a filter of documents, areabricks and language.
Filters can be combined.

![areabricks-report-list.png](docs/areabricks-report-list.png)

Filter by Areabrick
![areabricks-report-list-filter-areabrick.png](docs/areabricks-report-list-filter-areabrick.png)

Filter by Document
![areabricks-report-list-filter-document.png](docs/areabricks-report-list-filter-document.png)

Filter by Language
![areabricks-report-list-filter-language.png](docs/areabricks-report-list-filter-language.png)
