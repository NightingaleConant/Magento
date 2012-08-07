<?php

class NGC_Installment_Model_Observer
{

    /**
     * This function will be ran daily and will capture payments for both regular orders and orders that have an installment
     * plan.
     *
     * @static
     */
    static public function dailyCapturePayment()
    {
        //  Retrieve all orders that have the order status Shipped
        $collection = Mage::getModel('sales/order')->getCollection()
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter('status', array('in' => array('shipped', 'ready_to_collect')));

        foreach ($collection as $order) {
            //  Check if it is a free trial
/**
            if ($order->getFreeTrial()) {
                //  If ready to collect auth & capture payment
                if ($order->getFreeTrialReadyToCollect()) {

                //  If not ready to collect update order status to In Free Trial
                } else {

                }
            }
 */
            //  Update order status to Ready to Collect
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'ready_to_collect')->save();

            //  If order has installment plan check if we can capture payment
            if ($order->getInstallmentTypeId()) {
                $orderId        = $order->getRealOrderId();
                $date           = date("Y-m-d", Mage::getModel('core/date')->timestamp(time())) .' 23:59:59';
                $installment    = Mage::getModel('installment/master')->getCollection();
                $installment->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('installment_master_amount_due_date', array('lteq' => $date))
                    ->addFieldToFilter('installment_master_installment_authorized', 1);

                foreach ($installment as $transaction) {
                    $payment    = $order->getPayment();
                    $amount     = $transaction->getInstallmentMasterAmountDue();

                    $payment->getMethodInstance()
                            ->setStore($order->getStoreId())
                            ->capture($order->getPayment(), $amount);
                    $payment->setIsTransactionClosed(1);
                    $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, sprintf('Captured amount of %s for installment payment #%s', $amount, $transaction->getInstallmentMasterSequenceNumber()));

                    list($customerProfileId, $paymentProfileId, $authorizeCode) = explode('-', $payment->getPoNumber());
                    $currencyCode = Mage::app()->getStore($order->getStoreId())->getCurrentCurrencyCode();

                    if (strtolower($payment->getCcAvsStatus()) == 'ok') {
                        $transaction->setInstallmentMasterInstallmentPaid(true)
                                    ->save();
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'installment_plan_running')->save();
                    } else {
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
                }

            } else {
                $order->getPayment()->capture(null);
                if (strtolower($order->getPayment()->getCcAvsStatus()) == 'ok') {
                    $order->save();
                } else {
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'capture_failed')->save();
                }
            }
        }
    }

    /**
     * This function will get ran daily and will authorize and capture installment payments that are due today
     * @static
     *
     */
    static function dailyCaptureInstallmentPayment()
    {
        $date           = date("Y-m-d", Mage::getModel('core/date')->timestamp(time())) .' 23:59:59';

        $collection = Mage::getModel('installment/master')->getCollection();
        $collection->addFieldToFilter('installment_master_amount_due_date', array('lt' => $date))
                   ->addFieldToFilter('installment_master_installment_paid', 0)
                   ->addFieldToFilter('installment_master_suspend_installment', 0)
                   ->addFieldToFilter('order_id', array('like' => '100%'));

        $collection->getSelect()
            ->join(
                array('order' => $collection->getTable('sales/order')),
                'order.increment_id = main_table.order_id',
                array('order.increment_id')
            )
            ->where('order.status IN ("shipped", "ready_to_collect", "installment_plan_running", "installment_capture_failed", "capture_failed")');

        $currentDate = Mage::getModel('core/date')->gmtDate();
        foreach ($collection as $transaction) {
            $order      = Mage::getModel('sales/order')->loadByIncrementId($transaction->getOrderId());
            $orderId    = $order->getRealOrderId();
            if ($order->getRealOrderId()) {

                $payment    = $order->getPayment();
                $amount     = $transaction->getInstallmentMasterAmountDue();
                $maxAttempt = Mage::getStoreConfig('installment/grace_period_options/grace_period_attempts', $order->getStoreId());
                $attempt    = $transaction->getInstallmentMasterAttemptToAuthorize() + 1;
                if ($attempt <= $maxAttempt) {
                    if ($transaction->getInstallmentMasterInstallmentAuthorized()) {
                        $payment->getMethodInstance()
                            ->setStore($order->getStoreId())
                            ->capture($payment, $amount);
                    } else {
                        $payment->getMethodInstance()
                            ->setStore($order->getStoreId())
                            ->authorize($payment, $amount);

                        $payment->getMethodInstance()
                            ->setStore($order->getStoreId())
                            ->capture($payment, $amount);
                    }
                    $payment->setIsTransactionClosed(1);
                    $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, sprintf('Captured amount of %s for installment payment #%s', $amount, $transaction->getInstallmentMasterSequenceNumber()))->save();

                    //  insert transaction record into installment_transaction
                    list($customerProfileId, $paymentProfileId, $authorizeCode) = explode('-', $payment->getPoNumber());
                    $currencyCode = Mage::app()->getStore($order->getStoreId())->getCurrentCurrencyCode();

                    if ($payment->getCcAvsStatus() == 'Ok') {
                        $transaction->setInstallmentMasterInstallmentAuthorized(true)
                            ->setInstallmentMasterInstallmentPaid(true)
                            ->setInstallmentMasterAttemptToAuthorize($attempt)
                            ->save();

                        $installmentTrans = Mage::getModel('installment/transaction');
                        $installmentTrans->setOrderId($orderId)
                            ->setInstallmentTransactionTransactionSequenceNumber($attempt)
                            ->setInstallmentTransactionPaymentToken($payment->getCcTransId() .'-'. $payment->getPoNumber())
                            ->setInstallmentTransactionSequenceNumber($transaction->getInstallmentMasterSequenceNumber())
                            ->setInstallmentTransactionAuthorizeAmount($amount)
                            ->setInstallmentTransactionAuthorizeAttemptDate($currentDate)
                            ->setInstallmentTransactionAuthorizationCode($authorizeCode)
                            ->setInstallmentTransactionCurrencyCode($currencyCode)
                            ->save();

                        //  if last payment update state and status to complete
                        $installment = Mage::getModel('installment/master')->getCollection();
                        $installment->getSelect()->reset(Zend_Db_Select::COLUMNS)
                            ->columns('MAX(installment_master_sequence_number) as max_sequence_number')
                            ->where("order_id = '". $transaction->getOrderId() ."'")
                            ->group(array('order_id'));
                        $result = $installment->getData();
                        if (!empty($result) && is_array($result) && array_key_exists('max_sequence_number',$result[0])
                            && $result[0]['max_sequence_number'] == $transaction->getInstallmentMasterSequenceNumber())
                        {
                            foreach ($order->getInvoiceCollection() as $invoice) {
                                $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID)->save();
                            }
                        }

                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'installment_plan_running')->save();
                    } else {
                        //  Increase auth attempt for installment master record and update date
                        $date = NGC_Installment_Model_Master::calculateAmountDueDate(
                            Mage::getStoreConfig('installment/grace_period_options/grace_period_days',
                            $order->getStoreId())
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
                            ->setInstallmentTransactionAuthorizeAttemptDate($currentDate)
                            ->setInstallmentTransactionDeclineCode($authorizeCode)
                            ->setInstallmentTransactionDeclineTransationMessage($payment->getDeclineCode())
                            ->setInstallmentTransactionCurrencyCode($currencyCode)
                            ->save();

                        $fmtAmount = $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
                        $message = Mage::helper('sales')->__('Failed to authorized & captured amount of %s for installment payment #%s', $fmtAmount, $transaction->getInstallmentMasterSequenceNumber());
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'installment_capture_failed', $message)->save();

                        if ($transaction->getInstallmentMasterAutoAdjust()) {
                            NGC_Installment_Model_Master::autoAdjustInstallmentPayment($order, $transaction->getInstallmentMasterSequenceNumber());
                        }
                    }
                } else {
                    //  Max auth attempts have been reached, suspend current & future installment payments and update as hard decline
                    NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, $transaction->getInstallmentMasterSequenceNumber());
                }
            } else {
                //  TODO: throw exception about being unable to load an order
            }
        }
    }

    /**
     * Check if the order and installment payment can be processed
     *
     * @param $order
     */
    static protected function _canProcessPayment($order)
    {
        if ($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
            return false;
        }

        switch ($order->getStatus()) {
            case "ready_to_collect":
            case "installment_plan_running":
            case "installment_capture_failed":
                return true;
            default:
                return false;
        }
    }

    static protected function _calculateNextInstallmentDueDate()
    {
        $daysToPayment  = Mage::getStoreConfig('installment/grace_period_options/grace_period_days');
        return NGC_Installment_Model_Master::calculateAmountDueDate($daysToPayment);
    }
}

