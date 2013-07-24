<?php

/**
 * OSDN_Response_Status_Storage_Interface
 * 
 * @category OSDN
 * @package OSDN_Response
 */
interface OSDN_Response_Status_Storage_Interface
{
    
    /**
     * Get status object
     *
     * @return OSDN_Response_Status_Interface     status object
     */
    public function getStatus();
}
