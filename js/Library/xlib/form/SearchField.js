Ext.ns('xlib.form');

xlib.form.SearchField = Ext.extend(Ext.form.TwinTriggerField, {
    
    validationEvent: false,
    
    validateOnBlur: false,
    
    trigger1Class: 'x-form-clear-trigger',
    
    trigger2Class: 'x-form-search-trigger',
    
    hideTrigger1: true,
    
    hasSearch: false,
    
    paramName: 'query',
    
    minSearchLength: 2,
    
    initComponent: function() {
        xlib.form.SearchField.superclass.initComponent.apply(this, arguments);
        this.on('specialkey', function(f, e){
            if (e.getKey() == e.ENTER) {
                this.onTrigger2Click();
            }
        }, this);
    },
        
    onTrigger1Click: function() {
        if (this.hasSearch) {
            this.hideClearBtn();
            this.focus();
        }
    },
    
    showClearBtn: function() {
        this.triggers[0].show();
        this.hasSearch = true;
    },
    
    hideClearBtn: function() {
        this.el.dom.value = '';
        this.fireEvent('clear', this);
        this.triggers[0].hide();
        this.hasSearch = false;
    },
    
    onTrigger2Click: function() {
        var v = this.getRawValue();
        if (v.length < 1) {
            this.onTrigger1Click();
            return;
        }
        if (v.length < this.minSearchLength) {
        	xlib.Msg.error('Минмум символов для поиска: ' + this.minSearchLength);
            return;
        }
        this.fireEvent('click', this);
        this.triggers[0].show();
        this.focus();
    }
});