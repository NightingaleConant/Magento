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
 */class AW_Sarp_Model_Mysql4_Subscription_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected $_order_items;

    public function _construct()
    {
        parent::_construct();
        $this->_init('sarp/subscription_item');
    }

    /**
     * Applies filter for subscription
     * @param AW_Sarp_Model_Subscription $Subscription
     * @return AW_Sarp_Model_Mysql4_Subscription_Item_Collection
     */
    public function addSubscriptionFilter(AW_Sarp_Model_Subscription $Subscription)
    {
        if ($Subscription->getId()) {
            $this->getSelect()->where('subscription_id=?', $Subscription->getId());
        } else {
            throw new Mage_Core_Exception("Can't apply subscription filter for non-existing subscription");
        }
        return $this;
    }

    /**
     * Converts to according order items collections
     * @return Sales_Model_Mysql4_Order_Item_Collection
     */
    public function getOrderItems()
    {
        if (!$this->_order_items) {
            $ids = array();
            foreach ($this as $Item) {
                $ids[] = $Item->getPrimaryOrderItemId();
            }
            $this->_order_items = Mage::getModel('sales/order_item')->getCollection();

            $this->_order_items->getSelect()->where('item_id IN (' . implode(",", $ids) . ')');
        }
        return $this->_order_items;
    }

    /**
     * Applies filter for order
     * @param Mage_Sales_Model_Order $Subscription
     * @return AW_Sarp_Model_Mysql4_Subscription_Item_Collection
     */
    public function addOrderFilter(Mage_Sales_Model_Order $Order)
    {
        $this->getSelect()->where('primary_order_id=?', $Order->getId());
        return $this;
    }

}