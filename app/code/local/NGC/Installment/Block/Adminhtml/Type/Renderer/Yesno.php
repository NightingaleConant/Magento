<?php
class NGC_Installment_Block_Adminhtml_Type_Renderer_Yesno extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $value = ($row->getData('installment_type_auto_adjust') == 1) ?  Mage::helper('adminhtml')->__('Yes') : Mage::helper('adminhtml')->__('No');
        return $value;
    }

}