Ext.ns('PMS.Orders.Edit');

PMS.Orders.Edit.Notes = Ext.extend(Ext.Panel, {

	title: 'Комментарии',
	
    layout: 'border',
    
    permissions: true,
    
    orderId: null,
    
    border: false,
    
    initComponent: function() {

        this.store = new Ext.data.JsonStore({
            url: link('orders', 'index', 'get-notes'),
            baseParams: {orderId: this.orderId},
            root: 'rows',
            fields: ['name', 'text', {name: 'time', type: 'date', dateFormat: 'Y-m-d H:i:s'}]
        });
        
        this.field = new Ext.form.TextArea({
            region: 'center',
            style: 'border: 0',
        	height: 60,
        	flex: 1
        });
        
        this.items = [{
        	region: 'center',
        	border: false,
        	autoScroll: true,
            cls: this.permissions ? 'x-border-bottom' : '',
        	items: [new Ext.DataView({
        		autoHeight: true,
        		autoWidth: true,
        		itemSelector: 'div.search-item',
        		store: this.store,
        		tpl: new Ext.XTemplate(
    				'<tpl for="."><div class="search-item"><b>{name}</b>, ',
    				'<i>{time:date("d.m.Y H:i")}</i>.<br/><br/>{text}</div></tpl>'
        		)
        	})]
        }, {
            region: 'south',
            height: 60,
            cls: 'x-border-top',
            layout: 'border',
            hidden: !this.permissions,
            margins: '2px 0 0 0',
            border: false,
            items: [this.field, {
                width: 50,
                xtype: 'button',
                region: 'east',
                handleMouseEvents: false,
                tooltip: 'Отослать', 
                ctCls: 'chat-big-button',
                handler: function() {
                    this.saveData();
                },
                scope: this
            }]
        }];
        
        PMS.Orders.Edit.Notes.superclass.initComponent.apply(this, arguments);
        
        this.on('render', function() {
            new Ext.LoadMask(this.el, {
                msg: 'Загрузка...', 
                store: this.store
            });
            this.show();
        }, this, {delay: 50});
    },
        
    saveData: function() {
        var textValue = this.field.getValue();
        if (!Ext.isEmpty(textValue)) {
            Ext.Ajax.request({
                url: link('orders', 'index', 'add-note'),
                params: {orderId: this.orderId, text: textValue},
                callback: function() {
                    this.field.reset();
                    this.store.reload();
                },
                scope: this
            });
        }
    }
});