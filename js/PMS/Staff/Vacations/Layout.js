Ext.ns('PMS.Staff.Vacations');

PMS.Staff.Vacations.Layout = Ext.extend(Ext.Window, {
	
    resizable: false,
    
    width: 600,

    height: 500,
    
    modal: true,
    
    hidden: false,
    
    layout: 'border',
    
    personId: null,
    
	initComponent: function() {
        
        if (!this.personId) {
            throw 'personId is required!';
        }
        
        this.title = 'Табель отпусков №' + this.personId;
        
        this.info = new PMS.Staff.Info({
            region: 'north',
            height: 180,
            border: false,
            personId: this.personId
        });
        
        this.list = new PMS.Staff.Vacations.List({
            region: 'center',
            border: false,
            cls: 'x-border-top',
            personId: this.personId
        });

        this.items = [this.info, this.list];
        
        var addButton = new Ext.Button({
            text: 'Добавить',
            hidden: !acl.isUpdate('staff'),
            iconCls: 'add',
            handler: this.list.onAdd,
            scope: this.list
        });
        
        this.buttons = [addButton, {
            text: 'Закрыть',
            handler: function() {
                this.close();
            },
            scope: this
        }];
	            
		PMS.Staff.Vacations.Layout.superclass.initComponent.apply(this, arguments);
	}
    
});

Ext.reg('PMS.Staff.Vacations.Layout', PMS.Staff.Vacations.Layout);