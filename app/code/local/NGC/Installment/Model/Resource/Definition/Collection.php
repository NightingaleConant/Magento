<?php

class NGC_Installment_Model_Resource_Definition_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('installment/definition');
    }
}