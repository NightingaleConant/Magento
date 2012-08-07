<?php
class CJ_Reports_Block_Adminhtml_Renderer_Ordertypecode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        if (!$data = Mage::registry('custom_attribute_option_value')) {
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter('159')
                ->setPositionOrder('desc', true)
                ->load();
            foreach ($optionCollection as $option) {
                $data[$option->getOptionId()] = $option->getValue();
            }

            Mage::register('custom_attribute_option_value', $data);
        }
        if (isset($data[$row->getOrderTypeCode()])) {
            return $data[$row->getOrderTypeCode()];
        }
    }
}