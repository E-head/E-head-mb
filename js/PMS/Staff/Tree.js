Ext.ns('PMS.Staff');

PMS.Staff.Tree = Ext.extend(xlib.TreePanel, {

    title: 'Категории',

    untitledNodeName: 'Новая категория',
    
    loadURL: link('staff', 'categories', 'get'),
    
    addURL: link('staff', 'categories', 'add'),
    
    updateURL: link('staff', 'categories', 'update'),
    
    deleteURL: link('staff', 'categories', 'delete'),
    
    firstNodeSelected: true,
    
    ddAppendOnly: true,
    
    enableDrop: false,
    
    enableDD: false,
    
    rootVisible: true,
    
    readOnly: false,
    
    initComponent: function() {
    
        this.root = new Ext.tree.AsyncTreeNode({
            text: 'Все категории',
            id: '0',
            leaf: false,
            expanded: true,
            allowRemove: false,
            allowRename: false
        });
        
        this.tools = [{
            id: 'refresh',
            qtip: 'Обновить',
            handler: function() {
                this.getRootNode().reload();
            },
            scope: this
        }];
        
        this.loader = new Ext.tree.TreeLoader({
            url: this.loadURL,
            baseAttrs: {
                leaf: false
            }
        });
        
        PMS.Staff.Tree.superclass.initComponent.apply(this, arguments);
    },
    
    onContextMenu: function(node, e) {
        
        e.stopEvent();
        node.select();
        
        if (this.readOnly) {
            return;        
        }
        
        var menu = new Ext.menu.Menu();
        
        menu.add({
            text: 'Добавить',
            iconCls: 'add',
            handler: function() {
                this.createProcess(node);
            },
            scope: this
        });
        
        if (!this.isRoot(node)) {
            menu.add({
                text: 'Переименовать',
                iconCls: 'edit',
                handler: function() {
                    this.treeEditor.triggerEdit(node);
                }, 
                scope: this
            });
            
            menu.add({
                text: 'Удалить',
                iconCls: 'delete',
                handler: function() {
                    this.beforeRemove(node);
                }, 
                scope: this
            });
        }
        
        menu.showAt(e.getXY());
    },
    
    createProcess: function(node) {
        var text = 'Новая категория';
        Ext.Ajax.request({
            url: this.addURL,
            params: {
        		parent: node.id,
                name: text
            },
            callback: function(options, success, response) {
                var r = xlib.decode(response.responseText);
                if (success && r && r.success && r.id > 0) {
                    var newNode = new Ext.tree.TreeNode({
                        expanded: true,
                        text: text,
                        id: r.id 
                    });
                    node.expand();
                    node.appendChild(newNode);
                    this.treeEditor.triggerEdit(newNode);
                    return;
                }
                xlib.Msg.error('Не удалось добавить категорию.');
            },
            scope: this
        });
    },
    
    renameProcess: function(editor, node, value, startValue) {
        Ext.Ajax.request({
            url: this.updateURL,
            params: {
                node: node.id,
                name: value
            },
            callback: function(options, success, response) {
                var r = xlib.decode(response.responseText);
                var success = success && r && r.success;
                
                if (!success) {
                    this.revertRename(node, startValue);
                    xlib.Msg.error('Не удалось переименовать категорию.');
                }
            },
            scope: this
        });
    },
    
    removeProcess: function(node) {
        Ext.Ajax.request({
            url: this.deleteURL,
            params: {
                id: node.id
            },
            callback: function(options, success, response) {
                var msg = 'Ошибка при удалении.';
                if (true == success) {
                    var res = xlib.decode(response.responseText);
                    if (true == res.success) {
                        this.removeNode(node);
                        return;
                    } else if (res.errors) {
                        var msg;
                        switch (res.errors[0].code) {
                            case -20:
                                msg = 'Невозможно удалить. Категория не пустая.'
                                break;
                            default:
                        }
                    }
                }
                xlib.Msg.error(msg);
            },
            scope: this
        });
    }
});

Ext.reg('PMS.Staff.Tree', PMS.Staff.Tree);