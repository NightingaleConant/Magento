<?php
class NightingaleConant_HeldOrders_Block_Adminhtml_Heldorders extends Mage_Adminhtml_Block_Widget_Grid_Container {
 
    public function __construct()
    {
        $this->_blockGroup = 'heldorders';
        $this->_controller = 'adminhtml_heldorders';
        $this->_headerText = Mage::helper('heldorders')->__('Held Orders Report');
        parent::__construct();
        $this->setTemplate('heldorders/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('heldorders')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/sales', array('_current' => true));
    }
 
}