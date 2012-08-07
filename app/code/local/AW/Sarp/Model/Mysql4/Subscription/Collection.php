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
 */class AW_Sarp_Model_Mysql4_Subscription_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('sarp/subscription');
    }


    /**
     * Adds filter by customer
     * @param mixed $customer
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function addCustomerFilter($customer)
    {
        if (!is_int($customer)) {
            $id = $customer->getId();
        } else {
            $id = $customer;
        }
        $this->getSelect()->where('customer_id=?', $id);
        return $this;
    }

    /**
     * Adds filter by product/product SKU
     * @param mixed $product
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function addProductFilter($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $id = $product->getSku();
        } else {
            $id = $product;
        }
        $this->getSelect()->where('product_sku=?', $id);

        return $this;
    }

    /**
     * Adds only active subscriptions filter to collection
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function addActiveFilter()
    {
        $this->getSelect()->where('status=?', AW_Sarp_Model_Subscription::STATUS_ENABLED);
        return $this;
    }

    /**
     * Adds filter for all subscriptions that matching $date
     * @param object $date [optional]
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function addDateFilter($date = null)
    {
        $sequence = Mage::getModel('sarp/sequence')->getCollection()->prepareForPayment()->addDateFilter($date);
        $in = array();
        foreach ($sequence as $record) {
            $in[] = $record->getSubscriptionId();
        }
        if (!sizeof($in)) {
            // No subscriptions are present
            $in[] = -1;
        }
        $this->getSelect()->where('id IN (' . implode(',', $in) . ')');
        return $this;
    }

    /**
     * Adds filter for all subscriptions that matching today
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function addLessTodayFilter($date = null)
    {
        $sequence = Mage::getModel('sarp/sequence')->getCollection()->prepareForPayment()->addLessDateFilter($date);
        $in = array();
        foreach ($sequence as $record) {
            $in[] = $record->getSubscriptionId();
        }
        if (!sizeof($in)) {
            // No subscriptions are present
            $in[] = -1;
        }
        $this->getSelect()->where('id IN (' . implode(',', $in) . ')');
        return $this;
    }

    /**
     * Adds filter for all subscriptions that matching today
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function addTodayFilter()
    {
        return $this->addDateFilter();
    }


    /**
     * Attaches flat data to collection
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    protected function _initselect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(array('f' => Mage::getResourceModel('sarp/subscription_flat')->getMainTable()),
                                     'f.subscription_id=id'
        );

        return $this;
    }

}