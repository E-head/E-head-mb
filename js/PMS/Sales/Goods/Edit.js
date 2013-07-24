Ext.ns('PMS.Sales.Goods');

PMS.Sales.Goods.Edit = Ext.extend(Ext.Window, {
    
	sid: null,
    
    layout: 'border',

    width: 600,
    
    height: 500,
    
    border: false,
    
    resizable: false,
    
    modal: true,
    
    loadURL: link('sales', 'goods', 'get'),
    
    addURL: link('sales', 'goods', 'add'),
    
    updateURL: link('sales', 'goods', 'update'),
    
    initComponent: function() {
        
        this.title = (this.sid ? 'Редактирование' : 'Добавление') + ' продукции';
        
        this.formPanel = new PMS.Sales.Goods.Form({
            region: 'center'
        });
        
        this.gridPanel = new PMS.Sales.Goods.Ingredients({
            region: 'south',
            height: 250
        }); 
        
        this.items = [this.formPanel, this.gridPanel];
        
        this.buttons = [{
            text: 'Сохранить', 
            handler: this.saveData,
            scope: this
        }, {
            text: 'Отменить', 
            handler: function() {
                this.close();
            },
            scope: this
        }];
                
        PMS.Sales.Goods.Edit.superclass.initComponent.apply(this, arguments);
        
        this.show();
        
        this.gridPanel.getStore().on({
            add: this.recalculate,
            remove: this.recalculate,
            update: this.recalculate,
            load: this.recalculate,
            scope: this
        });
        
        this.formPanel.priceField.on('change', this.recalculate, this);
        this.formPanel.lossMarginField.on('change', this.recalculate, this);
        
        if (this.sid) {
            this.loadData(this.sid);
        }
    },
    
    recalculate: function() {
        
        var store = this.gridPanel.getStore(),
            summ = store.sum('cost'),
            price = this.formPanel.getForm().findField('price').getValue(),
            lossMargin = this.formPanel.getForm().findField('loss_margin').getValue();
        
        summTotal = summ + (summ / 100 * lossMargin);  

        var val = Ext.util.Format.number(summTotal, '0.000,00/i') 
            + 'р. (' + Ext.util.Format.number(summ, '0.000,00/i') 
            + 'р. + ' + Math.floor(lossMargin) + '%)';
        
        this.formPanel.costField.setValue(val);
        this.formPanel.marginField.setValue(
            Math.round((price - summTotal) / (summTotal/100)) + '%');
    },
    
    saveData: function() {
        
        if (this.formPanel.getForm().isValid()) {
            
            var params = this.formPanel.getForm().getFieldValues();
            var ingredients = [];
           
            this.gridPanel.getStore().each(function(record) {
                ingredients.push([record.get('id'), record.get('qty')]); 
            });
            
            params.id = this.sid;
            params.ingredients = Ext.encode(ingredients); 
            
            Ext.Ajax.request({
                url: this.sid ? this.updateURL : this.addURL,
                params: params,
                success: this.onSaveSuccess,
                failure: this.onFailure,
                scope: this
            });
        }
    },
    
    onSaveSuccess: function(response, options) {
        
        var res = xlib.decode(response.responseText);
        
        if (res.success) {
            this.fireEvent('saved');
        } else {
            this.onFailure();
        }
    },
    
    loadData: function(id) {
        
        Ext.Ajax.request({
            url: this.loadURL,
            params: {id: this.sid},
            success: this.onLoadSuccess,
            failure: this.onFailure,
            scope: this
        });
        
    },
    
    onLoadSuccess: function(response, options) {
        
        var res = xlib.decode(response.responseText);
        
        if (res.success) {
            this.formPanel.getForm().setValues(res.data);
            this.gridPanel.getStore().loadData(res.data.ingredients);
        } else {
            this.onFailure();
        }
    },
    
    onFailure: function() {
        xlib.Msg.error('Ошибка сервера');
    }
});

Ext.reg('PMS.Sales.Goods.Edit', PMS.Sales.Goods.Edit);