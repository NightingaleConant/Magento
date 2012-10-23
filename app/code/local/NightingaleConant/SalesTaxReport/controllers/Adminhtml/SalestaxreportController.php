<?php
 
class NightingaleConant_SalesTaxReport_Adminhtml_SalestaxreportController extends Mage_Adminhtml_Controller_Action {
 
    protected function _initAction() {
        $this->loadLayout();
        return $this;
    }
    
    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new Varien_Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }
    
    public function salesAction()
    {
        
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Sales'));

        //$this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales Report'), Mage::helper('adminhtml')->__('Sales Report'));

        $gridBlock = $this->getLayout()->getBlock('report_sales_sales.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }


 
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }
 
    public function exportPdfAction() {
        $fileName = 'salestaxreport.pdf';
        $content = $this->getLayout()->createBlock('salestaxreport/adminhtml_salestaxreport_grid')
                        ->getPdfFile();
        //$this->_sendUploadResponse($fileName, $content);
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function exportCsvAction() {
        $fileName = 'salestaxreport.csv';
        $content = $this->getLayout()->createBlock('salestaxreport/adminhtml_salestaxreport_grid')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }
     
    public function exportXmlAction() {
        $fileName = 'salestaxreport.xml';
        $content = $this->getLayout()->createBlock('salestaxreport/adminhtml_salestaxreport_grid')
                        ->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }
 
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
 
}