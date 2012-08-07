<?php

class NGC_Installment_Lib_Varien_Data_Form_Element_AmountDue extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('text');
        $this->setExtType('textfield');
    }

    public function getElementHtml()
    {
        $element = parent::getElementHtml();

        $html = $this->getBold() ? '<strong>' : '';
        $html.= $element;
        $html.= $this->getBold() ? '</strong>' : '';


        $button = Mage::app()->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('eav')->__('Adjust'),
                'class' => 'adjust',
                'onclick' => 'setLocation(\'' . $this->_getAdjustInstallmentUrl() . '\')',
            ));

        $html.= $button->toHtml();
        $html.= $this->getAfterElementHtml();

        return $html;
    }

    protected function _getAdjustInstallmentUrl()
    {
        $request = Mage::app()->getRequest();
        $id      = $request->getParam('id');
        return Mage::getModel('adminhtml/url')->getUrl('*/*/adjust/id/'. $id);
    }
}