Ext.ns('PMS.Storage.Requests');

PMS.Storage.Requests.Form = Ext.extend(xlib.form.FormPanel, {
    
    permissions: acl.isView('storage'),
    
    labelWidth: 100,
    
    defaults: {
        anchor: '100%'
    },
    
    initComponent: function() {
        
        this.items = [{
            xtype: 'hidden',
            name: 'asset_id'
        }, {
            layout: 'column',
            border: false,
            columns: 2,
            defaults: {
                layout: 'form',
                border: false
            },
            items: [{
                columnWidth: .88,
                items: [{
                    xtype: 'textfield',
                    fieldLabel: 'Наименование',
                    name: 'name',
                    allowBlank: false,
                    id: 'nameField',
                    anchor: '100%'
                }]
            }, {
                columnWidth: .12,
                labelWidth: 1, 
                items: [{
                    xtype: 'menuitem',
                    iconCls: 'prod_schd-icon',
                    text: 'Выбор',
                    style: 'padding-left: 22px;',
                    handler: this.onChoice,
                    scope: this
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
                columnWidth: .80,
                items: [{
                    xtype: 'numberfield',
                    fieldLabel: 'Количество',
                    name: 'qty',
                    validator: function(value) {
                        return value > 0 ? true : 'Значение должно быть больше нуля';
                    },
                    anchor: '100%'
                }]
            }, {
                columnWidth: .20,
                labelWidth: 1, 
                items: [{
                    xtype: 'PMS.Storage.Measures.ComboBox',
                    name: 'measure',
                    anchor: '100%'
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
                columnWidth: .45,
                items: [{
                    xtype: 'xlib.form.DateField',
                    format: 'd-m-Y',
                    fieldLabel: 'Заявка на дату',
                    allowBlank: false,
                    name: 'request_on',
                    hiddenName: 'request_on',
                    anchor: '95%'
                }]
            }, {
                columnWidth: .55,
                labelWidth: 80,
                items: [{
                    xtype: 'displayfield',
                    style: 'line-height: 18px;',
                    value: (new Date()).format(xlib.date.DATE_TIME_FORMAT),
                    fieldLabel: 'Дата подачи',
                    name: 'created',
                    anchor: '100%'
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
                columnWidth: .45,
                items: [{
                    xtype: 'numberfield',
                    fieldLabel: 'К заказу №',
                    name: 'order_id',
                    validator: function(value) {
                        return (value > 0 || Ext.isEmpty(value)) 
                            ? true 
                            : 'Значение должно быть больше нуля';
                    },
                    allowBlank: true,
                    anchor: '95%'
                }]
            }, {
                columnWidth: .55,
                labelWidth: 80,
                items: [{
                    xtype: 'displayfield',
                    style: 'line-height: 18px;',
                    value: xlib.username || '',
                    fieldLabel: 'Автор заявки',
                    name: 'account_name',
                    anchor: '100%'
                }]
            }]
        }];
        
        PMS.Storage.Requests.Form.superclass.initComponent.apply(this, arguments);
        
        this.processName.defer(150, this);
    },
    
    // Private functions 
    
    onChoice: function() {
        
        var assets = new PMS.Storage.Assets.Layout({
            title: false,
            readOnly: true
        });
        
        var w = new Ext.Window({
            title: 'Выбор ТМЦ',
            resizable: false,
            width: 900,
            height: 600,
            modal: true,
            layout: 'fit',
            items: [assets],
            buttons: [{
                text: 'OK',
                handler: function() {
                    
                    var record = assets.getSelected();
                    if (false === record) {
                        return;
                    }
                    
                    var assetField = this.getForm().findField('asset_id');
                    if (null === assetField) {
                        return;
                    }
                    
                    var nameField = this.getForm().findField('name');
                    var measureField = this.getForm().findField('measure');
                    if (null === nameField || null === measureField) {
                        return;
                    }
                    
                    assetField.setValue(record.get('id'));
                    nameField.setValue(record.get('name')).disable();
                    measureField.setValue(record.get('measure')).disable();
                    
                    w.close();
                    
                },
                scope: this
            }, {
                text: 'Отмена',
                handler: function() {
                    w.close();
                }
            }]
        });
        w.show();
    },
    
    processName: function() {
        
        var assetField = this.getForm().findField('asset_id');
        if (null === assetField 
        || Ext.isEmpty(assetField.getValue()) 
        || 0 === parseInt(assetField.getValue())) {
            return;
        }
                
        var nameField = this.getForm().findField('name');
        var measureField = this.getForm().findField('measure');
        if (null === nameField || null === measureField || Ext.isEmpty(nameField.getValue())) {
            return;
        }
        nameField.disable();
        measureField.disable();
        measureField.hideTriggerItem('btn0');
    }
});