Ext.grid.CheckColumn = function(config){
    Ext.apply(this, config);
    if(!this.id){
        this.id = Ext.id();
    }
    this.renderer = this.renderer.createDelegate(this);
};

Ext.grid.CheckColumn.prototype ={

    disabledCls: 'x-item-disabled',
    
    init : function(grid) {
        this.grid = grid;
        this.grid.on('render', function(){
            var view = this.grid.getView();
            view.mainBody.on('mousedown', this.onMouseDown, this);
        }, this);
    },

    onMouseDown : function(e, t) {
        
        var classExists = t.className && t.className.indexOf('x-grid3-cc-' + this.id) != -1;
        if (this.disabled || !classExists || -1 != t.className.indexOf(this.disabledCls)) {
            return;
        }
        
        var row             = this.grid.view.findRowIndex(t);
        var col             = this.grid.view.findCellIndex(t);
        var field           = this.grid.colModel.getDataIndex(col);
        var record          = this.grid.store.getAt(row);
        var originalValue   = +(record.data[this.dataIndex] == true);
        var value           = +(!originalValue);
        var xe = {
            grid: this.grid,
            record: record,
            field: field,
            value: value,
            originalValue: originalValue,
            row: row,
            column: col
        };
        
        if (false == this.grid.fireEvent('beforepress', xe)) {
            return;
        }
        record.set(this.dataIndex, value);
        this.grid.fireEvent('afteredit', xe, this.grid);
    },

    renderer : function(v, p, record){
        if (typeof v !== 'boolean') {
            v = parseInt(v, 10);
        }
        var disabledClassName = (this.disabled || v == -1) ? this.disabledCls : '';
        p.css += ' x-grid3-check-col-td';
        p.css += (this.disabled || v == -1) ? ' grid-check-column-disabled' : '';
        return '<div class="' + disabledClassName + ' x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'"> </div>';
    }
};