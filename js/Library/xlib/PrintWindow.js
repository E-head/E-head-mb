Ext.namespace('xlib');

xlib.PrintWindow = Ext.extend(Ext.Window, {

    modal: true,
    
    maximizable: true,
    
    width: 700,
    
    height: 550,
    
    title: 'Preview',
    
    iconCls: 'print',
    
    closeText: 'Close'),
    
    printText: 'Print',
    
    localHtml: null,
    
    src: null,
    
    initComponent: function() {
    	
    	this._iframeId = Ext.id();
    	
    	this.html = {
    		id : this._iframeId,
    		name : this._iframeId,
    		tag : 'iframe',
    		style: {
    			width : '100%',
                height : '100%',
                background: '#ffffff'
    		},
            width : '100%',
            height : '100%',
    		frameborder : '0'
    	}
    	
    	if (this.src) {
    		this.html.src = this.src; 
    	}
    	
    	if (!this.buttons) {
        	this.buttons = [{
    			text : this.printText,
    			handler : this.print,
    			iconCls: 'print',
    			scope : this
    		}, {
                text : this.closeText,
                handler : this.close,
                scope : this
            }];
    	}
    
    	xlib.PrintWindow.superclass.initComponent.call(this);
    	
    	if (this.localHtml) {
    		this.loadLocalData(this.localHtml);
    	}
    },
    
    show: function(animateTarget, cb, scope) {
    	xlib.PrintWindow.superclass.show.call(this, animateTarget, cb, scope);
    	this.tools.restore.hide();
    },
    
    print: function() {
    	var iframe_window = this.getIframeWindow();
    	if (Ext.isIE) {
    		iframe_window.focus();
    	}
    	iframe_window.print();
    },
    
    getIframe : function() {
    	return Ext.get(this._iframeId);
    },
    
    getIframeWindow : function() {
    	return this.getIframe().dom.contentWindow;
    },
    
    loadLocalData: function(html, title) {
    	this.src = null;
    	this.localHtml = html;
    	if (this.rendered) {
    		this.updateIframeContent(html, "/css/print.css");
    	} else {
    		this.on('render', function() {
    			this.updateIframeContent(html, "/css/print.css");
    		}, this, {delay: 300})
    	}
    },
    
    loadRemoteData: function(src) {
    	this.src = src;
        this.localHtml = null;
    	this.getIframe().getEl().src = src;  
    },
    
    updateIframeContent: function(html, cssLink, css) {
    	var document = this.getIframeWindow().document;
        var head = document.getElementsByTagName('head')[0];
        var body = Ext.getDom(document.body);
        
        if (cssLink) {
            var link = document.createElement("link");
            link.setAttribute("rel", "stylesheet");
            link.setAttribute("type", "text/css");
            link.setAttribute("href", cssLink);
            head.appendChild(link);
        }
        
        if (css) {
        }
        
        if (html) {
        	body.innerHTML = html;
        }
    }
    
});