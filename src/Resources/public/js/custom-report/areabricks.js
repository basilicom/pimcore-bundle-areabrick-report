pimcore.registerNS("pimcore.bundle.customreports.custom.definition.areabricks");
pimcore.bundle.customreports.custom.definition.areabricks = Class.create({
  element: null,
  sourceDefinitionData: null,
  columnSettingsCallback: null,
  fieldsToCheck: [],

  initialize: function (sourceDefinitionData, key, deleteControl, columnSettingsCallback) {
    this.columnSettingsCallback = columnSettingsCallback;
    this.sourceDefinitionData = sourceDefinitionData ? sourceDefinitionData : {
      filters: '',
      reviewFormId: ''
    };
    this.element = new Ext.form.FormPanel({
      key: key,
      bodyStyle: 'padding:10px;',
      autoHeight: true,
      border: false,
      tbar: deleteControl,
      labelWidth: 300,
      items: [
      ]
    });
  },

  getElement: function () {
    return this.element;
  },

  getValues: function () {
    var values = this.element.getForm().getFieldValues();
    values.type = 'areabricks';
    return values;
  },
});
