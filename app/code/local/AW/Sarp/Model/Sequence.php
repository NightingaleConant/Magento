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
class AW_Sarp_Model_Sequence extends Mage_Core_Model_Abstract
{

    protected $_product;
    /** Status 'pending' */
    const STATUS_PENDING = 'pending';
    /** Status 'pending for payment' */
    const STATUS_PENDING_PAYMENT = 'pending_paiment';
    /** Status 'paid' */
    const STATUS_PAYED = 'paid';
    /** Status 'failed' */
    const STATUS_FAILED = 'failed';
    /** Status 'archived' */
    const STATUS_ARCHIVED = 'archived';

    protected function _construct()
    {
        $this->_init('sarp/sequence');
    }

    /**
     * Returns assigned order instance
     * @return Mage_Sales_Order
     */
    public function getOrder()
    {
        if (!$this->getData('order') && $this->getOrderId()) {
            $this->setOrder(
                Mage::getModel('sales/order')->load($this->getOrderId())
            );
        } elseif (!$this->getData('order')) {
            $this->setOrder(
                Mage::getModel('sales/order')
            );
        }
        return $this->getData('order');
    }

    public function _beforeSave()
    {
        if ($this->exists() && !$this->getId()) {
            $this->_dataSaveAllowed = false;
        }
        return parent::_beforeSave();
    }

    /**
     * Tests if this subscription already exists
     * @return bool
     */
    public function exists()
    {
        $collection = $this->getCollection();

        $select = $collection->getSelect();
        $select
                ->where('date=?', $this->getDate())
                ->where('subscription_id=?', $this->getSubscriptionId());
        return !!$collection->count();
    }

    /**
     * Returns subscription object
     * @return AW_Sarp_Model_Subscription
     */
    public function getSubscription()
    {
        return Mage::getModel('sarp/subscription')->load($this->getSubscriptionId());
    }

    /**
     * Returns orders array by subscription
     * @param AW_Sarp_Model_Subscription $subscription
     * @return array
     */
    public function getOrdersBySubscription($subscription)
    {
        $subscriptionId = $subscription->getId();

        $collection = $this->getCollection();

        $select = $collection->getSelect()
                ->where('subscription_id=?', $subscriptionId);

        $orders = array();
        foreach ($collection as $item)
        {
            if ($item->getOrderId())
                $orders[] = Mage::getModel('sales/order')->load($item->getOrderId());
        }

        return $orders;
    }
}