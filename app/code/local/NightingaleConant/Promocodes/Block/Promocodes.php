<?php
class NightingaleConant_Promocodes_Block_Promocodes extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
        
     public function getPromocodes()     
     { 
        if (!$this->hasData('promocodes')) {
            $this->setData('promocodes', Mage::registry('promocodes'));
        }
        return $this->getData('promocodes');
        
    }
}