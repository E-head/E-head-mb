Ext.ns('PMS.Staff');

PMS.Staff.Form = Ext.extend(xlib.form.FormPanel, {

    itemId: null,
    
    categoryId: null,
    
    itemURL:    link('staff', 'index', 'get'),
    
    addURL:     link('staff', 'index', 'add'),
    
    updateURL:  link('staff', 'index', 'update'),
    
    loadMask: true,

    fileUpload: true,
    
    markFieldsDirty: false,
    
    permissions: acl.isUpdate('staff'),
    
    initComponent: function() {
        
        this.items = [{
            xtype: 'textfield',
            fieldLabel: 'Имя',
            name: 'name',
            allowBlank: false
        }, {
            xtype: 'textfield',
            fieldLabel: 'Должность',
            name: 'function',
            allowBlank: false
        }, {
            layout: 'column',
            border: false,
            columns: 3,
            defaults: {
                border: false,
                layout: 'form'
            },
            items: [{
                columnWidth: .33,
                items: [{
                    xtype: 'xlib.form.DateField',
                    fieldLabel: 'Принят на работу',
                    name: 'hire_date',
                    hiddenName: 'hire_date',
                    width: 100,
                    allowBlank: false
                }]
            }, {
                labelWidth: 100,
                columnWidth: .33,
                items: [{
                    xtype: 'xlib.form.combobox',
                    fieldLabel: 'Система оплаты',
                    name: 'pay_period',
                    hiddenName: 'pay_period',
                    mode: 'local',
                    displayField: 'name',
                    valueField: 'id',
                    store: new Ext.data.ArrayStore({
                        fields: ['id', 'name'],
                        data: PMS.Staff.PayPeriods
                    }),
                    selectFirst: true,
                    editable: false,
                    allowBlank: false,
                    width: 100
                }]
            }, {
                labelWidth: 80,
                columnWidth: .34,
                items: [{
                    xtype: 'numberfield',
                    fieldLabel: 'Тариф (руб.)',
                    name: 'pay_rate',
                    anchor: '100%',
                    allowBlank: false
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            columns: 2,
            defaults: {
                border: false,
                layout: 'form'
            },
            items: [{
                columnWidth: .8,
                items: [{
                    xtype: 'fileuploadfield',
                    fieldLabel: 'Резюме',
                    name: 'cv_file',
                    buttonText: '',
                    anchor: '100%',
                    allowBlank: true,
                    buttonCfg: {
                        iconCls: 'x-form-file-btn-icon'
                    }
                }]
            }, {
                columnWidth: .2,
                labelWidth: 10,
                items: [{
                    xtype: 'checkbox',
                    boxLabel: 'Удалить резюме',
                    name: 'del_file',
                    inputValue: 1,
                    disabled: !this.permissions,
                    handler: this.onDelFile,
                    scope: this
                }]
            }]
        }, {
            layout: 'column',
            border: false,
            columns: 2,
            defaults: {
                border: false,
                layout: 'form',
                anchor: '100%'
            },
            items: [{
                columnWidth: .82,
                items: [{
                    xtype: 'displayfield',
                    fieldLabel: 'Категория',
                    name: 'category_name',
                    allowBlank: true,
                    submitValue: false
                }]
            }, {
                columnWidth: .18,
                items: [{
                    text: 'Сменить категорию',
                    xtype: 'button',
                    disabled: !this.permissions,
                    handler: this.onChangeCategory,
                    scope: this
                }]
            }]
        }];
        
        PMS.Staff.Form.superclass.initComponent.apply(this, arguments);
        
        var w = this.getWindow(this.itemId).show();
        
        if (this.itemId) {
            this.getForm().load({
                url: this.itemURL,
                params: {
                    id: this.itemId
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
            width: 750,
            height: 200,
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
                        params: {
                            id: this.itemId,
                            category_id: this.categoryId
                        },
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
    },
    
    onDelFile: function(field, state) {
        var cvField = this.getForm().findField('cv_file');
        
        if (state) {
            cvField.oldValue = cvField.getValue();
            cvField.setValue('');
            cvField.disable();
        } else {
            cvField.setValue(cvField.oldValue);
            cvField.enable();
        }
    },
    
    onChangeCategory: function() {
        
        var self = this;
        
        var changeCategory = function(categoryId) {
            
            if (!(parseInt(categoryId) > 0)) {
                return;
            }
            
            Ext.Ajax.request({
                url: link('staff', 'index', 'change-category'),
                params: {
                    id: self.itemId,
                    category_id: categoryId
                },
                callback: function() {
                    tw.close();
                    self.getForm().load({
                        url: self.itemURL,
                        params: {
                            id: self.itemId
                        }
                    });
                }
            });
        }
        
        var tree = new PMS.Staff.Tree({
            border: false,
            title: false,
            header: false,
            readOnly: true,
            listeners: {
                beforedblclick: function(node, e) {
                    e.stopEvent();
                    changeCategory(node.id);
                    return false;
                }
            }
        })
        
        var tw = new Ext.Window({
            title: 'Категории',
            resizable: false,
            hidden: false,
            layout: 'fit',
            width: 300,
            height: 400,
            modal: true,
            items: [tree],
            buttons: [{
                text: 'Выбрать',
                handler: function() {
                    changeCategory(tree.getSelectionModel().getSelectedNode().id);
                },
                scope: this
            }, {
                text: 'Отмена',
                handler: function() {
                    tw.close();
                }
            }]
        });
    }
});

Ext.reg('PMS.Staff.Form', PMS.Staff.Form);