Ext.ns('PMS.Order');

PMS.Order.Preview = Ext.extend(Ext.grid.GridPanel, {

    listURL:    link('orders', 'goods', 'get-list'),
    
    addURL:     link('orders', 'goods', 'add'),
    
    updateURL:  link('orders', 'goods', 'update'),
    
    deleteURL:  link('orders', 'goods', 'delete'),
    
    border: false,
    
    loadMask: true,
    
    autoHeight: true,

    permissions: acl.isUpdate('orders'),
    
    orderId: null,
    
    onDate: null, 
    
    initComponent: function() {
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            baseParams: {order_id: this.orderId},
            //autoLoad: true,
            root: 'data',
            id: 'id',
            fields: ['id', 'name', 'number', 'price', {name: 'summ', type: 'float'}]
        });
        
        this.columns = [{
            header: 'Наименование',
            dataIndex: 'name',
            sortable: true,
            id: this.autoExpandColumn
        }, {
            header: 'Кол-во',
            dataIndex: 'number',
            sortable: true,
            width: 70
        }, {
            header: 'Цена (р.)',
            dataIndex: 'price',
            sortable: true,
            width: 100,
            renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                return Ext.util.Format.number(value, '0,000.00').replace(/,/g, ' ');
            }
        }, {
            header: 'Сумма (р.)',
            dataIndex: 'summ',
            sortable: true,
            width: 100,
            renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                return Ext.util.Format.number(value, '0,000.00').replace(/,/g, ' ');
            }
        }];
        
        this.summaryInfo = new Ext.form.DisplayField({
            value: '0.00 р.'
        });
        this.dateInfo = new Ext.form.DisplayField();
        
        this.bbar = new Ext.Toolbar({
            items: [
                {xtype: 'tbtext', text: '<b>Дата:</b>'}, 
                ' ', this.dateInfo, '->', 
                {xtype: 'tbtext', text: '<b>Сумма:</b>'}, 
                ' ', this.summaryInfo
            ]
        });
        
        PMS.Order.Preview.superclass.initComponent.apply(this, arguments);
        
        this.getStore().on('load', function(store, records, options) {
            var summ = Ext.util.Format.number(store.sum('summ'), '0,000.00').replace(/,/g, ' '),
                date = Ext.util.Format.date(this.onDate, xlib.date.DATE_TIME_FORMAT);
            this.summaryInfo.setValue(summ + ' р.');
            this.dateInfo.setValue(date);
        }, this);
    }    
});

Ext.reg('PMS.Order.Preview', PMS.Order.Preview);