Ext.namespace('xlib.Layout');

xlib.Layout.TabPanel = Ext.extend(Ext.TabPanel, {
    
	activeTab: 0,
    
    activeItem: 0,
    
    defaults: {
        closable: true
    },
	
	enableTabScroll: true,
	
	initComponent: function() {
    	xlib.Layout.TabPanel.superclass.initComponent.apply(this, arguments);
	},

    /**
     * Check if the comp ids present in Component manager storage
     * then this component is present and we can activate it
     * 
     * @param {Object} comp
     */
    add: function(comp) {
        if (typeof comp.id === 'string') {
            var c = Ext.getCmp(comp.id);
            if (c) {
                this.setActiveTab(c);
                return c;
            }
        }
		var cmp = xlib.Layout.TabPanel.superclass.add.apply(this, arguments);
        cmp.show();
        return cmp;
	}   
    	
});
                    
xlib.Layout.Workspace = Ext.extend(Ext.Viewport, {
 
    layout: 'border',
	
    mainMenu: [],
    
    initComponent: function() {
        
		this.menuToolbar = new Ext.Toolbar({
        	region: 'north',
            height: 30,
            items: this.mainMenu
        });
		
        this.tp = new xlib.Layout.TabPanel({
            region: 'center',
            margins: '1 1 1 1',
            border: true,
			defaults: {
				border: false,
                closable: true
			}
        });
                
        this.items = [this.tp, this.menuToolbar];
        xlib.Layout.Workspace.superclass.initComponent.apply(this, arguments);
    },
	
	getTabPanel: function() {
		return this.tp;
	},
    
    createComponent: function(cmp) {
        
        switch (typeof cmp) {
            case 'undefined':
            case 'object':
                if (typeof cmp.id != undefined) {
                    var id = cmp.id;
                    var component = Ext.getCmp(id);
                    return component || Ext.ComponentMgr.create(cmp); 
                } 
                throw 'The component id is missing.';
                break;
                
            default:
                throw 'The component is wrong';
        }
    }
    
});