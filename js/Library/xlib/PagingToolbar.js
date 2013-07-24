Ext.ns('xlib');

xlib.PagingToolbar = Ext.extend(Ext.PagingToolbar, {

    displayInfo: true,
    
    pageSize: 30,
    
    plugins: [],
    
    variations: null,
    
    ps: null,
    
    psConfig: {},
    
    variations: null,
    
    forceLoad: true,
    
    initComponent: function() {
        
        if (false !== this.ps) {
            var o = Ext.applyIf(this.psConfig || {}, {
                stateId: this.stateId,
                pageSize: this.pageSize,
                pagingToolbar: this
            });
            
            if (this.variations) {
                o.variations = this.variations;
            }
            if (this.forceLoad != undefined) {
                o.forceLoad = this.forceLoad;
            }
            
            this.ps = new Ext.ux.Andrie.pPageSize(o);
            this.plugins = this.plugins.concat(this.ps);
        }
        
        xlib.PagingToolbar.superclass.initComponent.apply(this, arguments);
    },
    
    beforeLoad: function(store, options) {
        xlib.PagingToolbar.superclass.beforeLoad.call(this);
        options.params = options.params || {};
        Ext.applyIf(options.params, {
            start: 0,
            limit: this.pageSize
        });
        return true;
    },
	
	/**
     * Change the active page
     * @param {Integer} page The page to display
     */
    changePage: function(page, refreshOnly){
		
		if (true === refreshOnly) {
		    var page = ((page-1) * this.pageSize).constrain(0, this.store.getTotalCount());
			var ps = {};
			ps[this.paramNames.start] = page;
            this.refreshOptions(ps);			
		} else {
		    xlib.PagingToolbar.superclass.changePage.apply(this, arguments);	
		}
    },
	
	/**
	 * Refresh paging options
	 * 
	 * @param {Object} o
	 * @return {xlib.PagingToolbar}
	 */
	refreshOptions: function(o) {
		var args = [this.getStore(), [], {params: o}];
        this.onLoad.apply(this, args);
		return this;  
	},
	
	/**
	 * Retrieve store
	 * 
	 * @return {Ext.data.Store}
	 */
	getStore: function() {
		return this.store;
	}
});