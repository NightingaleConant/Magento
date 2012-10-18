<?php

class NightingaleConant_SpecialItem_Block_Adminhtml_Specialitem_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('specialitem_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('specialitem')->__('Create New Associate Item'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('specialitem')->__('Create New Associate Item'),
          'title'     => Mage::helper('specialitem')->__('Create New Associate Item'),
          'content'   => $this->getLayout()->createBlock('specialitem/adminhtml_specialitem_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
