<?php

class NGC_Installment_Model_Resource_Master extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('installment/master', 'id');
    }
}