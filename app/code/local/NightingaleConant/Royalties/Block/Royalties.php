<?php
class NightingaleConant_Royalties_Block_Royalties extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getRoyalties()     
     { 
        if (!$this->hasData('royalties')) {
            $this->setData('royalties', Mage::registry('royalties'));
        }
        return $this->getData('royalties');
        
    }
}