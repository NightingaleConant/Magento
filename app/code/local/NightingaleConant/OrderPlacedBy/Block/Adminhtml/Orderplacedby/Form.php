<?php
class NightingaleConant_OrderPlacedBy_Block_Adminhtml_Orderplacedby_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/sales');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'orderplacedby_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $adminUserModel = Mage::getModel('admin/user');
        $userCollection = $adminUserModel->getCollection()->load(); 
        //Mage::log($userCollection->getData());
        
        $dateFormatIso = "yyyy-MM-dd";
        
        $filter = $this->getRequest()->getParam('filter');        
        
        if($filter) {
            $data = explode("&", urldecode(base64_decode($filter)));
            $from = ltrim($data[0], 'from=');
            $to = ltrim($data[1], 'to=');
            echo $orderplacedby = ltrim($data[2], 'orderplacedby=');
        } else {
            $from = "";
            $to = "";
            $orderplacedby = "";
        }

        $fieldset->addField('from', 'date', array(
            'name'      => 'from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('orderplacedby')->__('From'),
            'title'     => Mage::helper('orderplacedby')->__('From'),
            'required'  => true,
            'value'     => $from,
        ));
        
        $fieldset->addField('to', 'date', array(
            'name'      => 'to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('orderplacedby')->__('To'),
            'title'     => Mage::helper('orderplacedby')->__('To'),
            'required'  => true,
            'value'     => $to,
        ));
        
        $fieldset->addField('orderplacedby', 'select', array(
            'name'      => 'orderplacedby',
            'note'      => 'Currently this filter will not work as it\'s not yet developed',
            'label'     => Mage::helper('orderplacedby')->__('Order Placed By'),
            'title'     => Mage::helper('orderplacedby')->__('Order Placed By'),            
            'values'     => array(
                array('label'=>'-- Select --','value'=>''),
                array('label'=>'Admin','value'=>'admin'),
                array('label'=>'Customer','value'=>'customer')
            )
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
        
    }
    
    
}