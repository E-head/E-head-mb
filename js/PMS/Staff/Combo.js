Ext.ns('PMS.Staff');

PMS.Staff.Combo = Ext.extend(xlib.form.ComboBox, {
	
    lazyInit: false,
    
    displayField: 'name',
	
    valueField: 'id',
	
    hiddenName: 'staff_id',
	
    name: 'staff_id',
	
    fieldLabel: 'Сотрудник',
	
    loadURL: link('staff', 'index', 'get-list'),
    
    trackResetOnLoad: true,
	
	allowBlank: true,
	
    mode: 'remote',
    
    initComponent: function() {
        
        this.store = new Ext.data.JsonStore({
            url: this.loadURL,
            root: 'data',
            sortInfo: {
                field: 'name',
                direction: 'ASC'
            },
            fields: ['id', 'name'] 
        });
        
        PMS.Staff.Combo.superclass.initComponent.apply(this, arguments);
    }
});

Ext.reg('PMS.Staff.Combo', PMS.Staff.Combo);