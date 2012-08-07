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
class AW_Sarp_ProductController extends Mage_Adminhtml_Controller_Action
{
    public function convertAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
                $old_type = $product->getTypeId();
                $new_type = 'subscription_' . $old_type;

                $types = Mage_Catalog_Model_Product_Type::getTypes();
                if (empty($types[$new_type]['model'])) {
                    throw new Mage_Core_Exception("The product type $old_type can't be converted");
                }
                $this->__beforeConvert($product, $new_type);
                $product->setTypeId($new_type)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sarp')->__('Product was successfully converted to subscription'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirectReferer();
    }

    private function __beforeConvert($product, $newType)
    {
        //if converted from GROUPED
        if ($product->isGrouped()) {
            $product->setGroupedLinkData(array());
        }
        return $this;
    }
}