<?php
class Exinent_Panelreport_Block_Adminhtml_Report_Panelreport extends Mage_Adminhtml_Block_Widget_Grid_Container{    
    
    public function __construct() {
        $this->_blockGroup = 'panelreport';
        $this->_controller = 'adminhtml_report_panelreport';
        $this->_headerText = Mage::helper('panelreport')->__('Panel Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}