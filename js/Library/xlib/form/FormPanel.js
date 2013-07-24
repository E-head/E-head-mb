Ext.ns('xlib.form');

xlib.form.FormPanel = Ext.extend(Ext.form.FormPanel, {

    autoScroll: true,
    
    bodyBorder: false,
    
    waitMsgTarget: true,
    
    labelWidth: 120,
    
    monitorResize: true,
    
    border: false,
    
    buttonAlign: 'right',
    
	padding: '5px',
	
	markFieldsDirty: false,
	
	dirtyFieldClass: 'x-form-field-dirty',

    permissions: false,
    
	isReadyFlag: false,
	
    defaultAnchor: '-1',
    
    defaultAllowBlank: false,
    
    initComponent: function() {
        
        this.defaults = Ext.applyIf(this.defaults || {}, {
            allowBlank: this.defaultAllowBlank,
            anchor: this.defaultAnchor
        });
        
        xlib.form.FormPanel.superclass.initComponent.apply(this, arguments);
        
        /**
         * Collect not checked checkboxes and set the "zero" value
         * 
         * @param {Ext.form.FormPanel} f 
         * @param {Ext.form.Action} action 
         */
        this.on({
            beforeaction: function(form, action) {
                if ('submit' == action.type) {
                    var params = this.prepareCheckboxes();
                    this.setParams(params);
                }
            },
            scope: this
        });
        
        Ext.applyIf(this, {
            errorReader: new Ext.data.JsonReader({
                root: 'errors',
                successProperty: 'success'
            }, ['id', 'msg']),
			bodyStyle: {
				padding: this.padding
			}
        });
    },
    
    createForm: function() {
        var config = Ext.applyIf({listeners: {}}, this.initialConfig);
        return new Ext.form.BasicForm(null, config);
    },
    
    /**
     * Initialize fields
     * Check permission and disabled if not
     * 
     * @private
     */
    initFields: function() {
        xlib.form.FormPanel.superclass.initFields.apply(this, arguments);
        this.setPermissions(this.permissions);
        this.initChangeEvent();
        this.fireEvent.defer(1, this, ['ready', this]);
		this.isReadyFlag = true;
    }, 
    
	isReady: function() {
		return this.isReadyFlag;
	},
	
    /**
     * Added new param to baseParams
     * This params will be added to post|get params in AJAX request
     * If param exist in baseParams it will be overwrited
     *
     * @param {Object} v
     * return {xlib.form.FormPanel}
     */
    setParams: function(v) {
        if (!this.getForm().baseParams) {
            this.getForm().baseParams = {};
        }
        
        for (var i in v) {
            this.getForm().baseParams[i] = v[i];
        }
        return this;
    },
    
    /**
     * Set permissions to form
     * 
     * @param {Boolean} flag
     * @return {xlib.form.FormPanel}
     */
    setPermissions: function(flag) {
        this.getForm().items.each(function(item) {
            
            if (flag) {
                if ('undefined' != typeof item.permissions) {
                    item.setDisabled(false);
                }
            } else {
                if (!item.disabled) {
                    item.permissions = true;
                    item.setDisabled(true);
                }
            }

        }, this);
        
        this.permissions = flag;
        return this;
    },
    
    /**
     * Retrieve the form permissions
     * 
     * @return {Boolean}
     */
    getPermissions: function() {
        return true == this.permissions;
    },
    
    /**
     * add params to body style
     * if params exists it will be overwritten
     *
     * @param {Object} v key => value
     * 
     * return {xlib.form.FormPanel}
     */
    setBodyStyle: function(v) {
        for(var i in v) {
            this.bodyStyle[i] = v[i];
        }
        
        return this;
    },
    
    collectValues: function() {
        var params = this.getForm().getValues();
        Ext.applyIf(params || {}, this.prepareCheckboxes());
        return params;
    },

    prepareCheckboxes: function() {
        var ch = this.findByType('checkbox');
        var params = {};
        for (var i = 0; i < ch.length; i++) {
            if (!ch[i].getValue()) {
                params[ch[i].getName()] = 0;
            }
        }

        return params;
    },
    
    /**
     * Init events to present event change in FormPanel when one of fields changed
     * @return void
     */
    initChangeEvent: function() {
        this.getForm().items.each(function(f) {
            f.on('change', function(field) {
                if (this.markFieldsDirty) {
                	var el = field.getEl();
                    if(field.isDirty()) {
                        el.addClass(this.dirtyFieldClass);
                    } else {
                    	el.removeClass(this.dirtyFieldClass);
                    }
                }
                this.fireEvent('change', this, field);
            }, this);
        }, this);
    },
	
	/**
     * mark field dirty 
     * @return void 
     */
	markFieldDirty: function (fieldName) {
		var field = this.getForm().findField(fieldName);
		if (!field) {
			throw 'Uncorrect field name! Field could not be found!';
		}
		field.getEl().addClass(this.dirtyFieldClass);
	},
	
	/**
     * clear field dirty 
     * @return void 
     */
	clearFieldDirty: function (fieldName) {
        var field = this.getForm().findField(fieldName);
        if (!field) {
            throw 'Uncorrect field name! Field could not be found!';
        }
        field.getEl().removeClass(this.dirtyFieldClass);
    },
    
    /**
     * Reset form and clear dirty visual marks
     * @return void 
     */
    reset: function() {
    	this.getForm().reset();
    	this.clearDirty();
    	this.fireEvent('reset', this);
    },
    
    /**
     * Clear yellow background in all form fields 
     * @return void
     */
    clearDirty: function() {
        this.getForm().items.each(function(f) {
            f.getEl().removeClass(this.dirtyFieldClass);
        }, this);
    },
	
	/**
	 * hide field with label
	 * @param {Object} field
	 * @param {bool} disable -> to disable validation
	 * @return void
	 */
	hideField: function(field, disable){
		if (field.isFormField) {
			if (false !== disable) {
				field.disable();// for validation
			}
			field.hide();
			field.getEl().up('.x-form-item').setDisplayed(false); // hide label
		}
	},
	
	/**
	 * show field with label
	 * @param {Object} field
	 * @return void
	 */
	showField: function(field){
		if (field.isFormField) {
			field.enable();// for validation
			field.show();
			field.getEl().up('.x-form-item').setDisplayed(true); // hide label
		}
	},
	
	/**
	 * set visible for field
	 * @param {Object} field
	 * @param {bool} visible
	 * @return void
	 */
	setFieldVisible: function(field, visible){
		if(visible){
			this.showField(field);
		} else {
			this.hideField(field);
		}
	}
});