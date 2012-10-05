<?php
class NightingaleConant_Promocodes_Model_Mysql4_Promocodes_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    public function _construct() {
        parent::_construct();
        $this->_init('promocodes/promocodes');
    }
}

?>
