Ext.ns('PMS.Orders.Edit');

PMS.Orders.Edit.Print = Ext.extend(Ext.Panel, {
    
	title: 'Печать',
	
	layout: 'column',

	columns: 2,
    
    padding: '5px',

    autoHeight: true,
    
    border: false,
    
    defaults: {
        layout: 'form',
        columnWidth: .5,
        border: false
    },
    
    initComponent: function() {

        this.items = [{
            items: [{ 
                name: 'print_start_planned',
                hiddenName: 'print_start_planned',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Начало - план',
                disabled: !acl.isUpdate('orders', 'print', 'start_planned')
            }]
        }, {
            items: [{ 
                name: 'print_start_fact',
                hiddenName: 'print_start_fact',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Начало - факт',
                disabled: !acl.isUpdate('orders', 'print', 'start_fact')
            }]
        }, {
            items: [{             
                name: 'print_end_planned',
                hiddenName: 'print_end_planned',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Конец - план',
                disabled: !acl.isUpdate('orders', 'print', 'end_planned')
            }]
        }, {
            items: [{ 
                name: 'print_end_fact',
                hiddenName: 'print_end_fact',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Конец - факт',
                disabled: !acl.isUpdate('orders', 'print', 'end_fact')
            }]
        }];

        PMS.Orders.Edit.Print.superclass.initComponent.apply(this, arguments);
    }
});