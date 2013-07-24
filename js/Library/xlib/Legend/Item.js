Ext.ns('xlib.Legend');

xlib.Legend.Item = Ext.extend(Ext.BoxComponent, {

    /**
     * Legend color item
     * @param {String}
     */
    color: 'transparent',
    
    /**
     * Legend text label
     * @param {String}
     */
    text: 'Legend text',
    
    /**
     * Qtip text
     * @param {String} 
     */
    qtip: '',
    
    cls: '',
    
	onRender: function(ct, position){
    	
    	var rectangle = {
            tag: 'div', 
            'class': 'rectangle', 
            html: '&#160;'
        };
        
        if (this.color) {
            rectangle.style = 'background-color: ' + this.color;
        }
        
        if (this.cls) {
            rectangle['class'] = this.cls;
        }
    	
    	this.el = ct.createChild({
    	    tag: 'ul',
    	    id: this.getId(),
            cls: 'xlib-legend',
            children: [
                {tag: 'li', children: [rectangle]},
                {tag: 'li', 'class': "text", html: this.text, 'ext:qtip': this.qtip || ""}
            ]
        }, position);
    	
    	xlib.Legend.Item.superclass.onRender.apply(this, arguments); 
	}
});

Ext.reg('xlib.legend.item', xlib.Legend.Item);