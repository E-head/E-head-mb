Ext.ns('PMS.Orderslog');

PMS.Orderslog.Form = Ext.extend(xlib.form.FormPanel, {
    
	sid: null,
    
    autoHeight: true,
    
    resizable: false,
    
    addURL: link('orderslog', 'index', 'add'),
    
    labelWidth: 200,
    
    defaults: {
        
        allowBlank: false,
        
        allowNegative: false
        
    }, 
    
    initComponent: function() {
        
        this.items = [{
            name: 'id',
            xtype: 'hidden'
        }, {
            name: 'date',
            hiddenName: 'date',
            fieldLabel: 'Дата',
            xtype: 'xlib.form.DateField',
            value: new Date()
        }, {
            name: 'staff_id',
            hiddenName: 'staff_id',
            fieldLabel: 'Ответственный сотрудник',
            xtype: 'PMS.Staff.Combo'
        }, {
            name: 'summ_start',
            fieldLabel: 'Остаток в кассе на начало дня',
            xtype: 'numberfield',
            validator: this.validateInkasso.createDelegate(this)
        }, {
            name: 'summ_income',
            fieldLabel: 'Сумма выручки за день',
            xtype: 'numberfield',
            validator: this.validateInkasso.createDelegate(this)
        }, {
            name: 'summ_inkasso',
            fieldLabel: 'Сумма инкассации',
            xtype: 'numberfield',
            validator: this.validateInkasso.createDelegate(this)
        }, {
            name: 'summ_rest',
            readOnly: true,
            fieldLabel: 'Остаток в кассе на конец дня',
            xtype: 'numberfield'
        }, {
            fieldLabel: 'Назначение инкассации',
            xtype: 'displayfield'
        }, {
            name: 'inkasso_dst',
            xtype: 'textfield',
            hideLabel: true,
            value: 'Банк'
        }];
        
        PMS.Orderslog.Form.superclass.initComponent.apply(this, arguments);

        this.addEvents('saved');
    },
    
    onSave: function() {
        if (this.getForm().isValid()) {
            var params = {};
            if (this.sid) {
            	params['id'] = this.sid;
            }
            this.getForm().submit({
                url: this.sid ? this.updateURL : this.addURL,
                waitMsg: 'Запись...',
                params: params,
                success: function(f, action) {
                    if (true === action.result.success) {
                        this.sid = action.result.id;
                        this.fireEvent('saved', this.sid);
                    } else {
                        this.onFailure(f, action);
                    }
                }, 
                failure: function(response, options) {
                    var res = Ext.decode(response.responseText);
                    this.onFailure(res, options);
                },
                scope: this
            });
        }
    },
    
    showInWindow: function(cfg) {
        var w = new Ext.Window(Ext.apply({
            width: 400,
            height: 280,
            items: [this],
            modal: true,
            buttons: [
                {text: 'Сохранить', handler: this.onSave.createDelegate(this)}, 
                {text: 'Отменить', handler: function() {w.close();}}
            ] 
        }, cfg || {}));
        w.show();
        return w;
    },
    
    validateInkasso: function() {
        
        var summ_start     = this.getForm().findField('summ_start').getValue(),
            summ_income    = this.getForm().findField('summ_income').getValue(),
            summ_inkasso   = this.getForm().findField('summ_inkasso').getValue(),
            summ_restField = this.getForm().findField('summ_rest'),
            isValid        = summ_inkasso <= (summ_income + summ_start);
            
            var summ = summ_income + summ_start - summ_inkasso;
            summ_restField.setValue( !isValid ? 0 : Math.round(summ*100) / 100);
            
        return isValid; 
    },
    
    onFailure: Ext.emptyFn
});

Ext.reg('PMS.Orderslog.Form', PMS.Orderslog.Form);