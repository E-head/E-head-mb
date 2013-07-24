<?php

class OSDN_XmlRpc_Server extends Zend_XmlRpc_Server
{
	/**
     * Class to use for responses; defaults to {@link OSDN_XmlRpc_Response_Http}
     * @var string
     */
    protected $_responseClass = 'OSDN_XmlRpc_Response_Http';
}