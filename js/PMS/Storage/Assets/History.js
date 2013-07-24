Ext.ns('PMS.Storage.Assets');

PMS.Storage.Assets.History = Ext.extend(Ext.grid.GridPanel, {
    
    listURL: link('storage', 'assets', 'history'),
    
    assetId: null,

    title: false,
    
    border: false,
    
    loadMask: true,

    permissions: acl.isView('storage'),
    
    defaults: {
        sortable: false
    },
    
    initComponent: function() {
        
        if (!this.assetId) {
            throw 'Asset id is not set!';
        }
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.JsonStore({
            url: this.listURL,
            autoLoad: true,
            remoteSort: true,
            root: 'data',
            id: 'id',
            sortInfo: {
                field: 'created',
                direction: 'DESC'
            },
            baseParams: {
                asset_id: this.assetId
            },
            totalProperty: 'totalCount',
            fields: ['id', 'qty', 'account_name', 'order_id', 'request_id', 'asset_id', {
                name: 'created', 
                type: 'date', 
                dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER,
                convert: function(v, record) {
                    return Ext.util.Format.date(
                        Date.parseDate(v, xlib.date.DATE_TIME_FORMAT_SERVER), 
                        xlib.date.DATE_TIME_FORMAT
                    );
                }
            }]
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        
        this.columns = [{
            dataIndex: 'qty',
            width: 16,
            renderer: function(v, metaData) {
                metaData.css = v > 0 ? 'check-in' : 'check-out';
                var qtip = v > 0 ? 'Поступление' : 'Выдача';
                var text = '<span title="' + qtip + '">&nbsp;&nbsp;&nbsp;</span>'
                return text;
            }
        }, {
            header: 'Дата',
            dataIndex: 'created',
            width: 150
        }, {
            header: 'Количество',
            dataIndex: 'qty',
            width: 100,
            renderer: function(v, metaData) {
                return (v > 0 ? '+' : '') + v;
            }
        }, {
            header: 'Принял (имя)',
            dataIndex: 'account_name',
            id: this.autoExpandColumn
        }, {
            header: '№ заказа',
            dataIndex: 'order_id'
        }, {
            header: '№ заявки',
            dataIndex: 'request_id'
        }];
        
        this.bbar = new xlib.PagingToolbar({
            store: this.ds
        });
        
        PMS.Storage.Assets.History.superclass.initComponent.apply(this, arguments);
        
        var baseTitle = 'История движения ТМЦ',
            title = this.assetName 
                  ? baseTitle + ': <i>' + this.assetName + '<i>' 
                  : baseTitle;
        
        new Ext.Window({
            title: title, 
            layout: 'fit',
            resizable: false,
            width: 600,
            height: 500,
            modal: true,
            items: [this]
        }).show();
    }
});

Ext.reg('PMS.Storage.Assets.History', PMS.Storage.Assets.History);