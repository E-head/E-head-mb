Ext.ns('xlib.Acl');

xlib.Acl.Layout = Ext.extend(Ext.Panel, {
    
    layout: 'border',
    
    border: false,
    
    title: 'Менеджер доступа',
    
    id: 'xlib.acl.layout',
    
    initComponent: function() {
        
        this.roles = new xlib.Acl.Roles.Tree({
            region: 'west',
            width: 200,
            border: false,
            margins: '0 2 0 0',
			cls: 'x-border-right'
        });
        
        this.accounts = new xlib.Acl.Accounts.List({
            region: 'center',
			border: false,
			cls: 'x-border-bottom x-border-left'
        });
        
        this.permissions = new xlib.Acl.Permission.Tree({
            region: 'south',
            height: 300,
            margins: '2 0 0 0',
            split: true,
            border: false,
			cls: 'x-border-top x-border-left',
            loadUrl: link('admin', 'acl', 'get-list')
        });
        
        this.items = [
            this.roles, {
            region: 'center',
            layout: 'border',
            border: false,
            items: [this.accounts, this.permissions]
        }];
        
        xlib.Acl.Layout.superclass.initComponent.apply(this, arguments);
        this.roles.on('click', this.onRolesClick, this);
        this.roles.on('firstnodeselected', this.onRolesClick, this);
    },
        
    onRolesClick: function(node) {
        var isRoot = this.roles.isRoot(node);
        if (isRoot) {
            this.permissions.mask(true);    
        } else {
            this.permissions.unmask();
        }
        
        this.permissions.setRoleId(node.id);
        this.accounts.setRoleId(node.id);
        this.accounts.createAccountBtn.setDisabled(isRoot);
    }
});

Ext.reg('xlib.acl.layout', xlib.Acl.Layout);