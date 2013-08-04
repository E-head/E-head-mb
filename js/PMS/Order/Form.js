Ext.ns('PMS.Order');

PMS.Order.Form = Ext.extend(Ext.Panel, {
    
    title: 'Заказ товаров',
    
    permissions: true,
    
    baseCls: 'mb-splash',
    
    layout: 'fit',
    
    initComponent: function() {
        
        var store = new Ext.data.JsonStore({
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
                            'Кол-во: <input type="text" id="{id}" ',
                            'style="width: 50px;"></div>',
                        '</div>',
                    '</li></tpl>',
                '</ul>',
            '</div>'
        );
        
        this.items = new Ext.DataView({
            store: store,
            tpl: tpl,
            autoScroll: true,
            itemSelector: 'div.thumb-wrap'
        });
        
        this.dateField = new xlib.form.DateField({
            format: 'd-m-Y'
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
                text: 'Сумма:'
            }, ' ', this.summField, ' ', ' ', ' ', ' ', ' ', {
                text: 'Сделать заказ',
                pressed: true,
                handler: this.makeOrder
            }]
        });
        
        PMS.Order.Form.superclass.initComponent.apply(this, arguments);
        
    },
    
    makeOrder: function() {
    }
});

Ext.reg('PMS.Order.Form', PMS.Order.Form);