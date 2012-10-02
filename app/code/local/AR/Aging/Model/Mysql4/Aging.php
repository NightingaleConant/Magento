<?php
class AR_Aging_Model_Mysql4_Aging extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ar_aging/aging', 'sfo_id');
    }
}


?>