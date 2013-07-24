<?php

class PMS_FixedAssets_Reports
{
    public function generateReport()
    {
        $response = new OSDN_Response();
        $table = new PMS_FixedAssets_Table();

        $select = $table->getAdapter()->select()
        ->from(array('fa' => $table->getTableName()))
        ->joinLeft(array('s' => 'staff'),
            'fa.staff_id=s.id',
            array('staff_name' => 's.name'))
        ->joinLeft(array('faf' => 'fixed_assets_files'),
            'fa.id=faf.item_id',
            array('files' => new Zend_Db_Expr('count(faf.id)')))
        ->group('fa.id');
        //echo $select->assemble(); die;
        $res = $select->query()->fetchAll();

        if (!$res) {
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $response->setRowset($res);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }
}