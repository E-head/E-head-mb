Ext.ns('PMS.Staff.Payments');

PMS.Staff.Payments.Form = Ext.extend(xlib.form.FormPanel, {

    record: null,
    
    addURL:     link('staff', 'payments', 'add'),
    
    updateURL:  link('staff', 'payments', 'update'),
    
    markFieldsDirty: false,
    
    permissions: acl.isUpdate('staff'),
    
    personId: null,
    
    initComponent: function() {
        
        this.items = [{
            xtype: 'xlib.form.DateField',
            fieldLabel: 'Дата',
            name: 'date',
            hiddenName: 'date',
            anchor: '100%',
            allowBlank: false,
            value: new Date()
        }, {
            xtype: 'numberfield',
            fieldLabel: 'Сумма (руб.)',
            name: 'value',
            anchor: '100%',
            allowDecimals: false,
            allowNegative: false,
            allowBlank: false
        }];
        
        PMS.Staff.Payments.Form.superclass.initComponent.apply(this, arguments);
        
        if (this.record) {
            this.itemId = this.record.get('id');
            this.getForm().loadRecord(this.record);
        }
        
        var w = this.getWindow(this.itemId).show();
        
    },
    
    // Private functions 
    
    getWindow: function(id) {
        
        var params = {}
        
        if (id) {
            params.id = id;
        }
        
        if (this.personId) {
            params.staff_id = this.personId;
        }
        
        var w = new Ext.Window({
            title: !id ? 'Новая запись' : 'Запись № ' + id,
            resizable: false,
            hidden: false,
            width: 300,
            modal: true,
            items: [this],
            buttons: [{
                text: 'Сохранить',
                hidden: !this.permissions,
                handler: function() {
                    
                    if (!this.getForm().isValid()) {
                        return;
                    }
                    
                    this.getForm().submit({
                        params: params,
                        url: !this.itemId ? this.addURL : this.updateURL,
                        success: function(form, options) {
                            var o = options.result;
                            if (true == o.success) {
                                form.fireEvent('saved');
                                w.close();
                                return;
                            }
                            xlib.Msg.error('Не удалось сохранить.')
                        },
                        failure: function() {
                            xlib.Msg.error('Не удалось сохранить.')
                        }
                    });
                },
                scope: this
            }, {
                text: 'Отмена',
                handler: function() {
                    w.close();
                }
            }]
        });
        
        return w;
    }
});

Ext.reg('PMS.Staff.Payments.Form', PMS.Staff.Payments.Form);