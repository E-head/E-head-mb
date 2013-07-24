Ext.ns('xlib.Acl.Accounts');

xlib.Acl.Accounts.Form = Ext.extend(xlib.form.FormPanel, {
    
    accountId: null,
    
    permissions: true,
    
    defaultType: 'textfield',
    
    initComponent: function() {
        
        this.items = [{
            fieldLabel: 'Логин',
            name: 'login',
            disabled: true
        }, {
            fieldLabel: 'Имя',
            name: 'name'
        }, {
            fieldLabel: 'Email',
            name: 'email',
            vtype: 'email'
        }, {
            fieldLabel: 'Телефон',
            name: 'phone',
            allowBlank: true
        }, {
            fieldLabel: 'Активна',
            xtype: 'checkbox',
            name: 'active',
            inputValue: 1
        }];
        
        this.reader = this.initialConfig.reader = new Ext.data.JsonReader({
            root: 'rows'
        }, [
            'login', 'name', 'email', 'phone', 'active'
        ]);
        
        xlib.Acl.Accounts.Form.superclass.initComponent.apply(this, arguments);
    },
    
    getWindow: function() {
        var w = new Ext.Window({
            title: 'Редактирование учётной записи',
            resizable: false,
            modal: true,
            width: 300,
            items: [this],
            buttons: [{
                text: 'Сохранить',
                handler: function() {
                    this.getForm().submit({
                        url: link('admin', 'accounts', 'update'),
                        params: {
                            id: this.accountId
                        },
                        waitMsg: 'Сохранение...',
                        success: function() {
                            w.close();
                        }, 
                        failure: function() {
                            xlib.Msg.error('Сохранение не удалось.');
                        },
                        scope: this
                    });
                },
                scope: this
            }, {
            	text: 'Отмена',
                handler: function() {
                    w.close();
                },
                scope: this
            }],
            scope: this
        });
        
        w.on('show', function() {
            this.getForm().load({
                url: link('admin', 'accounts', 'fetch'),
                params: {
                    id: this.accountId
                },
                scope: this
            });
        }, this);
        return w;
    }
});

Ext.reg('xlib.acl.accounts.form', xlib.Acl.Accounts.Form);