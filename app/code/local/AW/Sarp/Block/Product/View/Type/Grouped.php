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
class AW_Sarp_Block_Product_View_Type_Grouped extends Mage_Catalog_Block_Product_View_Type_Grouped
{
    protected $_subscriptionInstance;

    public function __construct()
    {
        $this->_subscriptionInstance = new AW_Sarp_Block_Product_View_Type_Subscription($this);
        return parent::__construct();
    }

    public function getSubscription()
    {
        return $this->_subscriptionInstance;
    }

    /**
     * Return current template
     * @return string
     */
    public function getTemplate()
    {
        if (parent::getTemplate()) return parent::getTemplate();
        return $this->getSubscription()->getTemplate();
    }

    public function getSarpAssociatedProducts()
    {
        $products = $this->getAssociatedProducts();
        if (isset($products[0])) {
            $mainProductPeriods = $products[0]->getAwSarpPeriod();
        }
        else return array();

        $readyArray = array();
        foreach ($products as $product)
        {
            if ($this->_checkPeriods($mainProductPeriods, $product->getAwSarpPeriod())) {
                $readyArray[] = $product;
            }
        }
        return $readyArray;
    }

    protected function _checkPeriods($periodOne, $periodTwo)
    {
        $periodOneArray = explode(',', $periodOne);
        $periodTwoArray = explode(',', $periodTwo);

        foreach ($periodOneArray as $periodOneItem)
        {
            if (!in_array($periodOneItem, $periodTwoArray)) return false;
        }
        return true;
    }

    public function getJsonConfig()
    {
        $config = array();

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $this->getProduct()->getPrice();
        $_finalPrice = $this->getProduct()->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice);

        $config = array(
            'productId' => $this->getProduct()->getId(),
            'priceFormat' => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax' => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax' => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices' => Mage::helper('tax')->displayBothPrices(),
            'productPrice' => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice' => Mage::helper('core')->currency($_regularPrice, false, false),
            'skipCalculate' => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax' => $defaultTax,
            'currentTax' => $currentTax,
            'idSuffix' => '_clone',
            'oldPlusDisposition' => 0,
            'plusDisposition' => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition' => 0,
        );

        $responseObject = new Varien_Object();
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return Zend_Json::encode($config);
    }

}