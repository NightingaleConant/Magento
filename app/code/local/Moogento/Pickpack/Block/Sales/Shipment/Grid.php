<?php
class Moogento_Pickpack_Block_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function _prepareMassaction()
	{
		parent::_prepareMassaction();
		
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
		
		// $this->getMassactionBlock()->addItem('seperator2', array(
		// 	     'label'=> Mage::helper('sales')->__('---------------'),
		// 	     'url'  => '',
		// 	));
		// 	
		// 	$this->getMassactionBlock()->addItem('pdfpickjim_shipment', array(
		// 	     'label'=> Mage::helper('sales')->__('*test* PDF Order-combined Pick List'),
		// 	     'url'  => $this->getUrl('pickpack/sales_shipment/pickjim'),
		// 	));
		
		return $this;
	}
}
