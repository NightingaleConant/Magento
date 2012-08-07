<?php

/**
 *
 */
class CJ_Reports_Adminhtml_ArtransactionsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $request = $this->getRequest();

        $this->loadLayout()
            ->_setActiveMenu('report')
            ->_title($this->__('Ar Aging Report'));

        $this->_addBreadcrumb('Accounts Receivable', 'Accounts Receivable');

        $grid =  $this->getLayout()->createBlock('cj_reports/adminhtml_report_artransactions');

        $filter   = $grid->getParam($grid->getVarNameFilter(), null);
        if (is_string($filter)) {
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
            if (array_key_exists('cal_date', $data) && !empty($data['cal_date'])) {
                $grid = $this->getLayout()->createBlock('cj_reports/adminhtml_report_artransactionshistory');
            }
        }

        $this->_addContent($grid);

        if ('true' == $request->getQuery('isAjax')) {
            echo $grid->toHtml();
            exit;
        }
        $this->renderLayout();
    }

    public function exportSimpleCsvAction()
    {
        $filename = 'ar-aging.csv';
        $grid =  $this->getLayout()->createBlock('cj_reports/adminhtml_report_artransactions');
        $this->getResponse()->clearHeaders();
        return $this->_prepareDownloadResponse(
            $filename,
            $grid->getCsvFile()
        );
    }

    public function exportXmlAction()
    {
        $filename = 'ar-aging.xls';
        $grid =  $this->getLayout()->createBlock('cj_reports/adminhtml_report_artransactions');
        $this->getResponse()->clearHeaders();
        return $this->_prepareDownloadResponse(
            $filename,
            $grid->getExcelFile()
        );
    }
}
