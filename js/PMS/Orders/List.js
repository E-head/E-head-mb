Ext.ns('PMS.Orders');

PMS.Orders.List = Ext.extend(Ext.grid.GridPanel, {

    title:      'Заказы',
    
    listURL:    link('orders', 'index', 'get-list'),
    
    getURL:     link('orders', 'index', 'get'),
    
    addURL:     link('orders', 'index', 'add'),
    
    updateURL:  link('orders', 'index', 'update'),
    
    deleteURL:  link('orders', 'index', 'delete'),
    
    closeOrderURL: link('orders', 'index', 'close'),
    
    loadMask: true,

    permissions: acl.isUpdate('orders'),
    
    viewConfig: {
        
        getRowClass: function(record) {
            
            if (!Ext.isEmpty(record.get('closed'))) { 
                return 'x-row-task-closed';
            }
            
            if (Date.parseDate(record.get('ondate'), xlib.date.DATE_FORMAT) < (new Date()).clearTime()) { 
                return 'x-row-task-expired';
            }
        }
        
    },
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            autoLoad: true,
            root: 'data',
            id: 'id',
            totalProperty: 'totalCount',
            fields: ['id', 'account_name', 'account_id',
                {
                    name: 'created', 
                    type: 'date', 
                    dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER,
                    convert: function(v, record) {
                        return Ext.util.Format.date(
                            Date.parseDate(v, xlib.date.DATE_TIME_FORMAT_SERVER), 
                            xlib.date.DATE_FORMAT
                        );
                    }
                },
                {
                    name: 'ondate', 
                    type: 'date', 
                    dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER,
                    convert: function(v, record) {
                        return Ext.util.Format.date(
                            Date.parseDate(v, xlib.date.DATE_TIME_FORMAT_SERVER), 
                            xlib.date.DATE_FORMAT
                        );
                    }
                },
                {
                    name: 'closed', 
                    type: 'date', 
                    dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER,
                    convert: function(v, record) {
                        return Ext.util.Format.date(
                            Date.parseDate(v, xlib.date.DATE_TIME_FORMAT_SERVER), 
                            xlib.date.DATE_FORMAT
                        );
                    }
                }
            ]
        });
        
        this.sm = new Ext.grid.RowSelectionModel({singleSelect: true});
        
        var actions = new xlib.grid.Actions({
            autoWidth: true,
            items: [function (g, rowIndex, e) {
                
                var record = g.getStore().getAt(rowIndex);
                
                return Ext.isEmpty(record.get('closed')) ? {
                        text: 'Закрыть заказ',
                        iconCls: 'check',
                        hidden: !acl.isView('admin'),
                        handler: g.onCloseOrder,
                        scope: g
                    } : false;
                    
            }, function (g, rowIndex, e) {
                
                var record = g.getStore().getAt(rowIndex);
                
                return Ext.isEmpty(record.get('closed')) ? {
                        text: 'Редактировать',
                        iconCls: 'edit',
                        hidden: !g.permissions,
                        handler: g.onUpdate,
                        scope: g
                    } : false;
                    
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
            header: '№',
            dataIndex: 'id',
            width: 40
        }, {
            header: 'Дата',
            dataIndex: 'ondate',
            width: 100
        }, {
            header: 'Заказчик',
            dataIndex: 'account_name',
            id: this.autoExpandColumn
        }, {
            header: 'Создан',
            dataIndex: 'created',
            width: 150
        }, {
            header: 'Закрыт',
            dataIndex: 'closed',
            width: 150
        }];
        
        /*
        this.filtersPlugin = new Ext.grid.GridFilters({
            filters: [
                {type: 'date',  dataIndex: 'ondate'},
//                {type: 'string',  dataIndex: 'created'},
//                {type: 'string',  dataIndex: 'account_name'}
            ]
        });
        */
        this.plugins = [actions]; //, this.filtersPlugin];

        this.tbar = new Ext.Toolbar({
            items: [new Ext.Toolbar.Button({
                text: 'Новый заказ',
                iconCls: 'add',
                hidden: !this.permissions,
                tooltip: 'Добавить заказ',
                handler: this.onAdd,
                scope: this
            }) //, ' ', this.filtersPlugin.getSearchField({width: 400}), ' '
            ],
            scope: this
        });
        
        this.bbar = new xlib.PagingToolbar({
            //plugins: [this.filtersPlugin],
            store: this.ds
        });
        
        PMS.Orders.List.superclass.initComponent.apply(this, arguments);
    },
    
    onAdd: function(b, e) {
    	
        Ext.Ajax.request({
            url: this.addURL,
            callback: function (options, success, response) {
                var res = xlib.decode(response.responseText);
                if (true !== success && !res && false == res.success) {
                    xlib.Msg.error('Ошибка создания заказа.');
                    return;
                }
                var f = this.getStore().recordType;
                var record = new f({id: res.id}, res.id);
                this.onEdit(record);
            },
            scope: this
        });
    },
    
    onUpdate: function(g, rowIndex) {
        var record = g.getStore().getAt(rowIndex);
        this.onEdit(record);
    },
    
    onCloseOrder: function(g, rowIndex) {
    	
        var record = g.getStore().getAt(rowIndex);
        
        Ext.Ajax.request({
            url: this.closeOrderURL,
            params: {
                id: record.get('id')
            },
            callback: function (options, success, response) {
                var res = xlib.decode(response.responseText);
                if (true == success && res && true == res.success) {
                    g.getStore().reload();
                    return;
                }
                xlib.Msg.error('Ошибка при закрытии заказа.');
            },
            scope: this
        });
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
    
    onEdit: function(record) {
        
        var formPanel = new PMS.Orders.Form(),
            goodsPanel = new PMS.Orders.Goods({orderId: record.get('id')});
        
        var w = new Ext.Window({
            title: 'Заказ №' + record.get('id'),
            resizable: false,
            width: 900,
            modal: true,
            items: [formPanel, goodsPanel],
            buttons: [{
                text: 'Сохранить',
                handler: function() {
                    formPanel.getForm().submit({
                        url: this.updateURL,
                        success: function(form, options) {
                            var o = options.result;
                            if (true == o.success) {
                                w.close();
                                this.getStore().reload();
                                return;
                            }
                            xlib.Msg.error('Не удалось сохранить.')
                        },
                        failure: function() {
                            xlib.Msg.error('Не удалось сохранить.')
                        },
                        scope: this
                    });
                },
                scope: this
            }]
        });
        
        formPanel.getForm().loadRecord(record);
        w.show();
    }
});

Ext.reg('PMS.Orders.List', PMS.Orders.List);