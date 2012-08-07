<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Sales_Order_Email_Items extends Mage_Sales_Block_Order_Email_Items
{
    protected function _toHtml()
    {
        $emailBlock = $this->getLayout()->createBlock('amorderattr/fields_email');
        $emailBlock->setOrder($this->getOrder());
        return $emailBlock->toHtml() . parent::_toHtml();
    }
}