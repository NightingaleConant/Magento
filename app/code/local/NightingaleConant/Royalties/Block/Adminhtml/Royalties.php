<?php
class NightingaleConant_Royalties_Block_Adminhtml_Royalties extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_royalties';
    $this->_blockGroup = 'royalties';
    $this->_headerText = Mage::helper('royalties')->__('Royalty Rate Manager');
    $this->_addButtonLabel = Mage::helper('royalties')->__('Add Royalty Rate');
    parent::__construct();
  }
}