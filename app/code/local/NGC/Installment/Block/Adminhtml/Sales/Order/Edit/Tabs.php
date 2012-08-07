<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('installment_sales_order_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('installment')->__('Installment Payment Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('payment_information', array(
            'label'     => Mage::helper('installment')->__('Installment Payment Information'),
            'title'     => Mage::helper('installment')->__('Installment Payment Information'),
            'content'   => $this->getLayout()->createBlock('installment/adminhtml_sales_order_edit_tab_properties')->toHtml(),
            'active'    => true
        ));

        $this->addTab('transaction_history', array(
            'label'     => Mage::helper('installment')->__('Transaction History'),
            'title'     => Mage::helper('installment')->__('Transaction History'),
            'content'   => $this->getLayout()->createBlock('installment/adminhtml_sales_order_edit_tab_history')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
