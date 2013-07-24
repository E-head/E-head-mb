Ext.ns('xlib.Acl.Accounts');

xlib.Acl.Accounts.Combo = Ext.extend(xlib.form.ComboBox, {
	
    lazyInit: false,
    
    displayField: 'name',
	
    valueField: 'id',
	
    fieldLabel: 'Пользователь',
	
    resizable: false,
	
    trackResetOnLoad: true,
	
	allowBlank: true,
	
    mode: 'remote',
    
    overCls: '',
    
    filteringMode: 'remote',
    
    URL: null, 

    initComponent: function() {
        
        this.store = new Ext.data.JsonStore({
            url: this.URL,
            root: 'data',
            sortInfo: {
                field: 'name',
                direction: 'ASC'
            },
            fields: ['id', 'name'] 
        });
        
        xlib.Acl.Accounts.Combo.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('xlib.Acl.Accounts.Combo', xlib.Acl.Accounts.Combo);