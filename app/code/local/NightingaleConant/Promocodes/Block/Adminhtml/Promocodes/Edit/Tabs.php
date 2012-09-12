<?php

class NightingaleConant_Promocodes_Block_Adminhtml_Promocodes_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('promocodes_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('promocodes')->__('Create New Promocode'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('promocodes')->__('Create New Promocode'),
          'title'     => Mage::helper('promocodes')->__('Create New Promocode'),
          'content'   => $this->getLayout()->createBlock('promocodes/adminhtml_promocodes_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
