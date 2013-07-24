Ext.ns('PMS.Storage.Assets');

PMS.Storage.Assets.Layout = Ext.extend(Ext.Panel, {
	
	title: 'Склад',
	
    border: false,
    
    layout: 'border',
    
    readOnly: false,
    
	initComponent: function() {
        
        this.categories = new PMS.Storage.Assets.Tree({
            region: 'west',
            minWidth: 200,
            width: 300,
            split: true,
            border: false,
            margins: '0 2 0 0',
            readOnly: this.readOnly,
            cls: 'x-border-right'
        });
        
        this.assets = new PMS.Storage.Assets.List({
            region: 'center',
            border: false,
            readOnly: this.readOnly,
            cls: 'x-border-left'
        });
        
	    this.items = [this.categories, this.assets];
        
        var loadNodeItems = function(node) {
            this.assets.loadList(node.id, node.text);
        } 
        
        this.categories.on({
            click: loadNodeItems,
            contextmenu: loadNodeItems,
            firstnodeselected: loadNodeItems,
            textchange: function(node, text, oldText) {
                this.assets.setTitle(this.assets.baseTitle + '"' + text + '"');
            },
            scope: this
        });
        
		PMS.Storage.Assets.Layout.superclass.initComponent.apply(this, arguments);
	},
    
    getSelected: function() {
        var record = this.assets.getSelectionModel().getSelected();
        return (Ext.isObject(record) && parseInt(record.get('id')) > 0) ? record : false;
    }
});

Ext.reg('PMS.Storage.Assets.Layout', PMS.Storage.Assets.Layout);