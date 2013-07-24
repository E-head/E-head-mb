
Ext.ns('PMS.Storage.Requests');

PMS.Storage.Requests.List = Ext.extend(Ext.grid.GridPanel, {

    title:      'Заявки на снабжение',
    
    listURL:    link('storage', 'requests', 'get-list'),
    
    addURL:     link('storage', 'requests', 'add'),
    
    updateURL:  link('storage', 'requests', 'update'),
    
    deleteURL:  link('storage', 'requests', 'delete'),
    
    loadMask: true,

    permissions: acl.isView('storage'),
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            autoLoad: true,
            remoteSort: true,
            root: 'data',
            id: 'id',
            sortInfo: {
                field: 'request_on',
                direction: 'ASC'
            },
            totalProperty: 'totalCount',
            fields: ['id', 'asset_id', 'order_id', 'account_name', 'name', 'measure', 'qty', 
                {name: 'request_on', type: 'date', dateFormat: xlib.date.DATE_FORMAT_SERVER},
                {
                    name: 'created', 
                    type: 'date', 
                    dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER,
                    convert: function(v, record) {
                        return Ext.util.Format.date(
                            Date.parseDate(v, xlib.date.DATE_TIME_FORMAT_SERVER), 
                            xlib.date.DATE_TIME_FORMAT
                        );
                    }
                }
            ]
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        
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
        })
        
        this.columns = [{
            header: 'Заявка на дату',
            dataIndex: 'request_on',
            renderer: xlib.dateRenderer(xlib.date.DATE_FORMAT),
            sortable: true,
            width: 100
        }, {
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true,
            id: this.autoExpandColumn
        }, {
            header: 'Количество',
            dataIndex: 'qty',
            sortable: true,
            width: 100
        }, {
            header: 'Ед. измерения',
            dataIndex: 'measure',
            sortable: true,
            width: 100
        }, {
            header: 'Автор заявки',
            dataIndex: 'account_name',
            sortable: true,
            width: 150
        }, {
            header: 'Дата подачи',
            dataIndex: 'created',
            sortable: true,
            width: 150
        }, {
            header: 'К заказу №',
            dataIndex: 'order_id',
            sortable: true,
            width: 80
        }];
        
        this.filtersPlugin = new Ext.grid.GridFilters({
            filters: [{type: 'string',  dataIndex: 'name'}]
        });
        
        this.plugins = [actions, this.filtersPlugin];

        this.tbar = [new Ext.Toolbar.Button({
                text: 'Добавить заявку',
                iconCls: 'add',
                hidden: !this.permissions,
                tooltip: 'Добавить заявку',
                handler: this.onAdd,
                scope: this
            }), ' ', this.filtersPlugin.getSearchField({width: 400})
        ];
        
        this.bbar = new xlib.PagingToolbar({
            plugins: [this.filtersPlugin],
            store: this.ds
        });
        
        PMS.Storage.Requests.List.superclass.initComponent.apply(this, arguments);
        
        this.on('rowdblclick', this.onUpdate, this);
    },
    
    onAdd: function(b, e) {
        
        if (!this.permissions) {
            return;
        }
        var formPanel = new PMS.Storage.Requests.Form();
        var w = this.getWindow(formPanel, this.addURL, this.getStore(), false);
        w.show();
    },
    
    onUpdate: function(g, rowIndex) {
        
        if (!this.permissions) {
            return;
        }
        var formPanel = new PMS.Storage.Requests.Form();
        var record = g.getStore().getAt(rowIndex);
        var id = parseInt(record.get('id'));
        var w = this.getWindow(formPanel, this.updateURL, this.getStore(), id);
        w.show();
        formPanel.getForm().loadRecord(record);
    },
    
    onDelete: function(g, rowIndex) {
        var record = g.getStore().getAt(rowIndex);
        var id = parseInt(record.get('id'));
        xlib.Msg.confirm('Вы уверены?', function() {
            Ext.Ajax.request({
                url: this.deleteURL,
                params: {
                    id: id
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
            title: 'Заявка на ТМЦ',
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

Ext.reg('PMS.Storage.Requests.List', PMS.Storage.Requests.List);