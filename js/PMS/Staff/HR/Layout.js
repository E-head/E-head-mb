Ext.ns('PMS.Staff.HR');

PMS.Staff.HR.Layout = Ext.extend(Ext.Window, {
	
    resizable: false,
    
    width: 900,

    height: 230,
    
    modal: true,
    
    hidden: false,
    
    layout: 'border',
    
    personId: null,
    
	initComponent: function() {
        
        if (!this.personId) {
            throw 'personId is required!';
        }
        
        this.title = 'Табель учёта времени №' + this.personId;
        
        this.info = new PMS.Staff.Info({
            region: 'north',
            height: 180,
            border: false,
            personId: this.personId
        });
        
        this.time = new PMS.Staff.HR.Table({
            region: 'center',
            border: false,
            cls: 'x-border-top',
            personId: this.personId
        });

        this.items = [this.info, this.time];
        
        var saveButton = new Ext.Button({
            text: 'Сохранить',
            hidden: !acl.isUpdate('staff'),
            disabled: true,
            handler: this.time.saveHr,
            scope: this.time
        });
        
        this.buttons = [saveButton, {
            text: 'Закрыть',
            handler: function() {
                this.close();
            },
            scope: this
        }];
	            
		PMS.Staff.HR.Layout.superclass.initComponent.apply(this, arguments);
        
        this.time.on({
            afteredit: function() {
                saveButton.enable();
            },
            beforeload: function() {
                saveButton.disable();
            },
            saved: function() {
                saveButton.disable();
            },
            scope: this
        });
	},
    
    close: function() {
        var func = function() {
            PMS.Staff.HR.Layout.superclass.close.call(this);
        }
        this.time.checkUnsaved(func, {scope: this});
    }
    
});

Ext.reg('PMS.Staff.HR.Layout', PMS.Staff.HR.Layout);