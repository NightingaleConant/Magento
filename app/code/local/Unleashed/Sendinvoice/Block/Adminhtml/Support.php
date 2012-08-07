<?php

class Unleashed_Sendinvoice_Block_Adminhtml_Support extends Mage_Adminhtml_Block_Widget_Tabs
{

  
 public function __construct() {
        parent::__construct();
        $this->setTemplate('sendinvoice/support.phtml');
        $this->setFormAction(Mage::getUrl('*/*/update'));
    }

  protected function _beforeToHtml()
  {
      

     
      return parent::_beforeToHtml();
  }
  

  

 
  
  
  
}
