Ext.ns('PMS.Customers');

PMS.Customers.Form = Ext.extend(xlib.form.FormPanel, {
    
	sid: null,
    
    autoHeight: true,
    
    resizable: false,
    
    loadURL: link('orders', 'customers', 'get'),
    
    addURL: link('orders', 'customers', 'add'),
    
    updateURL: link('orders', 'customers', 'update'),
    
    initComponent: function() {
        
        this.items = [{
            name: 'id',
            xtype: 'hidden'
        }, {
            name: 'name',
            fieldLabel: 'Наименование',
            xtype: 'textfield',
            sortable: true,
			allowBlank: false
        }, {
            name: 'description',
            xtype: 'textarea',
            fieldLabel: 'Описание',
            sortable: true,
            allowBlank: true
        }];
        
        this.on('render', this.loadData, this, {delay: 50});
                
        PMS.Customers.Form.superclass.initComponent.apply(this, arguments);

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
            labelWidth: 80,
            width: 400,
            autoHeight: true,
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
    
    loadData: function() {
    	if (this.sid) {
	        this.load({
	            url: this.loadURL,
	            params: {id: this.sid},
	            waitMsg: 'Загрузка...',
	            success: function(form, options) {this.fireEvent('load');},
	            scope: this
	        });
    	}
    },
    
    onFailure: Ext.emptyFn
});

Ext.reg('PMS.Customers.Form', PMS.Customers.Form);