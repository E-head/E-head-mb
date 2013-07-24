Ext.ns('PMS.Orderslog');

PMS.Orderslog.List = Ext.extend(Ext.grid.GridPanel, {
    
    title: 'Выручка',
    
    autoScroll: true,
    
    border: false,
    
    rest: 0,
    
    loadMask: {msg: 'Загрузка...'},
    
    stripeRows: true,
    
    layout: 'fit',
    
    loadURL: link('orderslog', 'index', 'get-list'),
    
    deleteURL: link('orderslog', 'index', 'delete'),
    
    permissions: acl.isUpdate('admin'),
    
    viewConfig: {
    
        getRowClass: function(record) {
            
            var date = record.get('date'), created = record.get('created');  
            
            if (Ext.isDate(date) && Ext.isDate(created)
            && (date.format(xlib.date.DATE_FORMAT) != created.format(xlib.date.DATE_FORMAT)) 
            ) {
                return 'x-row-expired';
            }
            
        }
        
    },
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.cm = new Ext.grid.ColumnModel([{
            header: 'Дата',
            width: 70,
            sortable: true,
            renderer: xlib.dateRenderer(xlib.date.DATE_FORMAT),
            dataIndex: 'date'
        }, {
            header: 'Ответственный сотрудник',
            dataIndex: 'staff_name',
            id: this.autoExpandColumn
        }, {
            xtype: 'numbercolumn',
            width: 200,
            align: 'right',
            header: 'Остаток в кассе на начало дня',
            dataIndex: 'summ_start'
        }, {
            xtype: 'numbercolumn',
            width: 150,
            align: 'right',
            header: 'Сумма выручки за день',
            dataIndex: 'summ_income'
        }, {
            xtype: 'numbercolumn',
            width: 120,
            align: 'right',
            header: 'Сумма инкассации',
            dataIndex: 'summ_inkasso'
        }, {
            width: 200,
            header: 'Назначение инкассации',
            dataIndex: 'inkasso_dst'
        }, {
            xtype: 'numbercolumn',
            width: 200,
            align: 'right',
            header: 'Остаток в кассе на конец дня',
            dataIndex: 'summ_rest'
        }, {
            header: 'Добавлено',
            width: 120,
            renderer: xlib.dateRenderer(xlib.date.DATE_TIME_FORMAT),
            dataIndex: 'created'
        }]);
        
        this.cm.defaultSortable = true; 

        this.sm = new Ext.grid.RowSelectionModel({singleSelect: true});

        this.ds = new Ext.data.JsonStore({
	        url: this.loadURL,
	        totalProperty: 'totalCount',
            autoLoad: true,
	        remoteSort: true,
	        root: 'data',
            sortInfo: {
                field: 'date',
                direction: 'DESC'
            },
	        fields: [
	            {name: 'id'},
	            {name: 'date', type: 'date', dateFormat: xlib.date.DATE_FORMAT_SERVER},
	            {name: 'staff_name'},
	            {name: 'summ_start', type: 'float'},
	            {name: 'summ_income', type: 'float'},
	            {name: 'summ_inkasso', type: 'float'},
	            {name: 'summ_rest', type: 'float'},
	            {name: 'inkasso_dst'},
                {name: 'created', type: 'date', dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER}
	        ]
	    });
        
        this.ds.on('load', function(store, records, options) {
            var rest = 0;
            try {
                rest = store.reader.jsonData.rest
            } catch(e) {
            }
            this.rest = rest;
        }, this);
        
        this.tbar = [{
            text: 'Добавить',
            iconCls: 'add',
            handler: this.add.createDelegate(this),
            hidden: !this.permissions
        }, {
            text: 'Отчёт',
            iconCls: 'work_schd-icon',
            hidden: !acl.isView('reports'),
            handler: function() {
                new PMS.Reports.Orderslog();
            }
        }];
        
        this.bbar = new xlib.PagingToolbar({
            store: this.ds,
            displayInfo: true
        });
        
        var actionsPlugin = new xlib.grid.Actions({
	        autoWidth: true,
	        items: [{
                text: 'Удалить',
                iconCls: 'delete',
                handler: this.onDelete,
                hidden: !this.permissions
            }]
	    });
	    
	    this.plugins = [actionsPlugin];
        
        PMS.Orderslog.List.superclass.initComponent.apply(this, arguments);
    },
    
    onRowdblclick: function(g, rowIndex) {
    	this.edit(g, rowIndex);
    },
   
    add: function(g, rowIndex) {
		var form = new PMS.Orderslog.Form({permissions: this.permissions});
		var w = form.showInWindow({title: 'Выручка за день'});
		form.on('ready', function() {
            try {
                var summ_startField = form.getForm().findField('summ_start');
                if (summ_startField && summ_startField.setValue) {
                    summ_startField.setValue(this.rest);
                }
            } catch(e) {
            }
        }, this);
		form.on('saved', function() {this.getStore().reload(); w.close();}, this);
		w.show();
	},
    
    onDelete: function(g, rowIndex) {
        Ext.Msg.show({
            title: 'Подтверждение',
            msg: 'Вы уверены?',
            buttons: Ext.Msg.YESNO,
            fn: function(b) {
                if ('yes' == b) {
                    Ext.Ajax.request({
                        url: g.deleteURL,
                        success: function(res) {
                            var errors = Ext.decode(res.responseText).errors;
                            if (errors) {
                                xlib.Msg.error(errors[0].msg);
                                return;
                            }
                            g.getStore().reload();
                        },
                        failure: Ext.emptyFn(),
                        params: {id: g.getStore().getAt(rowIndex).get('id')}
                    });
                }
            },
            icon: Ext.MessageBox.QUESTION
        });
    } 
});

Ext.reg('PMS.Orderslog.List', PMS.Orderslog.List);