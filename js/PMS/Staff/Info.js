Ext.ns('PMS.Staff');

PMS.Staff.Info = Ext.extend(xlib.form.FormPanel, {
	
    title: false,
    
    permissions: true,
    
    autoHeight: true,

    personId: null,
            
	initComponent: function() {
        
        if (!this.personId) {
            throw 'personId is required!';
        }
        
        this.items = [{
            layout: 'column',
            border: false,
            columns: 3,
            defaults: {
                border: false,
                layout: 'form',
                defaults: {
                    style: 'line-height: 19px;'
                }
            },
            items: [{
                columnWidth: .6,
                labelWidth: 80,
                items: [{
                    xtype: 'displayfield',
                    fieldLabel: 'Имя',
                    name: 'name'
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Должность',
                    name: 'function'
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Категория',
                    name: 'category_name'
                }]
            }, {
                columnWidth: .4,
                items: [{
                    xtype: 'displayfield',
                    fieldLabel: 'Принят на работу',
                    name: 'hire_date'
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Система оплаты',
                    name: 'pay_period'
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Тариф',
                    name: 'pay_rate'
                }]
            }]
        }];

        this.initialConfig = {
            
            url: link('staff', 'index', 'get'),
            
            baseParams: {
                id: this.personId
            },
            
            reader: new Ext.data.JsonReader({
                idProperty: 'id',
                root: 'data',
                fields: ['name', 'function', 'category_name', {
                    name: 'hire_date', type: 'date', 
                    convert: function(v, record) {
                        return Ext.util.Format.date(
                            Date.parseDate(v, xlib.date.DATE_FORMAT_SERVER), 
                            xlib.date.DATE_FORMAT
                        );
                    }
                }, {
                    name: 'pay_period', 
                    convert: function(v, record) {
                        var pay_periods = new Ext.util.MixedCollection(); 
                        Ext.each(PMS.Staff.PayPeriods, function(item) {
                            pay_periods.add(item[0], item[1]);
                        });
                        return pay_periods.get(v);
                    }
                }, {
                    name: 'pay_rate', 
                    convert: function(v, record) {
                        var str = Ext.util.Format.number(v, '0,000.00');
                        return str.replace(/,/g, ' ') + ' руб.';
                    }
                }]
            })
        };
        
		PMS.Staff.Info.superclass.initComponent.apply(this, arguments);
        
        this.getForm().load();
	}
});

Ext.reg('PMS.Staff.Info', PMS.Staff.Info);