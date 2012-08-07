<?php

class NGC_Installment_Model_Master extends Mage_Core_Model_Abstract
{
    const INSTALLMENT_MASTER_SUSPENDED_REASON_MAX_LENGTH = 255;

    public function __construct()
    {
        $this->_init('installment/master');
    }

    public function generateInstallmentMasterTransactions($order)
    {
        $installmentTypeId = $order->getInstallmentTypeId();
        if ($installmentTypeId > 0) {
            $type = Mage::getModel('installment/type')->load($installmentTypeId);
            if ($type->getId()) {
                $orderId        = $order->getRealOrderId();
                $orderTotal     = $order->getGrandTotal() - $order->getShippingAmount();
                $currencyCode   = $order->getBaseCurrencyCode();

                //  Retrieve type and definitions
                $definitions = Mage::getModel('installment/definition')->getCollection();
                $definitions->addFieldToFilter('installment_type_id', $type->getInstallmentTypeId());

                // Calculate amount due, amount due date,
                foreach ($definitions as $definition) {
                    $daysToPayment  = $definition->getInstallmentDefinitionDaysToPayment();
                    $valueTypeId    = $definition->getInstallmentDefinitionRecordType();
                    $value          = $definition->getInstallmentDefinitionValueForType();

                    //  Calculate due date
                    $amountDueDate  = $this->calculateAmountDueDate($daysToPayment);

                    //  Calculate amount due
                    $amountDue      = $this->_calculateAmountDue($order->getStoreId(), $orderTotal, $value, $valueTypeId);
                    if ($valueTypeId == 1) {
                        $orderTotal     = $orderTotal - $amountDue;
                    }

                    $master = Mage::getModel('installment/master');
                    $master->setOrderId($orderId)
                           ->setInstallmentMasterSequenceNumber($definition->getInstallmentDefinitionSequenceNumber())
                           ->setInstallmentMasterAmountDue($amountDue)
                           ->setInstallmentMasterAmountDueDate($amountDueDate)
                           ->setInstallmentMasterCurrencyCode($currencyCode)
                           ->setInstallmentMasterAutoAdjust($type->getInstallmentTypeAutoAdjust())
                           ->save();
                }
            } else {
                // If we get here it means that a value was selected but the installment plan could not be found
                $message = Mage::helper('installment')->__('There has been an error processing your order. Installment plan does not exist.');
                Mage::throwException($message);
            }
        }
    }

    protected function _calculateAmountDue($storeId, $orderTotal, $value, $valueTypeId)
    {
        if ($valueTypeId == 1) {
            return $value;
        } else {
            $total = ($orderTotal * $value) / 100;
            $total = Mage::app()->getStore($storeId)->roundPrice($total);
            return max($total, 0);
        }
    }

    /**
     * Auto adjust future installment payment due dates for all sequence numbers great than the one passed to the method
     *
     * @static
     * @param $order
     * @param $seqNum
     */
    static public function autoAdjustInstallmentPayment($order, $seqNum)
    {
        $collection = Mage::getModel('installment/master')->getCollection();
        $collection->addFieldToFilter('order_id', $order->getRealOrderId())
            ->addfieldToFilter('installment_master_sequence_number', array('gt' => $seqNum));

        $gracePeriod = Mage::getStoreConfig('installment/grace_period_options/grace_period_days', $order->getStoreId());
        $interval    = ($gracePeriod == 1) ? 'day' : 'days';

        foreach ($collection as $transaction)
        {
            $dueDate = $transaction->getInstallmentMasterAmountDueDate();
            $dueDate = strtotime(date("Y-m-d h:m:s", strtotime($dueDate)) . " ". $gracePeriod ." ". $interval);
            $dueDate = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp($dueDate));

            $transaction->setInstallmentMasterAmountDueDate($dueDate)
                        ->save();
        }
    }

    static public function calculateAmountDueDate($daysToPayment)
    {
        if ($daysToPayment == 0) {
            $time = time();
        } else {
            $interval = ($daysToPayment == 1) ? 'day' : 'days';
            $time = strtotime('+'. $daysToPayment .' '. $interval);
        }

        return date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp($time));
    }

    /**
     * Suspend the current and future installment payment
     *
     * @static
     * @param $orderId
     * @param $seqNumber
     */
    static public function suspendInstallmentPayment($orderId, $seqNumber = 1, $suspendedReason = 'Hard Decline')
    {
        $date       =  date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $collection = Mage::getModel('installment/master')->getCollection();
        $collection->addFieldToFilter('order_id', $orderId)
                   ->addfieldToFilter('installment_master_sequence_number', array('gteq' => $seqNumber));

        foreach($collection as $installment)
        {
            $result = $installment->setInstallmentMasterSuspendInstallment(true)
                        ->setInstallmentMasterSuspendedDate($date)
                        ->setInstallmentMasterSuspendedReason($suspendedReason)
                        ->save();
        }

        return true;
    }

    static public function refundInstallmentPayment($observer)
    {
        $creditMemo = $observer->getCreditmemo();
        $orderId    = $creditMemo->getOrder()->getRealOrderId();

        //  Check if refunding entire order
        if ($creditMemo->getTotalQty() == $creditMemo->getOrder()->getTotalItemCount()) {
            if (!self::suspendInstallmentPayment($orderId, 1, 'Order was refunded')) {
                return Mage::throwException('Unable to suspend future installment payments');
            }

            return self::_refundCompleteOrder($creditMemo->getOrder());
        } else {
            if (!self::recalculateInstallmentPayment($orderId)) {
                return Mage::throwException('Unable to recalculate future installment payments');
            }
            return self::_refundPartialOrder($observer);
        }
    }

    static protected function _refundCompleteOrder($order)
    {
        $orderId = $order->getRealOrderId();

        if (!NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, 1, 'Order was refunded')) {
            return Mage::throwException('Unable to suspend future installment payments');
        }

        $payment    = $order->getPayment();
        $collection = Mage::getModel('installment/master')->getCollection();
        $collection->addFieldToFilter('order_id', $orderId)
                   ->addFieldToFilter('installment_master_installment_authorized', 1)
                   ->addFieldToFilter('installment_master_installment_paid', 1);

        $totalRefunded = 0;
        foreach ($collection as $installment) {
            if ($installment->getInstallmentMasterAmountDue()) {
                $transaction = Mage::getModel('installment/transaction')->getCollection();
                $transaction->addFieldToFilter('order_id', $orderId)
                            ->addFieldToFilter('installment_transaction_sequence_number', $installment->getInstallmentMasterSequenceNumber())
                            ->addFieldToFilter('installment_transaction_decline_code', array('null' => true));

                foreach($transaction as $record) {
                    $aTrans[] = $record;
                }

                $transaction = array_pop($aTrans);

                list($ccTransId, $customerProfileId, $paymentProfileId, $authorizeCode) = explode('-', $transaction->getInstallmentTransactionPaymentToken());

                $payment->setParentTransactionId($ccTransId);

                $transId = "{$ccTransId}-". Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;
                $payment->setTransactionId($transId);
                $payment->setCcTransId($transId);
                $payment->setIsTransactionClosed(1);

                $amount = $payment->getOrder()->getBaseCurrency()->formatTxt($installment->getInstallmentMasterAmountDue());
                $payment->getMethodInstance()->setStore($order->getStoreId())->refund($payment, $installment->getInstallmentMasterAmountDue());
                $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $payment, false, sprintf('Refunded amount of %s for installment payment #%s', $amount, $installment->getInstallmentMasterSequenceNumber()))->save();

                $totalRefunded += $installment->getInstallmentMasterAmountDue();

                $order->setTotalRefunded($totalRefunded);

                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Refunded amount of '. $amount)->save();
            }
        }
    }

    static protected function _refundPartialOrder($observer)
    {
        $creditMemo     = $observer->getCreditmemo();
        $order          = $creditMemo->getOrder();
        $payment        = $order->getPayment();

        $orderId        = $order->getRealOrderId();
        $newGrandTotal  = $order->getGrandTotal();
        $refunded       = 0;
        $totalRefund    = $creditMemo->getGrandTotal();
        $totalPaid      = self::getTotalPaid($orderId);

        $master = Mage::getModel('installment/master')->getCollection();
        $master->addFieldToFilter('order_id', $orderId);

        if (self::isPlanComplete($orderId)) {
            $payment->getMethodInstance()->setStore($order->getStoreId())->refund($order->getPayment(), $totalRefund);
            $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $payment, false, sprintf('Refunded amount of %s', $totalRefund))->save();
        } else {

            //  Customer has paid equal to the new grand total. Suspend future installment payments and close invoice
            if ($totalPaid == $newGrandTotal) {
                //  Suspend all future installments
                if (!NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, 1, 'Order was refunded')) {
                    return Mage::throwException('Unable to suspend future installment payments');
                }

                //  Close invoice
                foreach ($order->getInvoiceCollection() as $invoice) {
                    $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID)->save();
                }


            //  Customer has paid less than the new grand total. Update future installments to take into
            //  account the new order grand total
            } elseif ($totalPaid <= $newGrandTotal) {
                //  Iterate installment payments and calculate the remaining amount due.
                $planTotal = $totalPaid;
                foreach ($master as $installment) {
                    if (!$installment->getInstallmentMasterInstallmentPaid()) {
                        $newTotal = $planTotal + $installment->getInstallmentMasterAmountDue();

                        //  If the $newTotal plan total is less than the new grand total refund continue to next transaction
                        if ($newTotal < $newGrandTotal) {
                            $planTotal = $newTotal;
                            continue;

                        //  If the $newTotal plan total is equal to the new grand total then suspend all future installments
                        } elseif ($newTotal == $newGrandTotal) {
                            $nextSeqNum = $installment->getInstallmentMasterSequenceNumber() + 1;
                            if (!NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, $nextSeqNum, 'Order was refunded')) {
                                return Mage::throwException('Unable to suspend future installment payments');
                            }

                        //  The $newTotal plan total is greater than the new grand total. Calculate the difference,
                        //  decrease the installment payment by the difference, and suspend future payments
                        } else {
                            $totalDiff  = $newTotal - $newGrandTotal;

                            $newInstallmentTotal = $installment->getInstallmentMasterAmountDue() - $totalDiff;
                            $installment->setInstallmentMasterAmountDue($newInstallmentTotal)
                                        ->save();

                            $nextSeqNum = $installment->getInstallmentMasterSequenceNumber() + 1;
                            if (!NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, $nextSeqNum, 'Order was refunded')) {
                                return Mage::throwException('Unable to suspend future installment payments');
                            }
                        }
                    }
                }

            //  Customer has paid more than the new order grand total. Refund the difference and suspend any future
            //  installment payments, close invoice, and mark order as complete
            } elseif ($totalPaid > $newGrandTotal) {

                $totalDiff = $totalPaid - $newGrandTotal;
                $payment->getMethodInstance()->setStore($order->getStoreId())->refund($order->getPayment(), $totalDiff);
                $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $payment, false, sprintf('Refunded amount of %s', $totalRefund))->save();

                $master = Mage::getModel('installment/master')->getCollection();
                $master->addFieldToFilter('installment_master_installment_paided', 0)
                       ->addFieldToFilter('installment_master_installment_authorized', 0);

                foreach ($master as $installment) {
                    $nextSeqNum = $installment->getInstallmentMasterSequenceNumber();
                    break;
                }

                //  Suspend all future installments
                if (!NGC_Installment_Model_Master::suspendInstallmentPayment($orderId, $nextSeqNum, 'Order was refunded')) {
                    return Mage::throwException('Unable to suspend future installment payments');
                }

                //  Close invoice
                foreach ($order->getInvoiceCollection() as $invoice) {
                    $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID)->save();
                }

            }
        }
    }

    /**
     *  Check if all installment payments have been made. If all payments have been made returns true else false
     *
     * @static
     * @param $orderId
     * @return bool
     */
    static public function isPlanComplete($orderId)
    {
        $master = Mage::getModel('installment/master')->getCollection();
        $master->addFieldToFilter('order_id', $orderId);
        foreach ($master as $installment) {
            if ($installment->getInstallmentMasterInstallmentPaid()) {
                continue;
            }
            return false;
        }
        return true;
    }

    /**
     * Returns the current total amount paid for an installment plan
     * @static
     * @param $orderId
     * @return mixed
     */
    static public function getTotalPaid($orderId)
    {
        $master = Mage::getModel('installment/master')->getCollection();
        $master->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns('SUM(installment_master_amount_due) as total_amount')
            ->where("order_id = '". $orderId ."' AND installment_master_installment_paid = 1 AND installment_master_installment_authorized = 1");
        return array_shift($master->getColumnValues('total_amount'));
    }

    static public function recalculateInstallmentPayment($order)
    {
        // Figure out how much has already been paid

        // Regenerate the installment payments

        return true;
    }
}

