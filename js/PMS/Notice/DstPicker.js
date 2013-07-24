Ext.ns('PMS.Notice');

PMS.Notice.DstPicker = Ext.extend(Ext.grid.GridPanel, {

    title:      'Роли и пользователи системы',
    
    listURL:    link('admin', 'notice', 'get-dst'),
    
    deleteURL:  link('admin', 'notice', 'delete'),
    
    loadMask: true,
    
    permissions: acl.isUpdate('notice'),
    
    initComponent: function() {
        
        var self = this;
        
        this.autoExpandColumn = Ext.id();
        
        this.ds = new Ext.data.GroupingStore({
            autoDestroy: true,
            url: this.listURL,
            reader: new Ext.data.JsonReader({
                idProperty: 'id',
                root: 'data',
                fields: [{name: 'id', type: 'int'}, 'role', 'name', {name: 'checked', type: 'int'}]
            }),
            sortInfo: {field: 'name', direction: 'ASC'},
            groupField: 'role'
        });
        
        this.sm = new Ext.grid.RowSelectionModel();
        
        this.view = new Ext.grid.GroupingView({
            startCollapsed: true,
            hideGroupedColumn: true,
            groupTextTpl: '<span class="check-box-unchecked" ' +
                'style="float: right; display: block;" ' +
                'qtip="Очистить всех" xlib:startRow="{startRow}"></span> ' +
                '<span class="check-box-checked" ' +
                'style="float: right; display: block;" ' +
                'qtip="Отметить всех" xlib:startRow="{startRow}"></span>  ' +
                '{text}',
            interceptMouse: function(e, node) {
                
                var groupHeader = e.getTarget('.x-grid-group-hd', this.mainBody);
                if (!groupHeader) {
                    return;
                }
                
                var group = groupHeader.parentNode;
                
                e.stopEvent();
                if (e.getTarget('.check-box-unchecked', this.mainBody)) {
                    this.toggleGroup(group, true);
                    self.checkAll(node, false);
                } else if (e.getTarget('.check-box-checked', this.mainBody)) {
                    this.toggleGroup(group, true);
                    self.checkAll(node, true);
                } else if (e.getTarget('.x-grid-group-hd', this.mainBody)) {
                    this.toggleGroup(group);
                }
            },
            getRowClass: function(record, rowIndex, rp, ds) {
                Ext.apply(rp, {tstyle: 'border: none;'});
            }
        });
        
        var checkColumn = new xlib.grid.CheckColumn({
            width: 65,
            header: 'Выбор',
            inputValue: 1,
            dataIndex: 'checked'
        });
        
        this.columns = [checkColumn, {
            width: 65,
            header: 'Роль',
            dataIndex: 'role'
        }, {
            header: 'Название',
            id: this.autoExpandColumn,
            dataIndex: 'name'
        }];
        
        this.plugins = [checkColumn];
        
        PMS.Notice.DstPicker.superclass.initComponent.apply(this, arguments);
        
    },
    
    getValues: function() {
        var result = [];
        this.getStore().each(function(record) {
            result.push({
                id: record.get('id'),
                value: record.get('checked')
            });
        });
        return result;
    },
    
    setValues: function(values) {
        
        this.getView().expandAllGroups();
        
        Ext.each(values, function(i) {
            var record = this.getStore().getById(i);
            if (record) {
                record.set('checked', 1);
            }
        }, this);
        
        this.getStore().commitChanges();
    },
    
    checkAll: function(node, check) {
        
        var el = Ext.fly(node);
        if (!el) {
            return;
        }
        
        var startRow = el.getAttribute('startRow', 'xlib'); 
        if (!startRow) {
            return;
        }
        
        var record = this.getStore().getAt(startRow); 
        if (!record) {
            return;
        }
        
        this.getStore().each(function(r) {
            if (record.get('role') == r.get('role')) {
                r.set('checked', !check ? 0 : 1);
            }
        });
    }
    
});

Ext.reg('PMS.Notice.DstPicker', PMS.Notice.DstPicker);