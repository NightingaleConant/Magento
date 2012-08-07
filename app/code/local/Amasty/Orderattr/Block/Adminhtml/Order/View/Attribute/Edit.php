<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_View_Attribute_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amorderattr';
        $this->_objectId   = 'entity_id';
        $this->_controller = 'adminhtml_order_view_attribute';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('catalog')->__('Save Order Attributes'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
    	$order = Mage::registry('current_order');
        return Mage::helper('catalog')->__('Edit Attributes For The Order #%s', $order->getIncrementId());
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true, 'back'=>null));
    }
}
