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
 */class AW_Sarp_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    /* MAPRICE COPYPASTE BEGIN*/

    protected $data;
    protected $product;
    protected $mapCondition;
    protected $listPriceCondition;


    public function isCustomTemplateNeeded()
    {
        $res = false;
        if (Mage::helper('sarp')->extensionEnabled('AW_Maprice')) {
            $this->product = $this->getProduct();
            if ('catalog/product/price.phtml' == $this->getTemplate()
                && in_array($this->product->getTypeId(), array('simple', 'virtual', 'configurable', 'subscription_simple'))
            ) {
                $this->product = Mage::getModel('maprice/maprice')->getProductAttributes($this->product);
                $this->data = $this->product->getData();
                $maPrice = $this->data['minimum_advertised_price'];

                $this->mapCondition = $maPrice && $maPrice > floatval($this->product->getPrice());
                $this->listPriceCondition = (bool)($this->data['list_price']);
                $this->youSaveCondition = (bool)($this->data['you_save_price']);
                $res = ($this->listPriceCondition || $this->mapCondition || $this->youSaveCondition);
            }
        }
        return $res;
    }

    protected function _toHtml()
    {
        if (Mage::helper('sarp')->extensionEnabled('AW_Maprice')) {
            if (AW_Maprice_Helper_Data::isModuleOutputDisabled()) return parent::_toHtml();

            if ($this->isCustomTemplateNeeded()) {
                $sr = $this->helper('maprice')->getProductAttributes($this->data);

                switch ($this->getRequest()->getControllerName())
                {
                    case 'result':
                        $pageType = AW_Maprice_Model_Pagetype::PAGE_TYPE_SEARCH;
                        break;
                    case 'category':
                        $pageType = AW_Maprice_Model_Pagetype::PAGE_TYPE_CATEGORY;
                        break;
                    default:
                        $pageType = AW_Maprice_Model_Pagetype::PAGE_TYPE_PRODUCT;
                }

                $result = '';

                if ($this->listPriceCondition)
                    $result = Mage::getStoreConfig('maprice/pricetemplates/listpricetemplate');

                if ($this->mapCondition) {
                    if (in_array($pageType, explode(',', Mage::getStoreConfig('maprice/displaypriceson/price'))))
                        $result .= str_replace('#productId#', $this->product->getId(), $this->helper('maprice')->getButtonHtml());

                    if (AW_Maprice_Model_Pagetype::PAGE_TYPE_PRODUCT == $pageType)
                        $result .= Mage::getStoreConfig('maprice/button/additionaltext'); // additional html
                }
                else
                {
                    if (in_array($pageType, explode(',', Mage::getStoreConfig('maprice/displaypriceson/price'))))
                        $result .= Mage::getStoreConfig('maprice/pricetemplates/pricetemplate');

                    if ($this->data['you_save_price']
                        && in_array($pageType, explode(',', Mage::getStoreConfig('maprice/displaypriceson/yousaveprice')))
                    ) $result .= Mage::getStoreConfig('maprice/pricetemplates/yousavepricetemplate');
                }

                $result = Mage::getModel('core/email_template_filter')->filter($result);
                foreach ($sr as $search => $replace)
                    $result = str_replace('{' . $search . '}', $replace, $result);

                $this->setTemplate('maprice/maprice_link.phtml');
                $this->setMAPriceHtml($result);
                $this->setPageType($pageType);
            }
            return parent::_toHtml();
        }
        else
        {
            return parent::_toHtml();
        }
    }

    /* MAPRICE COPYPASTE END*/

    public function getProduct()
    {
        $product = parent::getProduct();
        $sarpProduct = Mage::getModel('catalog/product')->load($product->getId());

        if ($sarpProduct->getAwSarpEnabled()
            && Mage::helper('sarp')->isSubscriptionType($product)
            && ($sarpProduct->getAwSarpPeriod() != AW_Sarp_Model_Period::PERIOD_TYPE_NONE || !is_numeric($sarpProduct->getAwSarpPeriod()))
        ) {
            $newPrice = Mage::helper('sarp')->getSarpSubscriptionPrice($sarpProduct);

            if (!is_null($sarpProduct->getData('price'))) $sarpProduct->setPrice($newPrice);
            if (!is_null($sarpProduct->getData('final_price'))) $sarpProduct->setFinalPrice($newPrice);
            if (!is_null($sarpProduct->getData('minimal_price'))) $sarpProduct->setMinimalPrice($newPrice);
        }

        return $sarpProduct;
    }
}

?>
