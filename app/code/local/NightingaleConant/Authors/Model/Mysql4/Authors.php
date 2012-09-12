<?php
class NightingaleConant_Authors_Model_Mysql4_Authors extends Mage_Core_Model_Mysql4_Abstract {
    
    public function _construct() {
        $this->_init('authors/authors','author_id');
    }
    
}
?>
