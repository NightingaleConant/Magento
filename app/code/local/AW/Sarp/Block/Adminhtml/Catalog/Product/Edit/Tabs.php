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
/**
 * admin product edit tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AW_Sarp_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _prepareLayout()
    {
        $product = $this->getProduct();

        if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        if ($setId) {
            $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
                    ->setAttributeSetFilter($setId);
            if ( is_callable(array($groupCollection, 'setSortOrder')) ) {
                $groupCollection->setSortOrder();
            }
            $groupCollection
                    ->load();

            foreach ($groupCollection as $group) {
                $attributes = $product->getAttributes($group->getId(), true);
                // do not add grops without attributes

                foreach ($attributes as $key => $attribute) {
                    if (!$attribute->getIsVisible()) {
                        unset($attributes[$key]);
                    }
                }

                if (count($attributes) == 0) {
                    continue;
                }

                $this->addTab('group_' . $group->getId(), array(
                                                               'label' => Mage::helper('catalog')->__($group->getAttributeGroupName()),
                                                               'content' => $this->getLayout()->createBlock($this->getAttributeTabBlock())
                                                                       ->setGroup($group)
                                                                       ->setGroupAttributes($attributes)
                                                                       ->toHtml(),
                                                          ));
            }

            $this->addTab('inventory', array(
                                            'label' => Mage::helper('catalog')->__('Inventory'),
                                            'content' => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_inventory')->toHtml(),
                                       ));


            /**
             * Don't display website tab for single mode
             */
            if (!Mage::app()->isSingleStoreMode()) {
                $this->addTab('websites', array(
                                               'label' => Mage::helper('catalog')->__('Websites'),
                                               'content' => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_websites')->toHtml(),
                                          ));
            }

            $this->addTab('categories', array(
                                             'label' => Mage::helper('catalog')->__('Categories'),
                                             'url' => $this->getUrl('*/*/categories', array('_current' => true)),
                                             'class' => 'ajax',
                                        ));

            $this->addTab('related', array(
                                          'label' => Mage::helper('catalog')->__('Related Products'),
                                          'url' => $this->getUrl('*/*/related', array('_current' => true)),
                                          'class' => 'ajax',
                                     ));

            $this->addTab('upsell', array(
                                         'label' => Mage::helper('catalog')->__('Up-sells'),
                                         'url' => $this->getUrl('*/*/upsell', array('_current' => true)),
                                         'class' => 'ajax',
                                    ));

            $this->addTab('crosssell', array(
                                            'label' => Mage::helper('catalog')->__('Cross-sells'),
                                            'url' => $this->getUrl('*/*/crosssell', array('_current' => true)),
                                            'class' => 'ajax',
                                       ));

            $storeId = 0;
            if ($this->getRequest()->getParam('store')) {
                $storeId = Mage::app()->getStore($this->getRequest()->getParam('store'))->getId();
            }

            $alertPriceAllow = Mage::getStoreConfig('catalog/productalert/allow_price');
            $alertStockAllow = Mage::getStoreConfig('catalog/productalert/allow_stock');

            if ($alertPriceAllow || $alertStockAllow) {
                $this->addTab('productalert', array(
                                                   'label' => Mage::helper('catalog')->__('Product Alerts'),
                                                   'content' => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_alerts', 'admin.alerts.products')->toHtml()
                                              ));
            }

            if ($this->getRequest()->getParam('id', false)) {
                if (Mage::getSingleton('admin/session')->isAllowed('admin/catalog/reviews_ratings')) {
                    $this->addTab('reviews', array(
                                                  'label' => Mage::helper('catalog')->__('Product Reviews'),
                                                  'url' => $this->getUrl('*/*/reviews', array('_current' => true)),
                                                  'class' => 'ajax',
                                             ));
                }
                if (Mage::getSingleton('admin/session')->isAllowed('admin/catalog/tag')) {
                    $this->addTab('tags', array(
                                               'label' => Mage::helper('catalog')->__('Product Tags'),
                                               'url' => $this->getUrl('*/*/tagGrid', array('_current' => true)),
                                               'class' => 'ajax',
                                          ));

                    $this->addTab('customers_tags', array(
                                                         'label' => Mage::helper('catalog')->__('Customers Tagged Product'),
                                                         'url' => $this->getUrl('*/*/tagCustomerGrid', array('_current' => true)),
                                                         'class' => 'ajax',
                                                    ));
                }

            }

            /**
             * Do not change this tab id
             * @see Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs_Configurable
             * @see Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tabs
             */
            if ($this->getProduct()->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_GROUPED && $this->getProduct()->getTypeId() != 'subscription_grouped') {
                $this->addTab('customer_options', array(
                                                       'label' => Mage::helper('catalog')->__('Custom Options'),
                                                       'url' => $this->getUrl('*/*/options', array('_current' => true)),
                                                       'class' => 'ajax',
                                                  ));
            }

        }
        else {
            $this->addTab('set', array(
                                      'label' => Mage::helper('catalog')->__('Settings'),
                                      'content' => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_settings')->toHtml(),
                                      'active' => true
                                 ));
        }
        return Mage_Adminhtml_Block_Widget_Tabs::_prepareLayout();
    }

    /**
     * Retrive product object from object if not from registry
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!($this->getData('product') instanceof Mage_Catalog_Model_Product)) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        if (is_null(Mage::helper('adminhtml/catalog')->getAttributeTabBlock())) {
            return $this->_attributeTabBlock;
        }
        return Mage::helper('adminhtml/catalog')->getAttributeTabBlock();
    }

    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }
}
