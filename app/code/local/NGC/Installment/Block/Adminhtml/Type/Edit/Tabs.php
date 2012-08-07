<?php

class NGC_Installment_Block_Adminhtml_Type_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('installment_type_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('installment')->__('Installment Type Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('properties', array(
            'label'     => Mage::helper('installment')->__('Properties'),
            'title'     => Mage::helper('installment')->__('Properties'),
            'content'   => $this->getLayout()->createBlock('installment/adminhtml_type_edit_tab_properties')->toHtml(),
            'active'    => true
        ));

        $this->addTab('definition', array(
            'label'     => Mage::helper('installment')->__('Sequence Definition'),
            'title'     => Mage::helper('installment')->__('Sequence Definition'),
            'content'   => $this->getLayout()->createBlock('installment/adminhtml_type_edit_tab_definition')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
