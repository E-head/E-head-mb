Ext.ns('xlib.form');

/**
 * Combo extension. Allow local filtering, lazy set value, etc.
 */
xlib.form.ComboBox = Ext.extend(Ext.form.ComboBox, {
    
    //defaults options
    resizable: true,
    
    forceSelection: true,
    
    triggerAction: 'all',
    
    selectFirst: false,
    
    /**
     * Refresh default value after loading
     * 
     * @param {String}
     */
    trackResetOnLoad: false, 
    
    /**
     * Define if filtering mode will be executed locally
     * Can apply only "local" and "remote"
     * Param "local" by default 
     * 
     * @cfg {String}
     */
    filteringMode: 'local',

    /**
     * On every click on combo trigger store will be reloaded
     * 
     * @param {Boolean}
     */
    reloadOnChange: false,
    
    /**
     * Set reload on chage event
     * Possible are:
     *  "change",
     *  "select"
     */
    reloadOnChangeEvent: 'change',

    /**
     * Set display raw field
     * <example>
     *      {name}: ({date})
     * </example>
     * 
     * @param {String}
     */
    displayRawField: null,
    
    /**
     * Display field options
     * Can be used in XTemplate object
     * 
     * @param {Object}
     */
    displayRawFieldOptions: {},
    
    /**
     * Render the blank option in combo at first position
     * 
     * @param {Boolean}
     */
    allowBlankOption: false,
    
    /**
     * Data of blank option to override default empty values
     * 
     * @param {struct}
     */
    blankOptionData: null,
    
    /**
     * Position of blank option
     * Possible values: ['first', 'last']
     * Default value: 'first'
     *  
     * @param {string}
     */
    blankOptionPosition: 'first',
    
    /**
     * Update value when store is loaded
     * 
     * @param {Boolean}
     */
    preloadRecords: true,
    
    queryParam: 'filter[0][data][value]',
    
    minChars: 2,
    
	preloadOnNullValue: true,
	
    /**
     * Initialize component
     * Set handler for event "beforequery"
     */
    initComponent: function() {
    	xlib.form.ComboBox.superclass.initComponent.call(this);
        this.on('beforequery', this.onBeforeQuery);
		this.store.on('load', function() {
			this._storeLoaded = true;
            this.prepareDefaultValue(this.getStore());
		}, this, {single: true});
        if ('local' == this.mode && this.allowBlankOption) {
            this.insertBlankOption();
        }
		this.store.on('load', function(store, records) {
            if (this.allowBlankOption) {
                this.insertBlankOption();
            }
			if (this.getValue()) {
				this.setValue(this.getValue());
			}
            this.doSelectFirst();
		}, this);
        if (this.reloadOnChange) {
            this.on(this.reloadOnChangeEvent, function() {
                this.getStore().reload();
            }, this);
        }
        if (this.selectFirst && this.mode == 'local') {
            this.doSelectFirst();
        }
    },
    
    initList: function() {
        if (!this.tpl) {
            this.tpl = '<tpl for="."><div class="x-combo-list-item">'
                + this.getDisplayField()
                + '&nbsp;</div></tpl>';
        }
        xlib.form.ComboBox.superclass.initList.apply(this, arguments);
    },
	
	prepareDefaultValue: function(store) {
		this.selectByValue(this.hiddenValue, true);
	},
	
    /**
     * Executed before query
     * if filteringMode is local make only once request
     * and try filtering data locally
     * 
     * @param {Object} queryConfig 
     *      query       {String}    SQL query
     *      forceAll    {Boolean}   
     *      combo       {Object}    mean that "this"
     *      cancel      {Boolean}   
     */
    onBeforeQuery: function(queryConfig) {
        
		if (this.mode == 'remote' && this.filteringMode == 'local') {
            this.minChars = 1;
            
            if (this.loadedRecords) {
                this.store.clearFilter();
            }
            
            if (this.store.getCount() === 0) {
                this.onBeforeLoad();
                this.store.load({
                    callback: function() {
                        this.loadedRecords = true;
                        this.store.filter(this.displayField, queryConfig.query);
                    },
                    scope: this
                });
            }
            
            this.expand();            
            queryConfig.cancel = true;
            if (this.loadedRecords && queryConfig.query.length > 0) {
                this.store.filter(this.displayField, queryConfig.query);
            }
            this.restrictHeight();
        }
    },
    
    getDisplayedValue: function() {
    	var displayedValue, value = this.getValue();
    	if (value) {
    		this.getStore().each(function(rec) {
    			if (rec.get(this.valueField) == value) {
    				displayedValue = rec.get(this.displayField);
    				return false; 
    			}
    		}, this) 
    	}
    },
    
    /**
     * Set combo value
     * If combo is not loaded then loaded and try set value
     */
    setValue: function(v) {
        var s = this.getStore();
        if ('object' == Ext.type(s)) {
			s.setBaseParam('value', v);
        }
        if (this.preloadRecords && this.preloadOnNullValue 
        && 'remote' === this.mode && !this.isLoaded()) {
            this.initList();
            var args = arguments;
            this.onBeforeLoad();
            
            var p = {};
            if (this.pageSize > 0) {
                p.start = 0;
                p.limit = parseInt(this.pageSize);    
            }
            this.getStore().load({
                params: p,
                callback: function() {
                    this._storeLoaded = true;
                    this.setValue.apply(this, args);
                    
                    // set original value to new after loading
                    // and prevent changing value for Ext.BasicForm.isDirty()
                    if (this.trackResetOnLoad) {
                        this.originalValue = this.getValue();
                    }
                    
                    this.fireEvent('ready', this, this.getValue());
                },
                scope: this 
            });
            return this;
        }
        if (null === v) {
            v = '';
        }
        if (this.displayRawField) {
            var text = v;
            if (this.valueField) {
                var r = this.findRecord(this.valueField, v);
                if (r) {
                	var tpl = new Ext.XTemplate(this.getDisplayField(), 
                        this.displayRawFieldOptions || {});
                    text = tpl.apply(r.data);
                } else if (this.valueNotFoundText !== undefined) {
                    text = this.valueNotFoundText;
                }
            }
            this.lastSelectionText = text;
            if (this.hiddenField) {
                this.hiddenField.value = v;
            }
            Ext.form.ComboBox.superclass.setValue.call(this, text);
            this.value = v;
        } else {
        	xlib.form.ComboBox.superclass.setValue.call(this, v);
        }
        return this;
    },
    
    getDisplayField: function() {
        return this.displayRawField || ('{' + this.displayField + '}');
    },
    
    /**
     * Get value, displayed in combo
     * If not setted valueField or displayField - return combo value 
     */
    getDisplayValue: function() {
        if (this.valueField && this.displayField) {
            var mxcol = this.getStore().query(this.valueField, this.getValue());
            if (mxcol && mxcol.getCount() > 0) {
                return mxcol.first().get(this.displayField);
            }
        }
        return this.getValue();
    },
    
    // is combo already loaded?
    isLoaded: function() {
        return this._storeLoaded;
    },
	
    insertBlankOption: function() {
    	var fields = [];
        var names = {};
        if (this.getStore()) {
            this.getStore().fields.each(function(i) {
                fields.push({
                    name: i.name,
                    dateFormat: i.dateFormat,
                    defaultValue: i.defaultValue,
                    mapping: i.mapping,
                    sortDir: i.sortDir,
                    type: i.type
                });
                names[i.name] = '';
            });
        }
        var rf = Ext.data.Record.create(fields);
        var record = new rf(this.blankOptionData || names);
        switch(this.blankOptionPosition) {
            case 'last':
                if (this.getStore()) {
                    this.getStore().add([record]);
                }
                break;
            case 'first':
            default:
                if (this.getStore()) {
                    this.getStore().insert(0, [record]);
                }
        }
        return this;
    },
    
    getParams: function(q) {
        var p = xlib.form.ComboBox.superclass.getParams.apply(this, arguments);
		if (q) {
            p['filter[0][field]'] = this.displayField;
            p['filter[0][data][type]'] = 'string';
        }
        return p;
    },
    
    doSelectFirst: function() {
        if (!this.selectFirst) {
            return;
        }
        var record = this.getStore().getAt(0);
        if (record) {
            this.setValue(record.get(this.valueField));
            this.fireEvent('select', this, record, 0);
        } else{
            this.setValue(null);
        }
    }
});

Ext.reg('xlib.form.combobox', xlib.form.ComboBox);