<?php

class NGC_Installment_Block_Adminhtml_Type extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_type';
        $this->_blockGroup = 'installment';
        $this->_headerText = Mage::helper('installment')->__('Installment Types');
        $this->_addButtonLabel = Mage::helper('installment')->__('Add New Type');
        parent::__construct();

    }
}