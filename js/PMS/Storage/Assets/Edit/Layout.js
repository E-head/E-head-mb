Ext.ns('PMS.Storage.Assets.Edit');

PMS.Storage.Assets.Edit.Layout = Ext.extend(Ext.Panel, {
    
    record: null,
	
    border: false,
    
    defaultTitle: 'Запись о ТМЦ',
    
    layout: 'border',
    
    inWindow: false,
    
    categoryId: null,
    
	initComponent: function() {
        
        this.title = this.inWindow ? false : this.defaultTitle;
    
        this.categories = new PMS.Storage.Assets.Tree({
            region: 'west',
            width: 250,
            border: false,
            header: false,
            loadURL: link('storage', 'categories', 'get-complete-tree-checked'),
            margins: '0 2 0 0',
            cls: 'x-border-right'
        });
        
        this.categories.on('load', this.categories.expandAll);
        
        this.asset = new PMS.Storage.Assets.Edit.Form({
            region: 'center',
            border: false,
            cls: 'x-border-left'
        });
        
	    this.items = [this.categories, this.asset];
        
        if (Ext.isObject(this.record)) {
            var loader = this.categories.getLoader();
            loader.on("beforeload", function(treeLoader, node) {
                loader.baseParams.asset_id = this.record.get('id');
            }, this);
            this.categories.getRootNode().on('load', function() {
                this.loadData(this.record);
            }, this);
        } else if (null != this.categoryId) {
            // if creating inside category, make this category checked by default
            this.categories.on('load', function() {
                var node = this.categories.getRootNode().findChild('id', this.categoryId, true);
                if (null != node) {
                    node.getUI().toggleCheck(true);
                }
            }, this);
        }
        
		PMS.Storage.Assets.Edit.Layout.superclass.initComponent.apply(this, arguments);
        
        if (this.inWindow) {
            
            this.editWindow = new Ext.Window({
                layout: 'fit',
                title: this.defaultTitle,
                resizable: false,
                width: 600,
                height: 400,
                modal: true,
                buttons: [{
                    text: Ext.isObject(this.record) ? 'Сохранить' : 'Добавить',
                    handler: this.saveData,
                    scope: this
                }, {
                    text: 'Отмена',
                    handler: function() {
                        this.editWindow.close();
                    },
                    scope: this
                }]
            });
            
            this.title = false;
            this.editWindow.add(this);
            this.editWindow.show();
        }
	},
    
    loadData: function(record) {
        this.asset.getForm().loadRecord(record);
    },
    
    saveData: function() {
        var params = {
            categories: [this.categories.getChecked('id')]
        }; 
        if (Ext.isObject(this.record)) {
            params.id = this.record.get('id');
        }
        this.asset.getForm().submit({
            url: Ext.isObject(this.record) 
                ? link('storage', 'assets', 'update') 
                : link('storage', 'assets', 'add'),
            params: params,
            success: function(form, action) {
                this.fireEvent('saved');
                this.editWindow.close();
            },
            failure: function(form, action) {
                switch (action.failureType) {
                    case Ext.form.Action.CLIENT_INVALID:
                    case Ext.form.Action.CONNECT_FAILURE:
                    case Ext.form.Action.SERVER_INVALID:
                    default:
                        xlib.Msg.error('Ошибка сохранения.');
               }
            },
            scope: this
        });
    }
});

Ext.reg('PMS.Storage.Assets.Edit.Layout', PMS.Storage.Assets.Edit.Layout);