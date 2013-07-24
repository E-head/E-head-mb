Ext.ns('PMS.FixedAssets');

PMS.FixedAssets.List = Ext.extend(Ext.grid.GridPanel, {
    
    title: 'Основные средства',
    
    autoScroll: true,
    
    border: false,
    
    loadMask: {msg: 'Загрузка...'},
    
    stripeRows: true,
    
    layout: 'fit',
    
    viewConfig: {autoFill: true},
	
    loadURL: link('fixed-assets', 'index', 'get-list'),
    
    deleteURL: link('fixed-assets', 'index', 'delete'),
    
    permissions: acl.isUpdate('admin'),
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.cm = new Ext.grid.ColumnModel([{
            header: 'Инвентарный №',
            width: 100,
            dataIndex: 'inventory_number',
            sortable: true
        }, {
            width: 200,
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true
        }, {
            width: 100,
            header: 'Количество',
            dataIndex: 'qty',
            sortable: true
        }, {
            xtype: 'numbercolumn',
            align: 'right',
            width: 100,
            header: 'Стоимость',
            dataIndex: 'price',
            sortable: true
        }, {
            width: 200,
            header: 'Ответственный',
            dataIndex: 'staff_name',
            sortable: true
        }, {
            id: this.autoExpandColumn,
            header: 'Описание',
            dataIndex: 'description'
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
                field: 'name',
                direction: 'ASC'
            },
	        fields: [
	            {name: 'id'},
	            {name: 'inventory_number'},
	            {name: 'name'},
	            {name: 'qty'},
	            {name: 'price'},
	            {name: 'staff_name'},
                {name: 'description'}
	        ]
	    });
        
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
                window.open(link('fixed-assets', 'report', 'index', {}, 'html'));
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
	    
	    this.plugins = [new Ext.grid.GridFilters({
            filters: [
                {type: 'string',  dataIndex: 'name'},
                {type: 'string',  dataIndex: 'description'}
            ]}
        ), actionsPlugin];
        
        PMS.FixedAssets.List.superclass.initComponent.apply(this, arguments);
		
        if (this.permissions) {
            this.on('rowdblclick', this.onRowdblclick, this);
        }
    },
    
    onRowdblclick: function(g, rowIndex) {
    	this.edit(g, rowIndex);
    },
   
    add: function(g, rowIndex) {
		var form = new PMS.FixedAssets.Form({permissions: this.permissions});
		var w = form.showInWindow({title: 'Добавление'});
		form.on('saved', function() {this.getStore().reload(); w.close();}, this);
		w.show();
	},
	
	edit: function(g, rowIndex) {
		var form = new PMS.FixedAssets.Form({
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
                        failure: Ext.emptyFn(),
                        params: {id: g.getStore().getAt(rowIndex).get('id')}
                    });
                }
            },
            icon: Ext.MessageBox.QUESTION
        });
    } 
});

Ext.reg('PMS.FixedAssets.List', PMS.FixedAssets.List);