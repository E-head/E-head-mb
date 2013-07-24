Ext.ns('PMS.Staff.HR');

PMS.Staff.HR.Table = Ext.extend(Ext.grid.EditorGridPanel, {
	
    layout: 'fit',
    
    personId: null,
    
    dataURL: link('staff', 'hr', 'get'),
    
    saveURL: link('staff', 'hr', 'set'),
    
    date: (new Date()).getFirstDateOfMonth(),
    
    clicksToEdit: 1,
    
    enableHdMenu: false,
    
    enableColumnResize: false,
    
    enableColumnMove: false,
    
    enableColumnHide: false,
    
    columnLines: true,
    
	initComponent: function() {
        
        if (!this.personId) {
            throw 'personId is required!';
        }
        
        this.currentPeriod = new Ext.Toolbar.TextItem({
            style: 'font-weight: bold; margin-left: 310px;'
        });
        
        this.prevButton = new Ext.Toolbar.Button({
            tooltip: 'Предыдущий месяц',
            handler: function() {
                this.changeDate(-1);
            },
            scope: this
        });
        
        this.nextButton = new Ext.Toolbar.Button({
            tooltip: 'Следующий месяц',
            handler: function() {
                this.changeDate(1);
            }, 
            scope: this
        });
        
        this.tbar = [this.prevButton, '-', this.currentPeriod, '->', '-', this.nextButton]; 
        
        this.columns = [];
        
        this.store = new Ext.data.Store();
        
		PMS.Staff.HR.Table.superclass.initComponent.apply(this, arguments);
        
        this.on('render', function() {
            new Ext.LoadMask(this.el, {
                msg: 'Загрузка...',
                store: this
            });
            this.changeDate(0);
        }, this, {delay: 50});
        
	},
    
    changeDate: function(diff) {
        
        this.checkUnsaved(function() {
            this.date = this.date.add(Date.MONTH, diff);
            this.currentPeriod.setText(this.date.format('F Y'));
            this.prevButton.setText('<< ' + this.date.add(Date.MONTH, -1).format('F Y'));
            this.nextButton.setText(this.date.add(Date.MONTH, +1).format('F Y') + ' >>');
            this.loadHr();
            this.fireEvent('beforeload');
        }, {
            scope: this
        });
    },
    
    checkUnsaved: function(fn, params) {
        
        params = params || {};
        if (!Ext.isFunction(fn)) {
            return;
        }
        
        var records = this.getStore().getModifiedRecords();
        
        if (0 === records.length) {
            fn.call(params.scope||window, params);
        } else {
            xlib.Msg.confirm('Данные не сохранены, это действие приведёт ' +
                             'к потере введенных данных!<br /><br />Продолжить?',
                function() {
                    fn.call(params.scope||window, params);
                }
            );
        }
    },
    
    saveHr: function() {
        
        var records = this.getStore().getModifiedRecords();
        if (0 === records.length) {
            return;
        }
        
        Ext.Ajax.request({
            url: this.saveURL,
            params: {
                staff_id: this.personId,
                data: Ext.encode(records[0].getChanges()) 
            },
            callback: function(options, success, response) {
                var res = xlib.decode(response.responseText);
                if (true == success && res && true == res.success) {
                    this.getStore().commitChanges();
                    this.getSelectionModel().clearSelections();
                    this.fireEvent('saved');
                    return;
                }
                xlib.Msg.error('Ошибка записи.');
            },
            scope: this
        });
    },
    
    loadHr: function() {
        
        Ext.Ajax.request({
            url: this.dataURL,
            params: {
                staff_id: this.personId,
                date: this.date.format(xlib.date.DATE_FORMAT_SERVER)
            },
            callback: function(options, success, response) {
                var res = xlib.decode(response.responseText);
                if (true == success && res && true == res.success) {
                    
                    this.on('reconfigure', function(grid, store, colModel) {
                        store.loadData(res.data);
                        grid.fireEvent('load');
                    });
                    
                    this.reconfigure(new Ext.data.JsonStore({
                            fields: res.metaData 
                        }), new Ext.grid.ColumnModel({
                            defaults: {
                                width: 28,
                                sortable: false,
                                resizable: false,
                                fixed: true,
                                menuDisabled: true,
                                align: 'center',
                                editor: new Ext.form.NumberField({
                                    allowDecimals: false,
                                    allowNegative: false,
                                    maxValue: 24
                                })
                            },
                            columns: this.generateColumns(res.metaData)
                        })
                    );

                    
                    return;
                }
                xlib.Msg.error('Ошибка загрузки.');
            },
            scope: this
        });
    },
    
    generateColumns: function(metaData) {
        
        var columns = [];
        
        Ext.each(metaData, function(item) {
            var date = Date.parseDate(item.name, xlib.date.DATE_FORMAT_SERVER), 
                color = date.format('N') > 5 ? 'red' : 'black'; 
            columns.push({
                header: '<span style="color: ' + color + ';">' + date.format('d') + '</span>',
                tooltip: date.format('l') + '<br />' + date.format('d-m-Y'),
                dataIndex: item.name
            });
        });
        
        return columns;
    } 
});

Ext.reg('PMS.Staff.HR.Table', PMS.Staff.HR.Table);