<?php
class NGC_Installment_Block_Adminhtml_Sales_Order_Renderer_Installment_Suspended extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $value = ($row->getData('installment_master_suspend_installment') == 0) ?  Mage::helper('adminhtml')->__('No') : Mage::helper('adminhtml')->__('Yes');
        return $value;
    }

}