Ext.ns('PMS.Sales.Goods');

PMS.Sales.Goods.List = Ext.extend(Ext.grid.GridPanel, {
    
    title: 'Продукция',
    
    autoScroll: true,
    
    border: false,
    
    loadMask: {msg: 'Загрузка...'},
    
    stripeRows: true,
    
    layout: 'fit',
    
    viewConfig: {autoFill: true},
	
    loadURL: link('sales', 'goods', 'get-list'),
    
    deleteURL: link('sales', 'goods', 'delete'),
    
    permissions: true,
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.cm = new Ext.grid.ColumnModel([{
            header: 'Код',
            dataIndex: 'code',
            width: 70
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
            header: 'Цена',
            dataIndex: 'price',
            xtype: 'numbercolumn',
            width: 100,
            align: 'right'
        }, {
            header: 'Себестоимость',
            dataIndex: 'total_cost',
            xtype: 'numbercolumn',
            width: 120,
            align: 'right'
        }, {
            header: 'Наценка, %',
            width: 120,
            renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                var price = record.get('price'),
                    summTotal = record.get('total_cost');
                return Math.round((price - summTotal) / (summTotal/100));
            }
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
	            {name: 'id'},
	            {name: 'code'},
	            {name: 'name'},
	            {name: 'price', type: 'float'},
	            {name: 'measure'},
	            {name: 'total_cost', type: 'float'}
	        ]
	    });
        
        this.filtersPlugin = new Ext.grid.GridFilters({
            filters: [
                {type: 'string',  dataIndex: 'code'},
                {type: 'string',  dataIndex: 'name'}
            ]}
        );
        
        this.tbar = [{
            text: 'Добавить',
            iconCls: 'add',
            handler: this.add.createDelegate(this),
            hidden: !this.permissions
        }, this.filtersPlugin.getSearchField(), {
            text: 'Импорт',
            iconCls: 'work_schd-icon',
            handler: function() {
                new PMS.Orderslog.DbfImport();
            }
        }]; 
        
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
        
        PMS.Sales.Goods.List.superclass.initComponent.apply(this, arguments);
		
        if (this.permissions) {
            this.on('rowdblclick', this.onRowdblclick, this);
        }
    },
    
    onRowdblclick: function(g, rowIndex) {
    	this.edit(g, rowIndex);
    },
   
    add: function(g, rowIndex) {
		var editWin = new PMS.Sales.Goods.Edit();
		editWin.on('saved', function() {
            editWin.close();
            this.getStore().reload(); 
        }, this);
	},
	
	edit: function(g, rowIndex) {
		var editWin = new PMS.Sales.Goods.Edit({
            sid: this.getStore().getAt(rowIndex).get('id')
        });
		editWin.on('saved', function() {
            editWin.close();
            this.getStore().reload(); 
        }, this);
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

Ext.reg('PMS.Sales.Goods.List', PMS.Sales.Goods.List);