Ext.ns('PMS.Orders.Edit');

PMS.Orders.Edit.Info = Ext.extend(Ext.Panel, {
    
    layout: 'form',
    
    padding: '5px',
    
    title: 'Детали',
    
    permissions: acl.isUpdate('orders'),
    
    autoHeight: true,
    
    border: false,
    
    defaults: {
        xtype: 'panel',
        anchor: '100%',
        border: false,
        layout: 'column',
        columns: 2,
        defaults: {
			columnWidth: .5,
            border: false,
            layout: 'form'
        }
    },
    
    initComponent: function() {

        this.items = [{
        	xtype: 'PMS.Customers.Combo',
        	disabled: !acl.isUpdate('customers'),
        	anchor: '-1'
        }, {
            xtype: 'textfield',
            name: 'address',
            fieldLabel: 'Адрес',
            disabled: !acl.isUpdate('orders', 'address')
        }, {
            items: [{
                items: [{
                    name: 'success_date_planned',
                    hiddenName: 'success_date_planned',
                    xtype: 'xlib.form.DateField',
                    fieldLabel: 'Сдача (план)',
                    disabled: !acl.isUpdate('orders', 'success', 'planned'),
                    anchor: 0
                }, {
                    name: 'success_date_fact',
                    hiddenName: 'success_date_fact',
                    xtype: 'xlib.form.DateField',
                    fieldLabel: 'Сдача (факт)',
                    disabled: !acl.isUpdate('orders', 'success', 'fact'),
                    anchor: 0
                }]
            }, {
                items: [{
        			xtype: 'checkbox',
        			fieldLabel: 'Производство',
        			boxLabel: 'вкл./выкл.',
        			name: 'production',
                    inputValue: 1,
                    checked: true,
        			hidden: !acl.isView('orders', 'production'),
        			hideLabel: !acl.isView('orders', 'production'),
        			disabled: !acl.isUpdate('orders', 'production'),
        			anchor: 0,
        			handler: function(cb, status) {
        				this.fireEvent('productionChecked', status);
	                }, 
                    scope: this
                }, {
                    xtype: 'checkbox',
                    fieldLabel: 'Печать',
                    boxLabel: 'вкл./выкл.',
                    name: 'print',
                    inputValue: 1,
                    checked: true,
                    hidden: !acl.isView('orders', 'print'),
                    hideLabel: !acl.isView('orders', 'print'),
                    disabled: !acl.isUpdate('orders', 'print'),
                    anchor: 0,
                    handler: function(cb, status) {
                        this.fireEvent('printChecked', status);
                    }, 
                    scope: this
                }, {
        			xtype: 'checkbox',
        			fieldLabel: 'Монтаж',
        			boxLabel: 'вкл./выкл.',
        			name: 'mount',
                    inputValue: 1,
                    checked: true,
        			hidden: !acl.isView('orders', 'mount'),
        			hideLabel: !acl.isView('orders', 'mount'),
        			disabled: !acl.isUpdate('orders', 'mount'),
        			anchor: 0,
        			handler: function(cb, status) {
        				this.fireEvent('mountChecked', status);
	                }, 
                    scope: this
                }]
            }]
        }, {
        	name: 'description',
        	xtype: 'textarea',
        	fieldLabel: 'Описание',
        	height: 160,
        	disabled: !acl.isUpdate('orders', 'description')
        }, {
        	items: [{
        		items: [{
                    name: 'cost',
                    xtype: 'numberfield',
                    hidden: !acl.isView('orders', 'cost'),
                    fieldLabel: 'Стоимость',
                    disabled: !acl.isUpdate('orders', 'cost'),
                    anchor: 0,
                    minValue: 1
        		}]
        	}, {
        		items: [{
                    name: 'advanse',
                    xtype: 'numberfield',
                    hidden: !acl.isView('orders', 'cost'),
                    fieldLabel: 'Аванс',
                    disabled: !acl.isUpdate('orders', 'cost'),
                    anchor: 0
        		}]
        	}]
        }];
        
        PMS.Orders.Edit.Info.superclass.initComponent.apply(this, arguments);
        
        this.addEvents('productionChecked', 'printChecked', 'mountChecked');
    }
});