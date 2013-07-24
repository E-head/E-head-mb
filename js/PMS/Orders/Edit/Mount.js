Ext.ns('PMS.Orders.Edit');

PMS.Orders.Edit.Mount = Ext.extend(Ext.Panel, {
    
    title: 'Монтаж',
    
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
                name: 'mount_start_planned',
                hiddenName: 'mount_start_planned',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Начало - план',
                disabled: !acl.isUpdate('orders', 'mount', 'start_planned')
            }]
        }, {
            items: [{
                name: 'mount_start_fact',
                hiddenName: 'mount_start_fact',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Начало - факт',
                disabled: !acl.isUpdate('orders', 'mount', 'start_fact')
            }]
        }, {
            items: [{
                name: 'mount_end_planned',
                hiddenName: 'mount_end_planned',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Конец - план',
                disabled: !acl.isUpdate('orders', 'mount', 'end_planned')
            }]
        }, {
            items: [{
                name: 'mount_end_fact',
                hiddenName: 'mount_end_fact',
                xtype: 'xlib.form.DateField',
                fieldLabel: 'Конец - факт',
                disabled: !acl.isUpdate('orders', 'mount', 'end_fact')
            }]
        }];
        
        PMS.Orders.Edit.Mount.superclass.initComponent.apply(this, arguments);
    }
});