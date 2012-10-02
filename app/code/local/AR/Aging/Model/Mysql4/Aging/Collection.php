<?php
class AR_Aging_Model_Mysql4_Aging_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ar_aging/aging');
    }
}
?>