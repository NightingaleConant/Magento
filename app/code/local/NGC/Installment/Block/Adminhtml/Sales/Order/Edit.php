<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_sales_order';
        $this->_blockGroup = 'installment';

        $installment = Mage::registry('current_installment_payment');
        $authPaid    = ($installment->getInstallmentMasterInstallmentAuthorized() || $installment->getInstallmentMasterInstallmentPaid());

        if (!$authPaid) {
            $this->_addButton('split', array(
                'label'     => Mage::helper('installment')->__('Split Installment'),
                'onclick' => 'setLocation(\'' . $this->_getSplitInstallmentUrl() . '\')',
                'class'     => 'split'

            ));

            $this->_addButton('suspendcurrentandfutureinstallment', array(
                'label'     => Mage::helper('installment')->__('Suspend Current & Future Installment'),
                'onclick' => 'setLocation(\'' . $this->_getSuspendCurrentAndFutureInstallmentUrl() . '\')',
                'class'     => 'split'

            ));
        }

        if ($authPaid) {
            $this->_removeButton('save');
        }

//        $this->_addButton('saveandcontinue', array(
//            'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
//            'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
//            'class'     => 'save',
//        ), -100);

        $this->_updateButton('save', 'label', Mage::helper('installment')->__('Save Installment'));

        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->_getBackUrl() . '\')');

        $this->_removeButton('delete');

        if(!Mage::registry('current_installment_payment')->getId()) {
            $this->_removeButton('delete');
        }

    }

    public function _getSplitInstallmentUrl()
    {
        return $this->getUrl('*/*/split', array('id' => $this->getId()));
    }
    public function _getSuspendCurrentAndFutureInstallmentUrl()
    {
        return $this->getUrl('*/*/suspendcurrentandfuture', array('id' => $this->getId()));
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit'
        ));
    }

    public function _getBackUrl()
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::registry('current_installment_payment')->getOrderId());
        return $this->getUrl('*/sales_order/view', array('order_id' => $order->getId(), 'active_tab' => 'order_installment_payment'));
    }

    public function getId()
    {
        return Mage::registry('current_installment_payment')->getId();
    }

    public function getHeaderText()
    {
        if(!is_null($this->getId())) {
            return Mage::helper('installment')->__('Edit Installment Payment - Order Number "%s" - Sequence Number "%s"', $this->htmlEscape(Mage::registry('current_installment_payment')->getOrderId()), $this->htmlEscape(Mage::registry('current_installment_payment')->getInstallmentMasterSequenceNumber()));
        } else {
            return Mage::helper('installment')->__('New Installment Payment');
        }
    }
}