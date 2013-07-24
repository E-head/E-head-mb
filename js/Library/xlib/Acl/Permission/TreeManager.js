Ext.ns('xlib.Acl.Permission');

xlib.Acl.Permission.TreeManager = function(c) {
    Ext.apply(this, c || {});
    this.items = new Ext.util.MixedCollection();
    this.node.on({
        disabledchange: function(node, disabled) {
            if (disabled === true) {
                this.disableAll();
            } else {
                this.enableAll();
            }
        },
        scope: this
    });
};

xlib.Acl.Permission.TreeManager.prototype = {

    ui: null,
    
    node: null,
        
    add: function(key, o) {
        o.on({
            click: this.onClick,
            scope: this
        });
        this.items.add(key, o);
    },
    
    onClick: function(e, node) {
        if (this.node.parentNode.disabled === true) {
            return;
        }
        var el = this.getNodeEl(node);
        var checked = el.dom.checked === true;
        var name = el.dom.name;
        Ext.Ajax.request({
            url: link('admin', 'acl', 'allow'),
            params: {
                resourceId: this.node.id,
                privilege: name,
                value: +checked,
                roleId: this.node.ownerTree.getRoleId()
            },
            success: function(response) {
                var res = xlib.decode(response.responseText);
                if (res.success !== true) {
                    el.dom.checked = !checked;
                    return;
                }
                if (!checked) {
                    var f = function(n) {     // disable
                        if (n.ui.node == this.node) {
                            return;
                        }
                        var c = n.ui.manager.items.get(name).dom;
                        c.checked = false;
                        c.disabled = true;
                        if (n.hasChildNodes()) {
                            n.eachChild(f, this);
                        }
                    };
                    this.node.expand(true, false);
                } else {
                    var f = function(n) {       // enable
                        var c = n.ui.manager.items.get(name).dom;
                        c.disabled = false;
                    }
                    this.node.expand(false, false);
                }
                this.node.eachChild(f, this);
            },
            failure: Ext.emptyFn,
            scope: this
        });   
    },
    
    getNodeEl: function(node) {
        return this.items.get(node.name);
    },
    
    disable: function(item) {
        
    },
    
    disableAll: function() {
        this.items.each(function(i) {
            i.disabled = true;
            this.disable(i);
        }, this);
    }
};