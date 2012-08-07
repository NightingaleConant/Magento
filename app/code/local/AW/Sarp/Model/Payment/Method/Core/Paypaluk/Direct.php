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
 */class AW_Sarp_Model_Payment_Method_Core_Paypaluk_Direct extends Mage_PaypalUk_Model_Direct
{
    /** This event is dispatched after authorize on checkout */
    const EVENT_NAME_AUTH_AFTER = "sarp_paypaluk_checkout_authorize_after";
    /** This event is dispatched after capture on checkout */
    const EVENT_NAME_CAPTURE_AFTER = "sarp_paypaluk_checkout_capture_after";

    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            return $this;
        } else {
            $result = parent::validate();
            return $result;
        }
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            $Subscription = AW_Sarp_Model_Subscription::getInstance()->processPayment($payment->getOrder());
            return $this;
        }
        $result = parent::capture($payment, $amount);

        $verify_result = Mage::getModel('sarp/payment_method_paypaluk_direct')->getPnref($payment, $amount, $payment->getOrder()->getBaseCurrencyCode());
        Mage::dispatchEvent(self::EVENT_NAME_CAPTURE_AFTER, array('verification' => $verify_result));

        return $result;
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            $Subscription = AW_Sarp_Model_Subscription::getInstance()->processPayment($payment->getOrder());
            return $this;
        }
        $result = parent::authorize($payment, $amount);

        $verify_result = Mage::getModel('sarp/payment_method_paypaluk_direct')->getPnref($payment, $amount, $payment->getOrder()->getBaseCurrencyCode());
        Mage::dispatchEvent(self::EVENT_NAME_AUTH_AFTER, array('verification' => $verify_result));

        return $result;
    }

}