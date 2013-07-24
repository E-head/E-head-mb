Ext.ns('xlib.grid');

xlib.grid.CheckColumn = function(config) {
	Ext.apply(this, config);
    xlib.grid.CheckColumn.superclass.constructor.call(this);
};

Ext.extend(xlib.grid.CheckColumn, Ext.grid.CheckColumn, {

    renderer: function(v, p, record) {
        if (typeof v !== 'boolean') {
            v = parseInt(v, 10);
        }
        var qtip = this.getQtip(record);
        qtip = Ext.isEmpty(qtip) ? '' : 'qtip="' + qtip + '"';
        var className = (this.disabled || v == -1) ? this.disabledCls : '';
        p.css += ' x-grid3-check-col-td';
        p.css += (this.disabled || v == -1) ? ' grid-check-column-disabled' : '';
        return '<div class="' + className + ' x-grid3-check-col' + (v ? '-on' : '') 
                + ' x-grid3-cc-' + this.id + '"' + qtip + '> </div>';
    },
  
    getQtip: function(record) {
        return null;
    }
});