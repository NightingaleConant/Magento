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
 */class AW_Sarp_Model_Catalog_Product extends Mage_Catalog_Model_Product
{
    /**
     * Check is product configurable
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return ($this->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ||
                $this->getTypeId() == 'subscription_configurable');
    }

    public function isGrouped()
    {
        return ($this->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
                $this->getTypeId() == AW_Sarp_Model_Product_Type_Grouped_Subscription::TYPE_CODE);
    }

    public function getMinimalPrice()
    {
        if (Mage::getStoreConfigFlag('catalog/frontend/flat_catalog_product')) {
            if ($this->getTypeId() == AW_Sarp_Model_Product_Type_Grouped_Subscription::TYPE_CODE) {
                $associatedProducts = Mage::getModel('catalog/product_type_grouped')->getAssociatedProducts($this);
                $periods = explode(',', $this->getAwSarpPeriod());
                $prices = array();

                if ($associatedProducts) {
                    foreach ($associatedProducts as $item) {
                        $product = Mage::getModel('catalog/product')->load($item->getId($item->getId()));
                        if (reset($periods) > 0)
                            array_push($prices, $product->getAwSarpSubscriptionPrice());
                        else
                            array_push($prices, $product->getPrice());
                    }
                    return min($prices);
                }
            }
        }
        if ($this->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $associatedProducts = Mage::getModel('catalog/product_type_grouped')->getAssociatedProducts($this);
            $prices = array();

            foreach ($associatedProducts as $item)
                array_push($prices, $item->getPrice());

            return min($prices);

        }

        if (parent::getMinimalPrice() == null && $this->getTypeId() == AW_Sarp_Model_Product_Type_Grouped_Subscription::TYPE_CODE) {
            $associatedProducts = Mage::getModel('catalog/product_type_grouped')->getAssociatedProducts($this);
            $periods = explode(',', $this->getAwSarpPeriod());
            $prices = array();

            if ($associatedProducts) {
                foreach ($associatedProducts as $item) {
                    if (reset($periods) > 0)
                        array_push($prices, $item->getAwSarpSubscriptionPrice());
                    else
                        array_push($prices, $item->getPrice());
                }
                $minPrice = min($prices);
                $minPrice = number_format($minPrice, 4);
                return $minPrice;
            }
        }
        else
            return parent::getMinimalPrice();
    }

}
