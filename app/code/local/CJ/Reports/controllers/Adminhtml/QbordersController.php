<?php
/**
 *
 */
class CJ_Reports_Adminhtml_QbordersController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report')
            ->_title($this->__('Inventory Transactions'));
        $this->_addBreadcrumb('Inventory Transactions', 'Inventory Transactions');

        $grid =  $this->getLayout()->createBlock('cj_reports/adminhtml_report_inventorytransactions');
        $this->_addContent($grid);
        $request = $this->getRequest();
        if ('true' == $request->getQuery('isAjax')) {
            echo $grid->toHtml();
            exit;
        }

        $this->renderLayout();
    }

    public function gridAction()
    {
        return $this->indexAction();
    }


    public function exportCsvAction()
    {
        $filename = 'quickbooks.csv';
        $grid =  $this->getLayout()->createBlock('cj_reports/adminhtml_report_inventorytransactions');
        $this->getResponse()->clearHeaders();
        return $this->_prepareDownloadResponse(
            $filename,
            $grid->getCsvFile()
        );
    }

    public function exportXmlAction()
    {
        $filename = 'quickbooks.xls';
        $grid =  $this->getLayout()->createBlock('cj_reports/adminhtml_report_inventorytransactions');
        $this->getResponse()->clearHeaders();
        return $this->_prepareDownloadResponse(
            $filename,
            $grid->getExcelFile()
        );
    }
}
