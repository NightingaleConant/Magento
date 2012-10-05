<?php
class NightingaleConant_Royalties_Model_Mysql4_Rate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    public function _construct() {
        parent::_construct();
        $this->_init('royalties/rate');
    }
}

?>
