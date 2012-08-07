<?php 
class Unleashed_Sendinvoice_Block_Export extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('sendinvoice/adminhtml_sendinvoice'); //

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Export Products')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}
