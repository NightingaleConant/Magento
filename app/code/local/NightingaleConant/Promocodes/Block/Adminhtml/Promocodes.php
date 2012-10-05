<?php
class NightingaleConant_Promocodes_Block_Adminhtml_Promocodes extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_promocodes';
    $this->_blockGroup = 'promocodes';
    $this->_headerText = Mage::helper('promocodes')->__('Promo Codes Manager');
    $this->_addButtonLabel = Mage::helper('promocodes')->__('Add Promo code');    
    parent::__construct();
  }
}