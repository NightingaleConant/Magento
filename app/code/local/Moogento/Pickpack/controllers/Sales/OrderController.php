<?php
require_once 'app/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php';
class Moogento_Pickpack_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{

	/**
	 * Class Constructor
	 * call the parent Constructor
	 */

	public function __constuct()
	{
		parent::__construct();
	}


	protected function _initAction()
	{
		$this->loadLayout()
		->_setActiveMenu('sales/order')
		->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
		// ->_addBreadcrumb($this->__('Orders'), $this->__('Trash Orders'));
		return $this;
	}


	public function indexAction()
	{
		parent::indexAction();
	}

	public function mooinvoiceAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids');        
		$flag = false;
		$from_shipment = false;
		if (!empty($orderIds)) 
		{
				if(Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1)
				{         
					$pdf = Mage::getModel('sales/order_pdf_invoices')->getPdf2($orderIds,$from_shipment,'invoice');	 
					return $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
				}
				else
				{         
					$pdf = Mage::getModel('sales/order_pdf_invoices')->getPdfDefault($orderIds,$from_shipment,'invoice');	 
					return $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
				}
		}
		$this->_redirect('*/*/');
	}
	
	public function packAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids');        
		$flag = false;
		$from_shipment = false;
		if (!empty($orderIds)) 
		{
				if(Mage::getStoreConfig('pickpack_options/wonder/page_template') == 1)
				{         
					$pdf = Mage::getModel('sales/order_pdf_invoices')->getPdf2($orderIds,$from_shipment,'pack');	 
					return $this->_prepareDownloadResponse('packing-sheet'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
				}
				else
				{         
					$pdf = Mage::getModel('sales/order_pdf_invoices')->getPdfDefault($orderIds,$from_shipment,'pack');	 
					return $this->_prepareDownloadResponse('packing-sheet'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
				}
		}
		$this->_redirect('*/*/');
	}

	public function mooinvoicepackAction()
	{
		$orderIds = $this->getRequest()->getPost('order_ids');        
		$flag = false;
		$from_shipment = false;

		if (!empty($orderIds)) 
		{     
					$pdfA = Mage::getModel('sales/order_pdf_invoices')->getPdfDefault($orderIds,$from_shipment,'invoice');	 
					$pdfB = Mage::getModel('sales/order_pdf_invoices')->getPdfDefault($orderIds,$from_shipment,'pack');
					
					foreach ($pdfB->pages as $page){
						$pdfA->pages[] = $page;
					}

					return $this->_prepareDownloadResponse('invoice+packing-sheet'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdfA->render(), 'application/pdf');
		}

		$this->_redirect('*/*/');
	}
	
	public function pickAction(){
		$orderIds = $this->getRequest()->getPost('order_ids');

		if (!empty($orderIds)) 
		{
				$pdf = Mage::getModel('sales/order_pdf_invoices')->getPickSeparated2($orderIds);	 
				return $this->_prepareDownloadResponse('pick-list-separated'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
		}
		$this->_redirect('*/*/');
	}
	
	
	public function enpickAction(){
		$orderIds = $this->getRequest()->getPost('order_ids');

		if (!empty($orderIds)) 
		{
				$pdf = Mage::getModel('sales/order_pdf_invoices')->getPickCombined($orderIds);	 
				return $this->_prepareDownloadResponse('pick-list-combined'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
		}
		$this->_redirect('*/*/');
	}
	

	public function stockAction(){
		$orderIds = $this->getRequest()->getPost('order_ids');

		if (!empty($orderIds)) 
		{
				$pdf = Mage::getModel('sales/order_pdf_invoices')->getPickStock2($orderIds);	 
				return $this->_prepareDownloadResponse('out-of-stock-list'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
		}
		$this->_redirect('*/*/');
	}
	
	public function labelAction(){
		$orderIds = $this->getRequest()->getPost('order_ids');

		if (!empty($orderIds)) 
		{
				$pdf = Mage::getModel('sales/order_pdf_invoices')->getLabel($orderIds);	 
				return $this->_prepareDownloadResponse('address-labels'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
		}
		$this->_redirect('*/*/');
	}
	
		
	public function pickcsvAction(){
		$orderIds = $this->getRequest()->getPost('order_ids');

		if (!empty($orderIds)) 
		{
				$pdf = Mage::getModel('sales/order_pdf_invoices')->getCsvPickSeparated2($orderIds);	 
				//$pdf = 'test text';
				// Send the CSV file to the browser for download

				//header("Content-type: text/x-csv");
				//header("Content-Disposition: attachment; filename=$filename.csv");
				//echo $output;
				//exit;
				
				$fileName = 'pick-list-separated-csv'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.csv';
				return $this->_prepareDownloadResponse($fileName, $pdf);
				//return $this->_prepareDownloadResponse($fileName, $pdf->render(), 'text/x-csv');
		}
		$this->_redirect('*/*/');
	}
		
	// public function pickjimAction(){
	// 	$orderIds = $this->getRequest()->getPost('order_ids');
	// 
	// 	if (!empty($orderIds)) 
	// 	{
	// 			$pdf = Mage::getModel('sales/order_pdf_invoices')->getPickCombined($orderIds);	 
	// 			return $this->_prepareDownloadResponse('pick-list-combined'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
	// 	}
	// 	$this->_redirect('*/*/');
	// }
	

	// public function enpickAction(){
	// 	$orderIds = $this->getRequest()->getPost('order_ids');
	// 	$flag = false;
	// 	if (!empty($orderIds)) {
	// 		foreach ($orderIds as $orderId) {
	// 			$order = Mage::getModel('sales/order')->load($orderId);
	// 			$shipments = Mage::getResourceModel('sales/order_invoice_collection')
	// 			->addAttributeToSelect('*')
	// 			->setOrderFilter($orderId)
	// 			->load();
	//                 
	// 			if ($shipments->getSize() > 0) {
	// 				$flag = true;
	// 				foreach ($shipments as $v){
	// 					$a[]  = $v;
	// 				}
	// 			}
	// 		}
	// 		$pdf = Mage::getModel('sales/order_pdf_pick')->getPdf($a);            
	// 		if ($flag) {
	// 			return $this->_prepareDownloadResponse('combined-picklist'.Mage::getSingleton('core/date')->date('Y-m-d_H').'.pdf', $pdf->render(), 'application/pdf');
	// 		} else {
	// 			$this->_getSession()->addError($this->__('There are no printable documents related to selected orders'));
	// 			$this->_redirect('*/*/');
	// 		}
	// 	}
	// 	$this->_redirect('*/*/');
	// }
}
