Ext.ns('PMS.Order');

PMS.Order.Form = Ext.extend(xlib.form.FormPanel, {
    
    title: 'Заказ товаров',
    
    permissions: true,
    
    initComponent: function() {
        
        this.items = [];
        
        this.dateField = new xlib.form.DateField({
            format: 'd-m-Y'
        });
        
        this.summField = new Ext.form.DisplayField({
            value: '0.00 р.'
        }); 
        
        this.bbar = ['->', {
            xtype: 'tbtext',
            text: 'На дату:'
        }, ' ', this.dateField, ' ', ' ', ' ', ' ', ' ', {
            xtype: 'tbtext',
            text: 'Сумма:'
        }, ' ', this.summField, ' ', ' ', ' ', ' ', ' ', {
            text: 'Сделать<br/>заказ',
            pressed: true,
            scale: 'large'
        }];
        
        PMS.Order.Form.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('PMS.Order.Form', PMS.Order.Form);