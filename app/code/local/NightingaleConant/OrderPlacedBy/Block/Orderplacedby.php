<?php
class NightingaleConant_OrderPlacedBy_Block_Orderplacedby extends Mage_Core_Block_Template {
 
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
 
    public function getOrderPlacedBy() {
        if (!$this->hasData('orderplacedby')) {
            $this->setData('orderplacedby', Mage::registry('orderplacedby'));
        }
        return $this->getData('orderplacedby');
    }
 
}