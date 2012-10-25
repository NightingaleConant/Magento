<?php
class NightingaleConant_SpecialItem_Block_Adminhtml_Specialitem extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_specialitem';
    $this->_blockGroup = 'specialitem';
    $this->_headerText = Mage::helper('specialitem')->__('Associate Item Manager');
    $this->_addButtonLabel = Mage::helper('specialitem')->__('Add Associate Item');
    parent::__construct();
  }
}