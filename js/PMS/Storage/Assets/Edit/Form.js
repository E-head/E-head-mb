Ext.ns('PMS.Storage.Assets.Edit');

PMS.Storage.Assets.Edit.Form = Ext.extend(xlib.form.FormPanel, {
    
    permissions: acl.isUpdate('storage'),
    
    defaults: {
        anchor: '100%'
    },
    
    initComponent: function() {
        
        var updateSumm = function() {
            var summ = qtyField.getValue() * unitPriceField.getValue();
            summ = Ext.util.Format.number(summ, '0,000.00');
            summField.setValue(summ.replace(',', ' ') + ' р.');
        };
        
        var summField = new Ext.form.DisplayField({
            style: 'line-height: 18px;',
            anchor: 0,
            fieldLabel: 'Сумма'
        });
        
        var qtyField = new Ext.form.DisplayField({
            style: 'line-height: 18px;',
            anchor: 0,
            name: 'qty',
            value: 0,
            fieldLabel: 'Количество'
        });
        
        var unitPriceField = new Ext.form.NumberField({
            fieldLabel: 'Цена за ед. (р.)',
            name: 'unit_price',
            enableKeyEvents: true,
            listeners: {
                keyup: updateSumm
            }
        });
        
        this.items = [{
            xtype: 'label',
            fieldLabel: 'Наименование'
        }, {
            xtype: 'textarea',
            hideLabel: true,
            height: 190,
            name: 'name'
        }, {
            xtype: 'PMS.Storage.Measures.ComboBox',
            fieldLabel: 'Ед. измерения',
            name: 'measure',
            hiddenName: 'measure'
        }, unitPriceField, qtyField, summField];
        
        PMS.Storage.Assets.Edit.Form.superclass.initComponent.apply(this, arguments);
        
        this.on('ready', updateSumm, this, {delay: 200});
    }
    
});