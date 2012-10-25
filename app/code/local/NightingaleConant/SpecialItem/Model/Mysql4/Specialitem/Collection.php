<?php
class NightingaleConant_Specialitem_Model_Mysql4_Specialitem_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    public function _construct() {
        parent::_construct();
        $this->_init('specialitem/specialitem');
    }
}

?>
