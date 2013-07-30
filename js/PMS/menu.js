Ext.ns('PMS');

PMS.menuMessage = function() {
    xlib.Msg.info('Модуль на стадии разработки'); 
}

PMS.Menu = function(params) {
    
    params = params || {};
    
	var username  = params.username || '';
	var rolename  = params.rolename || '';
	var roleId    = parseInt(params.roleId);
    var enableMap = params.enableMap || false; 
    
	return [{
	    xtype: 'box',
	    autoEl: {
	        tag: 'div',
	        style: 'cursor: pointer;',
	        qtip: 'e-head.ru',
	        cls: 'e-head-logo'
	    },
	    listeners: {
	        render: function(box) {
	            box.el.on('click', function() {
	                window.open('http://e-head.ru/');
	            })
	        }
	    }
	}, ' ', ' ', ' ', ' ', ' ', {
	    xtype: 'box',
	    autoEl: {
	        tag: 'div',
	        style: 'cursor: pointer;',
	        qtip: 'mexicanburger.ru',
	        cls: 'mb-logo'
	    },
	    listeners: {
	        render: function(box) {
	            box.el.on('click', function() {
	                window.open('http://mexicanburger.ru');
	            })
	        }
	    }
	}, ' ', ' ', '-', ' ', ' ', {
        text: 'Заказы',
        iconCls: 'orders-icon',
        handler: function() {
            PMS.System.Layout.getTabPanel().add({
                iconCls: 'orders-icon',
                xtype: 'PMS.Orders.List',
                id: 'PMS.Orders.List'
            });
        }
    }, {
        text: 'База товаров',
        hidden: !acl.isView('admin'),
        iconCls: 'work_schd-icon',
        handler: function() {
            PMS.System.Layout.getTabPanel().add({
                iconCls: 'work_schd-icon',
                xtype: 'PMS.Goods.List',
                id: 'PMS.Goods.List'
            });
        }
    }, '->', {
		text: 'Менеджер доступа',
		iconCls: 'accounts_manager-icon',
		hidden: !acl.isView('admin'),
		handler: function() {
			PMS.System.Layout.getTabPanel().add({
				iconCls: 'accounts_manager-icon',
				xtype: 'xlib.acl.layout',
				id: 'xlib.acl.layout'
			});
		}
	}, new Ext.Toolbar.Button({
        text: 'Выход',
        tooltip: username + ' (' + rolename + ')',
        iconCls: 'exit-icon',
        handler: function() {
            window.location.href = '/index/logout';
        }
    })];
}