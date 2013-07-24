Ext.ns('PMS.Storage.Measures');

PMS.Storage.Measures.ComboBox = Ext.extend(xlib.form.ComboTrigger, {

    typeAhead: true,
    
    editable: false,
    
    triggerAction: 'all',
    
    lazyRender: true,
    
    lazyInit: false,
    
    mode: 'remote',
    
    displayField: 'name',
    
    selectFirst: true,
    
    overCls: '',
    
    updatePermissions: acl.isUpdate('storage'),
    
    listURL: link('storage', 'measures', 'get-all'),
    
    addURL: link('storage', 'measures', 'add'),

    initComponent: function() {
        
        this.store = new Ext.data.JsonStore({
            url: this.listURL,
            autoDestroy: true,
            storeId: 'MeasuresStore',
            root: 'data',
            idProperty: 'name',
            fields: ['name']
        });
        
        this.triggers = [{
            cls: 'add',
            name: 'btn0',
            overCls: '',
            permissions: this.updatePermissions,
            qtip: 'Добавить единицу измерения',
            handler: function(e, node) {
                
                this.collapse();
                
                var formPanel = new xlib.form.FormPanel({
                    permissions: this.updatePermissions,
                    labelWidth: 100,
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Наименование',
                        name: 'name'
                    }]
                });
                    
                var w = new Ext.Window({
                    title: 'Добавление единицы измерения',
                    resizable: false,
                    width: 300,
                    modal: true,
                    items: [formPanel],
                    buttons: [{
                        text: 'Сохранить',
                        handler: function() {
                            formPanel.getForm().submit({
                                url: this.addURL,
                                success: function(form, options) {
                                    var o = options.result;
                                    if (true == o.success) {
                                        w.close();
                                        this.getStore().reload();
                                        return;
                                    }
                                    xlib.Msg.error('Не удалось добавить.')
                                },
                                failure: function() {
                                    xlib.Msg.error('Не удалось добавить.')
                                },
                                scope: this
                            });
                        },
                        scope: this
                    }, {
                        text: 'Отмена',
                        handler: function() {
                            w.close();
                        },
                        scope: this
                    }]
                });
                w.show();
            },
            scope: this
        }];
        
        PMS.Storage.Measures.ComboBox.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('PMS.Storage.Measures.ComboBox', PMS.Storage.Measures.ComboBox);