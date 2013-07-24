Ext.ns('PMS.Storage.Assets');

PMS.Storage.Assets.Movement = Ext.extend(xlib.form.FormPanel, {
    
    title: false,
    
    permissions: acl.isUpdate('storage'),
    
    defaults: {
        anchor: '100%'
    },
    
    record: null,
    
    isIncome: true,
    
    URL: null, 
    
    autoHeight: true,
    
    initComponent: function() {
       
        if (!Ext.isObject(this.record)) {
            throw 'Record is not set!';
        }
        
        this.URL = link('storage', 'assets', this.isIncome ? 'income' : 'outgo');
        
        var qtyFieldConfig = {
            fieldLabel: 'Количество',
            allowDecimals: false,
            minValue: 1
        };
        if (!this.isIncome) {
            qtyFieldConfig.maxValue = this.record.get('qty');
        }
        this.qtyField = new Ext.form.NumberField(qtyFieldConfig);
        
        this.items = [{
            xtype: 'displayfield',
            fieldLabel: 'Наименование',
            name: 'name'
        }, {
            xtype: 'PMS.Storage.Measures.ComboBox',
            fieldLabel: 'Ед. измерения',
            disabled: true,
            updatePermissions: false,
            hideTrigger: true,
            name: 'measure'
        }, {
            xtype: 'displayfield',
            fieldLabel: 'Цена за ед. (р.)',
            name: 'unit_price'
        }, {
            xtype: 'displayfield',
            fieldLabel: 'В наличии',
            name: 'qty'
        },  this.qtyField];

        if (!this.isIncome) {
        /*
            this.receiverCombo = new xlib.Acl.Accounts.Combo({
                fieldLabel: 'Получатель',
                name: 'reciever_id',
                hiddenName: 'reciever_id',
                URL: link('storage', 'index', 'get-accounts')
            });
            this.items.push(this.receiverCombo);
        */
            this.orderField = new Ext.form.NumberField({
                fieldLabel: '№ заказа',
                name: 'order_id',
                allowDecimals: false,
                allowNegative: false
            });
            
            this.items.push(this.orderField);
        }
        
        PMS.Storage.Assets.Movement.superclass.initComponent.apply(this, arguments);
        
        this.w = new Ext.Window({
            layout: 'fit',
            title: this.isIncome ? 'Оприходование ТМЦ' : 'Выдача ТМЦ',
            resizable: false,
            width: 300,
            modal: true,
            items: [this],
            buttons: [{
                text: 'Сохранить',
                handler: this.saveData,
                scope: this
            }, {
                text: 'Отмена',
                handler: function() {
                    this.w.close();
                },
                scope: this
            }]
        });
        
        this.w.show();
        
        this.getForm().loadRecord(this.record);
    },
    
    saveData: function() {
        
        if (!this.getForm().isValid()) {
            return;
        }
        
        this.w.disable();
        
        var params = { 
            asset_id: this.record.get('id'),
            qty: this.qtyField.getValue()
        };
        
        if (!this.isIncome) {
            //params.reciever_id = this.receiverCombo.getValue();
            params.order_id = this.orderField.getValue();
        }
        
        Ext.Ajax.request({
           url: this.URL,
           params: params,
           success: function(res) {
                var responseText = Ext.decode(res.responseText);
                if (!responseText || !responseText.success) {
                    xlib.Msg.error('Ошибка на стороне сервера.');
                    this.w.enable();
                    return;
                }
                this.fireEvent('saved');
                this.w.close();
            },
            failure: function() {
                xlib.Msg.error('Ошибка связи с сервером.');
                this.w.enable();
            },
            scope: this
        });
    }    
});