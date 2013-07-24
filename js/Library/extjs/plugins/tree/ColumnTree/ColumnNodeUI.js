Ext.tree.ColumnTree = Ext.extend(Ext.tree.TreePanel, {
    lines:true,
    borderWidth: Ext.isBorderBox ? 0 : 2, // the combined left/right border for each cell
    cls:'x-column-tree',
    border: true,
    bodyBorder: true,
    defaultColumnWidth: 100,
    scrollOffset: 20,
    sortable: false,
	autoScroll: true,
	ddScroll: true,
	
    initComponent: function() {
        Ext.applyIf(this, {
            tbar: new Ext.Panel({
                border: false,
                height: 0
            })
        });
        
        Ext.tree.ColumnTree.superclass.initComponent.apply(this, arguments);
    },
    
    onRender : function(){
        Ext.tree.ColumnTree.superclass.onRender.apply(this, arguments);
        this.headers = this.tbar.createChild(
            {cls:'x-tree-headers'}, this.tbar.el);

        var cols = this.columns, c;
        var totalWidth = 0;

        for(var i = 0, len = cols.length; i < len; i++){
             c = cols[i];
             totalWidth += c.width || 0;
             this.headers.createChild({
                 cls:'x-tree-hd ' + (c.cls ? c.cls+'-hd' : ''),
                 cn: {
                     cls:'x-tree-hd-text',
                     html: c.header
                 }
             });
        }
        this.headers.createChild({cls:'x-clear'});
        // prevent floats from wrapping when clipped
        
        this.headers.setWidth(totalWidth);
        this.innerCt.setWidth(totalWidth);
		this.getTreeEl().setStyle('overflow', 'auto');
    },
    
    onResize: function(w, h) {
        
        Ext.tree.ColumnTree.superclass.onResize.apply(this, arguments);
        
        if (typeof w == 'undefined') {
            return;
        }
        
        var bw = Ext.isBorderBox ? 0 : 2;
        var cols = this.columns;
        var widths = [];
        var autoExpandColumnIndex = -1;
        
        for (var i = 0; i < cols.length; i++) {
            if (cols[i].dataIndex == this.autoExpandColumn) {
                autoExpandColumnIndex = i;
                widths.push(0);    
            } else if (null == cols[i].width) {
                widths.push(this.defaultColumnWidth);
            } else if (0 > cols[i].width) {
                widths.push(cols[i].width * w);
            } else {
                widths.push(cols[i].width);
            }
        }
        
        var sum = 0;
        for (var i = 0, len = widths.length; i < len; i++) {
            sum += widths[i];
        }
        
        if (-1 !== autoExpandColumnIndex) {
            widths[autoExpandColumnIndex] = w - sum - this.scrollOffset;
        }

        var totalWidth = 0;
        for (var j = 0, len = widths.length; j < len; j++) {
            totalWidth += widths[j];
            Ext.select("div.x-tree-hd:nth-child(" + (j + 1) + ")", false, "*TreeID*").setWidth(widths[j] - bw);
            Ext.select("div.x-tree-col:nth-child(" + (j + 1) + ")", false, "*TreeID*").setWidth(widths[j] - bw);
            this.columns[j].width = widths[j];
        }
        this.headers.setWidth(totalWidth + this.scrollOffset);
        this.innerCt.setWidth(totalWidth);
    },
	
	/**
     * Check if exists node in parent node with same name
     * 
     * @param {String} nodeName
     * @param {Ext.tree.TreeNode} parentNode
     */
    isNodeExists: function(nodeName, parentNode) {
		if (!parentNode.firstChild) {
			return false;
		}
		
	    var exists = parentNode.findChildBy(function(node) {
			return node.getUI().textNode.innerHTML == nodeName;
		}, this);
		return null !== exists;
    }
});

Ext.tree.ColumnNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
    focus: Ext.emptyFn, // prevent odd scrolling behavior
    onDblClick: Ext.emptyFn,
    renderElements : function(n, a, targetNode, bulkRender){
        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';
        var t = n.getOwnerTree();
        var cols = t.columns;
        var bw = t.borderWidth;
        var c = cols[0];

        var buf = [
             '<li class="x-tree-node"><div ext:tree-node-id="',n.id,'" class="x-tree-node-el x-tree-node-leaf ', a.cls,'">',
                '<div class="x-tree-col" style="width:',c.width-bw,'px;">',
                    '<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
                    '<img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow">',
                    '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on">',
                    '<a hidefocus="on" class="x-tree-node-anchor" href="',a.href ? a.href : "#",'" tabIndex="1" ',
                    a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '>',
                    '<span unselectable="on">', n.text || (c.renderer ? c.renderer(a[c.dataIndex], n, a) : a[c.dataIndex]),"</span></a>",
                "</div>"
         ];

         for(var i = 1, len = cols.length; i < len; i++) {
             c = cols[i];
             buf.push(
                '<div class="x-tree-col ', (c.cls ? c.cls : '' ), '" style="width:', c.width - bw, 'px;">',
                    '<div class="x-tree-col-text">',
                    (c.renderer ? c.renderer(a[c.dataIndex], n, a) : a[c.dataIndex]),
                    "</div>",
                "</div>"
            );
        }

        buf.push(
            '<div class="x-clear"></div></div>',
            '<ul class="x-tree-node-ct" style="display:none;"></ul>',
            "</li>");

        if(bulkRender !== true && n.nextSibling && n.nextSibling.ui.getEl()){
            this.wrap = Ext.DomHelper.insertHtml("beforeBegin",
                                n.nextSibling.ui.getEl(), buf.join(""));
        }else{
            this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf.join(""));
        }

        this.elNode = this.wrap.childNodes[0];
        this.ctNode = this.wrap.childNodes[1];
        var cs = this.elNode.firstChild.childNodes;
        this.indentNode = cs[0];
        this.ecNode = cs[1];
        this.iconNode = cs[2];
        this.anchor = cs[3];
        this.textNode = cs[3].firstChild;
        this.columns = this.elNode.childNodes;
        this.node.fireEvent('renderelements', this);
    }
});