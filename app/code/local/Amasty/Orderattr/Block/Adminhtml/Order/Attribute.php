<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amorderattr';
        $this->_controller = 'adminhtml_order_attribute';
        $this->_headerText = Mage::helper('amorderattr')->__('Manage Order Attributes');
        $this->_addButtonLabel = Mage::helper('amorderattr')->__('Add New Attribute');
        parent::__construct();
    }

}
