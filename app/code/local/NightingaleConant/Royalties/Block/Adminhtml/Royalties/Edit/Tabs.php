<?php

class NightingaleConant_Royalties_Block_Adminhtml_Royalties_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('royalties_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('royalties')->__('Create/Edit Royalty Rate'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('royalties')->__('Create / Edit Royalty Rate'),
          'title'     => Mage::helper('royalties')->__('Create / Edit Royalty Rate'),
          'content'   => $this->getLayout()->createBlock('royalties/adminhtml_royalties_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
