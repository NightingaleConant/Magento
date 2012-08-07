<?php

class CJ_Reports_Model_Resource_Artransactionshistory extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('cj_reports/artransactionshistory', 'id');
    }
}