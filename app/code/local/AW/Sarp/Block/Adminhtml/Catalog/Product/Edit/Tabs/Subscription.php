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
class AW_Sarp_Block_Adminhtml_Catalog_Product_Edit_Tabs_Subscription extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Reference to product objects that is being edited
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected $_config = null;

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Enable subscriptions');
    }

    public function getTabTitle()
    {
        return $this->__('Enable subscriptions');
    }


    public function canShowTab()
    {
        return strpos(Mage::registry('product')->getTypeId(), 'subscription') === false;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */


    protected function _toHtml()
    {

        $id = $this->getRequest()->getParam('id');

        try {
            $model = Mage::getModel('sarp/product_type_' . Mage::registry('product')->getTypeId() . '_subscription');
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setClass('add')
                    ->setType('button')
                    ->setOnClick('window.location.href=\'' . $this->getUrl('sarp_admin/product/convert', array('id' => $id)) . '\'')
                    ->setLabel('Convert this product to subscription');
            return $button->toHtml();
        } catch (exception $e) {
            return $this->__("Sorry, but this product cannot have a subscription");
        }
    }
}
