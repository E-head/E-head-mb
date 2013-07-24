<?php

class PMS_Storage_Reports
{
    public function generateReport()
    {
        $response = new OSDN_Response();

        $table = new PMS_Storage_Assets_Table();

        $res = $table->fetchAll()->toArray();

        if (!$res) {
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $response->setRowset($res);
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }
}