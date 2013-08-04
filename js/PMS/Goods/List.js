Ext.ns('PMS.Goods');

PMS.Goods.List = Ext.extend(Ext.grid.GridPanel, {

    title:      'База товаров',
    
    listURL:    link('goods', 'index', 'get-list'),
    
    getURL:     link('goods', 'index', 'get'),
    
    addURL:     link('goods', 'index', 'add'),
    
    updateURL:  link('goods', 'index', 'update'),
    
    deleteURL:  link('goods', 'index', 'delete'),
    
    loadMask: true,

    permissions: acl.isUpdate('admin'),
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            autoLoad: true,
            root: 'data',
            id: 'id',
            totalProperty: 'totalCount',
            fields: ['id', 'name', 'price', 'descr']
        });
        
        this.sm = new Ext.grid.RowSelectionModel({singleSelect: true});
        
        var actions = new xlib.grid.Actions({
            autoWidth: true,
            items: [{
                text: 'Редактировать',
                iconCls: 'edit',
                hidden: !this.permissions,
                handler: this.onUpdate,
                scope: this
            }, {
                text: 'Удалить',
                iconCls: 'delete',
                hidden: !this.permissions,
                handler: this.onDelete,
                scope: this
            }],
            scope: this
        });
        
        this.columns = [{
            header: 'ID',
            dataIndex: 'id',
            width: 100
        }, {
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true,
            width: 250
        }, {
            header: 'Описание',
            dataIndex: 'descr',
            id: this.autoExpandColumn
        }, {
            header: 'Цена (р.)',
            dataIndex: 'price',
            sortable: true,
            width: 120,
            renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                return Ext.util.Format.number(value, '0,000.00').replace(/,/g, ' ');
            }
        }];
        
        this.filtersPlugin = new Ext.grid.GridFilters({
            filters: [{type: 'string',  dataIndex: 'name'}]
        });
        
        this.plugins = [actions, this.filtersPlugin];

        this.tbar = new Ext.Toolbar({
            items: [new Ext.Toolbar.Button({
                text: 'Добавить товар',
                iconCls: 'add',
                hidden: !this.permissions,
                tooltip: 'Добавить товар',
                handler: this.onAdd,
                scope: this
            }), ' ', this.filtersPlugin.getSearchField({width: 400}), ' '
            ],
            scope: this
        });
        
        this.bbar = new xlib.PagingToolbar({
            plugins: [this.filtersPlugin],
            store: this.ds
        });
        
        PMS.Goods.List.superclass.initComponent.apply(this, arguments);
    },
    
    onAdd: function(b, e) {
    	
        var formPanel = new PMS.Goods.Form(),
            w = this.getWindow(formPanel, this.addURL, this.getStore(), false);
        w.show();
    },
    
    onUpdate: function(g, rowIndex) {
        var formPanel = new PMS.Goods.Form(),
            record = g.getStore().getAt(rowIndex),
            w = this.getWindow(formPanel, this.updateURL, this.getStore(), record.get('id'));
        w.show();
        formPanel.getForm().loadRecord(record);
    },
    
    onDelete: function(g, rowIndex) {
        
        var record = g.getStore().getAt(rowIndex);
        
        xlib.Msg.confirm('Вы уверены?', function() {
            Ext.Ajax.request({
                url: this.deleteURL,
                params: {
                    id: record.get('id')
                },
                callback: function (options, success, response) {
                    var res = xlib.decode(response.responseText);
                    if (true == success && res && true == res.success) {
                        g.getStore().reload();
                        return;
                    }
                    xlib.Msg.error('Ошибка при удалении.');
                },
                scope: this
            });    
        }, this);
    },
    
    // Private functions 
    
    getWindow: function(formPanel, url, store, id) {
         
        var w = new Ext.Window({
            title: 'Новый товар',
            resizable: false,
            width: 500,
            modal: true,
            items: [formPanel],
            buttons: [{
                text: 'Сохранить',
                handler: function() {
                    formPanel.getForm().submit({
                        params: !id ? {} : {id: id},
                        url: url,
                        success: function(form, options) {
                            var o = options.result;
                            if (true == o.success) {
                                w.close();
                                store.reload();
                                return;
                            }
                            xlib.Msg.error('Не удалось сохранить.')
                        },
                        failure: function() {
                            xlib.Msg.error('Не удалось сохранить.')
                        }
                    });
                }
            }, {
                text: 'Отмена',
                handler: function() {
                    w.close();
                }
            }]
        });
        
        return w;
    }
});

Ext.reg('PMS.Goods.List', PMS.Goods.List);