<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Sarp_Model_Subscription_Flat extends Mage_Core_Model_Abstract
{

    const DB_DELIMITER = "\r\n";
    const DB_EXPLODE_RE = "/[\r\n]+/";

    protected function _construct()
    {
        $this->_init('sarp/subscription_flat');
    }

    /**
     * Converts subscription products to text
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return
     */
    protected function _convertProductsText(AW_Sarp_Model_Subscription $Subscription)
    {
        $out = array();
        foreach ($Subscription->getItems() as $Item) {
            $out[] = $Item->getOrderItem()->getName() . " (" . intval($Item->getOrderItem()->getQtyOrdered()) . ")";
        }
        return implode(self::DB_DELIMITER, $out);
    }

    /**
     * Converts subscription products to sku text
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return
     */
    protected function _convertProductsSku(AW_Sarp_Model_Subscription $Subscription)
    {
        $out = array();
        foreach ($Subscription->getItems() as $Item) {
            $out[] = $Item->getOrderItem()->getSku();
        }
        return implode(self::DB_DELIMITER, $out);
    }

    /**
     * Presets flat data from subscription
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Subscription_Flat
     */
    public function setSubscription(AW_Sarp_Model_Subscription $Subscription)
    {

        Zend_Date::setOptions(array('extend_month' => true)); // Fix Zend_Date::addMonth unexpected result

        if (!$Subscription->isInfinite()) {
            $expireDate = $Subscription->getDateExpire()->toString(AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
        } else {
            $expireDate = null;
        }
        if ($Subscription->getIsNew()) {
            $lastOrderAmount = 0;
            $quote = clone $Subscription->getQuote();
            foreach ($quote->getItemsCollection() as $Item) {
                if (Mage::helper('sarp')->isSubscriptionType($Item)) {
                    $quote->removeItem($Item->getId());
                }
                $quote->getShippingAddress()->setCollectShippingRates(true)->collectTotals();
                $quote->collectTotals();
            }
            $lastOrderAmount = $Subscription->getQuote()->getGrandTotal();
            $virtual = $quote->getIsVirtual();

            unset($quote);
        } else {
            $lastOrderAmount = $Subscription->getLastOrder()->getGrandTotal();
            $virtual = $Subscription->getLastOrder()->getIsVirtual();
        }

        if ($Subscription->isActive()) {
            $paymentOffset = $Subscription->getPeriod()->getPaymentOffset();
            // Get next payment date
            if (!$Subscription->getLastPaidDate()) {

                $nextPaymentDate = $Subscription->getLastOrder()->getCreatedAtStoreDate();

                $nextPaymentDate = $Subscription->getNextSubscriptionEventDate($Subscription->getDateStart());

                $nextDeliveryDate = clone $Subscription->getDateStart();
                $nextDeliveryDate->addDayOfYear(0 + floatval($paymentOffset));
            } else {
                $nextPaymentDate = $Subscription->getNextSubscriptionEventDate();
            }
            if ($paymentOffset) {

                if (!$Subscription->getLastPaidDate()) {
                    // No payments made yet
                    $lastOrderDate = clone $Subscription->getDateStart();
                    $lastOrderDate->addDayOfYear(0 - floatval($paymentOffset));

                } else {
                    $lastOrderDate = $Subscription->getLastOrder()->getCreatedAtStoreDate();
                }

                $probablyDeliveryDate = clone $lastOrderDate;
                $probablyDeliveryDate = $probablyDeliveryDate->addDayOfYear(floatval($paymentOffset));

                if (($probablyDeliveryDate->compare(new Zend_Date, Zend_Date::DATE_SHORT) > 0)) {
                    $nextDeliveryDate = clone $lastOrderDate;
                }
                $nextPaymentDate->addDayOfYear(0 - floatval($paymentOffset));
            }
            if (!isset($nextDeliveryDate)) {
                $nextDeliveryDate = clone $nextPaymentDate;

            }

            $nextDeliveryDate = $nextDeliveryDate->addDayOfYear(floatval($paymentOffset))->toString(AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
            $nextPaymentDate = $nextPaymentDate->toString(AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
        } else {
            // Drop next payment date if subscription is suspended
            $nextDeliveryDate = $nextPaymentDate = null;
        }

        $this->setSubscriptionId($Subscription->getId())
                ->setCustomerName($Subscription->getCustomer()->getName())
                ->setCustomerEmail($Subscription->getCustomer()->getEmail())
                ->setFlatLastOrderStatus($Subscription->getLastOrder()->getStatus())
                ->setFlatLastOrderAmount($lastOrderAmount)
                ->setFlatLastOrderCurrencyCode($Subscription->getLastOrder()->getOrderCurrencyCode())
                ->setFlatDateExpire($expireDate)
                ->setHasShipping(1 - $virtual)
                ->setFlatNextPaymentDate($nextPaymentDate)
                ->setFlatNextDeliveryDate($nextDeliveryDate)
                ->setProductsText($this->_convertProductsText($Subscription))
                ->setProductsSku($this->_convertProductsSku($Subscription));

        $Subscription
                ->setCustomerName($Subscription->getCustomer()->getName())
                ->setCustomerEmail($Subscription->getCustomer()->getEmail())
                ->setFlatLastOrderStatus($Subscription->getLastOrder()->getStatus())
                ->setFlatLastOrderAmount($lastOrderAmount)
                ->setFlatLastOrderCurrencyCode($Subscription->getLastOrder()->getOrderCurrencyCode())
                ->setFlatDateExpire($expireDate)
                ->setHasShipping(1 - $virtual)
                ->setFlatNextPaymentDate($nextPaymentDate)
                ->setFlatNextDeliveryDate($nextDeliveryDate)
                ->setProductsText($this->_convertProductsText($Subscription))
                ->setProductsSku($this->_convertProductsSku($Subscription));
        return $this;
    }


}
