<?php
class NightingaleConant_Authors_Block_Adminhtml_Authors extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_authors';
    $this->_blockGroup = 'authors';
    $this->_headerText = Mage::helper('authors')->__('Author Manager');
    $this->_addButtonLabel = Mage::helper('authors')->__('Add Author');
    parent::__construct();
  }
}