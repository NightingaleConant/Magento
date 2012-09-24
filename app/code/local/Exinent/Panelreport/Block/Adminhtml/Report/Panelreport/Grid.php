<?php
/*
Prashant's test code for the report

SELECT aaoa.promo_code, ckc.ckc_key_code_name, count( aaoa.order_id ) , sum( sfo.subtotal ),  sum( sfo.shipping_amount )
FROM `amasty_amorderattr_order_attribute` AS aaoa
LEFT JOIN `custom_key_code` AS ckc ON aaoa.promo_code = ckc.ckc_key_code
LEFT JOIN `sales_flat_order` AS sfo ON aaoa.order_id = sfo.entity_id
WHERE aaoa.promo_code LIKE '%B6001B99%'
and sfo.created_at > '07/01/11'
and sfo.created_at < '07/17/12'
GROUP BY aaoa.promo_code
LIMIT 0 , 26

*/

class Exinent_Panelreport_Block_Adminhtml_Report_Panelreport_Grid extends Mage_Adminhtml_Block_Report_Grid {     

    public function __construct()    
    {
    	parent::__construct();
	$this->setSaveParametersInSession(false);
    	$this->setId('gridPanel');
    }

    protected function _prepareCollection()
    {
	parent::_prepareCollection();
        $this->getCollection()->initReport('panelreport/amastyorder_collection');
    }

    protected function _prepareColumns()
    {
	    $this->addColumn('promo_code', array(
		    'header'    =>Mage::helper('reports')->__('Promo Code'),
		    'type'  => 'text',
		    'index' => 'promo_code',
		    'sortable'  => true
		    )
	    );
		
	    /*if (!Mage::app()->isSingleStoreMode()) {
		$this->addColumn('store_id', array(
		    'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
		    'index'     => 'store_id',
		    'type'      => 'store',
		    'store_view'=> true,
		    'display_deleted' => true,
		));
	    }*/
        
	    $this->addColumn('ckc_key_code_name', array(
			'header'    =>Mage::helper('reports')->__('Description'),
			'type'  => 'text',
			'index' => 'ckc_key_code_name',
			'sortable'  => false
			)
	    );
        
	    $this->addColumn('total_orders', array(
			'header'    =>Mage::helper('reports')->__('Resp'),
			'type'  => 'text',
			'index' => 'total_orders',
			'sortable'  => false
			)
	    );
		
	    $this->addColumn('gross_total', array(
			'header'    =>Mage::helper('reports')->__('Grs Ord$'),
			'type'  => 'text',
			'index' => 'gross_total',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('shipping_amount', array(
			'header'    =>Mage::helper('reports')->__('S&H$'),
			'type'  => 'text',
			'index' => 'shipping_amount',
			'sortable'  => false
			)
	    );
		
	    $this->addColumn('tax_amount', array(
			'header'    =>Mage::helper('reports')->__('Sls Tax$'),
			'type'  => 'text',
			'index' => 'tax_amount',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('grs_sls', array(
			'header'    =>Mage::helper('reports')->__('Grs Sls$'),
			'type'  => 'text',
			'index' => 'grs_sls',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('total_refunded', array(
			'header'    =>Mage::helper('reports')->__('Retn$'),
			'type'  => 'text',
			'index' => 'total_refunded',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('return_percent', array(
			'header'    =>Mage::helper('reports')->__('Ret%'),
			'type'  => 'text',
			'index' => 'return_percent',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('adjustment_positive', array(
			'header'    =>Mage::helper('reports')->__('Adj$'),
			'type'  => 'text',
			'index' => 'adjustment_positive',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('adjustment_percent', array(
			'header'    =>Mage::helper('reports')->__('Adj%'),
			'type'  => 'text',
			'index' => 'adjustment_percent',
			'sortable'  => false
			)
	    );
	    
	    /*$this->addColumn('write_off', array(
			'header'    =>Mage::helper('reports')->__('W/O$'),
			'type'  => 'text',
			'index' => 'write_off',
			'sortable'  => false
			)
	    );*/
	    
	    $this->addColumn('base_total_paid', array(
			'header'    =>Mage::helper('reports')->__('Pay$'),
			'type'  => 'text',
			'index' => 'base_total_paid',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('base_total_percent', array(
			'header'    =>Mage::helper('reports')->__('Pay%'),
			'type'  => 'text',
			'index' => 'base_total_percent',
			'sortable'  => false
			)
	    );
	    
	    $this->addColumn('created_at', array(
		'header' => Mage::helper('sales')->__('Created Date'),
		'index' => 'created_at',
		'type' => 'datetime',
		'width' => '100px',
	    ));
        
	    
	    $this->addExportType('*/*/exportPanelreportCsv', Mage::helper('reports')->__('CSV'));
	    return parent::_prepareColumns();
	}
}