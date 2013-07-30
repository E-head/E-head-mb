Ext.ns('PMS.Orders');

PMS.Orders.Goods = Ext.extend(Ext.grid.GridPanel, {

    title:      'Товары',
    
    listURL:    link('orders', 'goods', 'get-list'),
    
    addURL:     link('orders', 'goods', 'add'),
    
    updateURL:  link('orders', 'goods', 'update'),
    
    deleteURL:  link('orders', 'goods', 'delete'),
    
    loadMask: true,
    
    height: 500,

    permissions: acl.isUpdate('orders'),
    
    orderId: null,
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            baseParams: {order_id: this.orderId},
            autoLoad: true,
            root: 'data',
            id: 'id',
            fields: ['id', 'name', 'number', 'price', {name: 'summ', type: 'float'}]
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
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true,
            id: this.autoExpandColumn
        }, {
            header: 'Кол-во',
            dataIndex: 'number',
            sortable: true,
            width: 120
        }, {
            header: 'Цена (р.)',
            dataIndex: 'price',
            sortable: true,
            width: 120,
            renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                return Ext.util.Format.number(value, '0,000.00').replace(/,/g, ' ');
            }
        }, {
            header: 'Сумма (р.)',
            dataIndex: 'summ',
            sortable: true,
            width: 120,
            renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                return Ext.util.Format.number(value, '0,000.00').replace(/,/g, ' ');
            }
        }];
        
        this.plugins = [actions];

        this.summaryInfo = new Ext.form.DisplayField({
            value: '0.00 р.'
        });
        
        this.tbar = new Ext.Toolbar({
            items: [{
                xtype: 'button',
                text: 'Добавить товар',
                iconCls: 'add',
                hidden: !this.permissions,
                tooltip: 'Добавить товар',
                handler: this.onAdd,
                scope: this
            }, '->', {
                xtype: 'button',
                iconCls: 'x-tbar-loading',
                tooltip: 'Обновить список',
                handler: function() {
                    this.getStore().reload();
                },
                scope: this
            }],
            scope: this
        });
        
        this.bbar = new Ext.Toolbar({
            items: ['->', {xtype: 'tbtext', text: '<b>Итого:</b>'}, ' ', this.summaryInfo]
        });
        
        PMS.Orders.Goods.superclass.initComponent.apply(this, arguments);
        
        this.getStore().on('load', function(store, records, options) {
            var summ = Ext.util.Format.number(store.sum('summ'), '0,000.00').replace(/,/g, ' ');
            this.summaryInfo.setValue(summ + ' р.');
        }, this);
    },
    
    onAdd: function(b, e) {
    	
        var goodsGrid = new PMS.Goods.List({
            border: false,
            title: false,
            height: 500
        }),
        w = new Ext.Window({
            title: 'Добавить товар к заказу №' + this.orderId,
            resizable: false,
            width: 800,
            modal: true,
            items: [goodsGrid],
            buttons: [{
                text: 'Добавить',
                handler: function() {
                    var record = goodsGrid.getSelectionModel().getSelected();
                    if (!record) return;
                    this.addGood(record.get('id'));
                    w.close();
                },
                scope: this
            }, {
                text: 'Отмена',
                handler: function() {
                    w.close();
                }
            }]
        });
        w.show();
    },
    
    addGood: function(id) {
    
        Ext.Ajax.request({
            url: this.addURL,
            params: {
                good_id: id,
                order_id: this.orderId
            },
            callback: function (options, success, response) {
                var res = xlib.decode(response.responseText);
                if (true !== success && !res && false == res.success) {
                    xlib.Msg.error('Ошибка при добавлении товара.');
                    return;
                }
                var f = this.getStore().recordType;
                var record = new f({id: res.id}, res.id);
                this.onEdit(record);
            },
            scope: this
        });
    },
    
    onEdit: function(record) {
        
        var formPanel = new xlib.form.FormPanel({
            permissions: this.permissions,
            items: [{
                xtype: 'hidden',
                name: 'id'
            }, {
                xtype: 'numberfield',
                name: 'number',
                allowDecimals: false,
                allowNegative: false,
                allowBlank: false,
                hideLabel: true
            }]
        }),
        w = new Ext.Window({
            title: 'Количество товара "' + (record.get('name') || '') + '"',
            resizable: false,
            width: 500,
            modal: true,
            items: [formPanel],
            buttons: [{
                text: 'Сохранить',
                handler: function() {
                    //if (!formPanel.isValid()) return;
                    formPanel.getForm().submit({
                        url: this.updateURL,
                        success: function(form, options) {
                            if (true !== options.result.success) {
                                xlib.Msg.error('Не удалось сохранить.')
                                return;
                            }
                            this.getStore().reload();
                            w.close();
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
    },
    
    onUpdate: function(g, rowIndex) {
        var record = g.getStore().getAt(rowIndex);
        this.onEdit(record);
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
    }
});

Ext.reg('PMS.Orders.Goods', PMS.Orders.Goods);