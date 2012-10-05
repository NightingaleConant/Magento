<?php
class NightingaleConant_Authors_Block_Authors extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getAuthors()     
     { 
        if (!$this->hasData('authors')) {
            $this->setData('authors', Mage::registry('authors'));
        }
        return $this->getData('authors');
        
    }
}