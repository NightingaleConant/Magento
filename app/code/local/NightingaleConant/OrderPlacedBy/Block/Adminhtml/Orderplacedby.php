<?php
class NightingaleConant_OrderPlacedBy_Block_Adminhtml_Orderplacedby extends Mage_Adminhtml_Block_Widget_Grid_Container {
 
    public function __construct()
    {
        $this->_blockGroup = 'orderplacedby';
        $this->_controller = 'adminhtml_orderplacedby';
        $this->_headerText = Mage::helper('orderplacedby')->__('Order Placed By Report');
        parent::__construct();
        $this->setTemplate('orderplacedby/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('orderplacedby')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/sales', array('_current' => true));
    }
 
}