Ext.ns('PMS.Staff.Vacations');

PMS.Staff.Vacations.List = Ext.extend(Ext.grid.GridPanel, {

    title:  'Список отпусков',
    
    listURL:    link('staff', 'vacations', 'get-list'),
    
    deleteURL:  link('staff', 'vacations', 'delete'),
    
    personId: null,
    
    loadMask: true,

    permissions: acl.isUpdate('staff'),

    defaultSortable: true,
    
    viewConfig: {
        forceFit: true    
    },
    
    initComponent: function() {
        
        if (!this.personId) {
            throw 'personId is required!';
        }
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            remoteSort: true,
            autoLoad: true,
            root: 'data',
            sortInfo: {
                field: 'from',
                direction: 'DESC'
            },
            totalProperty: 'totalCount',
            baseParams: {
                staff_id: this.personId
            },
            fields: [{
                name: 'id', 
                type: 'int'
            }, {
                name: 'from', 
                type: 'date', 
                dateFormat: xlib.date.DATE_FORMAT_SERVER
            }, {
                name: 'to', 
                type: 'date', 
                dateFormat: xlib.date.DATE_FORMAT_SERVER
            }]
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        
        var actions = new xlib.grid.Actions({
            autoWidth: true,
            items: [{
                text: 'Редактировать',
                iconCls: 'edit',
                hidden: !acl.isUpdate('staff'),
                handler: this.onUpdate,
                scope: this
            }, {
                text: 'Удалить',
                iconCls: 'delete',
                hidden: !acl.isUpdate('staff'),
                handler: this.onDelete,
                scope: this
            }, '-', {
                text: 'Добавить',
                iconCls: 'add',
                hidden: !acl.isUpdate('staff'),
                handler: this.onAdd,
                scope: this
            }],
            scope: this
        });
        
        this.colModel = new Ext.grid.ColumnModel({
            defaultSortable: true,
            columns: [{
                header: 'Начало',
                dataIndex: 'from',
                renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    return Ext.util.Format.date(value, xlib.date.DATE_FORMAT);
                }
            }, {
                header: 'Конец',
                dataIndex: 'to',
                renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    return Ext.util.Format.date(value, xlib.date.DATE_FORMAT);
                }
            }, {
                header: 'Продолжительность (д.)',
                renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                    var mksday = 1000 * 60 * 60 * 24, 
                        from = record.get('from'), 
                        to = record.get('to');
                    return ((to - from) / mksday + 1);
                }
            }]
        });
                
        this.plugins = [actions];
        
        this.bbar = new xlib.PagingToolbar({
            store: this.ds
        });
        
        PMS.Staff.Vacations.List.superclass.initComponent.apply(this, arguments);
        
        if (this.permissions) {
            this.on('rowdblclick', this.onUpdate, this);
        }
    },
    
    onAdd: function(b, e) {
        
        var formPanel = new PMS.Staff.Vacations.Form({
            personId: this.personId
        });
        
        formPanel.getForm().on('saved', function() {
            this.getStore().reload();
        }, this);
    },
    
    onUpdate: function(g, rowIndex) {
        
        var record = g.getStore().getAt(rowIndex);
        
        var formPanel = new PMS.Staff.Vacations.Form({
            record: record
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
    }
});

Ext.reg('PMS.Staff.Vacations.List', PMS.Staff.Vacations.List);