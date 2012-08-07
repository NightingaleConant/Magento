<?php
/**
 * DEBUGGING:
 *  If you wish to debug this grid, add ?debug=true to the url, this will add a column with the age
 *  of the invoice to aid you
 */
class CJ_Reports_Block_Adminhtml_Report_Artransactionshistory extends Mage_Adminhtml_Block_Sales_Invoice_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_invoice_grid');
        $this->setTemplate('reports/grid.phtml');
        $this->setUseAjax(true);
        $this->setCountTotals(true);

        // Default Sorting
        $this->setDefaultSort('billing_name');
        $this->setDefaultDir('ASC');

        $this->setSaveParametersInSession(true);
    }

    protected function getGridHeader()
    {
        return 'Historical Accounts Receivable';
    }

    protected function _prepareCollection()
    {
        $filter         = $this->getParam($this->getVarNameFilter(), null);

        $session        = Mage::getSingleton('admin/session');
        $sessionId      = $session->getSessionId();

        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $data = $this->helper('adminhtml')->prepareFilterString($filter);
            $this->_setFilterValues($data);
            if (array_key_exists('cal_date', $data) && !empty($data['cal_date'])) {
                $this->setDate($data['cal_date']);
            }

            if (array_key_exists('cal_date', $data) && !empty($data['cal_date'])) {
                //  Check if the session date for the loaded data in ar_report_history matches the current date
                $sessionDate = $session->getArReportHistoryDate();

                $date = date('Y-m-d', strtotime($data['cal_date']));

                if ($sessionDate != $date) {
                    $session->setArReportHistoryDate($date);
                    //  Check if the requested csv file exists
                    $filename =  Mage::getBaseDir('export') . DS .'ar_reporting'. DS .'ar-aging-'. $date .'.csv';
                    if (file_exists($filename)) {

                        $collection = Mage::getModel('cj_reports/artransactionshistory')->getCollection();
                        $collection->addFieldToFilter('session_id', $sessionId);

                        foreach ($collection as $arTrans) {
                            $arTrans->delete();
                        }

                        $row = 0;
                        if (($handle = fopen($filename, "r")) !== FALSE) {
                            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                                if (!feof($handle) && isset($obj)) {
                                    $obj->save();
                                }

                                if ($row != 0) {
                                    $obj = Mage::getModel('cj_reports/artransactionshistory');
                                    $obj->setSessionId($sessionId)
                                        ->setIncrementId($data[0])
                                        ->setBillingName($data[1])
                                        ->setCustomerGroup($data[2])
                                        ->setOrderType($data[3])
                                        ->setTotalPaid(substr($data[4],1));
                                    if (!empty($data[5])) {
                                        $obj->set030Days(substr($data[5],1));
                                    }
                                    if (!empty($data[6])) {
                                        $obj->set3160Days(substr($data[6],1));
                                    }
                                    if (!empty($data[7])) {
                                        $obj->set6190Days(substr($data[7],1));
                                    }
                                    if (!empty($data[8])) {
                                        $obj->set91120Days(substr($data[8],1));
                                    }
                                    if (!empty($data[9])) {
                                        $obj->set121180Days(substr($data[9],1));
                                    }
                                    if (!empty($data[10])) {
                                        $obj->set181240Days(substr($data[10],1));
                                    }
                                    if (!empty($data[11])) {
                                        $obj->set241Days(substr($data[11],1));
                                    }
                                }
                                $row++;
                            }
                            fclose($handle);
                        }
                    } else {
//                        Mage::throwException(Mage::helper('core')->__('Cannot locate report for '. $date));
                    }
                }
            }
        } else {
            if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            } else {
                if (0 !== sizeof($this->_defaultFilter)) {
                    $this->_setFilterValues($this->_defaultFilter);
                }
            }
        }

        $collection = Mage::getModel('cj_reports/artransactionshistory')->getCollection();
        $collection->addFieldToFilter('session_id', $sessionId);

        $this->setCollection($collection);

        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $this->_setCollectionOrder($this->_columns[$columnId]);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'id',
            array(
                'header'    => 'Invoice #',
                'index'     =>'increment_id',
                'sortable'  => true,
                'totals_label'      => Mage::helper('sales')->__('Total'),
            )
        );
        $this->addColumn(
            'billing_name',
            array(
                'header' => 'Customer',
                'index' => 'billing_name',
            )
        );

        $this->addColumn(
            'customer_group_code',
            array(
                'header' => 'Customer Group',
                'index' => 'customer_group',
                'sortable'  => true,
            )
        );

        $this->addColumn(
            'order_type_code',
            array(
                'header' => 'Order Type Code',
                'index' => 'order_type',
//                'renderer' => 'CJ_Reports_Block_Adminhtml_Renderer_Ordertypecode',
//                'filter'   => 'CJ_Reports_Block_Adminhtml_Filter_Ordertypecode',
                'sortable'  => true,
//                'type'      => 'options'
            )
        );

        $this->addColumn(
            'amount_due',
            array(
                 'header'    => Mage::helper('customer')->__('Amount'),
                 'index'     => 'total_paid',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'total'     => 'sum',
            )
        );

        $this->addcolumn(
            'current_period',
            array(
                 'header'   => '0 - 30 Days',
                 'index'     => '030_days',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'filter'    => false,
                 'sortable'  => false,
                 'total'     => 'sum',
            )
        );

        $this->addcolumn(
            '30days',
            array(
                 'header'   => '31 Days - 60 Days',
                 'index'     => '3160_days',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'filter'    => false,
                 'sortable'  => false,
                 'total'     => 'sum',
            )
        );
        $this->addcolumn(
            '60days',
            array(
                 'header'   => '61 Days - 90 Days',
                 'index'     => '6190_days',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'filter'    => false,
                 'sortable'  => false,
                 'total'     => 'sum',
            )
        );
        $this->addcolumn(
            '90days',
            array(
                 'header'   => '91 Days - 120 Days',
                 'index'     => '91120_days',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'filter'    => false,
                 'sortable'  => false,
                 'total'     => 'sum',
            )
        );
        $this->addcolumn(
            '120days',
            array(
                 'header'   => '121 Days - 180 Days',
                 'index'     => '121180_days',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'filter'    => false,
                 'sortable'  => false,
                 'total'     => 'sum',
            )
        );
        $this->addcolumn(
            '180days',
            array(
                 'header'   => '181 Days - 240 Days',
                 'index'     => '181240_days',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'filter'    => false,
                 'sortable'  => false,
                 'total'     => 'sum',
            )
        );
        $this->addcolumn(
            '240days',
            array(
                 'header'   => '241 Days +',
                 'index'     => '241_days',
                 'type'      => 'currency',
                 'align'     => 'right',
                 'currency'  => 'order_currency_code',
                 'filter'    => false,
                 'sortable'  => false,
                 'total'     => 'sum',
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                  array(
                      'caption' => Mage::helper('sales')->__('View'),
                      'url'     => array('base'=>'*/sales_invoice/view'),
                      'field'   => 'invoice_id'
                  )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            )
        );

        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        $this->addExportType('*/*/exportSimpleCsv', Mage::helper('customer')->__('CSV'));
        return $this;
    }

    protected function _prepareMassaction()
    {
        /** Remove all mass actions for now */
        return $this;
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*', array('_current'=> true));
    }

    public function getTotals()
    {
        $total      = array();
        $collection = $this->getCollection();
        if ($this->_isExport) {
            $collection->setPageSize($collection->getSize())->getItems();
        }

        foreach ($collection as $invoice) {
            foreach ($this->getColumns() as $column) {
                if ($column->getTotal() == "sum") {
                    if (array_key_exists($column->getIndex(), $total)) {
                        $total[$column->getIndex()] = $total[$column->getIndex()] + $invoice->getData($column->getIndex());
                    } else {
                        $total[$column->getIndex()] = $invoice->getData($column->getIndex());
                    }
                }
            }
        }

        return new Varien_Object($total);
    }
}
