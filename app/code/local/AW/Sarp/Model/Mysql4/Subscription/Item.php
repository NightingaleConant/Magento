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
class AW_Sarp_Model_Mysql4_Subscription_Item extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('sarp/subscription_item', 'id');
    }

    /**
     * Returns according order item
     * @return Mage_Sales_Model_Order_Item
     */
    public function getOrderItem()
    {
        if (!$this->getData('order_item')) {
            $this->setOrderItem(Mage::getModel('sales/order_item')->load($this->getPrimaryOrderItemId()));
        }
        return $this->getData('order_item');
    }

    /**
     * Returns according order
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->getData('order')) {
            $this->setOrder(Mage::getModel('sales/order')->load($this->getPrimaryOrderId()));
        }
        return $this->getData('order');
    }
}

