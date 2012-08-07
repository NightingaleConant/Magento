<?php
class Moogento_Pickpack_Block_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
	
	public function __construct()
	{
		parent::__construct();
	}

	//if($canada_post === true)
	//{
		protected $_importTypes = array();
	//}
	
	protected function _prepareMassaction()
	{
		parent::_prepareMassaction();
		//	
		//if($canada_post === true)
		//{
		$this->getMassactionBlock()->addItem('seperator0', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
		
		if (Mage::getStoreConfig('shipping/canadapostmgr/active')) {
			 $this->getMassactionBlock()->addItem('canadapostmgr', array(
             'label'=> Mage::helper('sales')->__('Export Shipment Details'),
             'url'  => $this->getUrl('*/sales_shipment_export/xmlexport'),
       		 ));
		}
		//}
		
		$this->getMassactionBlock()->addItem('seperator1', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
		$this->getMassactionBlock()->addItem('pdfpack_shipment', array(
		     'label'=> Mage::helper('sales')->__('PDF Packing Sheets'),
		     'url'  => $this->getUrl('pickpack/sales_shipment/pack'),
		));

		$this->getMassactionBlock()->addItem('pdfpick_shipment', array(
		     'label'=> Mage::helper('sales')->__('PDF Order-separated Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_shipment/pick'),
		));
		
		$this->getMassactionBlock()->addItem('pdfenpick_shipment', array(
		     'label'=> Mage::helper('sales')->__('PDF Shipment-combined Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_shipment/enpick'),
		));	
		
		$this->getMassactionBlock()->addItem('pdfstock_shipment', array(
		     'label'=> Mage::helper('sales')->__('PDF Out-of-stock List'),
		     'url'  => $this->getUrl('pickpack/sales_shipment/stock'),
		));
		
		$this->getMassactionBlock()->addItem('pdflabel_shipment', array(
		     'label'=> Mage::helper('sales')->__('PDF Address Labels'),
		     'url'  => $this->getUrl('pickpack/sales_shipment/label'),
		));
		
		return $this;
	}
	
	//if($canada_post === true)
	//{
		public function getButtonHtml($label, $onclick, $class='', $id=null) 
		{ 
		return $this->getLayout()->createBlock('adminhtml/widget_button')
		->setData(array(
		'label'=> $label,
		'onclick'=> $onclick,
		'class'=> $class,
		'type'=> 'button',
		'id'=> $id,
		))
		->toHtml();
		}
		
		public function getImportTypes()
    	{
        return empty($this->_importTypes) ? false : $this->_importTypes;
    	}
    
     	public function addImportType($label)
    	{
        $this->_importTypes[] = new Varien_Object(
            array(
                'label' => $label
            )
        );
        return $this;
    	}
    
     	protected function _prepareColumns()
    	{
    	$this->addImportType('shipments');
    	return parent::_prepareColumns();
    	}
    //}
	
}
