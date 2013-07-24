Ext.namespace('xlib.Msg');

xlib.Msg = {
    error: function(msg, fn, animEl){
        var s = Ext.Msg.show({
            title: 'Ошибка',
            animEl: animEl,
            msg: msg,
            buttons: Ext.MessageBox.OK,
            fn: fn || Ext.emptyFn,
            icon: Ext.Msg.ERROR
        });
    },
    
    errorCollection: function(errors){
        var t = new Ext.XTemplate('<tpl for=".">', '<span>{msg}</span><br />', '</tpl>');
        xlib.Msg.error(t.apply(errors));
    },
    
    confirm: function(msg, fn, scope){
        msg = msg || 'Вы уверены?';
        Ext.Msg.confirm('Подтверждение', msg, function(b) {
            
            if (b == 'yes' && typeof fn === 'function') {
                fn.createDelegate(scope)();
            }
        });
    },
    
    info: function(msg, animEl) {
        return Ext.Msg.show({
            animEl: animEl,
            buttons: Ext.MessageBox.OK,
            icon: Ext.MessageBox.INFO,
            modal: true,
            title: 'Информация',
            msg: msg
        });
    },
    
    getQtipSpan: function(tip, text) {
    	var t = new Ext.Template('<span style="overflow: hidden;" ext:qtip="{tip}">{text}</span>');
    	return t.apply({tip: tip, text: text});
    }
};