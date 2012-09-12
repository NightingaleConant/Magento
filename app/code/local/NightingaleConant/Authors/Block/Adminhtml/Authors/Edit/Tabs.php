<?php

class NightingaleConant_Authors_Block_Adminhtml_Authors_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('authors_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('authors')->__('Create New Author'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('authors')->__('Create New Author'),
          'title'     => Mage::helper('authors')->__('Create New Author'),
          'content'   => $this->getLayout()->createBlock('authors/adminhtml_authors_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
