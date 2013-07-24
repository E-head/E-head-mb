Ext.ns('PMS.Notice');

PMS.Notice.List = Ext.extend(Ext.grid.GridPanel, {

    title:      'Приказы и объявления',
    
    listURL:    link('admin', 'notice', 'get-list'),
    
    deleteURL:  link('admin', 'notice', 'delete'),
    
    loadMask: true,

    permissions: acl.isView('notice'),

    defaultSortable: true,
    
    viewConfig: {
        getRowClass: function(record) {
            return 1 == record.get('new') ? 'x-row-success' : '';
        }
    },
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            autoLoad: true,
            remoteSort: true,
            root: 'data',
            sortInfo: {
                field: 'id',
                direction: 'DESC'
            },
            totalProperty: 'totalCount',
            fields: [
                {name: 'id', type: 'int'}, 
                {name: 'dst_total', type: 'int'}, 
                {name: 'dst_read', type: 'int'}, 
                {name: 'new', type: 'int'}, 
                'type', 'text', 'account_name', 
                {
                    name: 'date', 
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
                text: 'Читать',
                iconCls: 'edit',
                hidden: !acl.isView('notice'),
                handler: this.onRead,
                scope: this
            }, {
                text: 'Редактировать',
                iconCls: 'edit',
                hidden: !acl.isUpdate('notice'),
                handler: this.onUpdate,
                scope: this
            }, {
                text: 'Удалить',
                iconCls: 'delete',
                hidden: !acl.isUpdate('notice'),
                handler: this.onDelete,
                scope: this
            }, '-', {
                text: 'Отчёт об ознакомлении',
                iconCls: 'prod_schd-icon',
                hidden: !acl.isUpdate('notice'),
                handler: this.onReport,
                scope: this
            }],
            scope: this
        });
        
        this.colModel = new Ext.grid.ColumnModel({
            defaultSortable: true,
            columns: [{
                header: '№',
                dataIndex: 'id',
                width: 40
            }, {
                header: 'Тип',
                dataIndex: 'type',
                renderer: Ext.util.Format.capitalize,
                width: 120
            }, {
                header: 'Текст',
                dataIndex: 'text',
                id: this.autoExpandColumn
            }, {
                header: 'Автор',
                dataIndex: 'account_name',
                width: 250
            }, {
                header: 'Дата',
                dataIndex: 'date',
                width: 120
            }, {
                header: 'Ознакомлены',
                width: 80,
                align: 'center',
                renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    var r = record.get('dst_read'), t = record.get('dst_total');
                    var color = (r != t) ? 'red' : 'green';
                    metaData.attr = 'style="color: ' + color + ';"'; 
                    return r + ' / ' + t;
                }
            }]
        });
        
        this.filtersPlugin = new Ext.grid.GridFilters({
            filters: [
                {type: 'string',  dataIndex: 'title'},
                {type: 'string',  dataIndex: 'text'}
            ]
        });
        
        this.plugins = [actions, this.filtersPlugin];

        this.tbar = new Ext.Toolbar({
            items: [new Ext.Toolbar.Button({
                    text: 'Добавить',
                    iconCls: 'add',
                    hidden: !this.permissions,
                    tooltip: 'Добавить',
                    handler: this.onAdd,
                    scope: this
                }), ' ', this.filtersPlugin.getSearchField({width: 400}), ' '
            ],
            plugins: [new xlib.Legend.Plugin({
                items: [{
                    color: '#99FF99',
                    text: 'Новые'
                }]
            })]
        });
        
        this.bbar = new xlib.PagingToolbar({
            plugins: [this.filtersPlugin],
            store: this.ds
        });
        
        PMS.Notice.List.superclass.initComponent.apply(this, arguments);
        
        this.on('rowdblclick', this.onRead, this);
        
    },
    
    onAdd: function(b, e) {
        
        var formPanel = new PMS.Notice.Form();
        
        formPanel.getForm().on('saved', function() {
            this.getStore().reload();
        }, this);
    },
    
    onUpdate: function(g, rowIndex) {
        
        var record = g.getStore().getAt(rowIndex);
        var id = parseInt(record.get('id'));
        
        var formPanel = new PMS.Notice.Form({
            itemId: id
        });
        
        formPanel.getForm().on('saved', function() {
            this.getStore().reload();
        }, this);
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
                callback: function(options, success, response) {
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
    
    onReport: function(g, rowIndex) {
        var record = g.getStore().getAt(rowIndex);
        var id = parseInt(record.get('id'));
        new PMS.Notice.DstInfo({itemId: id}).getWindow().show();
    },
    
    onRead: function(g, rowIndex) {
        var record = g.getStore().getAt(rowIndex);
        var id = parseInt(record.get('id'));
        var reader = new PMS.Notice.Reader();
        reader.on('close', function() {
            g.getStore().reload();
        });
        reader.show().loadData(id);
    }    
});

Ext.reg('PMS.Notice.List', PMS.Notice.List);