<?php
class NightingaleConant_HeldOrders_Block_Heldorders extends Mage_Core_Block_Template {
 
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
 
    public function getHeldOrders() {
        if (!$this->hasData('heldorders')) {
            $this->setData('heldorders', Mage::registry('heldorders'));
        }
        return $this->getData('heldorders');
    }
 
}