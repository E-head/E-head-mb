Ext.ns('PMS.Storage.Assets');

PMS.Storage.Assets.List = Ext.extend(Ext.grid.GridPanel, {

    title: true,
    
    baseTitle: 'Список ТМЦ из категории ',

    listURL: link('storage', 'assets', 'get-list'),
    
    addURL: link('storage', 'assets', 'add'),
    
    updateURL: link('storage', 'assets', 'update'),
    
    deleteURL: link('storage', 'assets', 'delete'),
    
    checkURL: link('storage', 'assets', 'check'),
    
    resetChecksURL: link('storage', 'assets', 'reset-checks'),
    
    loadMask: true,

    categoryId: null,
    
    permissions: acl.isUpdate('storage'),
    
    readOnly: false,
    
    initComponent: function() {
        
        this.sm = new Ext.grid.RowSelectionModel();
        
        this.autoExpandColumn = Ext.id();
        
        this.columns = [{
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true,
            id: this.autoExpandColumn
        }, {
            header: 'Ед. измерения',
            dataIndex: 'measure',
            sortable: true,
            width: 100
        }, {
            header: 'Количество',
            dataIndex: 'qty',
            sortable: true,
            width: 100
        }, {
            xtype: 'numbercolumn',
            align: 'right',
            header: 'Цена за единицу (р.)',
            dataIndex: 'unit_price',
            sortable: true,
            width: 120
        }, {
            xtype: 'numbercolumn',
            align: 'right',
            header: 'Сумма (р.)',
            width: 120,
            renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                return (record.get('qty') * record.get('unit_price'));
            }
        }];
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            remoteSort: true,
            root: 'data',
            id: 'id',
            totalProperty: 'totalCount',
            fields: ['id', 'name', 'measure', 'qty', 'checked', 
                {name: 'unit_price', type: 'float'}]
        });
        
        this.filtersPlugin = new Ext.grid.GridFilters({
            filters: [{type: 'string',  dataIndex: 'name'}]
        });
        
        this.plugins = [this.filtersPlugin];

        this.tbar = [{
            text: 'Добавить',
            iconCls: 'add',
            hidden: !(this.permissions && !this.readOnly),
            qtip: 'Добавить ТМЦ',
            handler: this.onAdd,
            scope: this
        }, ' ', this.filtersPlugin.getSearchField({width: 400}), ' ', ' ', ' '];
        
        this.bbar = new xlib.PagingToolbar({
            plugins: [this.filtersPlugin],
            store: this.ds
        });
        
        if (!this.readOnly) {
            
            var actions = new xlib.grid.Actions({
                autoWidth: true,
                items: [{
                    text: 'Оприходовать',
                    iconCls: 'check-in',
                    hidden: !this.permissions,
                    handler: this.onIncome,
                    scope: this
                }, function(g, rowIndex) {
                    return {
                        text: 'Выдать',
                        iconCls: 'check-out',
                        hidden: !g.permissions,
                        disabled: !(g.getStore().getAt(rowIndex).get('qty') > 0),
                        handler: g.onOutgo,
                        scope: g
                    }
                }, '-', {
                    text: 'История движения ТМЦ',
                    iconCls: 'details',
                    handler: function(g, rowIndex) {
                        var record = g.getStore().getAt(rowIndex);
                        new PMS.Storage.Assets.History({
                            assetId: record.get('id'),
                            assetName: record.get('name')
                        });
                    },
                    scope: this
                }, '-', {
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
            
            var checkColumn = new xlib.grid.CheckColumn({
                header: 'Пометка',
                width: 65,
                dataIndex: 'checked'
            });
            
            this.tbar.push({
                text: 'Сброс пометок',
                iconCls: 'settings',
                qtip: 'Сбросить все пометки со всех ТМЦ',
                handler: this.resetChecks,
                scope: this
            });
            
            this.tbar.push({
                text: 'Отчёт',
                iconCls: 'work_schd-icon',
                hidden: !acl.isView('reports'),
                handler: function() {
                     window.open(link('storage', 'report', 'index', {}, 'html'));
                }
            });
            
            this.plugins.push(checkColumn);
            
            this.plugins.push(actions);
            
            this.columns = [checkColumn].concat(this.columns);
            
            this.on({
                rowdblclick: this.permissions ? this.onUpdate : Ext.emptyFn,
                afteredit: function(params) {
                    if ('checked' == params.field) {
                        this.onCheck(params.record);
                    }
                },
                scope: this
            });
            
        }
     
        PMS.Storage.Assets.List.superclass.initComponent.apply(this, arguments);
        
        if (!this.title) {
            this.setTitle(this.baseTitle);
        }
    },
    
    onAdd: function(b, e) {

        var editAsset = new PMS.Storage.Assets.Edit.Layout({
            inWindow: true,
            categoryId: this.categoryId,
            listeners: {
                saved: function() {
                    this.getStore().reload();
                },
                scope: this
            }
        });

    },
    
    onUpdate: function(g, rowIndex) {

        var editAsset = new PMS.Storage.Assets.Edit.Layout({
            record: g.getStore().getAt(rowIndex), 
            inWindow: true,
            listeners: {
                saved: function() {
                    this.getStore().reload();
                },
                scope: this
            }
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
    
    onIncome: function(g, rowIndex) {

        var editAsset = new PMS.Storage.Assets.Movement({
            isIncome: true,
            record: g.getStore().getAt(rowIndex), 
            listeners: {
                saved: function() {
                    this.getStore().reload();
                },
                scope: this
            }
        });

    },
    
    onOutgo: function(g, rowIndex) {

        var editAsset = new PMS.Storage.Assets.Movement({
            isIncome: false,
            record: g.getStore().getAt(rowIndex), 
            listeners: {
                saved: function() {
                    this.getStore().reload();
                },
                scope: this
            }
        });

    },
    
    onCheck: function(record) {
        
        this.disable();
        
        Ext.Ajax.request({
           url: this.checkURL,
           params: { 
                id: record.get('id'),
                value: record.get('checked')
           },
           success: function(res) {
                var errors = Ext.decode(res.responseText).errors;
                if (errors) {
                    xlib.Msg.error(errors[0].msg);
                    record.reject();
                    this.el.unmask();
                    return;
                }
                record.commit();
                this.enable();
            },
            failure: function() {
                xlib.Msg.error('Ошибка связи с сервером.');
                record.reject();
                this.enable();
            },
            scope: this
        });
        
    },
    
    resetChecks: function(b) {
        
        xlib.Msg.confirm(b.qtip + '?', function() {
            
            Ext.Ajax.request({
                url: this.resetChecksURL,
                callback: function (options, success, response) {
                    var msg = 'Ошибка сброса.';
                    var res = xlib.decode(response.responseText);
                    if (true == success && res) {
                        if (true == res.success) {
                            this.getStore().reload();
                            return;
                        } else if (errors) {
                            var msg;
                        }
                    }
                    xlib.Msg.error(msg);
                },
                scope: this
            });
            
        }, this);
        
    },
    
    loadList: function(categoryId, categoryName) {
        this.setTitle(this.baseTitle + '"' + (categoryName || '') + '"');
        this.categoryId = categoryId;
        this.getStore().setBaseParam('categoryId', categoryId);
        this.getStore().load();
    }
});

Ext.reg('PMS.Storage.Assets.List', PMS.Storage.Assets.List);