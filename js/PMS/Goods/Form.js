Ext.ns('PMS.Goods');

PMS.Goods.Form = Ext.extend(xlib.form.FormPanel, {
    
    permissions: acl.isUpdate('goods'),
    
    defaults: {
        anchor: '100%'
    },
    
    initComponent: function() {
        
        this.items = [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'textfield',
            fieldLabel: 'Наименование',
            name: 'name'
        }, {
            xtype: 'numberfield',
            fieldLabel: 'Цена',
            name: 'price'
        }];
        
        PMS.Goods.Form.superclass.initComponent.apply(this, arguments);
    }
    
});