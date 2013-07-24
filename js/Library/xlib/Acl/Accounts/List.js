Ext.ns('xlib.Acl.Accounts');

xlib.Acl.Accounts.List = Ext.extend(Ext.grid.EditorGridPanel, {

    title: 'Учётные записи',

    viewConfig: {
        forceFit: true
    },

    loadMask: true,

    roleId: null,
    
    permissions: acl.isUpdate('admin'),
    
    initComponent: function() {
        
        this.ds = new Ext.data.Store({
            url: link('admin', 'accounts', 'get-accounts'),
            reader: new Ext.data.JsonReader({
                root: 'rows',
                id: 'id',
                totalProperty: 'total'
            }, [
                'id', 'name', 'email', 'phone', 
                {name: 'active', type: 'boolean'},
                'login'
            ])
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        
        var activeCheckColumn = new Ext.grid.CheckColumn({
            header: 'Активная',
            dataIndex: 'active'
        });
        
        var actions = new xlib.grid.Actions({
            autoWidth: true,
            items: [{
                text: 'Редактировать',
                iconCls: 'edit',
                hidden: !this.permissions,
                handler: function(g, rowIndex) {
                    var record = g.getStore().getAt(rowIndex);
                    var w = new xlib.Acl.Accounts.Form({
                        accountId: record.get('id')
                    }).getWindow();
                    
                    w.show();
                    w.on('close', function() {
                        this.getStore().reload();
                    }, this);
                },
                scope: this
            }, {
                text: 'Сменить пароль',
                iconCls: 'edit',
                hidden: !this.permissions,
                handler: this.onChangePassword,
                scope: this
            }, {
                text: 'Удалить',
                iconCls: 'delete',
                hidden: !this.permissions,
                handler: this.onDeleteAccount,
                scope: this
            }],
            scope: this
        })
        
        this.columns = [{
            header: 'Логин',
            dataIndex: 'login'
        }, {
            header: 'Имя',
            dataIndex: 'name',
            editor: new Ext.form.TextField({
                allowBlank: false
            })
        }, {
            header: 'Email',
            dataIndex: 'email',
            editor: new Ext.form.TextField({
                vtype: 'email'
            })
        }, {
            header: 'Телефон',
            dataIndex: 'phone',
            editor: new Ext.form.TextField()
        }, 
        activeCheckColumn
        ];
        
        this.plugins = [
            activeCheckColumn,
            actions
        ];
        
        this.createAccountBtn = new Ext.Toolbar.Button({
            text: 'Добавить',
            iconCls: 'add',
            hidden: !this.permissions,
            qtip: 'Добавить новую учётную запись',
            handler: this.onAddAccount,
            scope: this
        });
        
        this.bbar = new xlib.PagingToolbar({
            store: this.ds,
            items: ['-', this.createAccountBtn]
        });
        
        xlib.Acl.Accounts.List.superclass.initComponent.apply(this, arguments);
        
        this.on({
            afteredit: this.onAfterEdit,
            scope: this
        });
    },
    
    onAfterEdit: function(data, g) {
        
        Ext.Ajax.request({
            url: link('admin', 'accounts', 'update-field'),
            params: {
                field: data.field,
                value: data.value,
                id: data.record.get('id')
            },
            callback: function(options, success, response) {
                var r = xlib.decode(response.responseText);
                if (r && r.success) {
                    data.record.commit();
                    return;
                }
                data.grid.getStore().rejectChanges();
            },
            scope: this
        });
    },
    
    setRoleId: function (roleId) {
        var s = this.getStore();
        s.baseParams = s.baseParams || {};
        s.baseParams.roleId = roleId;
        this.roleId = roleId;
        s.reload();
    },
    
    getRoleId: function() {
        return this.roleId; 
    },
    
    onAddAccount: function(b, e) {
        
        var f = new xlib.form.FormPanel({
        	permissions: this.permissions,
        	defaultType: 'textfield',
            items: [{
                fieldLabel: 'Логин',
                name: 'login'
            }, {
                fieldLabel: 'Пароль',
                name: 'password'
            }]
        });
        
        var w = new Ext.Window({
            title: 'Создание новой учётной записи',
            resizable: false,
            width: 300,
            modal: true,
            items: [f],
            buttons: [{
                text: 'Создать',
                iconCls: 'add',
                handler: function() {
                    f.getForm().submit({
                        url: link('admin', 'accounts', 'add-account'),
                        params: {
                            roleId: this.getRoleId()
                        },
                        success: function(form, options) {
                            var o = options.result;
                            if (true == o.success) {
                                w.close();
                                this.getStore().reload();
                                return;
                            }
                            xlib.Msg.error('Не удалось создать учётную запись.')
                        },
                        failure: function() {
                        	xlib.Msg.error('Не удалось создать учётную запись.')
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
            }]
        });
        
        w.show();
    },
    
    onDeleteAccount: function(g, rowIndex) {
        
        var record = g.getStore().getAt(rowIndex);
        
        xlib.Msg.confirm('Вы уверены?', function() {
            Ext.Ajax.request({
                url: link('admin', 'accounts', 'delete-account'),
                params: {
                    id: record.get('id')
                },
                callback: function (options, success, response) {
                    if (true == success) {
                        var res = xlib.decode(response.responseText);
                        if (true == res.success) {
                            g.getStore().reload();
                            return;
                        }
                    }
                    xlib.Msg.error('Не удалось удаление.');
                },
                scope: this
            });    
        }, this);
    },
    
    onChangePassword: function(g, rowIndex) {
        
        var record = g.getStore().getAt(rowIndex);
        
        var f = new xlib.form.FormPanel({
            permissions: true,
            labelAlign: 'top',    
            items: [{
                fieldLabel: 'Введите новый пароль',
                xtype: 'textfield',
                name: 'password',
                anchor: '-1',
                maskRe: /^\w$/,
                regex: /^\w{3,50}$/,
                regexText: 'Значение может содержать толлько латинские быквы и цифры.',
                maxLength: 50,
                minLength: 3    
            }]
        });
        
        var w = new Ext.Window({
            width: 250,
            title: 'Смена пароля',
            items: [f],
            buttons: [{
                text: 'Сменить',
                handler: function() {
                    f.getForm().submit({
                        url: link('admin', 'accounts', 'change-password'),
                        params: {
                            id: record.get('id')
                        },
                        success: function(f, action) {
                            if (true == action.result.success) {
                                w.close();
                            }
                        }
                    });
                }
            }, {
                text: 'Отмена',
                handler: function() {
                    w.close();
                },
                scope: this
            }]
        });

        w.show()
    }
});

Ext.reg('xlib.acl.accounts.list', xlib.Acl.Accounts.List);