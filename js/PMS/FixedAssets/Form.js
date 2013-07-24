Ext.ns('PMS.FixedAssets');

PMS.FixedAssets.Form = Ext.extend(xlib.form.FormPanel, {
    
	sid: null,
    
    autoHeight: true,
    
    resizable: false,
    
    loadURL: link('fixed-assets', 'index', 'get'),
    
    addURL: link('fixed-assets', 'index', 'add'),
    
    updateURL: link('fixed-assets', 'index', 'update'),
    
    region: 'center',
    
    initComponent: function() {
        
        this.items = [{
            name: 'id',
            xtype: 'hidden'
        }, {
            name: 'inventory_number',
            fieldLabel: 'Инвентарный №',
            xtype: 'textfield',
			allowBlank: true
        }, {
            name: 'name',
            fieldLabel: 'Наименование',
            xtype: 'textfield',
			allowBlank: false
        }, {
            name: 'qty',
            fieldLabel: 'Количество',
            xtype: 'numberfield',
			allowBlank: false
        }, {
            name: 'price',
            fieldLabel: 'Стоимость',
            xtype: 'numberfield',
			allowBlank: false
        }, {
            name: 'description',
            xtype: 'textarea',
            fieldLabel: 'Описание',
            allowBlank: true
        }, {
            fieldLabel: 'Ответственный',
            xtype: 'PMS.Staff.Combo',
			allowBlank: false
        }];
        
        this.on('render', this.loadData, this, {delay: 50});
                
        PMS.FixedAssets.Form.superclass.initComponent.apply(this, arguments);

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
            layout: 'border',
            labelWidth: 80,
            width: 400,
            height: this.sid ? 470 : 270,
            defaults: {
                border: false
            },
            items: [this, {
                region: 'south',
                cls: 'x-border-top',
                height: this.sid ? 200 : 0,
                itemId: this.sid,
                xtype: this.sid ? 'PMS.FixedAssets.Files' : 'panel'
            }],
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

Ext.reg('PMS.FixedAssets.Form', PMS.FixedAssets.Form);