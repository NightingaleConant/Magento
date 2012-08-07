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
class AW_Sarp_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Returns true if such status is valid for subscription activation
     * @param object $status
     * @return bool
     */
    public function isOrderStatusValidForActivation($status)
    {
        if (
            ($status == Mage_Sales_Model_Order::STATE_COMPLETE) ||
            ($status == Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_GENERAL_ACTIVATE_ORDER_STATUS)) ||
            ($status == Mage_Sales_Model_Order::STATE_NEW && (Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_GENERAL_ACTIVATE_ORDER_STATUS) == 'pending'))
        ) {
            return true;
        }
        return false;
    }

    public function isSubscriptionItemInvoiced(AW_Sarp_Model_Subscription_Item $item)
    {
        $invoiced = (float)$item->getOrderItem()->getQtyInvoiced();
        if ($invoiced)
            return true;
        return false;
    }

    /**
     * Return true if such type is subscription type
     * @param mixed $typeId
     * @return bool
     */
    public static function isSubscriptionType($typeId)
    {
        if ($typeId instanceof Mage_Catalog_Model_Product) {
            $typeId = $typeId->getTypeId();
        } elseif (($typeId instanceof Mage_Sales_Model_Order_Item) || ($typeId instanceof Mage_Sales_Model_Quote_Item)) {
            $typeId = $typeId->getProductType();
        }
        return strpos($typeId, "subscription") !== false;
    }

    /**
     * Get all subscription types
     * @return array
     */
    public function getAllSubscriptionTypes()
    {
        $types = Mage::getConfig()->getNode('global')->xpath('catalog/product/type/*');
        $out = array();
        foreach ($types as $type) {
            $name = $type->getName();
            if ($this->isSubscriptionType($name)) {
                $out[] = $name;
            }
        }
        return $out;
    }

    /**
     * Check if product was reordered
     * @param int $productId
     * @return bool
     */

    public function isReordered($productId)
    {
        $quote = Mage::getModel('checkout/cart');
        foreach ($quote->getItems() as $item) {
            $info = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
            if (isset($info['product']) && isset($info['aw_sarp_order_id']) && isset($info['qty']))
                if (
                    $info['product'] == $productId
                    && $item->getQty() == $info['qty']
                    && !is_null($info['aw_sarp_order_id'])
                )
                    return true;
        }
        return false;
    }

    /**
     * Add Shipping cost to Sarp subscription price
     * @param object $product
     * @deprecated Use AW_Sarp_Model_Product_Price::getSarpSubscriptionPrice instead
     * @return float
     */
    public function getSarpSubscriptionPrice($product, $includeCatalogRule = false)
    {
        return Mage::getSingleton('sarp/product_price')->getSarpSubscriptionPrice($product, $includeCatalogRule);
    }

    public function getSarpFirstPeriodPrice($product)
    {
        return Mage::getSingleton('sarp/product_price')->getSarpFirstPeriodPrice($product);
    }

    /**
     * Checks if extension enabled by its name
     * @param string $extension_name
     * @return bool
     */

    public function extensionEnabled($extension_name)
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (!isset($modules[$extension_name])
            || $modules[$extension_name]->descend('active')->asArray() == 'false'
            || Mage::getStoreConfig('advanced/modules_disable_output/' . $extension_name)
        ) return false;
        return true;
    }

    /**
     * Check if guest ckeckout for product is enabled
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function checkoutAllowedForGuest($product)
    {
        $baseProduct = Mage::getModel('catalog/product')->load($product->getId());
        if ($baseProduct->getAwSarpAnonymousSubscription() == AW_Sarp_Model_Source_Yesnoglobal::GLOB)
            return Mage::getStoreConfig(AW_Sarp_Helper_Config::XML_PATH_GENERAL_ANONYMOUS_SUBSCRIPTIONS);
        return $baseProduct->getAwSarpAnonymousSubscription();
    }

    /**
     * Check version Of Magento
     * @param string $version
     * @return boolean
     */
    public function checkVersion($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }
}
