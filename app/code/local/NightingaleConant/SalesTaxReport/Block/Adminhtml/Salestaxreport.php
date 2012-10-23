<?php
class NightingaleConant_SalesTaxReport_Block_Adminhtml_Salestaxreport extends Mage_Adminhtml_Block_Widget_Grid_Container {
 
    public function __construct()
    {
        $this->_blockGroup = 'salestaxreport';
        $this->_controller = 'adminhtml_salestaxreport';
        $this->_headerText = Mage::helper('salestaxreport')->__('Sales Tax Report');
        parent::__construct();
        $this->setTemplate('salestaxreport/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('salestaxreport')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/sales', array('_current' => true));
    }
 
}