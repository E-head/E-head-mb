Ext.ns('PMS.Orderslog');

PMS.Orderslog.DbfImport = Ext.extend(Ext.Window, {
    
    title: 'Импорт данных',
    
    submitURL: link('orderslog', 'index', 'import'),
    
    resizable: false,
    
    width: 600,
    
    modal: true,
    
    border: false,
    
    initComponent: function() {
        
        var self = this;
        
        this.uploadForm = new xlib.form.FormPanel({
            permissions: true,
            fileUpload: true,
            bodyStyle: {'background-color': '#CCD8E7'},
            padding: '5px 0 0 0',
            border: false,
            items: [{
                xtype: 'fileuploadfield',
                name: 'f',
                hideLabel: true,
                buttonOnly: true,
                buttonText: 'Загрузить файл',
                allowBlank: false,
                listeners: {
                    fileselected: self.onProcess,
                    scope: self
                }
            }]
        });
        
        this.resultGrid = new Ext.grid.GridPanel({
            title: 'Продукция',
            height: 250,
            viewConfig: {
                autoFill: true,
                getRowClass: function(record) {
                    
                //  Goods status:
                //  0 = ОК
                //  1 = No ingredients assigned
                //  2 = Not exist in db
                    
                    switch (record.get('status')) {
                        case 2: 
                            return 'x-row-error';
                        case 1:
                            return 'x-row-expired';
                        default:
                    }
                }
            },
            cm: new Ext.grid.ColumnModel([{
                header: 'Код',
                dataIndex: 'CODE',
                width: 40
            }, {
                header: 'Наименование',
                dataIndex: 'NAME'
            }, {
                header: 'Количество',
                dataIndex: 'COUNT',
                align: 'right',
                width: 40
            }, {
                header: 'Сумма',
                dataIndex: 'SUMMA',
                align: 'right',
                xtype: 'numbercolumn',
                width: 40
            }]),
            ds: new Ext.data.ArrayStore({
                idProperty: 'CODE',
                fields: [
                    {name: 'CODE'},
                    {name: 'NAME'},
                    {name: 'COUNT', type: 'int'},
                    {name: 'SUMMA', type: 'float'},
                    {name: 'status', type: 'int'}
                ]
            }),
//            tbar: new Ext.Toolbar({
//                items: [' '],
                plugins: [new xlib.Legend.Plugin({
                    strategy: 'header',
                    items: [{
                        color: '#FF9999',
                        text: 'Позиция отсутствует в базе'
                    }, {
                        color: '#FFFF99',
                        text: 'Не заданы расходные материалы'
                    }]
                })]
//            })
        });
        
        this.calcGrid = new Ext.grid.GridPanel({
            title: 'Расходные материалы',
            border: false,
            cls: 'x-border-bottom x-border-left x-border-right',
            height: 250,
            viewConfig: {
                autoFill: true
            },
            cm: new Ext.grid.ColumnModel([{
                header: '№',
                hidden: true,
                dataIndex: 'id',
                width: 40
            }, {
                header: 'Наименование',
                dataIndex: 'name'
            }, {
                header: 'Ед. измерения',
                dataIndex: 'measure',
                width: 100
            }, {
                header: 'Кол-во',
                dataIndex: 'qty',
                width: 60,
                align: 'right'
            }, {
                header: 'Цена',
                dataIndex: 'price',
                xtype: 'numbercolumn',
                width: 40,
                align: 'right'
            }, {
                header: 'Сумма',
                dataIndex: 'cost',
                align: 'right',
                xtype: 'numbercolumn',
                width: 40
            }]),
            ds: new Ext.data.ArrayStore({
                idProperty: 'id',
                fields: [
                    {name: 'id'},
                    {name: 'name'},
                    {name: 'measure'},
                    {name: 'qty'},
                    {name: 'price', type: 'float'},
                    {name: 'cost', type: 'float'}
                ]
            })
        });
        
        this.items = [this.resultGrid, this.calcGrid, this.uploadForm];
        
        PMS.Orderslog.DbfImport.superclass.initComponent.apply(this, arguments);
        
        this.show();
    },
    
    onProcess: function() {

        if (!this.uploadForm.getForm().isValid()) {
            return;
        }
        
        this.resultGrid.getStore().removeAll(false);
        
        this.uploadForm.getForm().submit({
            url: this.submitURL,
            waitMsg: 'Загрузка...',
            success: this.onSuccess,
            failure: this.onFailure,
            scope: this
        });
        
    },
    
    onFailure: function(form, options) {
        xlib.Msg.error('Ошибка сервера');
    },
    
    onSuccess: function(form, options) {
        var o = options.result;
        if (!o.success) {
            this.onFailure();
        }
        this.resultGrid.getStore().loadData(o.data.goods);
        this.calcGrid.getStore().loadData(o.data.expendables);
        
        // Calculate and add the summary rows
        
        this.resultGrid.getStore().add(new (this.resultGrid.getStore()).recordType({
            CODE: '<b>Итого</b>',
            SUMMA: this.resultGrid.getStore().sum('SUMMA')
        }, ' '));

        this.calcGrid.getStore().add(new (this.calcGrid.getStore()).recordType({
            name: '<b>Итого</b>',
            cost: this.calcGrid.getStore().sum('cost')
        }, ' '));
    }
});