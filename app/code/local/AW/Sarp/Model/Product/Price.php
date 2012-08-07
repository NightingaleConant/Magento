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
class AW_Sarp_Model_Product_Price extends AW_Core_Object
{

    public static $doNotApplyFirstPeriodPrice = false;
    protected $_iterate;

    /**
     * @param Mage_Catalog_Model_Product $Product
     * @return boolean
     */
    public function isSubscriptionPriceSet(Mage_Catalog_Model_Product $Product)
    {
        $priceStr = trim($Product->getAwSarpSubscriptionPrice());
        if (strlen($priceStr)) {
            return true;
        }
        return false;
    }


    /**
     * TODO Remove this function from downloadable and simple subscription
     * Add Shipping cost to Sarp subscription price
     * @param Mage_Catalog_Model_Product $product
     * @deprecated Use getShippingCost($product) for getting ShippingCost only
     * @return float
     */
    public function getSarpSubscriptionPrice($product, $includeCatalogRule)
    {
        $shippingCost = $product->getAwSarpShippingCost();
        if ($shippingCost == '') {
            $shippingCost = Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_GENERAL_SHIPPING_COST);
        }

        if ($product->isGrouped()) {
            $price = $this->__getGroupedPrice($product);
        }
        else {
            $price = $this->__getSimplePrice($product, $includeCatalogRule);
        }
        if ($product->getData('type_id') == 'subscription_downloadable') {
            return $price;
        }

        if (!is_numeric($shippingCost)) {
            return $price;
        }
        return $price + $shippingCost;
    }

    public function getSarpFirstPeriodPrice($product)
    {
        $shippingCost = $product->getAwSarpShippingCost();
        if ($shippingCost == '') {
            $shippingCost = Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_GENERAL_SHIPPING_COST);
        }

        if ($product->getAwSarpFirstPeriodPrice() != '') {
            $price = $product->getPriceModel()->getPriceModel()->getFirstSubscriptionFinalPrice($product);
            //$price = $product->getAwSarpFirstPeriodPrice();
        } else {
            return null;
        }

        if ($product->getData('type_id') == 'subscription_downloadable') {
            return $price;
        }

        if (!is_numeric($shippingCost)) {
            return $price;
        }
        return $price + $shippingCost;
    }

    /**
     * Get Shipping cost for product
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getShippingCost($product)
    {
        $baseProduct = Mage::getModel('catalog/product')->load($product->getId());
        $shippingCost = $baseProduct->getAwSarpShippingCost();
        if ($shippingCost == '') {
            $shippingCost = Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_GENERAL_SHIPPING_COST);
        }

        if (!is_numeric($shippingCost)) $shippingCost = 0;
        return $shippingCost;
    }

    /**
     * Determines if product is ordered first time or not by the customer
     * @return
     */
    public function isOrderedFirstTime(Mage_Catalog_Model_Product $Product, $Customer = null)
    {
        $iteratorIsNotRunning = Mage::registry(AW_Sarp_Model_Subscription::ITERATE_STATUS_REGISTRY_NAME) != AW_Sarp_Model_Subscription::ITERATE_STATUS_RUNNING;

        //fix against fatal error with iterate
        if(!isset($this->_iterate[$Product->getId()])){
    	    $this->_iterate[$Product->getId()] = 1;
            return $iteratorIsNotRunning && !self::$doNotApplyFirstPeriodPrice;
        }
        elseif ($this->_iterate[$Product->getId()] < 3){
            $this->_iterate[$Product->getId()]++;
            return true;
        }
        else
            return false;

        /*
          if(!isset($this->_iterate[$Product->getId()])){
              $this->_iterate[$Product->getId()] = true;
              return $iteratorIsNotRunning && !self::$doNotApplyFirstPeriodPrice;
          }
          else
              return false;
          */
    }

    public function getSubscriptionFinalPrice(Mage_Catalog_Model_Product $product)
    {
        return $this->getSarpFinalPrice($product, $product->getAwSarpSubscriptionPrice());
    }

    public function getFirstSubscriptionFinalPrice(Mage_Catalog_Model_Product $product)
    {
        return $this->getSarpFinalPrice($product, $product->getAwSarpFirstPeriodPrice());
    }

    /*
      *	calculate Sarp Product Final price by given Price
     */

    public function getSarpFinalPrice(Mage_Catalog_Model_Product $product, $price)
    {

        $resource = Mage::getSingleton('core/resource');
        $db = $resource->getConnection('core_read');

        $select = $db->select()
                ->from($resource->getTableName('catalogrule/rule_product'))
                ->where('from_time <= ?', time())
                ->where('IF(to_time > 0, to_time >= ?, to_time = 0)', time())
                ->where('product_id = ?', $product->getId())
                ->where('website_id = ?', Mage::app()->getWebsite()->getId())
                ->where('customer_group_id = ?', Mage::getSingleton('customer/session')->getCustomer()->getGroupId())
                ->order('sort_order ASC');
        foreach ($db->fetchAll($select) as $rule) {
            $price = $this->calcPriceRule($rule['action_operator'], $rule['action_amount'], $price);
            if ((int)$rule['action_stop'])
                break;
        }

        return $price;
    }

    public function calcPriceRule($actionOperator, $ruleAmount, $price)
    {
        switch ($actionOperator) {
            case 'to_fixed':
                $priceRule = $ruleAmount;
                break;
            case 'to_percent':
                $priceRule = $price * $ruleAmount / 100;
                break;
            case 'by_fixed':
                $priceRule = $price - $ruleAmount;
                break;
            case 'by_percent':
                $priceRule = $price * (1 - $ruleAmount / 100);
                break;
        }
        return max($priceRule, 0);
    }

    private function __getSimplePrice($product, $includeCatalogRule)
    {
        if ($this->isSubscriptionPriceSet($product)) {
            if ($includeCatalogRule)
                $price = $product->getPriceModel()->getPriceModel()->getSubscriptionFinalPrice($product);
            else
                $price = $product->getAwSarpSubscriptionPrice();
        } else {
            $price = $product->getData('price');
        }
        return $price;
    }

    private function __getGroupedPrice($product)
    {
        $_prices = array();
        $childs = $product->getTypeInstance()->getAssociatedProducts();
        foreach($childs as $item) {
            $_prices[$item->getId()] = number_format($item->getAwSarpSubscriptionPrice(), 4);
        }
        $price = min($_prices);
        if (!is_numeric($price))
            $price = $product->getData('price');
        return $price;
    }

}