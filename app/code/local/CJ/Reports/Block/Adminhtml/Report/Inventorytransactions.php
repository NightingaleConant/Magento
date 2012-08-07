<?php

/**
 *
 */
class  CJ_Reports_Block_Adminhtml_Report_Inventorytransactions extends Mage_Adminhtml_Block_Sales_Order_Grid
{

    protected function getGridHeader()
    {
        return 'Inventory Transactions';
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'real_order_id',
            array(
                'header'=> Mage::helper('sales')->__('Order #'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'increment_id',
                'filter'    => false,
                'sortable'  => false,
            )
        );

        $this->addColumn(
            'order_type_id',
            array(
                 'header' => 'Order Type Id',
                 'index'  => 'order_type_id',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );

        $this->addColumn(
            'order_type_code',
            array(
                 'header' => 'Order Type Code',
                 'index'  => 'order_type_code',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_sales_account',
            array(
                 'header' => 'Sales Account',
                 'index'  => 'qb_sales_account',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_sales_account',
            array(
                 'header' => 'Sales Class',
                 'index'  => 'qb_sales_class',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_cost_account',
            array(
                 'header' => 'Cost Account',
                 'index'  => 'qb_cost_account',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_cost_class',
            array(
                 'header' => 'Cost Class',
                 'index'  => 'qb_cost_class',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_return_account',
            array(
                 'header' => 'Return Account',
                 'index'  => 'qb_return_account',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_return_class',
            array(
                 'header' => 'Return Class',
                 'index'  => 'qb_return_class',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_allowances_account',
            array(
                 'header' => 'Allowances Account',
                 'index'  => 'qb_allowances_account',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );
        $this->addColumn(
            'qb_allowances_class',
            array(
                 'header' => 'Allowances Class',
                 'index'  => 'qb_allowances_class',
                 'type'   => 'text',
                 'filter'    => false,
                 'sortable'  => false,
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                  'header'    => 'Store',
                  'index'     => 'store_id',
                  'type'      => 'store',
                  'store_view'=> true,
                  'display_deleted' => true,
             ));

        }

        $this->addColumn('status', array(
                                        'header' => Mage::helper('sales')->__('Status'),
                                        'index' => 'status',
                                        'type'  => 'options',
                                        'width' => '70px',
                                        'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
                                   ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                             array(
                                  'header'    => Mage::helper('sales')->__('Action'),
                                  'width'     => '50px',
                                  'type'      => 'action',
                                  'getter'     => 'getId',
                                  'actions'   => array(
                                      array(
                                          'caption' => Mage::helper('sales')->__('View'),
                                          'url'     => array('base'=>'*/sales_order/view'),
                                          'field'   => 'order_id'
                                      )
                                  ),
                                  'filter'    => false,
                                  'sortable'  => false,
                                  'index'     => 'stores',
                                  'is_system' => true,
                             ));
        }

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('sales')->__('Excel XML'));

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
}
