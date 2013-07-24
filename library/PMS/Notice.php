<?php

class PMS_Notice
{
	protected $_table;

	static $TYPES = array('объявление', 'приказ');

    public function __construct()
    {
        $this->_table = new PMS_Notice_Table();
    }

    /**
     * @param int $account_id
     * @return array Messages id`s not read by given account
     */
    public function getUnreadMessages($account_id)
    {
        $table = new PMS_Notice_Dst_Table();
        return $table->fetchAllColumn(array(
            'account_id = ?' => $account_id,
            'date IS NULL'
        ), null, 'notice_id');
    }

    public function get($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        if ($id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }

        $currentAccountId = OSDN_Accounts_Prototype::getId();

        $select = $this->_table->getAdapter()->select()
            ->from(array('n' => $this->_table->getTableName()), array('*'))
            ->joinLeft(array('a' => 'accounts'),
                'n.account_id=a.id', array('account_name' => 'a.name'))
            ->joinLeft(array('d' => 'notice_dst'),
                $this->_table->getAdapter()->quoteInto(
                    'd.notice_id=n.id AND d.account_id = ?', $currentAccountId), array(
                    'new'  => new Zend_Db_Expr('IF(d.date IS NULL
                                                AND d.account_id IS NOT NULL,1,0)')
                )
            )
            ->where('n.id = ?', $id)
            ;
        try {
            $rows = $select->query()->fetchAll();

            if (1 == $rows[0]['is_personal']) {
                $table = new PMS_Notice_Dst_Table();
                $rows[0]['dst'] = $table->fetchAllColumn(
                    array('notice_id = ?' => $rows[0]['id']), null, array('account_id')
                );
            }

            $response->setRowset($rows);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function getList($params)
    {
        $response = new OSDN_Response();

        $currentAccountId = OSDN_Accounts_Prototype::getId();

        $select = $this->_table->getAdapter()->select()
            ->from(array('n' => $this->_table->getTableName()),
                array('id', 'type', 'date',
                    'text' => new Zend_Db_Expr('CONCAT(SUBSTR(`text`,1,100),"...")')
                )
            )
            ->joinLeft(array('a' => 'accounts'),
                'n.account_id=a.id', array('account_name' => 'a.name'))
            ->joinLeft(array('d' => 'notice_dst'),
                'd.notice_id=n.id', array(
                    'dst_total' => 'count(*)',
                    'dst_read'  => new Zend_Db_Expr('SUM(IF(d.`date` IS NULL,0,1))')
                )
            )
            ->joinLeft(array('d1' => 'notice_dst'),
                $this->_table->getAdapter()->quoteInto(
                    'd1.notice_id=n.id AND d1.account_id = ?', $currentAccountId), array(
                    'new'  => new Zend_Db_Expr('IF(d1.date IS NULL
                                                AND d1.account_id IS NOT NULL,1,0)')
                )
            )
            ->group('n.id');

        $plugin = new OSDN_Db_Plugin_Select($this->_table, $select,
            array('type', 'account_name', 'date'));
        $plugin->setSqlCalcFoundRows(true);
        $plugin->parse($params);

        try {
            $rows = $select->query()->fetchAll();
            $response->setRowset($rows);
            $response->totalCount = $plugin->getTotalCountSql();
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function add(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            'is_personal'   => 'Int',
            '*'             => 'StringTrim'
        ), array(
            'type'          => array(array('InArray', self::$TYPES), 'presence' => 'required'),
            'is_personal'   => array(array('InArray', array(0, 1)), 'presence' => 'required'),
            'text'          => array(array('StringLength', 0, 65535), 'presence' => 'required')
        ), $params);

        $response = new OSDN_Response();

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        if (1 == $f->is_personal) {
            $dst = Zend_Json::decode($params['dst']);
            if (NULL == $dst) {
                return $response->addStatus(new PMS_Status(
                    PMS_Status::INPUT_PARAMS_INCORRECT, 'dst'
                ));
            }
        }

        $id = $this->_table->insert($f->getData());
        if (!$id) {
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        if ($this->_setDst($id, (1 == $f->is_personal) ? $dst : NULL) > 0) {
            return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
        }

        $response->id = $id;
        return $response->addStatus(new PMS_Status(PMS_Status::OK));
    }

    public function update(array $params)
    {
        $f = new OSDN_Filter_Input(array(
            'id'            => 'Int',
            'is_personal'   => 'Int',
            '*'             => 'StringTrim'
        ), array(
            'id'            => array('Id', 'presence' => 'required'),
            'type'          => array(array('InArray', self::$TYPES), 'presence' => 'required'),
            'is_personal'   => array(array('InArray', array(0, 1)), 'presence' => 'required'),
            'text'          => array(array('StringLength', 0, 65535), 'presence' => 'required')
        ), $params);

        $response = new OSDN_Response();

        $response->addInputStatus($f);
        if ($response->hasNotSuccess()) {
            return $response;
        }

        if (1 == $f->is_personal) {
            $dst = Zend_Json::decode($params['dst']);
            if (NULL == $dst) {
                return $response->addStatus(new PMS_Status(
                    PMS_Status::INPUT_PARAMS_INCORRECT, 'dst'
                ));
            }
        }

        try {
            $rows = $this->_table->updateByPk($f->getData(), $f->id);
            $status = PMS_Status::retrieveAffectedRowStatus($rows);

            if ($this->_setDst($f->id, (1 == $f->is_personal) ? $dst : NULL) > 0) {
                return $response->addStatus(new PMS_Status(PMS_Status::DATABASE_ERROR));
            }
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }

        return $response->addStatus(new PMS_Status($status));
    }

    public function delete($id)
    {
        $id = intval($id);
        $response = new OSDN_Response();
        if ($id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }
        $res = $this->_table->deleteByPk($id);
        $status = false === $res ? PMS_Status::DATABASE_ERROR : PMS_Status::OK;
        return $response->addStatus(new PMS_Status($status));
    }

    public function getDst()
    {
        $response = new OSDN_Response();

        $select = $this->_table->getAdapter()->select()
            ->from(array('a' => 'accounts'), array('id', 'name'))
            ->joinLeft(array('r' => 'acl_roles'),
                'a.role_id=r.id', array('role' => 'r.name'))
            ->where('active = 1')
            ;
        try {
            $rows = $select->query()->fetchAll();
            $response->setRowset($rows);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function dstInfo($notice_id)
    {
        $notice_id = intval($notice_id);
        $response = new OSDN_Response();
        if ($notice_id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }

        $select = $this->_table->getAdapter()->select()
            ->from(array('d' => 'notice_dst'), array('date'))
            ->joinLeft(array('a' => 'accounts'),
                'd.account_id=a.id', array('name' => 'a.name'))
            ->where('d.notice_id = ?', $notice_id)
            ;
        try {
            $rows = $select->query()->fetchAll();
            $response->setRowset($rows);
            $status = PMS_Status::OK;
        } catch (Exception $e) {
            if (OSDN_DEBUG) {
                throw $e;
            }
            $status = PMS_Status::DATABASE_ERROR;
        }
        return $response->addStatus(new PMS_Status($status));
    }

    public function setDstRead($notice_id)
    {
        $notice_id = intval($notice_id);
        $response = new OSDN_Response();
        if ($notice_id == 0) {
            return $response->addStatus(new PMS_Status(
                PMS_Status::INPUT_PARAMS_INCORRECT, 'id'));
        }

        $currentAccountId = OSDN_Accounts_Prototype::getId();
        $table = new PMS_Notice_Dst_Table();

        $res = $table->update(
            array('date' => new Zend_Db_Expr('NOW()')),
            array('notice_id = ?' => $notice_id, 'account_id = ?' => $currentAccountId)
        );
        $status = false === $res ? PMS_Status::DATABASE_ERROR : PMS_Status::OK;
        return $response->addStatus(new PMS_Status($status));
    }

    // Private functions

    /**
     * @param $notice_id int
     * @param $data array
     *      Example: array(
     *          array('id' => 1, 'value' => 1),
     *          array('id' => 2, 'value' => 0)
     *      )
     * @return int - The number of errors occurred
     */
    private function _setDst($notice_id, $data = NULL)
    {
        if (NULL === $data) {
            $accounts = new OSDN_Accounts_Table_Accounts();
            $rows = $accounts->fetchAllColumns('active = 1', null, array('id'))->toArray();
            $data = array();
            foreach ($rows as $row) {
                $data[] = array(
                    'id'    => intVal($row['id']),
                    'value' => 1
                );
            }
        }

        $errors = 0;

        $table = new PMS_Notice_Dst_Table();

        $res = $table->delete(array('notice_id = ?' => intval($notice_id)));
        if (false === $res) {
            $errors++;
        }

        foreach ($data as $pair) {

            if (!is_array($pair)) {
                continue;
            }
            if (!isset($pair['id']) || !isset($pair['value'])) {
                continue;
            }
            if (!is_int($pair['id']) || 0 == $pair['id']) {
                continue;
            }
            if (!is_int($pair['value']) || $pair['value'] != 1 ) {
                continue;
            }

            try {
                $res = $table->insert(array(
                    'notice_id'     => $notice_id,
                    'account_id'    => $pair['id']
                ));
                if (false === $res) {
                    $errors++;
                }
            } catch (Exception $e) {
                if (OSDN_DEBUG) {
                    throw $e;
                }
                $errors++;
            }
        }

        return $errors;
    }
}