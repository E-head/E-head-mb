Ext.ns('xlib.form');

xlib.form.ComboTrigger = Ext.extend(xlib.form.ComboBox, {
    
    validationEvent: false,
    
    validateOnBlur: false,
    
    trigger1Class: '',
    
    trigger2Class: 'add',
    
    width: 180,
    
    hasSearch: false,
    
    paramName: 'query',

    triggerAction: 'all',
	
	triggerQtip: null,
	
	disabledClass: 'x-item-field-disabled',
	
	/**
	 * The collection of triggers
	 * <code>
	 * [{
	 *     cls: 'add',
	 *     name: 'btn1',
	 *     permissions: true,
	 *     qtip: 'but1',
	 *     handler: function (e, node) {
	 *         alert('but1');
	 *     }
	 * }, {
	 *     ...
	 * }]
	 * </code>
	 */
	triggers: null,
    
    initComponent: function() {
		xlib.form.ComboTrigger.superclass.initComponent.apply(this, arguments);
        var myCn = [{
            tag: "img", 
			src: Ext.BLANK_IMAGE_URL, 
			cls: "x-form-trigger " + this.trigger1Class
		}];
        this.triggerId = Ext.id();
        if (this.triggers) {
			var ind = 1; 
			if (!Ext.isArray(this.triggers)) {
            	throw 'Property triggers should be an array!'; 
            }
        	Ext.each(this.triggers, function (trigger) {
				if (!trigger.permissions) {
					return;
				}
				ind++;
				var fn = trigger.handler || Ext.emptyFn;
				if (trigger.scope) {
					fn.createDelegate(trigger.scope);
				}
                this['onTrigger' + ind + 'Click'] =  fn; 
        		myCn.push({
        			id: this.triggerId + '-empty-' + (trigger.name || ''), 
                    tag: "img",
                    src: Ext.BLANK_IMAGE_URL,
                    style: 'width: 2px;' + (trigger.hidden ? 'display: none;' : '')
                });
                var p = {
                    id: this.triggerId + '-img-' + (trigger.name || ''),
                    tag: "img",
                    src: Ext.BLANK_IMAGE_URL,
                    cls: "x-form-trigger no-border-bottom x-form-trigger " + (trigger.iconCls || trigger.cls || this.trigger2Class),
					style: (trigger.hidden ? 'display: none;' : '')
                }
				if (trigger.qtip) {
                	p.qtip = trigger.qtip;
                }
                myCn.push(p);
        	}, this);
        } else {
    		if (this.permissions) {
    			myCn.push({
    				id: this.triggerId + '-empty',
    				tag: "img",
    				src: Ext.BLANK_IMAGE_URL,
    				style: 'width: 2px;'
    			});
				var p = {
                    id: this.triggerId + '-img',
                    tag: "img",
                    src: Ext.BLANK_IMAGE_URL,
                    cls: "x-form-trigger no-border-bottom " + this.trigger2Class
                } 
			    if (this.triggerQtip) {
                    p.qtip = this.triggerQtip;
                }
                myCn.push(p);
    		}
        }
		this.triggerConfig = {
            tag:'span', cls:'x-form-twin-triggers', cn: myCn
		};
        this.onTrigger1Click = this.onTriggerClick.createDelegate(this);
    },
    
    showTriggerItem: function(name) {
    	if (this.triggers) {
            Ext.getDom(this.triggerId + '-empty-' + name).style.display = 'inline';
            Ext.getDom(this.triggerId + '-img-' + name).style.display = 'inline';
        } else {
            Ext.getDom(this.triggerId + '-empty').style.display = 'inline';
            Ext.getDom(this.triggerId + '-img').style.display = 'inline';
        }
    },
    
    hideTriggerItem: function(name) {
    	if (this.triggers) {
    		//var name = arguments[0];
            Ext.getDom(this.triggerId + '-empty-' + name).style.display = 'none';
            Ext.getDom(this.triggerId + '-img-' + name).style.display = 'none';
    	} else {
        	Ext.getDom(this.triggerId + '-empty').style.display = 'none';
            Ext.getDom(this.triggerId + '-img').style.display = 'none';
    	}
    },
    
    // use TwinTriggerField method    
    initTrigger: function() {
        Ext.form.TwinTriggerField.prototype.initTrigger.apply(this, arguments);
    },

    // private
    onTrigger2Click: function(e, node) {
        if (this.disabled) {
            return;
        }
        this.fireEvent('customtriggerclick', this, node, e);
        this.onCustomTriggerClick(this, node, e);
    },
    onCustomTriggerClick: Ext.emptyFn
});

Ext.reg('xlib.form.combotrigger', xlib.form.ComboTrigger);