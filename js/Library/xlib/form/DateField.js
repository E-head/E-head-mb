Ext.ns('xlib.form');

xlib.form.DateField = Ext.extend(Ext.form.DateField, {

    hiddenFormat: 'Y-m-d',
    
    hiddenName: null,
    
    hiddenId: null,

    hiddenValue: null,
        
    setValue : function(date) {
        Ext.form.DateField.superclass.setValue.call(this, this.formatDate(this.parseDate(date)));
        this.updateHidden();
    },
    
    updateHidden: function() {
        if (this.hiddenField) {
            var v = this.getValue();
            var value = Ext.isDate(v) ? v.format(this.hiddenFormat) : '';
            this.hiddenField.value = value;
        }
    },
    
    onRender: function() {
        xlib.form.DateField.superclass.onRender.apply(this, arguments);
        
        if(this.hiddenName && !this.disabled) {
            this.hiddenField = this.el.insertSibling({
                tag:'input', 
                type:'hidden', 
                name: this.hiddenName, 
                id: this.hiddenId || this.hiddenName
            }, 'before', true);
            
            var v = this.getValue();
            this.hiddenField.value = Ext.isDate(v) ? v.format(this.hiddenFormat) : '';
            this.el.dom.removeAttribute('name');
        }
    },
	
	onTriggerClick: function() {
		if(this.disabled){
            return;
        }
		xlib.form.DateField.superclass.onTriggerClick.apply(this, arguments);
		
		this.menu.picker.on('select', function() {
			this.fireEvent('select', this);
		}, this);
	},
    
    onBlur: function() {
        this.updateHidden();
    },
    
    getHiddenValue: function() {
    	return (this.hiddenField)? this.hiddenField.value : '';
    }
});

Ext.reg('xlib.form.DateField', xlib.form.DateField);