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
class AW_Sarp_Block_Customer_Subscription_Summary extends Mage_Core_Block_Template
{


    /**
     * Returns current customer
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * Returns current order
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->getData('order')) {
            $this->setOrder($this->getSubscription()->getOrder());
        }
        return $this->getData('order');
    }

    /**
     * Returns current order
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!$this->getData('quote')) {
            $this->setQuote($this->getSubscription()->getQuote());
        }
        return $this->getData('quote');
    }

    public function getPaymentBlock()
    {
        if (!$this->getOrder()->getPayment()) {
            throw new AW_Sarp_Exception("Can't get order for subscription #{$this->getSubscription()->getId()}. DB Corrupt?");
        }
        return $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment());
    }

    public function getSubscriptionStatusLabel()
    {
        return Mage::getModel('sarp/source_subscription_status')->getLabel($this->getSubscription()->getStatus());
    }

}