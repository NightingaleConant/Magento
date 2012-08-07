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
class AW_Sarp_Model_Payment_Method_Paypaluk_Direct extends AW_Sarp_Model_Payment_Method_Paypal_Direct
{

    /** Web service model */
    const WEB_SERVICE_MODEL = 'sarp/web_service_client_paypaluk_nvp';

    /** PayPal UK setting to detect if authorize or capture */
    const XML_PATH_PPUK_PAYMENT_ACTION = 'payment/paypaluk_direct/fields/payment_action';

    /** PayPal UK setting to set order status */
    const XML_PATH_PPUK_ORDER_STATUS = 'payment/paypaluk_direct/order_status';

    /**
     * PNREF received from PayPal
     * @var string
     */
    protected static $_pnref;
    /**
     * AUTHCODE received from PayPal
     * @var string
     */
    protected static $_authcode;

    /**
     * Initializes web service instance
     * @return AW_Sarp_Model_Payment_Method_PaypalUk_Direct
     */
    protected function _initWebService()
    {
        $service = Mage::getModel(self::WEB_SERVICE_MODEL);
        $this->setWebService($service);
        return $this;
    }

    /**
     * This function is run when subscription is created and new order creates
     * @todo Add check for length of subscription period. If it is longer than 12 month, we have to fail subscription due to PayFlow reference transactions limitation
     * @param AW_Sarp_Model_Subscription $Subscription
     * @param Mage_Sales_Model_Order     $Order
     * @param Mage_Sales_Model_Quote     $Quote
     * @return AW_Sarp_Model_Payment_Method_PaypalUk_Direct
     */
    public function onSubscriptionCreate(AW_Sarp_Model_Subscription $Subscription, Mage_Sales_Model_Order $Order, Mage_Sales_Model_Quote $Quote)
    {
        // Get pnref and authcode and save that to payment
        if (!self::$_pnref) {
            throw new AW_Sarp_Exception("No PNRef set for subscription#%s", $Subscription->getId());
        }
        $Subscription
                ->setRealId(self::$_pnref)
                ->setRealPaymentId(self::$_authcode)
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
        $pnref = $this->getSubscription()->getRealId();
        $amt = $Order->getGrandTotal();

        $this->getWebService()
                ->getRequest()
                ->reset()
                ->setData(array(
                               'ORIGID' => $pnref,
                               'AMT' => floatval($amt)
                          ));

        if (strtolower(Mage::getStoreConfig(self::XML_PATH_PPUK_PAYMENT_ACTION) == 'sale')) {
            $result = $this->getWebService()->referenceCaptureAction();

        } else {
            $result = $this->getWebService()->referenceAuthAction();
        }

        if (($result->getResult() . '') == '0') {
            // Payment Succeded
        } else {
            throw new Mage_Core_Exception(Mage::helper('sarp')->__("PayFlow " . Mage::getStoreConfig(self::XML_PATH_PPUK_PAYMENT_ACTION) . " failed:[%s] %s", $result->getResult(), $result->getRespmsg()));
        }

    }

    /**
     * On cancel
     * @param AW_Sarp_Model_Subscription $Subscription
     * @param Mage_Sales_Model_Order     $Order
     * @param Mage_Sales_Model_Quote     $Quote
     * @return AW_Sarp_Model_Payment_Method_Abstract
     */
    public function onSubscriptionCancel(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this;
    }


    /**
     * On suspend
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Abstract
     */
    public function onSubscriptionSuspend(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this;
    }

    /**
     * On reactivatee
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Abstract
     */
    public function onSubscriptionReactivate(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this;
    }


    /**
     * Return CC verification result
     * @param Varien_Object $Payment
     * @param int $AMT
     * @param string $CURR
     * @return Varien_Object
     */
    public function getPnref($Payment, $AMT, $CURR)
    {
        $ccNumber = Mage::getSingleton('customer/session')->getSarpCcNumber();

        $expirationDate = $this->_formatExpDate($Payment->getCcExpMonth(), $Payment->getCcExpYear());

        $ccCode = Mage::getSingleton('customer/session')->getSarpCcCid();

        $this->getWebService()
                ->getRequest()
                ->reset()
                ->setData(array(
                               'ACCT' => $ccNumber,
                               'AMT' => 0,
                               'CVV2' => $ccCode,
                               'EXPDATE' => $expirationDate,
                               'FIRSTNAME' => "",
                               'LASTNAME' => '',
                               'CURRENCY' => $CURR,
                               'COMMENT1' => Mage::helper('sarp')->__("Subscription_PNRef_obtaining"),
                               'COMMENT2' => Mage::helper('sarp')->__("Dont_capture_please")
                          ));
        $result = $this->getWebService()->verifyAccountAction();
        if (($result->getResult() . '') == '0') {
            // Verification succeeded
            return new Varien_Object(array(
                                          'pnref' => $result->getPnref(),
                                          'authcode' => $result->getAuthcode()
                                     ));
        } else {
            throw new AW_Sarp_Exception(Mage::helper('sarp')->__("PayFlow verification failed:[%s] %s", $result->getResult(), $result->getRespmsg()));
        }
    }

    /**
     * Format date
     * @param Zend_Date date
     * @return string
     */
    protected function _formatDate(Zend_Date $Date)
    {
        return date('mdY', $Date->getTimestamp());
    }

    /**
     * Returns how much occurences will be generated by paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return int
     */
    protected function _getTotalBillingCycles(AW_Sarp_Model_Subscription $Subscription)
    {
        if ($Subscription->isInfinite()) {
            return 0;
        } else {
            // Calculate how much subscription events are generated
            return parent::_getTotalBillingCycles($Subscription);
        }
    }

    /**
     * Format expire date to PayFlow format
     * @param int $month
     * @param int $year
     * @return string
     */
    protected function _formatExpDate($month, $year)
    {
        return sprintf("%02s", intval($month)) . substr($year, -2);
    }


    /**
     * Method to save data from PayPal to static variables
     * Is called as event listener
     * @param Varien_Object $event
     */
    public function saveVerificationData($event)
    {
        $V = $event->getVerification();
        self::$_authcode = $V->getAuthcode();
        self::$_pnref = $V->getPnref();
    }


    /**
     * Checks if payment method can perform transaction now
     * @return bool
     */
    public function isValidForTransaction(AW_Sarp_Model_Sequence $Sequence)
    {
        return true;
    }
}
