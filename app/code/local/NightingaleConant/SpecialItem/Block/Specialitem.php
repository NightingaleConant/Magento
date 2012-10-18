<?php
class NightingaleConant_SpecialItem_Block_Specialitem extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getSpecialItem()     
     { 
        if (!$this->hasData('specialitem')) {
            $this->setData('specialitem', Mage::registry('specialitem'));
        }
        return $this->getData('specialitem');
        
    }
}