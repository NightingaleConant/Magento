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
abstract class AW_Sarp_Model_Payment_Method_Abstract extends AW_Core_Object implements AW_Sarp_Model_Payment_Method_Interface
{

    /**
     * Returns order id for transfered order
     * @param mixed $Order
     * @return
     */
    public function getOrderId($Order)
    {
        if (is_int($Order)) {
            return $Order;
        } else {
            return $Order->getId();
        }
    }

    /**
     * Returns order object for transfered order
     * @param mixed $Order
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($Order)
    {
        if (is_int($Order)) {
            return Mage::getModel('sales/order')->load($Order);
        } else {
            return $Order;
        }
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
        return $this;
    }

    /**
     * This function is run when subscription is created and new order creates
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
     * Cancels subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Abstract
     */
    public function onSubscriptionSuspend(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this;
    }

    /**
     * Cancels subscription at paypal
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Payment_Method_Abstract
     */
    public function onSubscriptionReactivate(AW_Sarp_Model_Subscription $Subscription)
    {
        return $this;
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