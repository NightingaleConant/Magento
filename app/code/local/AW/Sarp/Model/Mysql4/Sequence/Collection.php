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
 */class AW_Sarp_Model_Mysql4_Sequence_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{


    public function _construct()
    {
        parent::_construct();
        $this->_init('sarp/sequence');
    }

    /**
     * Adds filter by date
     * @param mixed $date [optional]
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function addDateFilter($date = null)
    {
        if ($date) {
            $date = Mage::app()->getLocale()->date($date);
        } else {
            $date = Mage::app()->getLocale()->date();
        }
        $this->getSelect()
                ->where('date=?', $date->toString('Y-MM-dd'));
        return $this;
    }

    /**
     * Adds filter by date
     * @param mixed $date [optional]
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function addLessDateFilter($date = null)
    {
        if ($date) {
            $date = Mage::app()->getLocale()->date($date);
        } else {
            $date = Mage::app()->getLocale()->date();
        }
        $this->getSelect()
                ->where('date<=?', $date->toString('Y-MM-dd'));
        return $this;
    }

    /**
     * Adds filter by subscription
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function addSubscriptionFilter(AW_Sarp_Model_Subscription $subscription)
    {
        $this->getSelect()->where('subscription_id=?', $subscription->getId());
        return $this;
    }


    /**
     * Prepares collection for payment selection
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function prepareForPayment()
    {
        return $this->addStatusFilter(AW_Sarp_Model_Sequence::STATUS_PENDING);
    }

    /**
     * Adds filter by status
     * @param int $status
     * @return AW_Sarp_Model_Mysql4_Sequence_Collection
     */
    public function addStatusFilter($status)
    {
        $this->getSelect()->where('status=?', $status);
        return $this;
    }
}