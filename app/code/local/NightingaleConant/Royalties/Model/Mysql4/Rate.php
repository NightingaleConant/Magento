<?php
class NightingaleConant_Royalties_Model_Mysql4_Rate extends Mage_Core_Model_Mysql4_Abstract {
    
    public function _construct() {
        $this->_init('royalties/rate', 'royalty_rate_id');        
    }
    
}
?>
