<?php
class Moogento_Pickpack_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
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
		
	
		$this->getMassactionBlock()->addItem('pdfinvoice_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Invoice'),
		     'url'  => $this->getUrl('pickpack/sales_order/mooinvoice'),
			));
			
		$this->getMassactionBlock()->addItem('pdfpack_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Packing Sheet'),
		     'url'  => $this->getUrl('pickpack/sales_order/pack'),
			));
			
		$this->getMassactionBlock()->addItem('pdfinvoice_pdfpack_order', array(
		 'label'=> Mage::helper('sales')->__('PDF Invoice & Packing Sheet'),
		 'url'  => $this->getUrl('pickpack/sales_order/mooinvoicepack'),
		));		
		
		$this->getMassactionBlock()->addItem('pdfpick_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Order-separated Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_order/pick'),
		));
		
		$this->getMassactionBlock()->addItem('pdfenpick_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Order-combined Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_order/enpick'),
		));	
		
		$this->getMassactionBlock()->addItem('pdfstock_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Out-of-stock List'),
		     'url'  => $this->getUrl('pickpack/sales_order/stock'),
		));
		
		$this->getMassactionBlock()->addItem('pdflabel_order', array(
		     'label'=> Mage::helper('sales')->__('PDF Address Labels'),
		     'url'  => $this->getUrl('pickpack/sales_order/label'),
		));
		
		$this->getMassactionBlock()->addItem('seperator2', array(
		     'label'=> Mage::helper('sales')->__('---------------'),
		     'url'  => '',
		));
		
		$this->getMassactionBlock()->addItem('csvpick_order', array(
		     'label'=> Mage::helper('sales')->__('CSV Order-separated Pick List'),
		     'url'  => $this->getUrl('pickpack/sales_order/pickcsv'),
			));
			
		// $this->getMassactionBlock()->addItem('seperator2', array(
		// 	     'label'=> Mage::helper('sales')->__('---------------'),
		// 	     'url'  => '',
		// 	));
		// 	
		// 	$this->getMassactionBlock()->addItem('pdfpickjim_order', array(
		// 	     'label'=> Mage::helper('sales')->__('*test* PDF Order-combined Pick List'),
		// 	     'url'  => $this->getUrl('pickpack/sales_order/pickjim'),
		// 	));
		
		return $this;
	}
}
