Ext.ns('PMS.Goods');

PMS.Goods.Form = Ext.extend(xlib.form.FormPanel, {
    
    permissions: acl.isUpdate('admin'),
    
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
        }, {
            xtype: 'textarea',
            fieldLabel: 'Описание',
            name: 'descr'
        }];
        
        PMS.Goods.Form.superclass.initComponent.apply(this, arguments);
    }
    
});