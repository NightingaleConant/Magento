<?php

class NGC_Installment_Model_Master_Observer
{

    public function onSalesOrderSaveBefore($observer)
    {
        if (false !== strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order')
            && 'save' == Mage::app()->getRequest()->getActionName()
            && !Mage::registry('installment_master_transaction_saved'))
        {
            $request = Mage::app()->getRequest();
            $payment = $request->getPost('payment');
            if ($payment && !empty($payment['installment_type_id'])) {

                $order = $observer->getEvent()->getOrder();
                $order->setInstallmentTypeId($payment['installment_type_id']);

                $master = Mage::getModel('installment/master');
                $master->generateInstallmentMasterTransactions($observer->getEvent()->getOrder());

                Mage::register('installment_master_transaction_saved', true);
            }
        }
    }

    public function onSalesModelServiceQuoteSubmitSuccess($observer)
    {
        $order = $observer->getOrder();
        if ($order->getInstallmentTypeId()) {
            $orderId        = $order->getRealOrderId();
            $installment    = Mage::getModel('installment/master')->getCollection();
            $date           = date("Y-m-d", Mage::getModel('core/date')->timestamp(time())) .' 23:59:59';
            $installment->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('installment_master_amount_due_date', array('lt' => $date));

            foreach ($installment as $transaction) {
                $maxAttempt = Mage::getStoreConfig('installment/grace_period_options/grace_period_attempts', $order->getStoreId());
                $attempt    = $transaction->getInstallmentMasterAttemptToAuthorize() + 1;

                if ($attempt <= $maxAttempt) {
                    $amount     = $transaction->getInstallmentMasterAmountDue();
                    $payment    = $order->getPayment();

                    $payment->getMethodInstance()->setStore($order->getStoreId())->authorize($payment, $amount);
                    $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, false, sprintf('Authorized amount of %s for installment payment #%s', $amount, $transaction->getInstallmentMasterSequenceNumber()));

                    if ($payment->getStatus() == 'Approved') {
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'ready_to_print')->save();

                        $transaction->setInstallmentMasterInstallmentAuthorized(true)
                                    ->setInstallmentMasterAttemptToAuthorize($attempt)
                                    ->save();

                        //  Insert row into installment_transaction for the order
                        $poNumber = $payment->getPoNumber();
                        list($customerProfileId, $paymentProfileId, $authorizeCode) = explode('-', $poNumber);
                        $currencyCode = Mage::app()->getStore($order->getStoreId())->getCurrentCurrencyCode();

                        $installmentTrans = Mage::getModel('installment/transaction');
                        $installmentTrans->setOrderId($orderId)
                                        ->setInstallmentTransactionTransactionSequenceNumber($attempt)
                                        ->setInstallmentTransactionPaymentToken($payment->getCcTransId() .'-'. $payment->getPoNumber())
                                        ->setInstallmentTransactionSequenceNumber($transaction->getInstallmentMasterSequenceNumber())
                                        ->setInstallmentTransactionAuthorizeAmount($amount)
                                        ->setInstallmentTransactionAuthorizeAttemptDate($payment->getCreatedAt())
                                        ->setInstallmentTransactionAuthorizationCode($authorizeCode)
                                        ->setInstallmentTransactionCurrencyCode($currencyCode)
                                        ->save();

                        //  Generate offline invoice for order
                        if(!$order->canInvoice()) {
                            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
                        }

                        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                        if (!$invoice->getTotalQty()) {
                            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                        }

                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);

                        $invoice->register();

                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $transactionSave->save();

                        $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN)->save();
                    } else {
                        //  Increase auth attempt for installment master record and update due date
                        $date = NGC_Installment_Model_Master::calculateAmountDueDate(
                            Mage::getStoreConfig('installment/grace_period_options/grace_period_days',
                            $order>getStoreId())
                        );
                        $transaction->setInstallmentMasterAttemptToAuthorize($attempt)
                                    ->setInstallmentMasterAmountDueDate($date)
                                    ->save();

                        //  Insert installment transaction record with failed auth details
                        $installmentTrans = Mage::getModel('installment/transaction');
                        $installmentTrans->setOrderId($orderId)
                            ->setInstallmentTransactionTransactionSequenceNumber($attempt)
                            ->setInstallmentTransactionPaymentToken($payment->getCcTransId() .'-'. $payment->getPoNumber())
                            ->setInstallmentTransactionSequenceNumber($transaction->getInstallmentMasterSequenceNumber())
                            ->setInstallmentTransactionAuthorizeAmount($amount)
                            ->setInstallmentTransactionAuthorizeAttemptDate($payment->getCreatedAt())
                            ->setInstallmentTransactionDeclineCode($authorizeCode)
                            ->setInstallmentTransactionDeclineTransationMessage($payment->getDeclineCode())
                            ->setInstallmentTransactionCurrencyCode($currencyCode)
                            ->save();

                        $fmtAmount = $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
                        $message = Mage::helper('sales')->__('Failed to captured amount of %s for installment payment #%s', $fmtAmount, $transaction->getInstallmentMasterSequenceNumber());
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'installment_capture_failed', $message)->save();
                    }
                } else {
                    //  Max auth attempts have been reached, suspend current & future installment payments and update as hard decline
                    NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, $transaction->getInstallmentMasterSequenceNumber());
                }
            }
        }
    }

    public function onSalesModelOrderPaymentCancel($observer)
    {
        if ($observer->getPayment()->getOrder()->getInstallmentTypeId()) {
            $orderId = $observer->getPayment()->getOrder()->getRealOrderId();

            if (NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, 1, 'Order was canceled')) {
                return Mage::throwException('Unable to suspend future installment payments');
            }

            if (NGC_Installment_Model_Master::refundInstallmentPayment($orderId)) {
                return mage::throwException('Unable to refund installment payments');
            }
        }
    }

    public function onSalesModelOrderPaymentVoid($observer)
    {
        if ($observer->getPayment()->getOrder()->getInstallmentTypeId()) {
            $orderId = $observer->getPayment()->getOrder()->getRealOrderId();

            if (NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, 1, 'Order was void')) {
                return Mage::throwException('Unable to suspend future installment payments');
            }

            if (NGC_Installment_Model_Master::refundInstallmentPayment($orderId)) {
                return mage::throwException('Unable to refund installment payments');
            }

        }
    }

    public function onSalesModelOrderCreditmemoRefund($observer)
    {
        if ($observer->getCreditmemo()->getOrder()->getInstallmentTypeId()) {
            if (NGC_Installment_Model_Master::refundInstallmentPayment($observer)) {
                return mage::throwException('Unable to refund installment payments');
            }
        }
    }


}

