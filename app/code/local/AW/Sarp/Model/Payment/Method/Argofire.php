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
 */class AW_Sarp_Model_Payment_Method_Argofire extends AW_Sarp_Model_Payment_Method_Abstract
{

    /** Web service model */
    const WEB_SERVICE_MODEL = 'sarp/web_service_client_argofire';
    /** New order status */
    const XML_PATH_ARGOFIRE_ORDER_STATUS = 'payment/argofire/order_status';

    public function __construct()
    {
        $this->_initWebService();
    }

    /**
     * This function is run when subscription is created and new order creates
     * @param AW_Sarp_Model_Subscription $Subscription
     * @param Mage_Sales_Model_Order     $Order
     * @param Mage_Sales_Model_Quote     $Quote
     * @return AW_Sarp_Model_Payment_Method_Argofire
     */
    public function onSubscriptionCreate(AW_Sarp_Model_Subscription $Subscription, Mage_Sales_Model_Order $Order, Mage_Sales_Model_Quote $Quote)
    {
        $this->createSubscription($Subscription, $Order, $Quote);
        return $this;
    }

    /**
     * Creates subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return
     */
    public function createSubscription(AW_Sarp_Model_Subscription $Subscription, $Order, $Quote)
    {

        // 1. Create subscription profile with 0 period
        // 2. Save CCInfoKey and CustomerInfoKey

        $Payment = $Quote->getPayment();

        // Credit Card number
        $ccNumber = Mage::getSingleton('customer/session')->getSarpCcNumber();

        // CVV
        //$ccCode = Mage::getSingleton('customer/session')->getSarpCcCid();

        // Glue month and year
        $expirationDate = sprintf("%02s", $Payment->getMethodInstance()->getInfoInstance()->getCcExpMonth()) . $Payment->getMethodInstance()->getInfoInstance()->getCcExpYear();
        // Customer name
        $customerName = $Quote->getBillingAddress()->getFirstname() . " " . $Quote->getBillingAddress()->getLastname();

        // Calculate start date
        $Date = $Subscription->getNextSubscriptionEventDate(new Zend_Date($Subscription->getDateStart()));
        $Date->addDayOfYear(0 - $Subscription->getPeriod()->getPaymentOffset());
        $date_start = $this->getWebService()->formatDate($Date);
        // Total
        $total = floatval($Subscription->getLastOrder()->getGrandTotal());


        $this->getWebService()
                ->getRequest()
                ->reset()
                ->setData(
            array(
                 'CcAccountNum' => $ccNumber,
                 'CcExpDate' => $expirationDate,
                 'FirstName' => $Quote->getBillingAddress()->getFirstname(),
                 'CustomerName' => $customerName,
                 'CustomerID' => $Quote->getCustomerEmail(),
                 'LastName' => $Quote->getBillingAddress()->getLastname(),
                 'BillingInterval' => "0",
                 'BillingPeriod' => "DAY",
                 'StartDate' => $date_start,
                 'EndDate' => $date_start,
                 'BillAmt' => $total,
                 'TotalAmt' => $total,
                 'ContractName' => Mage::helper('sarp')->__("Subscription #%s", $Subscription->getId()),
                 'ContractID' => $Subscription->getId()
            )
        );


        try {
            $result = $this->getWebService()->addRecurringCreditCard();
            $CCInfoKey = $result->CcInfoKey;
            $CustomerKey = $result->CustomerKey;

            /**
             * @todo Add and save ContractKey
             */

            $Subscription
                    ->setRealId($CustomerKey)
                    ->setRealPaymentId($CCInfoKey)
                    ->save();

        } catch (Exception $E) {
            throw new AW_Sarp_Exception("Argofire recurrent profile creation failed for subscription #{$Subscription->getId()} "
                                        . "and order #{$Order->getId()}",
                print_r($result, 1)
            );
        }
        $this->log(print_r($result, 1));
    }

    /**
     * Processes payment for specified order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function processOrder(Mage_Sales_Model_Order $PrimaryOrder, Mage_Sales_Model_Order $Order = null)
    {

        $amt = $Order->getGrandTotal();
        $inv = $Order->getIncrementId();

        $this->getWebService()
                ->getRequest()
                ->reset()
                ->setData(array(
                               'CcInfoKey' => $this->getSubscription()->getRealPaymentId(),
                               'Amount' => $amt,
                               'InvNum' => $inv,
                               'ExtData' => ''
                          ));

        $result = $this->getWebService()->processCreditCard();

    }


    protected function _convertPeriod($interval)
    {
        return strtoupper($interval);
    }


    /**
     * Initializes web service instance
     * @return AW_Sarp_Model_Payment_Method_Argofire
     */
    protected function _initWebService()
    {
        $service = Mage::getModel(self::WEB_SERVICE_MODEL);
        $this->setWebService($service);
        return $this;
    }

}
