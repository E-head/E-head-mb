Ext.ns('PMS.Notice');

PMS.Notice.DstInfo = Ext.extend(Ext.grid.GridPanel, {

    loadMask: true,
    
    itemId: null,

    reserveScrollOffset: true,
    
    border: false,
    
    initComponent: function() {
        
        if (!this.itemId) {
            throw 'itmeId is required!';
        }
        
        this.autoExpandColumn = Ext.id();
        
        this.store = new Ext.data.JsonStore({
            url: link('admin', 'notice', 'dst-info'),
            baseParams: {
                id: this.itemId
            },
            autoLoad: true,
            root: 'data',
            fields: ['name', {
                    name: 'date', 
                    type: 'date', 
                    dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER,
                    convert: function(v, record) {
                        return !v ? '-' : Ext.util.Format.date(
                            Date.parseDate(v, xlib.date.DATE_TIME_FORMAT_SERVER), 
                            xlib.date.DATE_TIME_FORMAT
                        );
                    }
                }
            ]
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        
        this.columns = [{
            header: 'Имя',
            dataIndex: 'name',
            id: this.autoExpandColumn
        }, {
            header: 'Дата ознакомления',
            dataIndex: 'date',
            width: 120
        }];
        
        PMS.Notice.DstInfo.superclass.initComponent.apply(this, arguments);
    },
    
    // Private functions 
    
    getWindow: function(id) {
        
       var w = new Ext.Window({
            title: 'Отчёт об ознакомлении для записи № ' + this.itemId,
            resizable: false,
            layout: 'fit',
            hidden: false,
            width: 400,
            height: 500,
            modal: true,
            items: [this],
            buttons: [{
                text: 'OK',
                handler: function() {
                    w.close();
                }
            }]
        });
        
        return w;
    }
});

Ext.reg('PMS.Notice.DstInfo', PMS.Notice.DstInfo);