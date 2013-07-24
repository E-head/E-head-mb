Ext.ns('PMS.Sales.Goods');

PMS.Sales.Goods.Form = Ext.extend(xlib.form.FormPanel, {
    
    autoHeight: true,

    permissions: true,
    
    labelWidth: 200,
    
    defaults: {
        xtype: 'textfield',
        allowBlank: false,
        anchor: '100%'
    },
    
    initComponent: function() {
        
        this.priceField = new Ext.form.NumberField({
            fieldLabel: 'Цена, р',
            allowNegative: false,
            name: 'price'
        });
        
        this.lossMarginField = new Ext.form.NumberField({
            fieldLabel: 'Размер списания, %',
            allowNegative: false,
            allowDeciminals: false,
            name: 'loss_margin'
        });
        
        this.costField = new Ext.form.DisplayField({
            fieldLabel: 'Себестоимость',
            name: 'cost',
            style: 'line-height: 20px;'
        });
        
        this.marginField = new Ext.form.DisplayField({
            fieldLabel: 'Наценка на продукт',
            name: 'margin',
            style: 'line-height: 20px;'
        });
        
        this.items = [{
            name: 'id',
            xtype: 'hidden'
        }, {
            fieldLabel: 'Код',
            name: 'code'
        }, {
            fieldLabel: 'Наименование',
            name: 'name'
        }, {
            xtype: 'PMS.Storage.Measures.ComboBox',
            fieldLabel: 'Ед. измерения',
            name: 'measure',
            hiddenName: 'measure'
        }, this.priceField, this.lossMarginField, this.costField, this.marginField];
        
        PMS.Sales.Goods.Form.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('PMS.Sales.Goods.Form', PMS.Sales.Goods.Form);