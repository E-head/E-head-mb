Ext.ns('PMS.Customers');

PMS.Customers.List = Ext.extend(Ext.grid.GridPanel, {
    
    actions: [],
	
    autoScroll: true,
    
    border: false,
    
    loadMask: {msg: 'Загрузка...'},
    
    stripeRows: true,
    
    layout: 'fit',
    
    viewConfig: {autoFill: true},
	
    loadURL: link('orders', 'customers', 'get-list'),
    
    deleteURL: link('orders', 'customers', 'delete'),
    
    permissions: acl.isUpdate('customers'),
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.cm = new Ext.grid.ColumnModel([{
            header: '№',
            width: 40,
            dataIndex: 'id',
            sortable: true
        }, {
            width: 200,
            header: 'Название',
            dataIndex: 'name',
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
	            {name: 'name'},
                {name: 'description'}
	        ]
	    });
        
        this.bbar = new xlib.PagingToolbar({
            store: this.ds,
            displayInfo: true,
            items: ['-', {
            	text: 'Добавить',
            	iconCls: 'add',
            	handler: this.add.createDelegate(this),
                hidden: !this.permissions
            }]
        });
        
        var actions = [{
            text: 'Добавить',
            iconCls: 'add',
            handler: this.add.createDelegate(this),
            hidden: !this.permissions
        }, '-', {
            text: 'Редактировать',
            iconCls: 'edit',
            handler: this.edit.createDelegate(this),
            hidden: !this.permissions
        }, {
            text: 'Удалить',
            iconCls: 'delete',
            handler: this.onDelete,
            hidden: !this.permissions
        }];
        
        if (Ext.isArray(this.actions) && !Ext.isEmpty(this.actions)) {
            this.actions = this.actions.concat(actions);
        } else {
            this.actions = actions;
        }
        
        var actionsPlugin = new xlib.grid.Actions({
	        autoWidth: true,
	        items: this.actions
	    });
	    
	    this.plugins = [new Ext.grid.GridFilters({
            filters: [
                {type: 'string',  dataIndex: 'name'},
                {type: 'string',  dataIndex: 'description'}
            ]}
        ), actionsPlugin];
        
        PMS.Customers.List.superclass.initComponent.apply(this, arguments);
		
        if (this.permissions) {
            this.on('rowdblclick', this.onRowdblclick, this);
        }
    },
    
    onRowdblclick: function(g, rowIndex) {
    	this.edit(g, rowIndex);
    },
   
    add: function(g, rowIndex) {
		var form = new PMS.Customers.Form({permissions: this.permissions});
		var w = form.showInWindow({title: 'Добавление'});
		form.on('saved', function() {this.getStore().reload(); w.close();}, this);
		w.show();
	},
	
	edit: function(g, rowIndex) {
		var form = new PMS.Customers.Form({
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
                                var msg;
                                switch (errors[0].code) {
                                    case -20:
                                        msg = 'Невозможно удалить. ' +
                                            'Субъект связан с одним или более заказами.'
                                        break;
                                    default:
                                        msg = errors[0].msg;
                                }
                                xlib.Msg.error(msg);
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

Ext.reg('PMS.Customers.List', PMS.Customers.List);