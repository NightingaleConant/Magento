<?php
class NightingaleConant_Promocodes_Model_Mysql4_Promocodes extends Mage_Core_Model_Mysql4_Abstract {
    
    public function _construct() {
        $this->_init('promocodes/promocodes','ckc_entity_id');
    }
    
}
?>
