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
class AW_Sarp_Block_Product_Price extends Mage_Catalog_Block_Product_Price
{
    /**
     * Returns true if product has subscriptions options
     * @return bool
     */
    public function hasSubscriptionOptions()
    {
        return $this->getProduct()->getTypeInstance()->hasSubscriptionOptions();
    }

    public function subscriptionEnabled()
    {
        return $this->getProduct()->getAwSarpEnabled();
    }

    public function isRelated()
    {
        return $this->getIdSuffix() == '-related' ? true : false;
    }

    public function getCustomOptions()
    {

        $customOptions = array();
        $customOptionsColl = Mage::getModel('catalog/product_option')->getCollection();
        $customOptionsColl->getSelect()
                ->joinLeft(array('type_value' => $customOptionsColl->getTable('catalog/product_option_type_value')), 'main_table.option_id = type_value.option_id', array('option_type_id' => 'type_value.option_type_id'))
                ->joinLeft(array('type_price' => $customOptionsColl->getTable('catalog/product_option_type_price')), 'type_value.option_type_id = type_price.option_type_id', array('price' => 'type_price.price', 'price_type' => 'type_price.price_type'))
                ->where('main_table.product_id = ?', $this->getProduct()->getId());
        foreach ($customOptionsColl->getData() as $option) {
            $customOptions[$option['option_type_id']] = $option;
        }
        $store = $this->getProduct()->getStore();
		foreach ($customOptions as $key => $option) {
            if ($option['price_type'] == 'percent') continue;
			$customOptions[$key]['price'] = $this->__currencyByStore($option['price'], $store, false, false);
		}
        return Zend_Json::encode($customOptions);
    }

    private function __currencyByStore($value, $store = null, $format = true, $includeContainer = true)
    {
        try {
            if (!($store instanceof Mage_Core_Model_Store)) {
                $store = Mage::app()->getStore($store);
            }

            $value = $store->convertPrice($value, $format, $includeContainer);
        }
        catch (Exception $e){
            $value = $e->getMessage();
        }

        return $value;
    }
}
