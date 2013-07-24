Ext.ns('PMS.Orders.Edit');

PMS.Orders.Edit.Production = Ext.extend(Ext.Panel, {
    
	title: 'Производство',
	
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
                name: 'production_start_planned',
                hiddenName: 'production_start_planned',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Начало - план',
                disabled: !acl.isUpdate('orders', 'production', 'start_planned')
            }]
        }, {
            items: [{ 
                name: 'production_start_fact',
                hiddenName: 'production_start_fact',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Начало - факт',
                disabled: !acl.isUpdate('orders', 'production', 'start_fact')
            }]
        }, {
            items: [{             
                name: 'production_end_planned',
                hiddenName: 'production_end_planned',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Конец - план',
                disabled: !acl.isUpdate('orders', 'production', 'end_planned')
            }]
        }, {
            items: [{ 
                name: 'production_end_fact',
                hiddenName: 'production_end_fact',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Конец - факт',
                disabled: !acl.isUpdate('orders', 'production', 'end_fact')
            }]
        }];

        PMS.Orders.Edit.Production.superclass.initComponent.apply(this, arguments);
    }
});