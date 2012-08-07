<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Attribute_View extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $list = Mage::app()->getLayout()->createBlock('amorderattr/adminhtml_order_attribute_view_list');
        $html = preg_replace('@<div class="entry-edit">(\s*)<div class="entry-edit-head">(\s*)(.*?)head-products@', 
                                    $list->toHtml() .'<div class="entry-edit"><div class="entry-edit-head">$3head-products', $html, 1);
        return $html;
    }
}