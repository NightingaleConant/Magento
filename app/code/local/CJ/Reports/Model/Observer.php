<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ajt
 * Date: 7/30/12
 * Time: 9:22 AM
 * To change this template use File | Settings | File Templates.
 */
class CJ_Reports_Model_Observer
{
    static public function dailyArReportExport()
    {
        $date       = date("Y-m-d", Mage::getModel('core/date')->timestamp(time()));
        $filename   = 'ar-aging-'. $date .'.csv';
        $path       = Mage::getBaseDir('export') . DS .'ar_reporting'. DS;
        $grid       = Mage::app()->getLayout()->createBlock('cj_reports/adminhtml_report_artransactions');

        $csvFile = $grid->getCsvFile();

        if (!file_exists($path)) {
            mkdir($path);
        }

        $filename = $path . $filename;
        if (!copy($csvFile['value'], $filename)) {
            return Mage::throwException(Mage::helper('core')->__('Cannot save AR Aging Report.'));
        }

        unlink($csvFile['value']);

        $report = Mage::getModel('cj_reports/artranactionshistory')->getCollection();
        foreach ($report as $row) {
            $row->delete();
        }
    }
}
