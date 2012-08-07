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
 */require_once 'Mage/Adminhtml/controllers/Catalog/ProductController.php';

class AW_Sarp_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * Create new product page
     */
    public function newAction()
    {
        $product = $this->_initProduct();

        Mage::dispatchEvent('catalog_product_new_action', array('product' => $product));

        if ($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup');
        } else {
            $_additionalLayoutPart = '';
            if (
                ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                 || $product->getTypeId() == AW_Sarp_Model_Product_Type_Configurable_Subscription::PRODUCT_TYPE_CONFIGURABLE)
                && !($product->getTypeInstance()->getUsedProductAttributeIds())
            ) {
                $_additionalLayoutPart = '_new';
            }
            $this->loadLayout(array(
                                   'default',
                                   strtolower($this->getFullActionName()),
                                   'adminhtml_catalog_product_' .
                                   ($product->getTypeId() == AW_Sarp_Model_Product_Type_Configurable_Subscription::PRODUCT_TYPE_CONFIGURABLE
                                           ? Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                                           : $product->getTypeId())
                                   . $_additionalLayoutPart
                              ));
            $this->_setActiveMenu('catalog/products');
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

}
