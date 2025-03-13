
# Installation

add to bundles.php

```php
return [
    // ...
    PimcorePluginAreabrickReportBundle::class => ['all' => true],
];
```

# Configuration

* create a pimcore_custom_reports.yaml and add it to your config.yaml

```yaml
imports:
    - { resource: 'pimcore_custom_reports.yaml' }
```

```yaml
pimcore_custom_reports:
  adapters:
    areabricks: basilicom.areabrick_report.custom_report.adapter.factory
  definitions:
    areabricks:
      name: areabricks
      sql: ''
      dataSourceConfig:
        -
          type: areabricks
      columnConfiguration:
        -
          name: areabrickId
          display: true
          export: true
          order: false
          width: 200
          label: Areabrick
          filter: ''
          displayType: ''
          filter_drilldown: filter_and_show
          id: extModel426-1
        -
          name: documentId
          display: true
          export: true
          order: false
          width: ''
          label: Dokument
          filter: ''
          displayType: null
          filter_drilldown: filter_and_show
          columnAction: openDocument
          id: extModel426-2
      niceName: 'Areabrick (keine Pagination)'
      group: ''
      groupIconClass: ''
      iconClass: ''
      menuShortcut: false
      reportClass: ''
      chartType: ''
      pieColumn: null
      pieLabelColumn: null
      xAxis: null
      yAxis: {  }
      modificationDate: 1741099298
      creationDate: 1741020069
      shareGlobally: true
      sharedUserNames: {  }
      sharedRoleNames: {  }

```
