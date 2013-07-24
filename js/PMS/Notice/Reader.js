Ext.ns('PMS.Notice');

PMS.Notice.Reader = Ext.extend(Ext.Window, {

    title: 'Приказы и уведомления',
    
    loadURL: link('admin', 'notice', 'get'),
    
    readURL: link('admin', 'notice', 'read'),
    
    resizable: false,
    
    hidden: false,
    
    width: 800,
    
    height: 500,
    
    modal: true,
    
    layout: 'fit',
    
    padding: 10,

    autoScroll: true,
    
    border: false,
    
    initComponent: function() {
        
        this.dataView = new Ext.DataView({
            
            autoHeight:true,
            
            ctCls: 'x-panel-body',
            
            store: new Ext.data.JsonStore({
                url: this.loadURL,
                root: 'data',
                fields: [
                    {name: 'id', type: 'int'}, 
                    {name: 'new', type: 'int'},
                    {name: 'type', type: 'string', convert: Ext.util.Format.capitalize},
                    'text', 'account_name', 
                    {
                        name: 'date', 
                        type: 'date', 
                        dateFormat: xlib.date.DATE_TIME_FORMAT_SERVER,
                        convert: function(v, record) {
                            return Ext.util.Format.date(
                                Date.parseDate(v, xlib.date.DATE_TIME_FORMAT_SERVER), 
                                xlib.date.DATE_FORMAT
                            );
                        }
                    }
                ],
                listeners: {
                    load: function(store, records, options) {
                        if (Ext.isArray(records) 
                        && Ext.isObject(records[0])
                        && 1 == records[0].get('new')
                        ) {
                            this.okButton.setText('Принять');
                            this.okButton.setHandler(this.read, this);
                        }
                    }, 
                    scope: this
                }
            }),
            
            tpl: new Ext.XTemplate('<tpl for=".">' +
                '<center><h1 style="font-size: 200%">{type}</h1><br />' +
                '<i>№ {id} от {date}, автор: {account_name}</i></center>' +
                '<br /><br />{text}</tpl>')
                
        }); 
        
        this.items = [this.dataView]; 
        
        this.okButton = new Ext.Button({
            text: 'ОK',
            handler: function() {
                this.close();
            },
            scope: this
        });
        
        this.buttons = [this.okButton];
        
        PMS.Notice.Reader.superclass.initComponent.apply(this, arguments);
        
        new Ext.LoadMask(this.body, {
            msg: 'Загрузка...',
            store: this.dataView.getStore()
        });
    },
    
    loadData: function(id) {
        this.dataView.getStore().load({params: {id: id}});
    },
    
    read: function() {
        Ext.Ajax.request({
            url: this.readURL,
            params: this.dataView.getStore().lastOptions.params,
            callback: function(options, success, response) {
                var res = xlib.decode(response.responseText);
                if (true == success && res && true == res.success) {
                    this.close();
                    return;
                }
                xlib.Msg.error('Ошибка подтверждения.');
            },
            scope: this
        });
    }
    
});

Ext.reg('PMS.Notice.Reader', PMS.Notice.Reader);