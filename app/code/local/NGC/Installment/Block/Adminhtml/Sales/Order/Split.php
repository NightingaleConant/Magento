<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Split extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_sales_order';
        $this->_blockGroup = 'installment';
        $this->_mode = 'split';

        $this->_updateButton('save', 'label', Mage::helper('installment')->__('Save New Installment'));

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->_getBackUrl() . '\')');

        $this->_removeButton('delete');

        if(!Mage::registry('current_installment_payment')->getId()) {
            $this->_removeButton('delete');
        }

    }

    public function _getBackUrl()
    {
        return $this->getUrl('*/sales_order_payment/edit', array('id' => $this->getId()));
    }

    public function getId()
    {
        return Mage::registry('current_installment_payment')->getId();
    }

    public function getHeaderText()
    {
        if(!is_null($this->getId())) {
            return Mage::helper('installment')->__('Split Installment Payment - Order Number "%s" - Sequence Number "%s"', $this->htmlEscape(Mage::registry('current_installment_payment')->getOrderId()), $this->htmlEscape(Mage::registry('current_installment_payment')->getInstallmentMasterSequenceNumber()));
        } else {
            return Mage::helper('installment')->__('New Installment Payment');
        }
    }
}