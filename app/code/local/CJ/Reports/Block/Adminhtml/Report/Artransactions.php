<?php
/**
 * DEBUGGING:
 *  If you wish to debug this grid, add ?debug=true to the url, this will add a column with the age
 *  of the invoice to aid you
 */
class CJ_Reports_Block_Adminhtml_Report_Artransactions extends Mage_Adminhtml_Block_Sales_Invoice_Grid
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
        return 'Accounts Receivable';
    }

    protected function _prepareCollection()
    {
        /** @var $collection \Mage_Sales_Model_Mysql4_Order_Invoice_Grid_Collection */
        $collection = Mage::getResourceModel('sales/order_invoice_grid_collection');
        $collection->addAttributeToFilter('main_table.state', Mage_Sales_Model_Order_Invoice::STATE_OPEN);

        $collection->getSelect()
                ->join(
                    array('order' => $collection->getTable('sales/order')),
                    'order.increment_id = main_table.order_increment_id',
                    array('order.increment_id')
                );
        $collection->getSelect()
                ->join(
                    array('customer_group' => $collection->getTable('customer/customer_group')),
                    'customer_group.customer_group_id = order.customer_group_id',
                    array('customer_group.customer_group_code')
                );
        $collection->getSelect()
                ->join(
                    array('custom_attribute' => $collection->getTable('amorderattr/order_attribute')),
                    'custom_attribute.order_id = main_table.order_id',
                    array('custom_attribute.order_type_code')
                );
        $collection->getSelect()
                ->columns('(`main_table`.`grand_total` - COALESCE( (SELECT SUM(`installment_master`.`installment_master_amount_due`) FROM `installment_master` WHERE `installment_master`.`order_id` = `main_table`.`order_increment_id` AND `installment_master`.`installment_master_installment_paid` = 1), 0) ) as total_paid');

        $this->setCollection($collection);

        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter   = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
                if (array_key_exists('cal_date', $data) && !empty($data['cal_date'])) {
                    $this->setDate($data['cal_date']);
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
                'index' => 'customer_group_code',
                'filter_index' => 'customer_group.customer_group_code',
                'sortable'  => true,
            )
        );

        $this->addColumn(
            'order_type_code',
            array(
                'header' => 'Order Type Code',
                'index' => 'order_type_code',
                'filter_index' => 'custom_attribute.order_type_code',
                'renderer' => 'CJ_Reports_Block_Adminhtml_Renderer_Ordertypecode',
                'filter'   => 'CJ_Reports_Block_Adminhtml_Filter_Ordertypecode',
                'sortable'  => true,
                'type'      => 'options'
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
                 'index'     => '0days',
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
                 'index'     => '30days',
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
                 'index'     => '60days',
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
                 'index'     => '90days',
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
                 'index'     => '120days',
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
                 'index'     => '180days',
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
                 'index'     => '240days',
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
