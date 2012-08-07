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
class AW_Sarp_Model_Product_Type_Downloadable_Price extends Mage_Downloadable_Model_Product_Price
{

    /**
     * Return price helper
     * @todo optimize load
     * @return Mage_Core_Model_Abstract
     */
    public function getPriceModel()
    {
        return Mage::getSingleton('sarp/product_price');
    }

    /**
     * Add different params (options price, link price) to price
     * @param Mage_Catalog_Model_Product $product
     * @param int $qty
     * @param float $price
     * @return float
     */

    public function calculateFinalPrice($product, $qty, $price)
    {
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $price);
        $finalPrice += $this->getLinksPrice($product);
        return $finalPrice;
    }

    /**
     * Adds links prices to base product price (only if they can be purchased separately)
     * @return float
     */
    public function getLinksPrice($product)
    {
        $linkPrice = 0;
        if ($product->getLinksPurchasedSeparately()) {
            if ($linksIds = $product->getCustomOption('downloadable_link_ids')) {
                $links = $product->getTypeInstance(true)
                        ->getLinks($product);
                foreach (explode(',', $linksIds->getValue()) as $linkId)
                {
                    if (isset($links[$linkId])) {
                        $linkPrice += $links[$linkId]->getPrice();
                    }
                }
            }
        }
        return $linkPrice;
    }

    public function getFinalPrice($qty = null, $product)
    {

        try {
            $baseProduct = Mage::getModel('catalog/product')->setStoreId($product->getStoreId())->load($product->getId());
            if ($this->getPriceModel()->isSubscriptionPriceSet($baseProduct)) {
                if ($product->getCustomOption('aw_sarp_subscription_type') && ($typeId = $product->getCustomOption('aw_sarp_subscription_type')->getValue())) {
                    if ($typeId == AW_Sarp_Model_Period::PERIOD_TYPE_NONE) {
                        return parent::getFinalPrice($qty, $product);
                    } else
                    {
                        if ($this->isOrderedFirstTime($baseProduct)
                            && is_numeric($baseProduct->getAwSarpFirstPeriodPrice())
                            && $baseProduct->getAwSarpFirstPeriodPrice() >= 0
                            && !Mage::helper('sarp')->isReordered($product->getId())
                        ) {
                            return $this->calculateFinalPrice($product, $qty, $baseProduct->getAwSarpFirstPeriodPrice());
                        }
                        else
                        {
                            return $this->calculateFinalPrice($product, $qty, Mage::helper('sarp')->getSarpSubscriptionPrice($baseProduct));
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            die();

        }
        return parent::getFinalPrice($qty, $product);
    }

    public function getPrice($product)
    {

        if ($this->getPriceModel()->isSubscriptionPriceSet($product) && $product->getAwSarpEnabled()) {
            $priceWithShippingCost = Mage::helper('sarp')->getSarpSubscriptionPrice($product);
            if ($product->getCustomOption('aw_sarp_subscription_type') && ($typeId = $product->getCustomOption('aw_sarp_subscription_type')->getValue())) {
                if ($typeId == AW_Sarp_Model_Period::PERIOD_TYPE_NONE) {
                    return parent::getPrice($product);
                } else {
                    return $priceWithShippingCost + $this->getLinksPrice($product);
                }
            } elseif ($product->getTypeInstance()->getDefaultSubscriptionPeriodId() != AW_Sarp_Model_Period::PERIOD_TYPE_NONE) {
                return $priceWithShippingCost + $this->getLinksPrice($product);
            }
        }
        return parent::getPrice($product);
    }

    /**
     * Determines if product is ordered first time or not by the customer
     * @return
     */
    public function isOrderedFirstTime(Mage_Catalog_Model_Product $Product, $Customer = null)
    {
        return $this->getPriceModel()->isOrderedFirstTime($Product, $Customer);
    }

}
