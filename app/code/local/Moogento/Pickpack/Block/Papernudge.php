<?php
class Moogento_Pickpack_Block_Papernudge extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:40px;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }

        $top = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $right = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        $bottom = $element->setValue(isset($values[2]) ? $values[2] : null)->getElementHtml();
        $left = $element->setValue(isset($values[3]) ? $values[3] : null)->getElementHtml();
        return $top.'  '.Mage::helper('adminhtml')->__('Top, pt'). '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$right.'  '. Mage::helper('adminhtml')->__('Right, pt'). '<br />'.$bottom.'  '. Mage::helper('adminhtml')->__('Bottom, pt'). '    '.$left.'  '. Mage::helper('adminhtml')->__('Left, pt') ;
    }
}