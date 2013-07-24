Ext.ns('PMS.Staff');

PMS.Staff.Layout = Ext.extend(Ext.Panel, {
	
	title: 'Кадры',
	
    border: false,
    
    layout: 'border',
    
    readOnly: false,
    
	initComponent: function() {
        
        this.categories = new PMS.Staff.Tree({
            region: 'west',
            minWidth: 200,
            width: 300,
            split: true,
            border: false,
            margins: '0 2 0 0',
            readOnly: this.readOnly,
            cls: 'x-border-right'
        });
        
        this.persons = new PMS.Staff.List({
            region: 'center',
            border: false,
            readOnly: this.readOnly,
            cls: 'x-border-left'
        });
        
	    this.items = [this.categories, this.persons];
        
        var loadNodeItems = function(node) {
            this.persons.loadList(node.id, node.text);
        } 
        
        this.categories.on({
            click: loadNodeItems,
            contextmenu: loadNodeItems,
            firstnodeselected: loadNodeItems,
            textchange: function(node, text, oldText) {
                this.persons.setTitle(this.persons.baseTitle + '"' + text + '"');
            },
            scope: this
        });
        
		PMS.Staff.Layout.superclass.initComponent.apply(this, arguments);
	},
    
    getSelected: function() {
        var record = this.persons.getSelectionModel().getSelected();
        return (Ext.isObject(record) && parseInt(record.get('id')) > 0) ? record : false;
    }
});

Ext.reg('PMS.Staff.Layout', PMS.Staff.Layout);