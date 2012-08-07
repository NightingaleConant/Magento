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
 */class AW_Sarp_Model_Mysql4_Period_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected $_hasProductFilterApplied = false;
    protected $_product = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('sarp/period');

    }

    protected function _initSelect()
    {
        $res = parent::_initSelect();
        $this->getSelect()->order('sort_order', 'ASC');
        return $res;
    }

    /**
     * Adds filter by product to collection
     * @param Mage_Catalog_Model_Product $Product
     * @return AW_Sarp_Model_Mysql4_Period_Collection
     */
    public function addProductFilter(Mage_Catalog_Model_Product $Product)
    {
        $this->_hasProductFilterApplied = 1;
        $this->_product = $Product;
        $periodIds = $Product->getAwSarpPeriod();
        if ($periodIds) {
            $condition = "id IN (" . ($periodIds) . ")";
        } else {
            $condition = "0";
        }

        $this->getSelect()->where($condition);
        return $this;
    }

    /**
     * Detects if assigned product has "No subscription" option
     * @return bool
     */
    public function hasNoSubscriptionOption()
    {
        if ($Product = $this->getProduct()) {
            $opts = $Product->getAwSarpPeriod();
            if ((strpos($opts, '-1') !== false) || !trim($opts)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Says wether product filter has been applied or not
     * @return bool
     */
    public function hasProductFilterApplied()
    {
        return $this->_hasProductFilterApplied;
    }

    public function getProduct()
    {
        return $this->_product;
    }

}