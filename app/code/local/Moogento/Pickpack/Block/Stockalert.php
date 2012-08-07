<?php
class Moogento_Pickpack_Block_Stockalert extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:80px;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $value = $element->getValue();
        } else {
            $value = 1;
        }

        $from = $element->setValue(isset($value) ? $value : null)->getElementHtml();        
        return $from.'   '.Mage::helper('adminhtml')->__('items');
    }
}