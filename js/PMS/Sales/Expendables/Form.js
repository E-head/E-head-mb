Ext.ns('PMS.Sales.Expendables');

PMS.Sales.Expendables.Form = Ext.extend(xlib.form.FormPanel, {
    
	sid: null,
    
    loadURL: link('sales', 'expendables', 'get'),
    
    addURL: link('sales', 'expendables', 'add'),
    
    updateURL: link('sales', 'expendables', 'update'),
    
    defaults: {allowBlank: false},
    
    initComponent: function() {
        
        this.items = [{
            xtype: 'hidden',
            name: 'id'
        }, {
            fieldLabel: 'Наименование',
            xtype: 'textfield',
            name: 'name'
        }, {
            fieldLabel: 'Стоимость',
            xtype: 'numberfield',
            name: 'price',
            allowNegative: false
        }, {
            fieldLabel: 'Ед. измерения',
            xtype: 'PMS.Storage.Measures.ComboBox',
            name: 'measure',
            hiddenName: 'measure'
        }];
        
        this.on('render', this.loadData, this, {delay: 50});
                
        PMS.Sales.Expendables.Form.superclass.initComponent.apply(this, arguments);

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
                success: function(form, action) {
                    if (true === action.result.success) {
                        this.sid = action.result.id;
                        this.fireEvent('saved', this.sid);
                    } else {
                        this.onFailure(form, action);
                    }
                }, 
                failure: this.onFailure,
                scope: this
            });
        }
    },
    
    showInWindow: function(cfg) {
        var w = new Ext.Window(Ext.apply({
            labelWidth: 80,
            width: 400,
            modal: true,
            items: [this],
            buttons: [
                {text: 'Сохранить', handler: this.onSave.createDelegate(this)}, 
                {text: 'Отменить', handler: function() {w.close();}}
            ] 
        }, cfg || {}));
        w.show();
        return w;
    },
    
    loadData: function() {
    	if (this.sid) {
	        this.load({
	            url: this.loadURL,
	            params: {id: this.sid},
	            waitMsg: 'Загрузка...',
	            success: function(form, options) {this.fireEvent('load');},
                failure: this.onFailure,
	            scope: this
	        });
    	}
    },
    
    onFailure: function() {
        xlib.Msg.error('Ошибка сервера');
    }
});

Ext.reg('PMS.Sales.Expendables.Form', PMS.Sales.Expendables.Form);