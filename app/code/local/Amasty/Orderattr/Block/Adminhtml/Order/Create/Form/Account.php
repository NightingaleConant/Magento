<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Create_Form_Account extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Account
{
    protected function _toHtml()
    {
        $html       = parent::_toHtml();
        $attributes = Mage::app()->getLayout()->createBlock('amorderattr/adminhtml_order_create_form_attributes');
        return $html . $attributes->toHtml();
    }
}