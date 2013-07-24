Ext.ns('xlib.grid');

xlib.grid.Actions = function(config){
    Ext.apply(this.rowConfig, config || {});
    xlib.grid.Actions.superclass.constructor.call(this);
};

Ext.extend(xlib.grid.Actions, Ext.util.Observable, {
    
    rowConfig: {},
    
    init: function(grid) {

        this.grid = grid;
        if (!this.rowConfig.items) {
            return;
        }
        
        var item = this.rowConfig.items;
        this.grid.rowActionsConfig = this.rowConfig.items;
        this.grid.on({
            rowcontextmenu: this.onRowContextMenu,
            scope: this
        });
    },

    onRowContextMenu: function (g, rowIndex, e) {
        
        e.stopEvent();
        
        var record = g.getStore().getAt(rowIndex);
        if (!g.getSelectionModel().isIdSelected(record.get('id'))) {
        	g.getSelectionModel().selectRow(rowIndex);
        }
        
        var menu = new Ext.menu.Menu();
        var count = 0;
        
        Ext.each(g.rowActionsConfig, function(i) {
            
            var item = null;
            if ('function' === typeof i) {
                item = i(g, rowIndex, e);
            } else {
                item = i;
            }
            
            if (false !== item) {
                var isSeparator = item instanceof Ext.menu.Separator || item == '-';
                if (!isSeparator && !(item.hidden || item.disabled)) {
                    item = new Ext.menu.Item(item);
                    
                    if (item.handler) {
                        var h = item.handler;
                        item.setHandler(function(){
                            h.createDelegate(this, [g, rowIndex, e])();
                        }, item.scope || window);
                    }
                    count++;
                }
                
                menu.add(item);
            }
                                    
        }, this);
        
        if (count > 0) {
            menu.showAt(e.getXY());
        }
    }
});