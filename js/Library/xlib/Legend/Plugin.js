Ext.ns('xlib.Legend');

xlib.Legend.Plugin = function(cfg) {
    Ext.apply(this, cfg || {});
    xlib.Legend.Plugin.superclass.constructor.call(this);    
};

Ext.extend(xlib.Legend.Plugin, Ext.util.Observable, {
	
	items: [],

	ancestor: null,
	
	strategy: '',
    
    baseCls: 'xlib-legend-',
    
    cls: '',
    
	init: function(a) {
	
		this.ancestor = a;
		this.strategy = this.strategy || a.getXType();
		
		switch(this.strategy) {
		   case 'window':
		       this.baseCls += 'window';
               a.on('render', this.onRenderHeaderStrategy, this);
		       break;
		   case 'panel':
		       this.baseCls += 'panel';
		       a.on('render', this.onRenderHeaderStrategy, this);
		       break;
		       
           case 'header':
               this.baseCls += 'header';
               a.on('render', this.onRenderHeaderStrategy, this);
		       break;
		        
		   case 'toolbar':		// special for Ext.Toolbar
		       this.baseCls += 'toolbar';
		       a.afterRender = a.afterRender.createSequence(this.onRenderToolbarStrategy, this);
		       break;
			   
		   default:
		       throw 'The strategy `' + this.strategy + '` is not supported.';
		}
	},
	
	onRenderHeaderStrategy: function() {

		var header = this.ancestor.header;

		var tt = new Ext.Template([
			'<ul class="xlib-legend {cls}">',
				'<li><div class="rectangle" style="background-color: {color}">&nbsp;</div></li>',
				'<li class="text" ext:qtip="{qtip}">{text}</li>',
			'</ul>'
        ].join('')).compile();
		
		var ct = document.createElement('div');
		ct.id = Ext.id();
		ct.className = 'xlib-legend-container';
		
		var el = Ext.fly(ct);
		el.insertBefore(header.child('span'));
		
		for(var i = this.items.length - 1; 0 <= i; i--) {
		    this.items[i].cls = this.baseCls;
			tt.append(el, this.items[i]);
		}
	},
	
	getHtml: function() {

		var tt = new Ext.Template([
			'<ul class="xlib-legend {cls}">',
				'<li><div class="rectangle" style="background-color: {color}">&nbsp;</div></li>',
				'<li class="text" ext:qtip="{qtip}">{text}</li>',
			'</ul><br>'
        ].join('')).compile();
		
		var html = '';
		for(var i = 0; i < this.items.length; i++) {
		    this.items[i].cls = this.baseCls;
			html += tt.apply(this.items[i]);
		}
		return '<br>' + html;
	},
	
	onRenderToolbarStrategy: function() {
		for(var i = 0; i < this.items.length; i++) {
			this.items[i].xtype = 'xlib.legend.item';
			this.ancestor.add(this.items[i]);
		}
	}
});