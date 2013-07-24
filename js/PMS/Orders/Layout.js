Ext.ns('PMS.Orders');

PMS.Orders.Layout = Ext.extend(Ext.Panel, {
	
	title: 'Заказы',
	
    border: false,
    
    layout: 'border',
    
	initComponent: function() {
    	
    	this.listPanel = new PMS.Orders.List({
    		region: 'center',
    		border: false,
    		cls: 'x-border-right x-border-bottom'
    	});
    	
    	this.filesPanel = new PMS.Orders.Files({
    		region: 'center',
    		border: false,
    		cls: 'x-border-left x-border-bottom'
    	});

		this.infoPanel = new PMS.Orders.Info({
			width: 320,
			margins: '0 2px 0 0',
			cls: 'x-border-left x-border-right x-border-bottom',
            region: 'west'
        });
        
		this.descriptionPanel = new Ext.Panel({
			title: 'Описание',
			region: 'south',
			autoScroll: true,
    		margins: '2px 0 0 0',
            border: false,
			padding: 5,
			bodyCssClass: 'images-view',
			cls: 'x-border-right x-border-top',
			height: 130
		})
		
        this.notesPanel = new PMS.Orders.Edit.Notes({
        	margins: '2px 0 0 0',
        	cls: 'x-border-left x-border-top',
        	region: 'south',
        	permissions: false,
        	height: 130
        });
		
	    this.items = [{
	    	region: 'center',
	    	layout: 'border',
	    	border: false,
	    	items: [this.listPanel, this.descriptionPanel]	
	    }, {
            layout: 'border',
            width: 450,
			region: 'east',
			border: false,
            margins: '0 0 0 2px', 
            defaults: {
	    		border: false
            },
            items: [this.infoPanel, this.filesPanel, this.notesPanel]
        }];
        
        this.listPanel.on('orderselect', function(record) {
            this.infoPanel.loadData(record);
            this.filesPanel.loadData(record.data);
            if (acl.isView('orders', 'description')) {
            	this.descriptionPanel.update(record.get('description'));
            }
            this.notesPanel.store.setBaseParam('orderId', record.get('id'));
            this.notesPanel.store.load();
        }, this);
        
		PMS.Orders.Layout.superclass.initComponent.apply(this, arguments);
	}
});

Ext.reg('PMS.Orders.Layout', PMS.Orders.Layout);