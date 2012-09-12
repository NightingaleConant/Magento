<?php

class NightingaleConant_Costupdater_Model_Mysql4_Dailycost_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('costupdater/dailycost');
    }
}