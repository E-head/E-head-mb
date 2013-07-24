Ext.ns('xlib.form');

xlib.form.PasswordField = Ext.extend(Ext.form.TextField, {

	inputType: 'password',
	
	enableKeyEvents: true,
	
    initComponent: function() {
		xlib.form.PasswordField.superclass.initComponent.apply(this, arguments);
        this.on('keydown', this.detectCaps, this);
        this.on('keypress', this.detectCaps, this);
        this.on('render', function() {
        	var elp = this.getErrorCt();
            if (!elp) { // field has no container el
                this.el.dom.title = msg;
                return;
            }
            this.warningIcon = elp.createChild({cls:'x-form-warning-icon'});
        }, this);
    },
	
    oncapslock: function(state) {
        var msg = 'Caps Lock activated';
        this.warningIcon.alignTo(this.el, 'tl-tr', [2, 0]);
        this.warningIcon.dom.qtip = msg;
        this.warningIcon.dom.qclass = 'x-form-invalid-tip';
        this.warningIcon.setVisible(state);
        
    },
    
    detectCaps: function(f, event) {
        var e = event.browserEvent;
        var n = e.keyCode ? e.keyCode : e.charCode;
        if (e.type == "keypress") {
            var c = String.fromCharCode(n);
            var cUC = c.toUpperCase();
            var cLC = c.toLowerCase();
            if (cUC != cLC) {
                f.oncapslock((e.shiftKey && cLC == c) || (!e.shiftKey && cUC == c));
            } 
        } else if (e.type == "keydown" && n == event.CAPS_LOCK) {
            f.oncapslock(false);
        }
    }
    
});

Ext.reg('xlib.form.passwordfield', xlib.form.PasswordField);