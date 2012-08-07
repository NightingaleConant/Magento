<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Suspendcurrentandfuture extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_sales_order';
        $this->_blockGroup = 'installment';
        $this->_mode = 'suspendcurrentandfuture';

        $this->_updateButton('save', 'label', Mage::helper('installment')->__('Suspend Current & Future Installments'));
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->_getBackUrl() . '\')');

        $this->_removeButton('delete');
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
        $installment = Mage::registry('current_installment_payment');
        $collection  = Mage::getModel('installment/master')->getCollection()->addFieldToFilter('order_id', $installment->getOrderId());
        $aCollection = $collection->getData();

        $sequence = $installment->getInstallmentMasterSequenceNumber();

        foreach ($aCollection as $installmentseq) {
            if ($installmentseq['installment_master_sequence_number'] > $installment->getInstallmentMasterSequenceNumber()) {
                $sequence .= ', '. $installmentseq['installment_master_sequence_number'];
            }
        }

        return Mage::helper('installment')->__('Suspend Current and Future Installment Payment - Order Number "%s" - Sequence Number(s) "%s"', $this->htmlEscape($installment->getOrderId()), $this->htmlEscape($sequence));
    }
}