Ext.ns('xlib');

xlib.TreePanel = Ext.extend(Ext.tree.TreePanel, {
    
    /**
     * Panel is scrollable
     */
    autoScroll: true,
    
    /**
     * Register container in scroll manager
     */
    containerScroll: true,
    
    /**
     * Disabled animation 
     * Some bugs in Opera
     */
    animate: Ext.isOpera ? false : true,
    
    /**
     * Set unique name on rename|move|remove|add
     */
    uniqueNodes: true,
    
    /**
     * Select first node
     */
    firstNodeSelected: false,
    
    /**
     * Preload the children on move node
     * Using for example if set options uniqueNodes = true then must check if in
     * node children all nodes are unique
     */
    preloadOnAppend: true,
    
    ddGroup: 'DDGROUP',
    
    enableDD: true,
    
    untitledNodeName: 'untitled',
    
    border: false,
    
    sortable: true,
    
    allowRename: true,
    
    initComponent: function() {

        if (this.sortable) {
            this.treeSorter = new Ext.tree.TreeSorter(this, {
                folderSort: true
            });
        }
        
        if (this.allowRename) {
            Ext.apply(this, {
                treeEditor: new Ext.tree.TreeEditor(this, {
                    allowBlank: false,
                    revertInvalid: true
                })
            });
            
            // disable rename on click
            this.treeEditor.beforeNodeClick = function(node,e){return true;};
            this.treeEditor.on({
                beforestartedit:    {scope: this, fn: this.onBeforeStartEdit},
                beforecomplete:     {scope: this, fn: this.onBeforeComplete}
            });
        }
        
        xlib.TreePanel.superclass.initComponent.call(this);
        
        this.on({
            beforenodedrop:     {scope: this, fn: this.onBeforeNodeDrop},
            beforemovenode:     {scope: this, fn: this.onBeforeMoveNode},
            click:              {scope: this, fn: this.clickProcess},
            contextmenu:        {scope: this, fn: this.onContextMenu},
            scope: this
        });
        
        if (true === this.firstNodeSelected) {
            this.on('load', function(node) {
                if (this.isRoot(node)) {
                    var f = function(node) {
                        this.fireEvent('firstnodeselected', node);
                        node.select();
                    };
                    
                    if (this.rootVisible) {
                        f.apply(this, [node]);
                        return;
                    }
                    
                    this.getRootNode().expand(false, this.animate, function() {
                        var firstChild = node.firstChild;
                        if (null !== firstChild) {
                            firstChild.ensureVisible(f.apply(this, [firstChild]));
                        }    
                    }.createDelegate(this));
                    
                }
            }, this);
        }
           
        this.addEvents(
            
            /**
             * Fires before add new template
             * @params e    drop event
             *      tree - The TreePanel
             *      target - The node being targeted for the drop
             *      data - The drag data from the drag source
             *      point - The point of the drop - append, above or below
             *      source - The drag source
             *      rawEvent - Raw mouse event
             *      dropNode - Drop node(s) provided by the source OR you can supply node(s) to be inserted by setting them on this object.
             *      cancel - Set this to true to cancel the drop.
             *      dropStatus - If the default drop action is cancelled but the drop is valid, setting this to true will prevent the animated "repair" from appearing.
             */
            'beforeaddcorenode',
            
            /**
             * Fires after add new template finished successfully
             * 
             * @params tree     The tree   
             * @params node     The tree node
             * 
             */
            'addcorenode',
            
            /**
             * Fires before a node is moved to a new location in the tree.
             *  
             * @params tree         Tree    - The node parent tree 
             * @params node         Node    - The node being moved
             * @params oldParent    Node    - The parent of the node
             * @params newParent    Node    - The new parent the node is moving to 
             * @params index        Number  - The index it is being moved to
             * 
             * @return   Bool               false to cancel the move.
             */
            'beforemovecorenode',
            
            /**
             * Fires before a node is moved to a new location in the tree but
             * the node with same text is exists
             *  
             * @params tree         Tree    - The node parent tree 
             * @params node         Node    - The node being moved
             * @params oldParent    Node    - The parent of the node
             * @params newParent    Node    - The new parent the node is moving to 
             * @params index        Number  - The index it is being moved to
             * 
             * @return   Bool               false to cancel the move.
             */
            'beforemovecorenodeexists',
            
            /**
             * Fires after node is moved to a new location in the tree. 
             *
             * @params tree         TreePanel   - The node parent tree
             * @params node         Node        - The node being moved
             * @params oldParent    Node        - The parent of the node
             * @params newParent    Node        - The new parent the node is moving to 
             * @params index        Number      - The index it is being moved to
             */
            'movecorenode',
            
            /**
             * @params tree         TreePanel   - The tree
             * @params node         Node        - The node being renamed
             * @params value        String      - The new value
             * @params startValue   String      - The start value
             * 
             * @return              false to cancel the rename
             */
            'beforerenamecorenode',
            
            /**
             * @params tree         Tree panel  - The tree
             * @params node         Node        - The renaming node
             * @params value        String      - The new value
             * @params startValue   String      - The start value
             */
            'renamecorenode',
            
            /**
             * @params tree         TreePanel   - The tree
             * @params node         Node        - The node being removed
             * 
             * @return              false to cancel remove
             */
            'beforeremovecorenode',
            
            /**
             * @params tree         TreePanel   - The tree
             * @params  node        Node        - The removed node
             */
            'removecorenode',
            
            /**
             * Fires then flag this.firstNodeSelected === true and first node selected
             * 
             * @param node          {Ext.tree.TreeNode} The tree node
             */
            'firstnodeselected'
        );
    },
    
    /**
     * Fires before editor is showed
     *  
     * @param {Ext.tree.TreeEditor} editor
     * @param {Ext.Element} boundEl
     * @param {String} value
     * 
     * @return false to cancel editor
     */
    onBeforeStartEdit: function(editor, boundEl, value) {
        if (editor.editNode.attributes.allowRename === false) {
            return false;
        }
    },
    
    onBeforeComplete: function(editor, value, startValue) {
        
        if (this.allowRename !== true) {
            return false;
        }
        
        // this condition prevent the recursion
        if (this.editCompleteInProgress === true) {
            this.editCompleteInProgress = false;
            return;
        }
        
        var node = editor.editNode;
        var parentNode = node == this.getRootNode() ? node : node.parentNode;
        
        if (this.uniqueNodes === true) {
            if (this.isNodeExists(editor.field.getRawValue(), parentNode) && value != startValue) {
                editor.field.markInvalid();
                return false;
            }
        }
        
        this.editCompleteInProgress = true;
        
        if (value == startValue || false === this.fireEvent('beforerenamecorenode', this, node, value, startValue)) {
            editor.cancelEdit();
            return false;
        }
        
        if (false === this.renameProcess(editor, node, value, startValue)
            || false === this.fireEvent('renamecorenode', this, node, value, startValue)) {
            editor.cancelEdit();
            return false;
        }
        
        this.editCompleteInProgress = false;
    },
    
    /**
     * Rename process function
     * Can be using when extend the grid
     * 
     * @param {Ext.tree.TreeEditor} editor
     * @param {Ext.tree.TreeNode} node
     * @param {String} value
     * @param {String} startValue
     */
    renameProcess: function(editor, node, value, startValue) 
    {},
    
    /**
     * Rename tree node
     * 
     * @param {Ext.tree.TreeNode} node
     * @param {String} text
     */
    revertRename: function(node, text) {
        node.setText(text);
    },
    
    /**
     * Check if exists node in parent node with same name
     * 
     * @param {String} nodeName
     * @param {Ext.tree.TreeNode} parentNode
     */
    isNodeExists: function(nodeName, parentNode) {
        return parentNode.findChild('text', nodeName) !== null;
    },
    
    // private
    onBeforeMoveNode: function(tree, node, oldParent, newParent, index) {
    	
        // dirty hack for prevent recursion    
        if (this.moveNodeInProgress === true) {
            return;
        }
        
        if (this.preloadOnAppend === true && !newParent.isExpanded()) {
            newParent.expand(false, false, function() {
                this.onBeforeMoveNode(tree, node, oldParent, newParent, index);
            }.createDelegate(this));
            return;
        }
        
        if (this.uniqueNodes === true) {
            if (this.isNodeExists(node.text, newParent)) {
                return this.onBeforeMoveCoreNodeExists(node, oldParent, newParent, index) === true;
            }
        }

        if (false === this.fireEvent('beforemovecorenode', this, node, oldParent, newParent, index)) {
            return false;
        }
        
        this.moveProcess(this, node, oldParent, newParent, index);
        return false;
    },
    
    onBeforeMoveCoreNodeExists: function() 
    {},
    
    moveProcess: function(tree, node, oldParent, newParent, index) 
    {},
    
    moveNode: function(node, parentNode) {
        // dirty hack for prevent recursion
        this.moveNodeInProgress = true;
        parentNode.appendChild(node);
        this.moveNodeInProgress = false;
        this.fireEvent('movecorenode', this, node, parentNode);
    },
    
    // private
    onBeforeNodeDrop: function(e) {
        if (e.source == this.addDragProxy) {
            e.dropStatus = true;
            this.beforeAddProcess(e);
        }
    },
    
    // private
    beforeAddProcess: function(e) {

        var node = e.target;
        var parentNode = node.isLeaf() ? node.parentNode : node;
        e._parentNode = parentNode;
        if (this.preloadOnAppend === true && !parentNode.isExpanded()) {
            parentNode.expand(false, false, function() {
                this.beforeAddProcess(e);
            }.createDelegate(this));
            return false;
        }

        if (false === this.fireEvent('beforeaddcorenode', this, e)) {
            return false;
        }
       
        if (false === this.addProcess(e)) {
            return false;        
        }
    },
    
    addProcess: function(e) 
    {},
    
    /**
     * Add new node to parent node
     * 
     * @param {Ext.tree.TreeNode} destinationNode
     * @param {Ext.tree.TreeNode} node
     */
    addNode: function(destinationNode, node) {

        if (destinationNode.isLeaf()) {
            destinationNode.parentNode.appendChild(node);
        } else {
            destinationNode.appendChild(node);
        }
        this.fireEvent('addcorenode', this, node, destinationNode);
    },
    
    /**
     * Generate node name
     * For retrieve unique node name for parent node 
     * must set param
     *  <code>this.uniqueNodes = true</code>
     * 
     * @param {Ext.tree.TreeNode} parentNode
     * @param {String}      prefix
     */
    generateNodeName: function(parentNode, prefix) {
        prefix = prefix || this.untitledNodeName;

        var name = prefix;
        if (this.uniqueNodes === true) {
			
            var i = 1;
            do {
                name = prefix + '_' + i;
            } while (this.isNodeExists(name, parentNode) === true && i++ < 20000);
        }
        return name;
    },
    
    clickProcess: function(node, e) {},
    
    /**
     *  
     * @param {Object} node
     * @private
     * @return Boolean      false if drop not success
     */
    beforeRemove: function(node) {
        if (false === this.fireEvent('beforeremovecorenode', this, node)) {
            return false;
        }
        this.removeProcess(node);
        return true;
    },
    
    removeProcess: function(node) 
    {},
    
    /**
     * Remove node from tree
     * 
     * @param {Ext.tree.TreeNode} node
     */
    removeNode: function(node) {
        node.parentNode.removeChild(node);
        this.fireEvent('removecorenode', this, node);
    },
    
    onContextMenu: function(node, e) {},
    
    /**
     * Get node location separated by "/"
     * 
     * @param {Core.Tree.TreeNode}  node         
     */
    getLocation: function(node, afterNode) {
        
        var path, p, a;
        var stack = [];
        
        // get path for non-root node
        if(node !== this.getRootNode() && node != afterNode) {
            p = node.parentNode;

            a = [node.text];
            stack.push(node);
            while(p && p !== this.getRootNode() && p != afterNode) {
                a.unshift(p.text);
                stack.unshift(p);
                p = p.parentNode;
            }
            a.unshift(this.getRootNode().attributes.path || '');
            path = a.join(this.pathSeparator);
        }

        // path for root node is it's path attribute
        else if(node.attributes) {
            path = node.attributes.path || '';
        }

        // a little bit of security: strip leading / or .
        // full path security checking has to be implemented on server
        path = path.replace(/^[\/\.]*/, '');
        return {
            path: path,
            stack: stack
        };
    },
    
    getStack: function(node) {
        return this.getLocation(node).stack;
    },
    
    getNodePath: function(node, afterNode) {
        return this.getLocation(node, afterNode).path;
    },
    
    getNodePathFrom: function(node) {
        return this.getNodePath(node, this.getStack(node).shift());
    },
	
	isRoot: function(node) {
		return node == this.getRootNode();
	}   
});