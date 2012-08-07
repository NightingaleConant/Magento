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
 */class AW_Sarp_Model_Payment_Method_Paypal_Direct extends AW_Sarp_Model_Payment_Method_Paypal_Abstract
{
    const XML_PATH_PP_DIRECT_SANDBOX_FLAG = 'paypal/wpp/sandbox_flag';
    const XML_PATH_PP_API_USERNAME = 'paypal/wpp/api_username';
    const XML_PATH_PP_API_PASSWORD = 'paypal/wpp/api_password';
    const XML_PATH_PP_API_SIGNATURE = 'paypal/wpp/api_signature';
    const XML_PATH_PP_NEW_ORDER_STATUS = 'payment/paypal_direct/order_status';

    const WEB_SERVICE_MODEL = 'sarp/web_service_nvp_client';

    const ENDPOINT_SANDBOX = 'sandbox';
    const ENDPOINT_PRODUCTION = '';


    const SUBSC_STATUS_ACTIVE = "Active";
    const SUBSC_STATUS_PENDING = "Pending";
    const SUBSC_STATUS_CANCELLED = "Cancelled";
    const SUBSC_STATUS_SUSPENDED = "Suspended";
    const SUBSC_STATUS_EXPIRED = "Expired";

    public function __construct()
    {
        $this->_initWebService();
    }

    /**
     * Initializes web service instance
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    protected function _initWebService()
    {
        $service = Mage::getModel(self::WEB_SERVICE_MODEL);
        $service
                ->setAPIUsername(Mage::getStoreConfig(self::XML_PATH_PP_API_USERNAME))
                ->setAPIPassword(Mage::getStoreConfig(self::XML_PATH_PP_API_PASSWORD))
                ->setAPISignature(Mage::getStoreConfig(self::XML_PATH_PP_API_SIGNATURE))
                ->setAPIEndpoint(Mage::getStoreConfig(self::XML_PATH_PP_DIRECT_SANDBOX_FLAG) ? self::ENDPOINT_SANDBOX
                                         : self::ENDPOINT_PRODUCTION);
        $this->setWebService($service);

        return $this;
    }

    /**
     * Retrieves info about subscription
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return array
     */
    public function getSubscriptionDetails(AW_Sarp_Model_Subscription $Subscription)
    {
        if (!$this->getData('subscription_details')) {
            $this->getWebService()
                    ->getRequest()
                    ->reset()
                    ->setData(array(
                                   'PROFILEID' => $Subscription->getRealId()
                              ));
            $result = $this->getWebService()->runRequest('GetRecurringPaymentsProfileDetails');
            $this->setData('subscription_details', $result);
        }
        return $this->getData('subscription_details');
    }

    /**
     * This function is run when subscription is created and new order creates
     * @param AW_Sarp_Model_Subscription $Subscription
     * @param Mage_Sales_Model_Order     $Order
     * @param Mage_Sales_Model_Quote     $Quote
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    public function onSubscriptionCreate(AW_Sarp_Model_Subscription $Subscription, Mage_Sales_Model_Order $Order, Mage_Sales_Model_Quote $Quote)
    {
        $this->createSubscription($Subscription, $Order, $Quote);
        return $this;
    }

    /**
     * Cancels subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    public function onSubscriptionCancel(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this->cancelSubscription($Subscription);
    }

    /**
     * Cancels subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    public function onSubscriptionSuspend(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this->suspendSubscription($Subscription);
    }

    /**
     * Cancels subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    public function onSubscriptionReactivate(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this->reactivateSubscription($Subscription);
    }

    /**
     * Checks if payment method can perform transaction now
     * @return bool
     */
    public function isValidForTransaction(AW_Sarp_Model_Sequence $Sequence)
    {
        $Subscription = $this->getSubscription();
        $result = $this->getSubscriptionDetails($Subscription);

        if (isset($result['PROFILEID'])) {
            $nextBillingDate = new Zend_Date($result['NEXTBILLINGDATE'], Zend_Date::ISO_8601);

            /* Check if next billing date is not current sequence item
             * If so, that means that paypal hasn't yet made transaction
             */
            $currentPaymentDate = new Zend_Date($Sequence->getDate(), AW_Sarp_Model_Subscription::DB_DATE_FORMAT);
            if ($nextBillingDate->compare($currentPaymentDate, Zend_Date::DATE_SHORT) <= 0) {
                // Paypal hasn't created transaction yet
                $status = $this->_convertPPStatus($result['STATUS']);

                if ($Subscription->getStatus() != $status) {
                    $Subscription->setStatus($status)->save();
                }
                $this->log("Paypal hasn't run transaction for {$currentPaymentDate->toString()} yet.");
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Processes payment for specified order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function processOrder(Mage_Sales_Model_Order $PrimaryOrder, Mage_Sales_Model_Order $Order = null)
    {
        $Subscription = $this->getSubscription();
        $result = $this->getSubscriptionDetails($Subscription);

        if (isset($result['PROFILEID'])) {
            $status = $this->_convertPPStatus($result['STATUS']);

            if ($Subscription->getStatus() != $status) {
                $Subscription->setStatus($status)->save();
            }
        } else {
            throw new AW_Sarp_Exception("Subscription #{$Subscription->getId()} is not found at Paypal");
        }
    }

    /**
     * Creates subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return
     */
    public function createSubscription(AW_Sarp_Model_Subscription $Subscription, $Order, $Quote)
    {
        $Payment = $Quote->getPayment();

        $ccNumber = Mage::getSingleton('customer/session')->getSarpCcNumber();
        $expirationDate = ($Payment->getMethodInstance()->getInfoInstance()->getCcExpMonth()) . $Payment->getMethodInstance()->getInfoInstance()->getCcExpYear();
        $ccCode = Mage::getSingleton('customer/session')->getSarpCcCid();
        $ccType = $this->_convertCCType($Payment->getMethodInstance()->getInfoInstance()->getCcType());


        $Date = $Subscription->getNextSubscriptionEventDate(new Zend_Date($Subscription->getDateStart()));

        $Date->addDayOfYear(0 - $Subscription->getPeriod()->getPaymentOffset());

        $date_start = date('c', $Date->getTimestamp());

        //$amount = floatval($Subscription->getLastOrder()->getGrandTotal());
        // Due to first price can be different than
        AW_Sarp_Model_Product_Price::$doNotApplyFirstPeriodPrice = true;

        $order = clone $Order;

        $order
                ->setReordered(true);

        //Mage::getSingleton('adminhtml/session_quote')->setQuote(null);
        $no = Mage::getSingleton('sarp/order_create')->reset();
        $no
                ->setRecollect(1);

        $no->getSession()->setUseOldShippingMethod(true);
        $no->setSendConfirmation(false);
        $no->setData('account', $Subscription->getCustomer()->getData());
        $arr = array();

        foreach ($Subscription->getItems() as $Item) {
            $arr[] = $Item->getPrimaryOrderItemId();
        }
        $no->setItemIdFilter($arr);


        /*$Q = $no->getQuote();
        foreach ($Q->getItemsCollection() as $QI) {
            $Q->removeItem($QI->getId());
        }*/

        $no->initFromOrder($order);


        //
        //        $Q = $no->getQuote();
        //        foreach ($Q->getAllAddresses() as $address) {
        //            $address->setCollectShippingRates(true);
        //        }
        //        $no->collectShippingRates();

        $amount = $no->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->getGrandTotal();

        //$no->reset();

        /*$Q = $no->getQuote();
        foreach ($Q->getItemsCollection() as $QI) {
            $Q->removeItem($QI->getId());
        }*/


        //        echo $amount;
        //        global $Fi;
        //        if(!isset($Fi)) $Fi =1;
        //        else $Fi ++;
        //
        //        if($Fi >=2) die;
        //        else return;


        $this->getWebService()
                ->getRequest()
                ->reset()
        /* here ++++ */
                ->setData(array(
                               'CREDITCARDTYPE' => $ccType,
                               'ACCT' => $ccNumber,
                               'CVV2' => $ccCode,
                               'EXPDATE' => $expirationDate,
                               'FIRSTNAME' => $Quote->getBillingAddress()->getFirstname(),
                               'LASTNAME' => $Quote->getBillingAddress()->getLastname(),
                               'CURRENCYCODE' => $Subscription->getLastOrder()->getOrderCurrencyCode(),
                               'PROFILESTARTDATE' => $date_start,
                               'AMT' => $amount,
                               'BILLINGPERIOD' => $this->_convertPeriod($Subscription->getPeriod()),
                               'BILLINGFREQUENCY' => $Subscription->getPeriod()->getPeriodValue(),
                               'DESC' => Mage::helper('sarp')->__("Subscription #%s", $Subscription->getId()),
                               'SUBSCRIBERNAME' => $Quote->getBillingAddress()->getFirstname() . " " . $Quote->getBillingAddress()->getLastname(),
                               'PROFILEREFERENCE' => $Order->getIncrementId() . "-{$Subscription->getId()}",
                               'EMAIL' => $Quote->getCustomerEmail(),
                               'STREET' => $Quote->getBillingAddress()->getStreet(0),
                               'CITY' => $Quote->getBillingAddress()->getCity(),
                               'STATE' => $Quote->getBillingAddress()->getRegion(),
                               'COUNTRYCODE' => $Quote->getBillingAddress()->getCountryId(),
                               'ZIP' => $Quote->getBillingAddress()->getPostcode()
                          ));

        if (!$Subscription->isInfinite()) {
            // Subscription has expire date
            $this->getWebService()
                    ->getRequest()
                    ->setData('TOTALBILLINGCYCLES', $this->_getTotalBillingCycles($Subscription));
        }

        $result = $this->getWebService()->runRequest('CreateRecurringPaymentsProfile');
        if (isset($result['PROFILEID'])) {
            // Profile created
            $profileId = $result['PROFILEID'];
            $Subscription
                    ->setRealId($profileId)
                    ->save();
        } else {
            throw new AW_Sarp_Exception("PayPal recurrent profile creation failed for subscription #{$Subscription->getId()} and order #{$Order->getId()}", print_r($result, 1));
        }
        $this->log(print_r($result, 1));
    }

    /**
     * Cancels subscription at Paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    public function cancelSubscription(AW_Sarp_Model_Subscription $Subscription)
    {
        $result = $this->getSubscriptionDetails($Subscription);
        if ($this->_convertPPStatus($result['STATUS']) == AW_Sarp_Model_Subscription::STATUS_CANCELED) {
            return $this;
        }

        $this->getWebService()
                ->getRequest()
                ->reset()
        /* here ++++ */
                ->setData(array(
                               'PROFILEID' => $Subscription->getRealId(),
                               'ACTION' => 'Cancel'
                          ));

        $result = $this->getWebService()->runRequest('ManageRecurringPaymentsProfileStatus');
        if (isset($result['PROFILEID'])) {
            $this->log("Subscription #{$Subscription->getId()} cancelled at Paypal");
        } else {
            throw new AW_Sarp_Exception("PayPal recurrent profile cancel failed for subscription #{$Subscription->getId()}");
        }
        return $this;
    }

    /**
     * Suspends subscription at Paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    public function suspendSubscription(AW_Sarp_Model_Subscription $Subscription)
    {
        $result = $this->getSubscriptionDetails($Subscription);
        $status = $this->_convertPPStatus($result['STATUS']);
        if (
            $status != AW_Sarp_Model_Subscription::STATUS_ENABLED
        ) {
            return $this;
        }

        $this->getWebService()
                ->getRequest()
                ->reset()
        /* here ++++ */
                ->setData(array(
                               'PROFILEID' => $Subscription->getRealId(),
                               'ACTION' => 'Suspend'
                          ));

        $result = $this->getWebService()->runRequest('ManageRecurringPaymentsProfileStatus');
        if (isset($result['PROFILEID'])) {
            $this->log("Subscription #{$Subscription->getId()} suspended at Paypal");
        } else {
            throw new AW_Sarp_Exception("PayPal recurrent profile suspension failed for subscription #{$Subscription->getId()}");
        }
        return $this;
    }

    /**
     * Reactivates subscription at Paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Paypal_Direct
     */
    public function reactivateSubscription(AW_Sarp_Model_Subscription $Subscription)
    {
        $result = $this->getSubscriptionDetails($Subscription);
        $status = $this->_convertPPStatus($result['STATUS']);
        if (
            $status != AW_Sarp_Model_Subscription::STATUS_SUSPENDED
        ) {
            return $this;
        }
        $this->getWebService()
                ->getRequest()
                ->reset()
        /* here ++++ */
                ->setData(array(
                               'PROFILEID' => $Subscription->getRealId(),
                               'ACTION' => 'Reactivate'
                          ));

        $result = $this->getWebService()->runRequest('ManageRecurringPaymentsProfileStatus');
        if (isset($result['PROFILEID'])) {
            $this->log("Subscription #{$Subscription->getId()} reactivated at Paypal");
        } else {
            throw new AW_Sarp_Exception("PayPal recurrent profile reactivation failed for subscription #{$Subscription->getId()}");
        }
        return $this;
    }

    /**
     * Converts period to Paypal::CreateRecurringPaymentsProfile format
     * @param AW_Sarp_Model_Period $Period
     * @return
     */
    protected function _convertPeriod(AW_Sarp_Model_Period $Period)
    {
        switch ($Period->getPeriodType()) {
            case 'month':
                return 'Month';
            case 'week':
                return 'Week';
            case 'year':
                return 'Year';
        }
        return 'Day';
    }

    /**
     * Converts magento's CC type to PayPal CC type
     * @param string $typeId
     * @return
     */
    protected function _convertCCType($typeId)
    {
        return Mage::getModel('paypal/api_nvp')->getCcTypeName($typeId);
    }

    /**
     * Returns how much occurences will be generated by paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return
     */
    protected function _getTotalBillingCycles(AW_Sarp_Model_Subscription $Subscription)
    {
        if ($Subscription->isInfinite()) {
            return 9999;
        } else {
            // Calculate how much subscription events are generated
            return Mage::getModel('sarp/sequence')->getCollection()->addSubscriptionFilter($Subscription)->count();
        }
    }

    /**
     * Converts PayPal status to Sarp status
     * @param string $status
     * @return string
     */
    protected function _convertPPStatus($status)
    {
        switch ($status) {
            case self::SUBSC_STATUS_CANCELLED:
                return AW_Sarp_Model_Subscription::STATUS_CANCELED;
            case self::SUBSC_STATUS_EXPIRED:
                return AW_Sarp_Model_Subscription::STATUS_EXPIRED;
            case self::SUBSC_STATUS_ACTIVE:
                return AW_Sarp_Model_Subscription::STATUS_ENABLED;
            case self::SUBSC_STATUS_PENDING:
            case self::SUBSC_STATUS_SUSPENDED:
                return AW_Sarp_Model_Subscription::STATUS_SUSPENDED;
        }
    }

}

