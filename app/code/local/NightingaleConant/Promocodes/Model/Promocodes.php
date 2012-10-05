<?php
class NightingaleConant_Promocodes_Model_Promocodes extends Mage_Core_Model_Abstract {
    
    public function _construct() 
    {
        parent::_construct();
        $this->_init('promocodes/promocodes');
    }
        
}

?>
