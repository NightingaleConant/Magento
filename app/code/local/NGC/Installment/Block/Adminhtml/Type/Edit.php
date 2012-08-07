<?php

class NGC_Installment_Block_Adminhtml_Type_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_type';
        $this->_blockGroup = 'installment';

        $this->_updateButton('save', 'label', Mage::helper('installment')->__('Save Installment Type'));
        $this->_updateButton('delete', 'label', Mage::helper('installment')->__('Delete Installment Type'));

        if(!Mage::registry('current_installment_type')->getId()) {
            $this->_removeButton('delete');
        }

    }

    public function getHeaderText()
    {
        if(!is_null(Mage::registry('current_installment_type')->getId())) {
            return Mage::helper('installment')->__('Edit Installment Type "%s"', $this->htmlEscape(Mage::registry('current_installment_type')->getInstallmentTypeId()));
        } else {
            return Mage::helper('installment')->__('New Installment Type');
        }
    }
}