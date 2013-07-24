Ext.ns('xlib.Acl');

xlib.Acl.Manager = function() {
    
    var privileges = xlib.Acl.Storage.Privileges || {};
    var permissions = xlib.Acl.Storage.Permissions || {};
    var resources = new Ext.util.MixedCollection();
    
    Ext.each(xlib.Acl.Storage.getResources(), function(i, index) {
        resources.add(i[0], {
            name: i[1],
            parent: i[2] || false
        });
    });
    
    return {
    	
        isView: function() {
            var resourceId = this.fetchResource.apply(this, arguments);
            return this.isAllowedPrivilege(resourceId, xlib.Acl.Storage.getPrivileges().view);
        },
        
        isAdd: function() {
            var resourceId = this.fetchResource.apply(this, arguments);
            return this.isAllowedPrivilege(resourceId, xlib.Acl.Storage.getPrivileges().add);
        },
        
        isUpdate: function() {
            var resourceId = this.fetchResource.apply(this, arguments);
            return this.isAllowedPrivilege(resourceId, xlib.Acl.Storage.getPrivileges().update);
        },
        
        isDelete: function() {
            var resourceId = this.fetchResource.apply(this, arguments);
            return this.isAllowedPrivilege(resourceId, xlib.Acl.Storage.getPrivileges()['delete']);
        },
        
        isAllowedPrivilege: function(resourceId, privilege) {
            var collection = xlib.Acl.Storage.getPermissions()[resourceId];
            if (!collection || !Ext.isArray(collection)) {
                return false;
            }
            if (-1 == collection.indexOf(new String(privilege).toString())) {
                return false;
            }
            return true;
        },

        fetchResource: function(resource) {
            var resourceId = false;
            for (var i = 0, l = arguments.length; i < l; i++) {
                var resourceId = this.fetchByParent(arguments[i], resourceId);
                if (false == resourceId) {
                    break;
                }
            }
            if (false === resourceId) {
                var p = [];
                for(var i = 0, l = arguments.length; i < l; i++) {
                    p.push(arguments[i]);
                }
                if (DEBUG) {
                    Ext.Ajax.request({
                        url: link('admin', 'acl', 'insert-resource'),
                        params: {
                            resource: p.join(',')
                        },
                        scope: this
                    });
                }
            }
            return resourceId;
        },
        
        fetchByParent: function(resource, parent) {
            var resource = resource.toLowerCase();
            var resourceId = false;
            var resourceObj = resources.find(function(item, key) {
                if (resource == item.name) {
                    if (false == parent || item.parent == parent) {
                        resourceId = key;
                        return true;
                    }
                }
            });
            return resourceId;
        }
    };
}();

acl = xlib.Acl.Manager;