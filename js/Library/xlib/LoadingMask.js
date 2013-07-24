if (typeof xlib == 'undefined') {
	xlib = {};
} 

/**
 * Simple loading mask
 * Display modal window on top
 * This function is singleton
 * You can't create this directly
 * Simple examle:
 * <code>
 * xlib.LoadingMask
 * </code>
 */
xlib.LoadingMask = function() {
    
    // signalize that loading mask container is already rendered
    var rendered = false;
    
    // loading mask container
    var container = null;
    
    // msg container
    var msg = null;
    
    // get document.body
    var body = document.getElementsByTagName('BODY')[0];

    // initialization of container
    if (false == rendered) {
    
        container = document.createElement('DIV');
        container.id = '-loading-mask';
        
        var loading = document.createElement('DIV');
        loading.id = 'xlib-loading';
        
        var indicator = document.createElement('DIV');
        indicator.className = 'xlib-loading-indicator';
        
        var text = document.createElement('DIV');
        text.className = 'xlib-loading-indicator-text';
        text.innerHTML = 'загрузка';
        
        // create container add add additional subcontainers        
        indicator.appendChild(text);
        loading.appendChild(indicator);
        container.appendChild(loading);
        container.style.display = 'none';
        body.appendChild(container);
        rendered = true;
    }
    
    return {
        
        // show mask at specified position 
        showAt: function(text, topOffset, leftOffset) {
        	if (topOffset) {
        	   loading.style.top = topOffset;
        	}
        	if (leftOffset) {
        	   loading.style.left = leftOffset;
        	}
            this.show(text);
        },
        
        // show mask
        show: function(text) {
            container.style.display = 'block';
        },
        
        // hide mask
        hide: function() {
            container.style.display = 'none';
        },
        
        // destroy mask
        destroy: function () {
            if (rendered) {
                body.removeChild(container);
                rendered = false;
            }            
        }
    };
}();