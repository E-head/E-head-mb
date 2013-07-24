Ext.ns('PMS.Storage.Assets.Edit');

PMS.Storage.Assets.Edit.Tree = Ext.extend(PMS.Storage.Assets.Tree, {

    assetId: null,
    
    header: false
    
});

Ext.reg('PMS.Storage.Assets.Edit.Tree', PMS.Storage.Assets.Edit.Tree);