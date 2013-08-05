Ext.ns('PMS.Order');

PMS.Order.Form = Ext.extend(Ext.Panel, {
    
    title: 'Заказ товаров',
    
    permissions: true,
    
    baseCls: 'mb-splash',
    
    layout: 'fit',
    
    initComponent: function() {
        
        this.dataStore = new Ext.data.JsonStore({
            url: link('goods', 'index', 'get-list'),
            autoLoad: true,
            root: 'data',
            fields: ['id', 'name', 'descr', {name: 'price', type: 'float'}]
        });
        
        var tpl = new Ext.XTemplate(
            '<div id="gallery">',
                '<ul class="portfolio">',
                    '<tpl for="."><li>',
                        '<span class="image-border">',
                            '<img width="253" height="191" src="/files/{id}.jpg" ',
                            'class="attachment-portfolio-post-thumbnail wp-post-image" ',
                            'style="display: block;">',
                        '</span>',
                        '<div class="folio-desc">',
                            '<h3 style="color: #008837;">{name}</h3>',
                            '<p class="excerpt">{descr}</p>',
                            '<div class="price">{price} р. / кг</div>',
                            '<div align="right" style="padding-right: 30px;">',
                            'Кол-во: <input class="field" type="text" ',
                            'good_id="{id}" name="{name}" price="{price}"></div>',
                        '</div>',
                    '</li></tpl>',
                '</ul>',
            '</div>'
        );
        
        this.dataView = new Ext.DataView({
            store: this.dataStore,
            tpl: tpl,
            autoScroll: true,
            itemSelector: 'input.field'
        });
        
        this.items = this.dataView;
        
        this.dateField = new xlib.form.DateField({
            editable: false,
            format: 'd-m-Y',
            value: (new Date()).add(Date.DAY, 1)
        });
        
        this.timeField = new Ext.form.TimeField({
            forceSelection: true,
            editable: false,
            increment: 60,
            format: 'H:i',
            minValue: '07:00',
            maxValue: '21:00',
            width: 70,
            value: '07:00'
        });
        
        this.summField = new Ext.form.DisplayField({
            value: '0.00 р.'
        }); 
        
        this.bbar = new Ext.Toolbar({
            cls: 'mb-clear',
            style: 'padding: 50px;',
            items: [{
                xtype: 'tbtext',
                text: 'На дату:'
            }, ' ', this.dateField, ' ', ' ', ' ', ' ', ' ', {
                xtype: 'tbtext',
                text: 'время:'
            }, ' ', this.timeField, ' ', ' ', ' ', ' ', ' ', {
                xtype: 'tbtext',
                text: 'Сумма:'
            }, ' ', this.summField, ' ', ' ', ' ', ' ', ' ', {
                text: 'Заказать',
                width: 80,
                //scale: 'medium',
                pressed: true,
                handler: this.makeOrder,
                scope: this
            }]
        });
        
        PMS.Order.Form.superclass.initComponent.apply(this, arguments);
        
        this.dataStore.on('load', this.setListeners, this);
        
    },
    
    setListeners: function() {
        
        var nodes = this.dataView.getNodes(), summ = 0;
        Ext.each(nodes, function(item) {
            var node = Ext.get(item);
            node.on('change', this.updateSumm, this);
        }, this);
        
        var value = Ext.util.Format.number(summ, '0,000.00').replace(/,/g, ' ') + ' р.';
        this.summField.setValue(value);
    },
    
    updateSumm: function() {
        
        var nodes = this.dataView.getNodes(), summ = 0;
        Ext.each(nodes, function(item) {
            var node = Ext.get(item),
                number = parseInt(node.getValue() || 0),
                price = parseInt(node.getAttribute('price') || 0);
            summ += (number * price);
        }, this);
        
        var s = Ext.util.Format.number(summ, '0,000.00').replace(/,/g, ' ') + 'р. ';
        this.summField.setValue(s);
    },
    
    getData: function() {
        
        var nodes = this.dataView.getNodes(), summ = 0, data = [];
        
        Ext.each(nodes, function(item) {
            var node = Ext.get(item),
                number = parseInt(node.getValue() || 0),
                price = parseInt(node.getAttribute('price') || 0);
            summ = (number * price);
            if (summ) {
                data.push({
                    good_id: node.getAttribute('good_id'),
                    name: node.getAttribute('name'),
                    number: number,
                    price: price,
                    summ: summ
                });
                summ = 0;
            }
        }, this);
        
        return data;
    },
    
    makeOrder: function() {
        
        this.updateSumm();
        
        var onDate = this.dateField.getValue().add(Date.HOUR, 
            parseInt(this.timeField.getValue())),
            data = this.getData(),
            panel = new PMS.Order.Preview({
                onDate: onDate
            });
        
        if (!data.length) {
            xlib.Msg.error('Не выбраны товары для заказа.');
            return;
        }
            
        var w = new Ext.Window({
            title: 'Ваш заказ',
            resizable: false,
            width: 500,
            modal: true,
            items: [panel],
            buttons: [{
                text: 'Оформить заказ',
                handler: function() {
                    this.addOrder(data, onDate, w);
                },
                scope: this
            }, {
                text: 'Отмена',
                handler: function() {
                    w.close();
                }
            }]
        });
        w.show();
        panel.getStore().loadData({data: data});
    },
    
    addOrder: function(data, onDate, w) {
        
        var requestData = [];
        
        Ext.each(data, function(item) {
            requestData.push({
                good_id: item.good_id,
                number: item.number
            });
        }, this);
        
        Ext.Ajax.request({
            url: link('orders', 'index', 'make'),
            params: {
                ondate: onDate,
                data: Ext.encode(requestData)
            },
            callback: function (options, success, response) {
                var res = xlib.decode(response.responseText);
                if (true == success && res && true == res.success) {
                    this.dataStore.reload();
                    w.close();
                    xlib.Msg.info('Заказ принят, спасибо за покупку.');
                    return;
                }
                xlib.Msg.error('Ошибка при оформлении заказа.');
            },
            scope: this
        });
    }
});

Ext.reg('PMS.Order.Form', PMS.Order.Form);