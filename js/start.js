Ext.onReady(function(){

    Ext.BLANK_IMAGE_URL = '/js/Library/extjs/framework/resources/images/default/s.gif';
    Ext.enableListenerCollection = true;
    Ext.QuickTips.init();
    
    Ext.form.DateField.prototype.format = 'd-m-Y';
    Ext.DatePicker.prototype.startDay = 1;
    
    Ext.menu.RangeMenu.prototype.icons = {
        gt: '/js/Library/extjs/plugins/grid/GridFiltering/img/greater_then.png', 
        lt: '/js/Library/extjs/plugins/grid/GridFiltering/img/less_then.png',
        eq: '/js/Library/extjs/plugins/grid/GridFiltering/img/equals.png',
        neq: '/js/Library/extjs/plugins/grid/GridFiltering/img/not_equals.png'
    };
    Ext.grid.filter.StringFilter.prototype.icon = '/js/Library/extjs/plugins/grid/GridFiltering/img/find.png';
    
    // disable stateful
    Ext.Window.prototype.stateful = false;
    Ext.form.Field.prototype.stateful = false;
    Ext.form.FieldSet.prototype.stateful = false;
    
    Ext.ns('xlib.date');
    xlib.date.TIME_FORMAT = 'H:i';
    xlib.date.DATE_FORMAT = 'd-m-Y';
    xlib.date.DATE_WEEK_FORMAT = 'd-m-Y, l';
    xlib.date.DATE_TIME_WITHOUT_SECONDS_FORMAT = 'd-m-Y H:i';
    xlib.date.DATE_TIME_FORMAT = 'd-m-Y H:i:s';
    xlib.date.DATE_TIME_FORMAT_SERVER = 'Y-m-d H:i:s';
    xlib.date.DATE_FORMAT_SERVER = 'Y-m-d';
   
    // FIX for IE9
    if ((typeof Range !== "undefined") && !Range.prototype.createContextualFragment) {
        Range.prototype.createContextualFragment = function(html) {
            var frag = document.createDocumentFragment(),
            div = document.createElement("div");
            frag.appendChild(div);
            div.outerHTML = html;
            return frag;
        };
    }
    
    Ext.Ajax.on({
        requestexception: function(conn, response, options) {
            switch (response.status) {
                case 401:
                    alert("Авторизация истекла! \n Вы будете перенаправлены на страницу входа.");
                    window.location.reload();
                    break;
                    
                case 404:
                    xlib.Msg.error('Страница не найдена');
                    break;
                    
                case 405:
                
                    var t = ['<b>Нет прав!</b>'];
                    
                    var result = xlib.decode(response.responseText);
                    if (Ext.isArray(result.trace) && result.trace.length > 0) {

                        t = t.concat(['<table style="padding-top: 20px">', '<tr>', '<td colspan="2"><b>Запрашиваемый ресурс:</b></td></tr>']);
                        
                        Ext.each(result.trace, function(i) {
                            t.push('<tr><td><b>Ресурс:</b> <i>' + i.resource.join('->') + '</i></td></tr>');
                            t.push('<tr><td><b>Привилегия:</b> <i>' + i.privilege + '</i></td></tr>');
                        }, this);
                        
                        t.push('</table>');
                    }
                        
                    xlib.Msg.error(t.join(''));
                    break;
                    
                case 500:
                	xlib.Msg.error('Ошибка сервера');
                    break;
            }
        }  
    });
    
    xlib.LoadingMask.hide();
    xlib.applicationInitialization(function() {
        
    	Ext.ns('PMS.System');

        PMS.System.Layout = new xlib.Layout.Workspace({
    		mainMenu: PMS.Menu({
                username:  xlib.username || '', 
                rolename:  xlib.rolename || '', 
                roleId:    xlib.roleId || '',
                enableMap: xlib.enableMap || false
            })
        });

        PMS.OrdersTab = PMS.System.Layout.getTabPanel().add({
            iconCls: 'orders-icon',
            closable: false,
            xtype: 'PMS.Orderslog.List',
            id: 'PMS.Orderslog.List'
        });
        
        /*
        PMS.System.Layout.getTabPanel().add({
            iconCls: 'work_schd-icon',
            xtype: 'PMS.Sales.Goods.List',
            id: 'PMS.Sales.Goods.List'
        });
        */
        
        PMS.System.Layout.doLayout();
        
        if (Ext.isArray(xlib.messages) && xlib.messages.length > 0) {
            Ext.each(xlib.messages, function(item) {
                new PMS.Notice.Reader().show().loadData(item);
            });
        }
    });
});