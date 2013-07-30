Ext.ns('PMS.Orders');

PMS.Orders.Form = Ext.extend(xlib.form.FormPanel, {
    
    permissions: true,
    
    initComponent: function() {
        
        this.items = [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'xlib.form.DateField',
            format: 'd-m-Y',
            fieldLabel: 'Дата',
            allowBlank: false,
            name: 'ondate',
            hiddenName: 'ondate',
            value: (new Date())
        }];
        
        PMS.Orders.Form.superclass.initComponent.apply(this, arguments);
    }
});