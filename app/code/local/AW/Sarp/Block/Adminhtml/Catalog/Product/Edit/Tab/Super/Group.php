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
 * Product in category grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class AW_Sarp_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Group extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
{
    protected function _prepareCollection()
    {
        if ($this->_getProduct()->getTypeId() == 'subscription_grouped') {
            $allowProductTypes = array();
            foreach (Mage::getConfig()->getNode('global/catalog/product/type/subscription_grouped/allow_product_types')->children() as $type) {
                $allowProductTypes[] = $type->getName();
            }

            $collection = Mage::getModel('catalog/product_link')->useGroupedLinks()
                    ->getProductCollection()
                    ->setProduct($this->_getProduct())
                    ->addAttributeToSelect('*')
                    ->addFilterByRequiredOptions()
                    ->addAttributeToFilter('type_id', $allowProductTypes);

            $this->setCollection($collection);
            return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        }
        else
        {
            return parent::_prepareCollection();
        }
    }
}
