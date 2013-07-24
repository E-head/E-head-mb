Ext.ns('PMS.Staff.Vacations');

PMS.Staff.Vacations.Form = Ext.extend(xlib.form.FormPanel, {

    record: null,
    
    personId: null,
    
    periodMaxLength: 24,
    
    addURL:     link('staff', 'vacations', 'add'),
    
    updateURL:  link('staff', 'vacations', 'update'),
    
    markFieldsDirty: false,
    
    permissions: acl.isUpdate('staff'),
    
    labelWidth: 50,
    
    initComponent: function() {
        
        this.items = [{
            xtype: 'xlib.form.DateField',
            fieldLabel: 'Начало',
            name: 'from',
            hiddenName: 'from',
            anchor: '100%',
            allowBlank: false,
            id: 'startdt',
            vtype: 'daterange',
            endDateField: 'enddt'
        }, {
            xtype: 'xlib.form.DateField',
            fieldLabel: 'Конец',
            name: 'to',
            hiddenName: 'to',
            anchor: '100%',
            allowBlank: false,
            id: 'enddt',
            vtype: 'daterange',
            startDateField: 'startdt'
        }];
        
        PMS.Staff.Vacations.Form.superclass.initComponent.apply(this, arguments);
        
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
            width: 200,
            modal: true,
            items: [this],
            buttons: [{
                text: 'Сохранить',
                hidden: !this.permissions,
                handler: function() {
                    
                    if (!this.getForm().isValid()) {
                        return;
                    }
                    
                    if (this.periodMaxLength && this.periodMaxLength > 0) {
                        var mksday = 1000 * 60 * 60 * 24,
                            from = this.getForm().findField('startdt').getValue(),
                            to = this.getForm().findField('enddt').getValue();
                        
                        if ((to - from) > mksday * (this.periodMaxLength - 1)) {
                            xlib.Msg.error('Период отпуска не может превышать '
                                + this.periodMaxLength + '&nbsp;д.');
                            return;
                        }
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

Ext.reg('PMS.Staff.Vacations.Form', PMS.Staff.Vacations.Form);