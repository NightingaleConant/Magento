<?php
class NightingaleConant_HeldOrders_Block_Adminhtml_Heldorders_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/sales');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'heldorders_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $dateFormatIso = "yyyy-MM-dd";
        
        $filter = $this->getRequest()->getParam('filter');
        
        if($filter) {
            $data = explode("&", urldecode(base64_decode($filter)));
            $from = ltrim($data[0], 'from=');
            $to = ltrim($data[1], 'to=');
        } else {
            $from = "";
            $to = "";
        }

        $fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('heldorders')->__('From'),
            'title'     => Mage::helper('heldorders')->__('From'),
            'required'  => true,
            'value'     => $from,
        ));
        
        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('heldorders')->__('To'),
            'title'     => Mage::helper('heldorders')->__('To'),
            'required'  => true,
            'value'     => $to,
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
        
    }
    
    
}