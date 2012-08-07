<?php
/**
 * Sales Order Shipment PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Pick extends Mage_Sales_Model_Order_Pdf_Aabstract
{
	public function getPdf($shipments = array())
	{

		$this->_beforeGetPdf();
		$this->_initRenderer('shipment');
		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();

		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$pdf->pages[] = $page;

		$this->_setFontBold($style, 10);

		// page title
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontItalic($page,20);
		$page->drawText(Mage::helper('sales')->__('Order-combined Pick List'), 31, 800, 'UTF-8');

		$this->y = 777;

		$items = array();
		$itemc = array();
		$k=0;
		foreach ($shipments as $ship) {
			$shipment = $ship;
			foreach ($ship->getAllItems() as $item){
				$items["key".$k]=$item;
				$itemc["key".$k]=$item;
				$k++;
			}
		}
			
		$nitems = array();

		foreach($itemc as $k1 =>$va){
			$v = array_shift($items);
			$v->_num = $v->getQty();
			foreach ($items as $key=>$value){
				if ($value->getOrderItem()->getProductOptionByCode('simple_sku')){
					$sku1 = $value->getOrderItem()->getProductOptionByCode('simple_sku');
					$sku2 = $v->getOrderItem()->getProductOptionByCode('simple_sku');
				}else{
					$sku1 = $value->getSku();
					$sku2 = $v->getSku();
				}
				if($sku1==$sku2){
					$v->_num = $v->getQty()+$value->getQty();
					$value->setQty(0);
					$items[$key]=$value;
				}
			}
			$nitems[$k1] = $v;
		}

		$quan = 0;
		$amount = 0;
		$nvalues = array();
		foreach($nitems as $item){
			if($item->_num > 0){
				$ke = $item->getSku();
				$quan=$quan+$item->_num;
				$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$item->getSku());								
				$amount=$amount+($item->_num)*(is_object($product)?$product->getCost():0);
				$nvalues[$ke]= $item;
			}
		}
		ksort($nvalues,SORT_REGULAR);
		foreach($nvalues as $item){
			if($item->_num!=0){
				$order = $shipment->getOrder();
				if ($item->getOrderItem()->getParentItem()) {
					continue;
				}
				if ($this->y<15) {
					$page = $this->newPage();
				}
				$page = $this->_drawItem($item, $page, $order);
			}
		}

		$lineBlock = array(
            'lines'  => array(),
            'height' => 15
		);

		$currency = Mage::getStoreConfig('pickpack_options/messages/pickpack_currency');
		$currency = Mage::app()->getLocale()->currency($currency)->getSymbol();
		$costyn = Mage::getStoreConfig('pickpack_options/messages/pickpack_cost');
		$countyn = Mage::getStoreConfig('pickpack_options/messages/pickpack_count');
		if($countyn==0){
			$lineBlock['lines'][] = array(
			array(
                            'text'      => "Total quantity",
                            'feed'      => 475,
                            'align'     => 'right',
                            'font_size' => 10,
                            'font'      => 'bold'
                            ),
                            array(
                            'text'      => $quan,
                            'feed'      => 565,
                            'align'     => 'right',
                            'font_size' => 10,
                            'font'      => 'bold'
                            )
                            );

		}
		if($costyn == 0){
			$lineBlock['lines'][] = array(
			array(
                            'text'      => "Total cost",
                            'feed'      => 475,
                            'align'     => 'right',
                            'font_size' => 10,
                            'font'      => 'bold'
                            ),
                            array(
                            'text'      => $currency."  ".($amount),
                            'feed'      => 565,
                            'align'     => 'right',
                            'font_size' => 10,
                            'font'      => 'bold'
                            )
                            );
		}
		$page = $this->drawLineBlocks($page, array($lineBlock));

		$printdate = Mage::getStoreConfig('pickpack_options/messages/pickpack_packprint');
		if($printdate!=1){
		$this->_setFontBold($page);
        $page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A'), 210, 18, 'UTF-8');
		}
        
		$this->_afterGetPdf();

		if ($shipment->getStoreId()) {
			Mage::app()->getLocale()->revert();
		}
		return $pdf;
	}


	public function newPage(array $settings = array())
	{
		$page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
		$this->_getPdf()->pages[] = $page;
		$this->y = 800;
		return $page;
	}
}