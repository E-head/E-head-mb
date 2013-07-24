Ext.ns('xlib.Acl.Permission');

xlib.Acl.Permission.TreeNodeUI = Ext.extend(Ext.tree.ColumnNodeUI, {
    onCheckboxClick: function() {},
	onCheckChange: function() {}
});

xlib.Acl.Permission.Tree = Ext.extend(Ext.tree.ColumnTree, {

    animCollapse: false,
    
    animate: false,
    
    autoScroll:true,
    
    title: 'Редактор уровней доступа',
    
    autoExpandColumn: 'resource',
    
    allowRename: false,
    
    allowRemove: false,
    
    enableDD: false,
    
    enableDrop: true,
    
    loadUrl: null,
    
    rootVisible: false,
    
    initComponent: function() {
        
        this.tools = [{
            id: 'refresh',
            qtip: 'Обновить',
            handler: function() {
                this.getRootNode().reload();
            },
            scope: this
        }];
        
		var tpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<div class="column-tree-action-item">',
                '<input type="checkbox" name="{name}" qtip="{qtip}" ',
                '{[values.checked == true ? " checked " : ""]}' ,
                '{[values.disabled == true ? " disabled " : ""]} />',
            '</div>',
            '</tpl>'
        ).compile();

		var renderer = function(attribs, name, node) {
            var disabled = false;
            var manager = node.parentNode.ui.manager;
            if (manager) {
                disabled = !manager.items.get(name).dom.checked;
            }    
			return tpl.apply({
				qtip: name,
				name: name,
				checked: attribs[name] ? 1 == attribs[name] : '',
				disabled: disabled
			});
		};
		        
        this.columns = [{
            header: 'Ресурсы',
            dataIndex: 'resource',
            width: 100
        }, {
			header: 'Просмотр',
            width: 100,
            cls: 'column-tree-row-actions',
            renderer: function(a, node, attribs) {
                return renderer(attribs, 'view', node);
            }
//		}, {
//			header: 'Добавление',
//			width: 100,
//			cls: 'column-tree-row-actions',
//			renderer: function(a, node, attribs) {
//				return renderer(attribs, 'add', node);
//			}
		}, {
            header: 'Редактирование',
            width: 100,
            cls: 'column-tree-row-actions',
            renderer: function(a, node, attribs) {
                return renderer(attribs, 'update', node);
            }
//        }, {
//            header: 'Удаление',
//            width: 100,
//            cls: 'column-tree-row-actions',
//            renderer: function(a, node, attribs) {
//                return renderer(attribs, 'delete', node);
//            }
        }];

        this.loader = new Ext.tree.TreeLoader({
            dataUrl: this.loadUrl,
            uiProviders:{
                'col': xlib.Acl.Permission.TreeNodeUI
            },
            baseAttrs: {
                uiProvider: 'col',
                cls: 'x-tree-node-leaf'
            },
            listeners: {
                load: function(tree, node, response) {
                    this.syncSize();
                    if (node.disabled === true) {
                        node.eachChild(function(n){
                            n.disable();
                        }, this);
                    }
                },
                scope: this
            }
        });

        this.root = new Ext.tree.AsyncTreeNode({
            id: '0',
            draggable: false,
            text: 'Ресурсы',
            expanded: false
        });
        
        xlib.Acl.Permission.Tree.superclass.initComponent.apply(this, arguments);
        this.on({
            renderelements: this.onRenderElements,
			addcorenode: function(tree, node, destinationNode) {
				node.parentNode.reload();
			}, 
            contextmenu: this.onContextMenu
        });
        
        // I don't know another way how to disable first loading :(
        this.getLoader().on('beforeload', function() {
            this.clear();
            return false;
        }, this, {single: true});
    },

	mask: function(maskOnly) {
        if (true === maskOnly) {
            var m = this.bwrap.mask();
        } else {
            var m = this.bwrap.mask('Загрузка...', 'x-mask-loading');
        }
		
		m.setStyle('z-index', 5000);
	},
	
	unmask: function() {
		this.bwrap.unmask();
	},
	
    onRenderElements: function(ui) {
		ui.manager = new xlib.Acl.Permission.TreeManager({
            ui: ui,
            node: ui.node
        });
		
        var count = 2;
		for (var i = 1; i <= count; i++) {
			var comp = Ext.get(ui.columns[i].firstChild.firstChild.firstChild);
			ui.manager.add(comp.dom.name, comp);
		}
    },
	
    onRemove: function(node) {
    	xlib.Msg.confirm('Вы уверены?', function() {
            Ext.Ajax.request({
                url: link('admin', 'acl', 'delete-resource'),
                params: {
                    resourceId: node.id
                },
                success: function(response) {
                    var res = xlib.decode(response.responseText);
                    if (res.success === true) {
                        node.remove();
                        return;
                    }
                    xlib.Msg.error('Не удалось удалить ресурс.');
                }   
            });         
        });
    },

    setRoleId: function(id) {
        this.clear();
        this.getLoader().baseParams.roleId = id;
        if (id > 0) {
            this.mask();
            this.getRootNode().reload(function() {
                this.unmask();
            }.createDelegate(this))
        }
        this.roleId = id;
    },
    
    clear: function() {
        delete this.getLoader().baseParams.roleId;
        var root = this.getRootNode();
        while(root.firstChild){
            root.removeChild(root.firstChild);
        }
    },
    
    getRoleId: function() {
        return this.roleId;
    },
    
    onContextMenu: function(node, e) {
        e.stopEvent();
        var menu = new Ext.menu.Menu({
            items: [{
                text: 'Удалить',
                qtip: 'Удалить ресурс',
                iconCls: 'delete',
                handler: function() {
                    this.onRemove(node);
                },
                scope: this
            }],
            scope: this
        });
        menu.showAt(e.getXY());
    }
});

Ext.reg('xlib.acl.permission.tree', xlib.Acl.Permission.Tree);