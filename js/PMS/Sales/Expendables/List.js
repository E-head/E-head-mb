Ext.ns('PMS.Sales.Expendables');

PMS.Sales.Expendables.List = Ext.extend(Ext.grid.GridPanel, {
    
    title: 'Расходные материалы',
    
    autoScroll: true,
    
    border: false,
    
    loadMask: {msg: 'Загрузка...'},
    
    stripeRows: true,
    
    layout: 'fit',
    
    viewConfig: {autoFill: true},
	
    loadURL: link('sales', 'expendables', 'get-list'),
    
    deleteURL: link('sales', 'expendables', 'delete'),
    
    permissions: true,
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.cm = new Ext.grid.ColumnModel([{
            header: '№',
            hidden: true,
            dataIndex: 'id',
            width: 40
        }, {
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true,
            id: this.autoExpandColumn
        }, {
            header: 'Ед. измерения',
            dataIndex: 'measure',
            width: 100
        }, {
            header: 'Стоимость',
            dataIndex: 'price',
            xtype: 'numbercolumn',
            width: 100,
            align: 'right'
        }]);
        
        this.sm = new Ext.grid.RowSelectionModel({singleSelect: true});

        this.ds = new Ext.data.JsonStore({
	        url: this.loadURL,
	        totalProperty: 'totalCount',
            autoLoad: true,
	        remoteSort: true,
	        root: 'data',
            sortInfo: {
                field: 'name',
                direction: 'ASC'
            },
	        fields: [
	            {name: 'id', type: 'int'},
	            {name: 'name'},
	            {name: 'measure'},
	            {name: 'price', type: 'float'}
	        ]
	    });
        
        this.filtersPlugin = new Ext.grid.GridFilters({
            filters: [{type: 'string',  dataIndex: 'name'}]}
        );
        
        this.tbar = [{
            text: 'Добавить',
            iconCls: 'add',
            handler: this.add.createDelegate(this),
            hidden: !this.permissions
        }, this.filtersPlugin.getSearchField()]; 
        
        this.bbar = new xlib.PagingToolbar({
            store: this.ds,
            displayInfo: true
        });
        
        var actionsPlugin = new xlib.grid.Actions({
	        autoWidth: true,
	        items: [{
                text: 'Редактировать',
                iconCls: 'edit',
                handler: this.edit.createDelegate(this),
                hidden: !this.permissions
            }, {
                text: 'Удалить',
                iconCls: 'delete',
                handler: this.onDelete,
                hidden: !this.permissions
            }]
	    });
	    
	    this.plugins = [this.filtersPlugin, actionsPlugin];
        
        PMS.Sales.Expendables.List.superclass.initComponent.apply(this, arguments);
		
        if (this.permissions) {
            this.on('rowdblclick', this.onRowdblclick, this);
        }
    },
    
    onRowdblclick: function(g, rowIndex) {
    	this.edit(g, rowIndex);
    },
   
    add: function(g, rowIndex) {
		var form = new PMS.Sales.Expendables.Form({permissions: this.permissions});
		var w = form.showInWindow({title: 'Добавление'});
		form.on('saved', function() {this.getStore().reload(); w.close();}, this);
		w.show();
	},
	
	edit: function(g, rowIndex) {
		var form = new PMS.Sales.Expendables.Form({
            permissions: this.permissions,
			sid: this.getStore().getAt(rowIndex).get('id')
		});
        var w = form.showInWindow({title: 'Редактирование'});
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
                        failure: function() {
                            xlib.Msg.error('Ошибка сервера');
                        },
                        params: {id: g.getStore().getAt(rowIndex).get('id')}
                    });
                }
            },
            icon: Ext.MessageBox.QUESTION
        });
    } 
});

Ext.reg('PMS.Sales.Expendables.List', PMS.Sales.Expendables.List);