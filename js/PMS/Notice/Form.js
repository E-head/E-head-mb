Ext.ns('PMS.Notice');

PMS.Notice.Types = [ ['объявление'], ['приказ'] ];

PMS.Notice.Form = Ext.extend(xlib.form.FormPanel, {

    itemId: null,
    
    itemURL:    link('admin', 'notice', 'get'),
    
    addURL:     link('admin', 'notice', 'add'),
    
    updateURL:  link('admin', 'notice', 'update'),
    
    loadMask: true,

    markFieldsDirty: false,
    
    labelWidth: 70,
    
    defaults: {
        disabledClass: ''
    },
    
    permissions: acl.isUpdate('notice'),
    
    initComponent: function() {
        
        var dstPicker = new PMS.Notice.DstPicker({
            border: false,
            title: false,
            width: 240,
            height: 325,
            hideHeaders: true
        });
        
        this.items = [{
            layout: 'column',
            border: false,
            defaults: {
                border: false,
                layout: 'form'
            },
            items:[{
                width: 250,
                items: [{
                    xtype: 'xlib.form.combobox',
                    fieldLabel: 'Тип',
                    anchor: '90%',
                    name: 'type',
                    mode: 'local',
                    displayField: 'name',
                    valueField: 'name',
                    store: new Ext.data.ArrayStore({
                        fields: ['name'],
                        data: PMS.Notice.Types
                    }),
                    selectFirst: true,
                    editable: false,
                    allowBlank: false
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Автор',
                    name: 'account_name',
                    submitValue: false,
                    value: xlib.username || ''
                }, {
                    xtype: 'displayfield',
                    fieldLabel: 'Дата',
                    name: 'date',
                    submitValue: false,
                    value: (new Date()).format(xlib.date.DATE_TIME_FORMAT)
                }, {
                    xtype: 'radiogroup',
                    fieldLabel: 'Адресаты',
                    value: '0',
                    name: 'is_personal',
                    defaults: {
                        name: 'is_personal'
                    },
                    items: [{
                        inputValue: '0',
                        boxLabel: 'все'
                    }, {
                        inputValue: '1',
                        boxLabel: 'выбор'
                    }],
                    listeners: {
                        change: function(radiogroup, radio) {
                            if (radio.getRawValue() == '1') {
                                dstPicker.enable();
                                if (!dstPicker.getStore().getCount()) {
                                    dstPicker.getStore().load();
                                }
                            } else {
                                dstPicker.disable();
                            }
                        }
                    }
                }, 
                dstPicker]
            }, {
                columnWidth: 1,
                items: [{
                    xtype: 'textarea',
                    hideLabel: true,
                    name: 'text',
                    anchor: '100% 100%',
                    height: 415,
                    allowBlank: false
                }]
            }]
        }];
        
        PMS.Notice.Form.superclass.initComponent.apply(this, arguments);
        
        this.dstPicker = dstPicker;
        
        var w = this.getWindow(this.itemId).show();
        
        if (this.itemId) {
            this.getForm().load({
                url: this.itemURL,
                params: {
                    id: this.itemId
                },
                success: function(form, action) {
                    
                    var dst = action.result.data.dst;
                    
                    if (!Ext.isArray(dst)) {
                        return
                    }
                    
                    var is_personal = form.findField('is_personal');
                    if (!is_personal || !is_personal.getValue()) {
                        return;
                    }
                    
                    var store = dstPicker.getStore();
                    if (!store) {
                        return;
                    }
                    
                    if (store.getCount() > 0) {
                        dstPicker.setValues(dst);
                    } else {
                        store.on('load', function() {
                            dstPicker.setValues(dst);
                        })
                    }
                }
            });
        }
    },
    
    // Private functions 
    
    getWindow: function(id) {
        
       var w = new Ext.Window({
            title: !id ? 'Новая запись' : 'Запись № ' + id,
            resizable: false,
            hidden: false,
            width: 800,
            height: 500,
            modal: true,
            items: [this],
            buttons: [{
                text: 'Сохранить',
                hidden: !this.permissions,
                handler: function() {
                    
                    if (!this.getForm().isValid()) {
                        return;
                    }
                    
                    var is_personal = this.getForm().findField('is_personal');
                    if (!is_personal || !is_personal.getValue()) {
                        return;
                    }
                    
                    var scope = this, params = {}, submit = function() {
                        
                        if (1 == is_personal.getValue().getRawValue()) {
                            params.dst = Ext.encode(scope.dstPicker.getValues());
                        }
                        
                        scope.getForm().submit({
                            params: params,
                            url: !id ? scope.addURL : scope.updateURL,
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
                    };
                    
                    if (id) {
                        params.id = id;
                        xlib.Msg.confirm(
                            "Сохранение записи приведёт к сбросу " +
                            "статуса \"Ознакомлен\" у всех адресатов " +
                            "для этой записи. <br />" +
                            "Продолжить сохранение?", 
                            submit);
                    } else {
                        submit();
                    }
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

Ext.reg('PMS.Notice.Form', PMS.Notice.Form);