Ext.ns('PMS.Storage.Measures');

PMS.Storage.Measures.List = Ext.extend(Ext.grid.GridPanel, {

    title:      'Единицы измерения',
    
    listURL:    link('storage', 'measures', 'get-all'),
    
    addURL:     link('storage', 'measures', 'add'),
    
    deleteURL:  link('storage', 'measures', 'delete'),
    
    loadMask: true,
    
    viewConfig: {
        forceFit: true
    },

    permissions: acl.isUpdate('storage'),
    
    initComponent: function() {
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            autoLoad: true,
            root: 'data',
            idProperty: 'name',
            fields: ['name']
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        
        var actions = new xlib.grid.Actions({
            autoWidth: true,
            items: [{
                text: 'Удалить',
                iconCls: 'delete',
                hidden: !this.permissions,
                handler: this.onDelete,
                scope: this
            }],
            scope: this
        })
        
        this.columns = [{
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true,
            width: 100
        }];
        
        this.plugins = [actions];

        this.tbar = [{
            iconCls: 'x-tbar-loading',
            qtip: 'Обновить',
            handler: function() {
                this.getStore().reload();
            },
            scope: this
        }, {
            text: 'Добавить единицу измерения',
            iconCls: 'add',
            hidden: !this.permissions,
            qtip: 'Добавить',
            handler: this.onAdd,
            scope: this
        }];
        
        PMS.Storage.Measures.List.superclass.initComponent.apply(this, arguments);
    },
    
    onAdd: function(b, e) {
        
        var formPanel = new xlib.form.FormPanel({
            permissions: this.permissions,
            labelWidth: 100,
            items: [{
                xtype: 'textfield',
                fieldLabel: 'Наименование',
                name: 'name'
            }]
        })
            
        this.formWindow = new Ext.Window({
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
                                this.formWindow.close();
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
                    this.formWindow.close();
                },
                scope: this
            }]
        });
        this.formWindow.show();
    },
    
    onDelete: function(g, rowIndex) {
        var record = g.getStore().getAt(rowIndex);
        xlib.Msg.confirm('Вы уверены?', function() {
            Ext.Ajax.request({
                url: this.deleteURL,
                params: {
                    name: record.get('name')
                },
                callback: function (options, success, response) {
                    var msg = 'Ошибка при удалении.';
                    var res = xlib.decode(response.responseText);
                    if (true == success && res) {
                        if (true == res.success) {
                            g.getStore().reload();
                            return;
                        } else if (errors) {
                            var msg;
                            switch (errors[0].code) {
                                case -20:
                                    msg = 'Невозможно удалить. ' +
                                        'Субъект связан с одним или более заказами.'
                                    break;
                                default:
                            }
                        }
                    }
                    xlib.Msg.error(msg);
                },
                scope: this
            });    
        }, this);
    },
    
    // ------------------------ Private functions ------------------------------
    
    getForm: function() {
        return new xlib.form.FormPanel({
            permissions: this.permissions,
            labelWidth: 100,
            items: [{
                xtype: 'textfield',
                fieldLabel: 'Наименование',
                name: 'name'
            }]
        });
    },
    
    showWindow: function(config) {
    }
});

Ext.reg('PMS.Storage.Measures.List', PMS.Storage.Measures.List);