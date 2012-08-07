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
class AW_Sarp_Model_Period extends Mage_Core_Model_Abstract
{

    const PERIOD_TYPE_NONE = -1;

    protected $_product;

    protected function _construct()
    {
        $this->_init('sarp/period');
    }

    /**
     * If rule matches date, returns true
     * @param Zend_Date $Date
     * @return bool
     * @deprecated
     * TODO implement
     */
    public function match(Zend_Date $Date)
    {

    }


    /**
     * Returns collection of products with sepcified period
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Product_Collection
     */
    public function getAffectedProducts()
    {
        if (!$this->getId()) {
            throw new Mage_Core_Exception($this->__("Can't load products for period that doesn't exist"));
        }
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
                ->addAttributeToSelect('aw_sarp_is_enabled')
                ->addAttributeToSelect('aw_sarp_period_id')
                ->addFieldToFilter(array(
                                        array('attribute' => 'aw_sarp_enabled', 'gt' => '0')
                                   ))
                ->addFieldToFilter(array(
                                        array('attribute' => 'aw_sarp_period_id', 'eq' => $this->getId())
                                   ));
        return $collection;
    }

    /**
     *
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    public function getAffectedSubscriptions()
    {
        $coll = Mage::getModel('sarp/subscription')->getCollection();
        $coll->getSelect()->where('period_type=?', $this->getId());
        return $coll;
    }

    /**
     * Validates if model is valid
     * @throws AW_Sarp_Exception
     * @return AW_Sarp_Model_Period
     */
    public function validate()
    {
        if (((int)$this->getPeriodValue()) < 1) {
            throw new AW_Sarp_Exception("Period must be more 0");
        }
        return $this;
    }

    /**
     * Returns true if period never expires
     * @return bool
     */
    public function isInfinite()
    {
        return !$this->getExpireValue();
    }

    /**
     * Returns excluded week days
     * @return array
     */
    public function getExcludedWeekdays()
    {
        if (!is_array($this->getData('excluded_weekdays'))) {
            $this->setData('excluded_weekdays', preg_split('/[^0-9]+/', $this->getData('excluded_weekdays'), -1, PREG_SPLIT_NO_EMPTY));
        }
        return $this->getData('excluded_weekdays');
    }

    /**
     * Checks if date is allowed
     * @param Zend_Date $Date
     * @return bool
     */
    public function isAllowedDate(Zend_Date $Date, $Product = null)
    {
        if ($Product && !$Product->getAwSarpHasShipping()) return true;
        if (!$Product || $Product->getAwSarpHasShipping()) {
            $arr = $Date->toArray();
            $weekday = $arr['weekday'] == 7 ? 0 : $arr['weekday'];
            return (array_search($weekday, $this->getExcludedWeekdays()) === false);
        } else {
            return true;
        }
    }


    /**
     * Returns nearest available date starting from today
     * @return Zend_Date
     */
    public function getNearestAvailableDay()
    {
        $Date = new Zend_Date;
        Zend_Date::setOptions(array('extend_month' => true));
        $Date->addDayOfYear((int)$this->getPaymentOffset());
        $exclDays = $this->getExcludedWeekdays();
        Zend_Date::setOptions(array('extend_month' => true)); // Fix Zend_Date::addMonth unexpected result
        if (count($exclDays) == 7) {
            return $Date->addYear(99);
        }
        while (!$this->isAllowedDate($Date)) {
            $Date = $Date->addDayOfYear(1);
        }
        return $Date;
    }


    protected function _afterLoad()
    {

        //	$this->setExcludedWeekdays(explode(',', $this->getExcludedWeekdays()));
        return parent::_afterLoad();
    }

    public function _beforeSave()
    {
        if (is_array($wd = $this->getExcludedWeekdays())) {
            $this->setExcludedWeekdays(implode(',', $wd));
        }
        return parent::_beforeSave();
    }
}
