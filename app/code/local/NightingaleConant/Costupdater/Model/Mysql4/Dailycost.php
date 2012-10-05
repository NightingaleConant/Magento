<?php

class NightingaleConant_Costupdater_Model_Mysql4_Dailycost extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('costupdater/dailycost', 'dailycost_id');
    }
}
