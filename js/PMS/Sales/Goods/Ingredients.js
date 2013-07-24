Ext.ns('PMS.Sales.Goods');

PMS.Sales.Goods.Ingredients = Ext.extend(Ext.grid.GridPanel, {
    
    layout: 'fit',
    
    title: 'Состав',
    
    autoScroll: true,
    
    permissions: true,
    
    viewConfig: {autoFill: true},
    
    initComponent: function() {
    
        this.autoExpandColumn = Ext.id();
        
        this.cm = new Ext.grid.ColumnModel([{
            header: '№',
            hidden: true,
            dataIndex: 'id',
            width: 40
        }, {
            header: 'Наименование',
            dataIndex: 'name',
            id: this.autoExpandColumn
        }, {
            header: 'Ед. изм.',
            dataIndex: 'measure',
            width: 60
        }, {
            header: 'Кол-во',
            dataIndex: 'qty',
            width: 60,
            align: 'right'
        }, {
            header: 'Цена',
            dataIndex: 'price',
            xtype: 'numbercolumn',
            format: '0.000,00/i',
            width: 60,
            align: 'right'
        }, {
            header: 'Стоимость',
            dataIndex: 'cost',
            xtype: 'numbercolumn',
            format: '0.000,00/i',
            width: 90,
            align: 'right'
        }]);
        
        this.cm.defaultSortable = true; 

        this.sm = new Ext.grid.RowSelectionModel({singleSelect: true});

        this.ds = new Ext.data.JsonStore({
            sortInfo: {
                field: 'id',
                direction: 'ASC'
            },
            fields: [
                {name: 'id', type: 'int'},
                {name: 'name'},
                {name: 'price', type: 'float'},
                {name: 'measure'},
                {name: 'qty', type: 'int'},
                {name: 'cost', type: 'float'}
            ]
        });

        this.tbar = [{
            text: 'Добавить',
            iconCls: 'add',
            handler: this.onAdd,
            scope: this,
            hidden: !this.permissions
        }]; 
        
        this.plugins = [new xlib.grid.Actions({
            autoWidth: true,
            items: [{
                text: 'Редактировать',
                iconCls: 'edit',
                handler: this.onEditExp,
                hidden: !this.permissions,
                scope: this
            }, {
                text: 'Удалить',
                iconCls: 'delete',
                handler: this.onDeleteExp,
                hidden: !this.permissions
            }]
        })];
        
        PMS.Sales.Goods.Ingredients.superclass.initComponent.apply(this, arguments);
        
        this.on('rowdblclick', function(grid, rowIndex) {
            this.onAddExp(grid.getStore().getAt(rowIndex));
        }, this);
    },
    
    onAdd: function() {
        
        var expendables = new PMS.Sales.Expendables.List({
            permissions: false,
            title: false,
            listeners: {
                rowdblclick: function(grid, rowIndex, event) {
                    var record = grid.getStore().getAt(rowIndex); 
                    this.onAddExp.call(this, record);
                    expWin.close();
                },
                scope: this
            }
        })
        
        var expWin = new Ext.Window({
            title: 'Выбор ингредиента',
            layout: 'fit',
            width: 400,
            height: 400,
            items: [expendables],
            modal: true,
            buttons: [{
                text: 'Выбрать', 
                handler: function() {
                    var sm = expendables.getSelectionModel();
                    if (!sm.hasSelection()) {
                        return;
                    }
                    var record = sm.getSelected();
                    this.onAddExp.call(this, record);
                    expWin.close();
                },
                scope: this
            }, {text: 'Отменить', handler: function() {expWin.close();}}] 
        });
        
        expWin.show(); 
    },
    
    onAddExp: function(expRecord) {
        
        var qtyField = new Ext.form.NumberField({
            allowDeciminals: false,
            allowNegative: false,
            allowBlank: false,
            border: true,
            padding: 0,
            listeners: {
                specialkey: function(field, e) {
                    if (e.getKey() == e.ENTER) {
                        handleFn.call(this);
                    }
                },
                scope: this
            },
            scope: this
        });
        
        var handleFn = function() {
            
            if (!qtyField.isValid()) {
                return;
            }
            
            var qty = qtyField.getValue(),
                cost = expRecord.get('price') * qty
                recordExist = this.getStore().getById(expRecord.get('id'));
            
            if (!recordExist) {
                expRecord.set('qty', qty);
                expRecord.set('cost', cost);
                this.getStore().add(expRecord);
            } else {
                recordExist.set('qty', qty);
                recordExist.set('cost', cost);
                this.getStore().commitChanges();
            }
            qtyWin.close();
        };
        
        var qtyWin = new Ext.Window({
            title: 'Количество',
            layout: 'fit',
            width: 200,
            autoHeight: true,
            border: false,
            items: [qtyField],
            modal: true,
            buttons: [{
                text: 'OK', 
                handler: handleFn,
                scope: this
            }, {text: 'Отменить', handler: function() {qtyWin.close();}}],
            scope: this
        });
        qtyWin.show();
    },
    
    onEditExp: function(grid, rowIndex, event) {
        this.onAddExp(grid.getStore().getAt(rowIndex));
    },
    
    onDeleteExp: function(grid, rowIndex, event) {
        grid.getStore().removeAt(rowIndex);
    }
    
});

Ext.reg('PMS.Sales.Goods.Ingredients', PMS.Sales.Goods.Ingredients);