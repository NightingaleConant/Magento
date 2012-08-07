<?php

class NGC_Installment_Adminhtml_Sales_Order_PaymentController extends Mage_Adminhtml_Controller_action
{

    protected function _initPayment()
    {
        $this->_title($this->__('Installments'))->_title($this->__('Installment Payment'));

        Mage::register('current_installment_payment', Mage::getModel('installment/master'));
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            Mage::registry('current_installment_payment')->load($id);
        }

    }

    /**
     * Edit or create installment type.
     */
    public function editAction()
    {
        $this->_initPayment();
        $this->loadLayout();

        $currentPayment = Mage::registry('current_installment_payment');

        if (!is_null($currentPayment->getId())) {
            $this->_addBreadcrumb(Mage::helper('installment')->__('Edit Type'), Mage::helper('installment')->__('Edit Installment Payment'));
        } else {
            $this->_addBreadcrumb(Mage::helper('installment')->__('New Type'), Mage::helper('installment')->__('New Installment Payment'));
        }

        $this->_title($currentPayment->getId() ? $currentPayment->getCode() : $this->__('New Type'));

        $block = $this->getLayout()->createBlock('installment/adminhtml_sales_order_edit', 'sales_order_edit');
        $this->_addContent($block->setEditMode((bool)Mage::registry('current_installment_payment')->getId()))
             ->_addLeft($this->getLayout()->createBlock('installment/adminhtml_sales_order_edit_tabs'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        $installment = Mage::getModel('installment/master');
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $installment->load($id);
        }

        try {
            $authPaid    = ($installment->getInstallmentMasterInstallmentAuthorized() || $installment->getInstallmentMasterInstallmentPaid());
            if ($authPaid && $this->getRequest()->getParam('suspend_installment')) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('installment')->__('The installment has been authorized and/or paid and can not be split.'));
                $order = Mage::getModel('sales/order')->loadByIncrementId($installment->getOrderId());
                $this->getResponse()->setRedirect($this->getUrl('*/sales_order/view', array('order_id' => $order->getId(), 'active_tab' => 'order_installment_payment')));
            }

            $reason = ($this->getRequest()->getParam('suspend_installment')) ? $this->getRequest()->getParam('suspended_reason') : '';
            $installment->setInstallmentMasterAmountDue($this->getRequest()->getParam('amount_due'))
                ->setInstallmentMasterAmountDueDate(Varien_Date::formatDate($this->getRequest()->getParam('amount_due_date')))
                ->setInstallmentMasterSuspendInstallment($this->getRequest()->getParam('suspend_installment'))
                ->setInstallmentMasterSuspendedReason($reason)
                ->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('installment')->__('The installment payment has been saved.'));

            $id = Mage::getModel('sales/order')->loadByIncrementId($this->getRequest()->getParam('order_id'))->getId();

            $this->getResponse()->setRedirect($this->getUrl('*/sales_order/view', array(
                'order_id' => $id,
                'active_tab' => 'order_installment_payment'
            )));

            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setInstallmentPaymentData($installment->getData());
            $this->getResponse()->setRedirect($this->getUrl('*/sales_order_payment/edit', array('id' => $id)));
            return;
        }

    }

    public function suspendcurrentandfutureAction()
    {
        $this->_initPayment();
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('installment/adminhtml_sales_order_suspendcurrentandfuture', 'sales_order_suspendcurrentandfuture');
        $this->_addContent($block->setEditMode((bool)Mage::registry('current_installment_payment')->getId()));

        $this->renderLayout();
    }

    public function suspendcurrentandfuturepostAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $reason = $this->getRequest()->getParam('suspended_reason');

        $installment = Mage::getModel('installment/master')->load($id);

        $authPaid    = ($installment->getInstallmentMasterInstallmentAuthorized() || $installment->getInstallmentMasterInstallmentPaid());
        if ($authPaid) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('installment')->__('The installment has been authorized and/or paid and can not be suspended.'));
            $order = Mage::getModel('sales/order')->loadByIncrementId($installment->getOrderId());
            $this->getResponse()->setRedirect($this->getUrl('*/sales_order/view', array('order_id' => $order->getId(), 'active_tab' => 'order_installment_payment')));
        }

        $collection  = Mage::getModel('installment/master')->getCollection()->addFieldToFilter('order_id', $installment->getOrderId());

        foreach ($collection as $installmentseq) {
            if ($installmentseq->getInstallmentMasterSequenceNumber() >= $installment->getInstallmentMasterSequenceNumber()) {
                $installmentseq->setInstallmentMasterSuspendInstallment(true)
                               ->setInstallmentMasterSuspendedReason($reason)
                               ->save();
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('installment')->__('The installment(s) have been suspended.'));

        $this->getResponse()->setRedirect($this->getUrl('*/sales_order_payment/edit', array('id' => $id)));
    }

    public function adjustAction()
    {
        $this->_initPayment();
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('installment/adminhtml_sales_order_adjust', 'sales_order_adjust');
        $this->_addContent($block->setEditMode((bool)Mage::registry('current_installment_payment')->getId()));

        if ($this->getRequest()->getParam('order_id')) {
            $installment = Mage::registry('current_installment_payment');

            try {
                $authPaid    = ($installment->getInstallmentMasterInstallmentAuthorized() || $installment->getInstallmentMasterInstallmentPaid());
                if ($authPaid) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('installment')->__('The installment that has been authorized and/or paid can not be adjusted.'));
                    $order = Mage::getModel('sales/order')->loadByIncrementId($installment->getOrderId());
                    $this->getResponse()->setRedirect($this->getUrl('*/sales_order/view', array('order_id' => $order->getId(), 'active_tab' => 'order_installment_payment')));
                }

                $installment->setInstallmentMasterAmountDue($this->getRequest()->getParam('amount_due'))
                            ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('installment')->__('The installment has been adjusted.'));
                $order = Mage::getModel('sales/order')->loadByIncrementId($installment->getOrderId());
                $this->getResponse()->setRedirect($this->getUrl('*/sales_order/view', array('order_id' => $order->getId(), 'active_tab' => 'order_installment_payment')));

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setInstallmentPaymentData($payment->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/sales_order_payment/edit', array('id' => $id)));
                return;
            }
        }
        $this->renderLayout();
    }

    public function splitAction()
    {
        $this->_initPayment();
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('installment/adminhtml_sales_order_split', 'sales_order_split');
        $this->_addContent($block->setEditMode((bool)Mage::registry('current_installment_payment')->getId()));

        if ($this->getRequest()->getParam('order_id')) {
            $installment = Mage::registry('current_installment_payment');

            try {
                $authPaid    = ($installment->getInstallmentMasterInstallmentAuthorized() || $installment->getInstallmentMasterInstallmentPaid());
                if ($authPaid) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('installment')->__('The installment that has been authorized and/or paid can not be split.'));
                    $order = Mage::getModel('sales/order')->loadByIncrementId($installment->getOrderId());
                    $this->getResponse()->setRedirect($this->getUrl('*/sales_order/view', array('order_id' => $order->getId(), 'active_tab' => 'order_installment_payment')));
                }

                //  update original installment with new amount due and due date
                $installment->setInstallmentMasterAmountDue($this->getRequest()->getParam('amount_due'))
                            ->setInstallmentMasterAmountDueDate($this->getRequest()->getParam('amount_due_date'))
                            ->save();

                //  get new installment sequence number
                $aCollection = Mage::getModel('installment/master')->getCollection()->addFieldToFilter('order_id', $installment->getOrderId())->getData();
                $lastSeq = array_pop($aCollection);

                $newSeqId = $lastSeq['installment_master_sequence_number'] + 1;

                //  add new installment
                $newInstallment = Mage::getModel('installment/master');
                $newInstallment->setOrderId($installment->getOrderId())
                               ->setInstallmentMasterSequenceNumber($newSeqId)
                               ->setInstallmentMasterAuthorizedPayment(false)
                               ->setInstallmentMasterInstallmentPaid(false)
                               ->setInstallmentMasterAmountDue($this->getRequest()->getParam('new_amount_due'))
                               ->setInstallmentMasterAmountDueDate($this->getRequest()->getParam('new_amount_due_date'))
                               ->setInstallmentMasterSuspendInstallment(false)
                               ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('installment')->__('The installment has been split.'));
                $order = Mage::getModel('sales/order')->loadByIncrementId($installment->getOrderId());
                $this->getResponse()->setRedirect($this->getUrl('*/sales_order/view', array('order_id' => $order->getId(), 'active_tab' => 'order_installment_payment')));

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setInstallmentPaymentData($payment->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/sales_order_payment/edit', array('id' => $id)));
                return;
            }

        }

        $this->renderLayout();
    }



}