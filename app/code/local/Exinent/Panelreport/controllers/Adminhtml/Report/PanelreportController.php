<?php
class Exinent_Panelreport_Adminhtml_Report_PanelreportController extends Mage_Adminhtml_Controller_Action {

    public function _initAction()
    {
        $this->loadLayout()->_addBreadcrumb(Mage::helper('panelreport')->__('Panelreport'),
        Mage::helper('panelreport')->__('Panelreport'));
        return $this;    
    }
     
    public function marketAction()    
    {        
        $this->_title($this->__('Panelreport'))->_title($this->__('Reports'))->_title($this->__('Marketing Panel Report'));        
        $this->_initAction()
          ->_setActiveMenu('panelreport/report')
          ->_addBreadcrumb(Mage::helper('panelreport')->__('Marketing Panel Report'), Mage::helper('panelreport')->__('Marketing Panel Report'))        
          ->_addContent($this->getLayout()->createBlock('panelreport/adminhtml_report_panelreport'))
          ->renderLayout();
    }
     
    public function exportPanelreportCsvAction()
    {        
        $fileName   = 'panelreport.csv';
        $content    = $this->getLayout()->createBlock('panelreport/adminhtml_report_panelreport_grid')
                                         ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }
}