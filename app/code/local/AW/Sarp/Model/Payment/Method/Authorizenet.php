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
 */class AW_Sarp_Model_Payment_Method_Authorizenet extends AW_Sarp_Model_Payment_Method_Abstract
{

    const XML_PATH_AUTHORIZENET_API_LOGIN_ID = 'payment/authorizenet/login';
    const XML_PATH_AUTHORIZENET_TEST_MODE = 'payment/authorizenet/test';
    const XML_PATH_AUTHORIZENET_DEBUG = 'payment/authorizenet/debug';
    const XML_PATH_AUTHORIZENET_TRANSACTION_KEY = 'payment/authorizenet/trans_key';
    const XML_PATH_AUTHORIZENET_PAYMENT_ACTION = 'payment/authorizenet/payment_action';
    const XML_PATH_AUTHORIZENET_ORDER_STATUS = 'payment/authorizenet/order_status';
    const XML_PATH_AUTHORIZENET_SOAP_TEST = 'payment/authorizenet/soap_test';

    const WEB_SERVICE_MODEL = 'sarp/web_service_client_authorizenet';

    public function __construct()
    {
        $this->_initWebService();
    }

    /**
     * Initializes web service instance
     * @return AW_Sarp_Model_Payment_Method_Authorizenet
     */
    protected function _initWebService()
    {
        $service = Mage::getModel(self::WEB_SERVICE_MODEL);
        $this->setWebService($service);
        return $this;
    }

    /**
     * This function is run when subscription is created and new order creates
     * @param AW_Sarp_Model_Subscription $Subscription
     * @param Mage_Sales_Model_Order     $Order
     * @param Mage_Sales_Model_Quote     $Quote
     * @return AW_Sarp_Model_Payment_Method_Abstract
     */
    public function onSubscriptionCreate(AW_Sarp_Model_Subscription $Subscription, Mage_Sales_Model_Order $Order, Mage_Sales_Model_Quote $Quote)
    {
        $this->createSubscription($Subscription, $Order, $Quote);
        return $this;
    }

    public function createSubscription($Subscription, $Order, $Quote)
    {
        $this->getWebService()
                ->setSubscriptionName(Mage::helper('sarp')->__('Subscription #%s', $Subscription->getId()))
                ->setSubscription($Subscription)
                ->setPayment($Quote->getPayment());
        $CIMInfo = $this->getWebService()->createCIMAccount();

        $CIMId = $this->getWebService()->getCIMCustomerProfileId($CIMInfo);
        $CIMPaymentId = $this->getWebService()->getCIMCustomerPaymentProfileId($CIMInfo);

        $Subscription
                ->setRealId($CIMId)
                ->setRealPaymentId($CIMPaymentId)
                ->save();
        return $this;

    }

    /**
     * Processes payment for specified order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function processOrder(Mage_Sales_Model_Order $PrimaryOrder, Mage_Sales_Model_Order $Order = null)
    {
        if ($Order->getBaseGrandTotal() > 0) {
            $result = $this->getWebService()
                    ->setSubscription($this->getSubscription())
                    ->setOrder($Order)
                    ->createTransaction();
            $ccTransId = @$result->transactionId;
            $Order->getPayment()->setCcTransId($ccTransId);
        }
    }
}