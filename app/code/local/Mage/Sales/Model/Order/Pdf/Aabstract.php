<?php
/**
 * Sales Order PDF abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Order_Pdf_Aabstract extends Varien_Object
{
	public $y;
	/**
	 * Item renderers with render type key
	 *
	 * model    => the model name
	 * renderer => the renderer model
	 *
	 * @var array
	 */
	protected $_renderers = array();

	const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';
	const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';
	const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';

	/**
	 * Zend PDF object
	 *
	 * @var Zend_Pdf
	 */
	protected $_pdf;

	/**
	 * Retrieve PDF
	 *
	 * @return Zend_Pdf
	 */
	abstract public function getPdf();

	/**
	 * Returns the total width in points of the string using the specified font and
	 * size.
	 *
	 * This is not the most efficient way to perform this calculation. I'm
	 * concentrating optimization efforts on the upcoming layout manager class.
	 * Similar calculations exist inside the layout manager class, but widths are
	 * generally calculated only after determining line fragments.
	 *
	 * @param string $string
	 * @param Zend_Pdf_Resource_Font $font
	 * @param float $fontSize Font size in points
	 * @return float
	 */
	public function widthForStringUsingFontSize($string, $font, $fontSize)
	{
		$drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
		$characters = array();
		for ($i = 0; $i < strlen($drawingString); $i++) {
			$characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
		}
		$glyphs = $font->glyphNumbersForCharacters($characters);
		$widths = $font->widthsForGlyphs($glyphs);
		$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
		return $stringWidth;

	}

	/**
	 * Calculate coordinates to draw something in a column aligned to the right
	 *
	 * @param string $string
	 * @param int $x
	 * @param int $columnWidth
	 * @param Zend_Pdf_Resource_Font $font
	 * @param int $fontSize
	 * @param int $padding
	 * @return int
	 */
	public function getAlignRight($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5)
	{
		$width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
		return $x + $columnWidth - $width - $padding;
	}

	/**
	 * Calculate coordinates to draw something in a column aligned to the center
	 *
	 * @param string $string
	 * @param int $x
	 * @param int $columnWidth
	 * @param Zend_Pdf_Resource_Font $font
	 * @param int $fontSize
	 * @return int
	 */
	public function getAlignCenter($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize)
	{
		$width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
		return $x + round(($columnWidth - $width) / 2);
	}

	protected function insertLogo(&$page, $page_size = 'a4', $pack_or_invoice = 'pack', $store = null)
	{
		$sub_folder = 'logo_pack';
		$option_group = 'wonder';
		//echo 'pack_or_invoice:'.$pack_or_invoice;exit;
		if($pack_or_invoice == 'wonder_invoice')
		{
			$sub_folder = 'logo_invoice';
			$option_group = 'wonder_invoice';
		}
		
		$packlogo_filename = Mage::getStoreConfig('pickpack_options/'.$option_group.'/pack_logo', $store);
		if($packlogo_filename){
			$packlogo_path = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/'.$sub_folder.'/' . $packlogo_filename;
			if (is_file($packlogo_path)) {	
				$packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);			
				if($page_size == 'letter') $page->drawImage($packlogo, 20, 734, 289, 775);
				else $page->drawImage($packlogo, 20, 784, 289, 825);
			}
		}else{
			$image = Mage::getStoreConfig('sales/identity/logo', $store);
			if ($image) {
				$image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
				if (is_file($image)) {
					$image = Zend_Pdf_Image::imageWithPath($image);
					// $page->drawImage($image, $left, $bottom, $right, $top);
					if($page_size == 'letter') $page->drawImage($image, 20, 734, 289, 775);
					else $page->drawImage($image, 20, 784, 289, 825);
					
				}
			}
		}
		//return $page;
	}

	protected function insertAddress(&$page, $location = 'top', $store = null)
	{
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontRegular($page, 7);
		$page->setLineWidth(12);
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));

			$shippingaddress = Mage::getStoreConfig('pickpack_options/wonder/pickpack_shipaddress');
			$ships = explode(',',$shippingaddress);
			$returngaddress = Mage::getStoreConfig('pickpack_options/wonder/pickpack_returnaddress');
			$returns = explode(',',$returngaddress);
			$returngfont = Mage::getStoreConfig('pickpack_options/wonder/pickpack_returnfont');
			$this->_setFontRegular($page, isset($returngfont)?$returngfont:9);
			if($returngfont >= 14){
			$page->setLineWidth(2);
			$page->drawLine(310+$returns[1], 24+$returns[0], 310+$returns[1], 205+$returns[0]);
			$page->drawLine(20+$ships[1], 24+$ships[1], 20+$ships[0], 205+$ships[0]);
			$page->setLineWidth(0);
		
			$this->y = 165;
			$page->drawText('From :', 320+$returns[1], $this->y+$returns[1], 'UTF-8');
			$this->y -=28;
			foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value)
			{
				if ($value!=='')
				{
					$page->drawText(trim(strip_tags($value)), 320+$returns[1], $this->y+$returns[1], 'UTF-8');
					$this->y -=25;
				}
			}	
			}
			else
			{
				
			// $page->setLineWidth(2);
			// 			$page->drawLine(310+$returns[1], 44+$returns[0], 310+$returns[1], 135+$returns[0]);
			// 			$page->drawLine(20+$ships[1], 44+$ships[1], 20+$ships[0], 135+$ships[0]);
			// 			$page->setLineWidth(0);

			// $returnglogo = Mage::getStoreConfig('pickpack_options/wonder/pickpack_returnlogo');
			// 		$nudgelogo = Mage::getStoreConfig('pickpack_options/wonder/pickpack_nudgelogo');
			// 		$newlogo = explode(',',$nudgelogo);
			// 		if($returnglogo==1)
			// 		{
			// 			$image = Mage::getStoreConfig('pickpack_options/wonder/pickpack_logo', $store);
			// 			if ($image) 
			// 			{
			// 				$image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_third/' . $image;
			// 				if (is_file($image)) 
			// 				{
			// 					$image = Zend_Pdf_Image::imageWithPath($image);
			// 					$page->drawImage($image, 315+$newlogo[1], 110+$newlogo[0], 500+$newlogo[0], 130+$newlogo[0]);
			// 				}
			// 			}
			// 		}
			// 		$this->y = 105;
			// 		$page->drawText('From :', 320+$returns[1], $this->y+$returns[1], 'UTF-8');
			// 		$this->y -=18;
			// 		foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value)
			// 		{
			// 			if ($value!=='')
			// 			{
			// 				$page->drawText(trim(strip_tags($value)), 320+$returns[1], $this->y+$returns[1], 'UTF-8');
			// 				$this->y -=9;
			// 			}
			// 		}	
			// }	
		}
	}


	/**
	 * Format address
	 *
	 * @param string $address
	 * @return array
	 */
	protected function _formatAddress($address)
	{
		$return = array();
		foreach (split('\|', $address) as $str) {
			foreach (Mage::helper('core/string')->str_split($str, 65, true, true) as $part) {
				if (empty($part)) {
					continue;
				}
				$return[] = $part;
			}
		}
		return $return;
	}

	protected function insertOrder(&$page, $order, $putOrderId = true)
	{
		/* @var $order Mage_Sales_Model_Order */
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
		$page->drawRectangle(25, 780, 570, 758);

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
		$this->_setFontBold($page,18);

		$page->drawText(Mage::helper('sales')->__('#') .$order->getRealOrderId() . '  /  ' . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), 31, 762, 'UTF-8');
			

		$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));
		$page->drawRectangle(25, 755, 570, 730);

		$shippingaddress = Mage::getStoreConfig('pickpack_options/wonder/pickpack_shipaddress');
		$ships = explode(',',$shippingaddress);
		$shippingfont = Mage::getStoreConfig('pickpack_options/wonder/pickpack_shipfont');
		// bottom address order id
		$page->setFillColor(new Zend_Pdf_Color_Rgb(0.8, 0.8, 0.8));
		if($shippingfont >= 14){
		$page->drawRectangle(19+$ships[1], 205+$ships[0], 200+$ships[1], 190+$ships[0]);			
		$page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
		$this->_setFontBold($page);
		$page->drawText(Mage::helper('sales')->__('#') .$order->getRealOrderId(), 31+$ships[1], 195+$ships[1], 'UTF-8');
		}else{
		$page->drawRectangle(19+$ships[1], 135+$ships[0], 200+$ships[1], 123+$ships[0]);			
		$page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
		$this->_setFontBold($page);
		$page->drawText(Mage::helper('sales')->__('#') .$order->getRealOrderId(), 31+$ships[1], 125+$ships[1], 'UTF-8');
		}
		/* Calculate blocks info */

		/* Billing Address */
		// $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
		$shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
		$shippingMethod  = $order->getShippingDescription();

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
		$this->_setFontItalic($page,16);

		$page->drawText(Mage::helper('sales')->__('shipping address'), 31, 738 , 'UTF-8');
		$this->_setFontRegular($page);

		$y = 720 - (count($shippingAddress) * 10 + 11);

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

		$page->drawRectangle(25, 730, 570, $y);
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->y = 715;

		foreach ($shippingAddress as $value)
		{
			if ($value!=='')
			{
				$address_line = strip_tags(trim(str_replace('T: Phone','',$value)));
				if(preg_match('~^T:~',$address_line))
				{
					$this->y -=10;
				}
				$page->drawText($address_line, 35, $this->y, 'UTF-8');
				$this->y -=10;
			}
		}




		$bottom_coords =105;
		$this->_setFontRegular($page, isset($shippingfont)?$shippingfont:13);
		//		$page->drawText('To :', 31+$ships[1], $bottom_coords+$ships[1], 'UTF-8');
		//		$bottom_coords -=20;

		$newshippingAddress = array();
		$i = 0;
		foreach ($shippingAddress as $ks=>$shipad){
			if($ks == 0){
				$newshippingAddress[$i]="      ".$shipad;
			}
			elseif($ks == 1){
				foreach (explode(',',$shipad) as $skv){
					$newshippingAddress[$i] = $skv.',';
					$i++;
				}
			}else{
				$newshippingAddress[$i]=$shipad;
			}
			$i++;
		}
		if($shippingfont >= 14){
	    $bottom_coords =170;
		foreach ($newshippingAddress as $value)
		{
			if ($value!=='')
			{
				$address_line = strip_tags(trim(str_replace('T: Phone','',$value)));
				if(preg_match('~^T:~',$address_line))
				{
					$bottom_coords -=22;
				}
				$page->drawText($address_line, 31+$ships[1], $bottom_coords+$ships[1], 'UTF-8');
				$bottom_coords -=22;
			}
		}
		}else{
		foreach ($newshippingAddress as $value)
		{
			if ($value!=='')
			{
				$address_line = strip_tags(trim(str_replace('T: Phone','',$value)));
				if(preg_match('~^T:~',$address_line))
				{
					$bottom_coords -=10;
				}
				$page->drawText($address_line, 31+$ships[1], $bottom_coords+$ships[1], 'UTF-8');
				$bottom_coords -=10;
			}
		}
		}
		//		foreach ($shippingAddress as $value)
		//		{
		//			if ($value!=='')
		//			{
		//				$address_line = strip_tags(trim(str_replace('T: Phone','',$value)));
		//
		//				if(preg_match('~^T:~',$address_line))
		//				{
		//					$bottom_coords -=10;
		//					$this->y -=10;
		//				}
		//
		//				$page->drawText($address_line, 35, $this->y, 'UTF-8');
		//
		//
		//				// bottom address
		//				$page->drawText($address_line, 31+$ships[1], $bottom_coords+$ships[1], 'UTF-8');
		//
		//				$this->y -=10;
		//				$bottom_coords -=10;
		//			}
		//		}


		$this->_setFontRegular($page);

		$this->y = 700;
			
		$paymentLeft = 35;
		$yPayments   = $this->y - 15;
			
		$this->y -=15;

		$page->drawText($shippingMethod, 285, $this->y, 'UTF-8');

		$yShipments = $this->y;

		$yShipments -=10;
		$tracks = $order->getTracksCollection();

		$yShipments -= 7;

		$currentY = min($yPayments, $yShipments);

		$this->y = $currentY;
		$this->y -= 15;
}

protected function insertOrderPicklist(&$page, $order, $putOrderId = true)
{
	/* @var $order Mage_Sales_Model_Order */
	// $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
	//         $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
	//         $page->drawRectangle(25, 780, 570, 758);

	// page title
	// $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
	// 	$this->_setFontBold($page,16);
	// 	$page->drawText(Mage::helper('sales')->__('Pick List'), 31, 800, 'UTF-8');
	// $this->y = 777;

	// ID rectangle
	$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
	$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));
	$page->drawRectangle(27, $this->y, 570, ($this->y - 20));


	// order #
	$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
	$this->_setFontBold($page,14);
	$page->drawText(Mage::helper('sales')->__('#') .$order->getRealOrderId(), 31, ($this->y - 15), 'UTF-8');

	$barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
	if($barcodes==1){
		$barcodeString = $this->convertToBarcodeString($order->getRealOrderId());
		$page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
		$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 16);
		$page->drawText($barcodeString, 250, ($this->y - 20), 'CP1252');
	}
	// bottom address order id
	// $page->setFillColor(new Zend_Pdf_Color_Rgb(0.8, 0.8, 0.8));
	// 	$page->drawRectangle(19, 135, 200, 123);

	// $page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
	// 		$this->_setFontBold($page);
	// 		$page->drawText(Mage::helper('sales')->__('#') .$order->getRealOrderId(), 31, 125, 'UTF-8');

	/* Calculate blocks info */

	/* Billing Address */
	// $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
	// $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
	
	$shmethod = Mage::getStoreConfig('pickpack_options/picks/pickpack_shipmethod');
	if($shmethod == 0){
	$this->_setFontBold($page,12);
	$shippingMethod  = $order->getShippingDescription();
	$page->drawText($shippingMethod, 420, ($this->y - 15), 'UTF-8');
	}
	// $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
	//        $this->_setFontItalic($page,16);

	/*
	 $page->drawText(Mage::helper('sales')->__('shipping address'), 31, 738 , 'UTF-8');
	 $this->_setFontRegular($page);

	 $y = 720 - (count($shippingAddress) * 10 + 11);

	 $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

	 $page->drawRectangle(25, 730, 570, $y);
	 $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
	 $this->y = 715;
	 $bottom_coords =105;

	 $page->drawText('To :', 31, $bottom_coords, 'UTF-8');
	 $bottom_coords -=20;

	 foreach ($shippingAddress as $value)
	 {
	 if ($value!=='')
	 {
	 $address_line = strip_tags(trim(str_replace('T: Phone','',$value)));

	 if(preg_match('~^T:~',$address_line))
	 {
	 $bottom_coords -=10;
	 $this->y -=10;
	 }

	 $page->drawText($address_line, 35, $this->y, 'UTF-8');

	 // bottom address
	 $page->drawText($address_line, 31, $bottom_coords, 'UTF-8');

	 $this->y -=10;
	 $bottom_coords -=10;
	 }
	 }*/

	$this->y -= 30;

	$paymentLeft = 35;
	$yPayments   = $this->y;

	//	$this->y -=15;

	//	$page->drawText($shippingMethod, 285, $this->y, 'UTF-8');

	$yShipments = $this->y;

	//	$yShipments -=10;
	//	$tracks = $order->getTracksCollection();

	// $yShipments -= 7;

	$currentY = $yShipments;

	$this->y = $currentY;
	//$this->y -= 15;
}

protected function _sortTotalsList($a, $b) {
	if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
		return 0;
	}

	if ($a['sort_order'] == $b['sort_order']) {
		return 0;
	}

	return ($a['sort_order'] > $b['sort_order']) ? 1 : -1;
}

protected function _getTotalsList($source)
{
	$totals = Mage::getConfig()->getNode('global/pdf/totals')->asArray();
	usort($totals, array($this, '_sortTotalsList'));

	return $totals;
}

protected function insertTotals($page, $source){

	// make 1 to show subtotals
	$show_price = 0;

	if($show_price > 0)
	{

		$order = $source->getOrder();
		//        $font = $this->_setFontBold($page);

		$totals = $this->_getTotalsList($source);

		$lineBlock = array(
            'lines'  => array(),
            'height' => 15
		);
		foreach ($totals as $total)
		{
			$amount = $source->getDataUsingMethod($total['source_field']);
			$displayZero = (isset($total['display_zero']) ? $total['display_zero'] : 0);

			if ($amount != 0 || $displayZero)
			{
				$amount = $order->formatPriceTxt($amount);

				if (isset($total['amount_prefix']) && $total['amount_prefix'])
				{
					$amount = "{$total['amount_prefix']}{$amount}";
				}

				//$fontSize = (isset($total['font_size']) ? $total['font_size'] : 10);
				$fontSize = 10;
				//$page->setFont($font, $fontSize);

				$label = Mage::helper('sales')->__($total['title']) . ':';

				$lineBlock['lines'][] = array(
				array(
                        'text'      => $label,
                        'feed'      => 475,
                        'align'     => 'right',
                        'font_size' => $fontSize,
                        'font'      => ''
                        ),
                        array(
                        'text'      => $amount,
                        'feed'      => 565,
                        'align'     => 'right',
                        'font_size' => $fontSize,
                        'font'      => ''
                        ),
                        );

                        //                $page->drawText($label, 475-$this->widthForStringUsingFontSize($label, $font, $fontSize), $this->y, 'UTF-8');
                        //                $page->drawText($amount, 565-$this->widthForStringUsingFontSize($amount, $font, $fontSize), $this->y, 'UTF-8');
                        //                $this->y -=15;
			}
		}

		//        echo '<pre>';
		//        var_dump($lineBlock);

		$page = $this->drawLineBlocks($page, array($lineBlock));
		return $page;
	}
	else return;
}

protected function _parseItemDescription($item)
{
	$matches = array();
	$description = $item->getDescription();
	if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
		return $matches[1];
	}

	return array($description);
}

/**
 * Before getPdf processing
 *
 */
protected function _beforeGetPdf() {
	$translate = Mage::getSingleton('core/translate');
	/* @var $translate Mage_Core_Model_Translate */
	$translate->setTranslateInline(false);
}

/**
 * After getPdf processing
 *
 */
protected function _afterGetPdf() {
	$translate = Mage::getSingleton('core/translate');
	/* @var $translate Mage_Core_Model_Translate */
	$translate->setTranslateInline(true);
}

protected function _formatOptionValue($value, $order)
{
	$resultValue = '';
	if (is_array($value)) {
		if (isset($value['qty'])) {
			$resultValue .= sprintf('%d', $value['qty']) . ' x ';
		}

		$resultValue .= $value['title'];

		if (isset($value['price'])) {
			$resultValue .= " " . $order->formatPrice($value['price']);
		}
		return  $resultValue;
	} else {
		return $value;
	}
}

protected function _initRenderer($type)
{
	$node = Mage::getConfig()->getNode('global/pdf/'.$type);
	foreach ($node->children() as $renderer) {
		$this->_renderers[$renderer->getName()] = array(
                'model'     => (string)$renderer,
                'renderer'  => null
		);
	}
}

/**
 * Retrieve renderer model
 *
 * @throws Mage_Core_Exception
 * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
 */
protected function _getRenderer($type)
{
	if (!isset($this->_renderers[$type])) {
		$type = 'default';
	}

	if (!isset($this->_renderers[$type])) {
		Mage::throwException(Mage::helper('sales')->__('Invalid renderer model'));
	}

	if (is_null($this->_renderers[$type]['renderer'])) {
		$this->_renderers[$type]['renderer'] = Mage::getSingleton($this->_renderers[$type]['model']);
	}

	return $this->_renderers[$type]['renderer'];
}

/**
 * Public method of protected @see _getRenderer()
 *
 * Retrieve renderer model
 *
 * @param string $type
 * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
 */
public function getRenderer($type)
{
	return $this->_getRenderer($type);
}

/**
 * Draw Item process
 *
 * @param Varien_Object $item
 * @param Zend_Pdf_Page $page
 * @param Mage_Sales_Model_Order $order
 * @return Zend_Pdf_Page
 */
protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order)
{
	$type = $item->getOrderItem()->getProductType();
	$renderer = $this->_getRenderer($type);
	$renderer->setOrder($order);
	$renderer->setItem($item);
	$renderer->setPdf($this);
	$renderer->setPage($page);
	$renderer->setRenderedModel($this);

	$renderer->draw();

	return $renderer->getPage();
}

protected function _setFontRegular($object, $size = 10)
{
	//$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
	$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

	$object->setFont($font, $size);
	return $font;
}

protected function _setFontBold($object, $size = 10)
{
	//$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
	$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
	$object->setFont($font, $size);
	return $font;
}

protected function _setFontItalic($object, $size = 10)
{
	//$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
	$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
	$object->setFont($font, $size);
	return $font;
}

protected function _setFontBoldItalic($object, $size = 10)
{
	//$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
	$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
	$object->setFont($font, $size);
	return $font;
}

protected function _setFont($object, $style = 'regular', $size = 10, $font = 'helvetica', $non_standard_characters = 0, $color = '')
{
	// echo '$style:'.$style.' $size:'.$size.' $font:'.$font.' $non_standard_characters:'. $non_standard_characters.' $color:'.$color;exit; 
	//$non_standard_characters 	= $this->_getConfig('non_standard_characters', '', false,$wonder);
 	// if($non_standard_characters != 1)
 	// 	{
 	// 		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
 	// 	}
 	// 	else
 	// 	{
 	// 		$font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Vera.ttf');  
 	// 	}
	
	//$font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
	switch($font) {
		case 'helvetica':
			switch($style) {
				case 'regular' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arial.ttf');  
				break;
				case 'italic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'ariali.ttf');  
				break;
				case 'bold' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arialbd.ttf');  
				break;
				case 'bolditalic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arialbi.ttf');  
				break;
				default:
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arial.ttf');  
				break;
			}
		break;
		
		case 'courier':
			switch($style) {
				case 'regular' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'cour.ttf');  
				break;
				case 'italic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'couri.ttf');  
				break;
				case 'bold' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'courbd.ttf');  
				break;
				case 'bolditalic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'courbi.ttf');  
				break;
				default:
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'cour.ttf');  
				break;
			}
		break;
		
		case 'times':
			switch($style) {
				case 'regular' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'times.ttf');  
				break;
				case 'italic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'timesi.ttf');  
				break;
				case 'bold' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'timesbd.ttf');  
				break;
				case 'bolditalic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'timesbi.ttf');  
				break;
				default:
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'times.ttf');  
				break;
			}
		break;
		
		default:
			switch($style) {
				case 'regular' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arial.ttf');  
				break;
				case 'italic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'ariali.ttf');  
				break;
				case 'bold' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arialbd.ttf');  
				break;
				case 'bolditalic' :
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arialbi.ttf');  
				break;
				default:
					if($non_standard_characters != 1)
					{
						$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
					}
					else $font = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arial.ttf');  
				break;
			}
		break;
	}
			
	if($color) $object->setFillColor(new Zend_Pdf_Color_Html($color));
	$object->setFont($font, $size);
	return $font;
}

/**
 * Set PDF object
 *
 * @param Zend_Pdf $pdf
 * @return Mage_Sales_Model_Order_Pdf_Abstract
 */
protected function _setPdf(Zend_Pdf $pdf)
{
	$this->_pdf = $pdf;
	return $this;
}

/**
 * Retrieve PDF object
 *
 * @throws Mage_Core_Exception
 * @return Zend_Pdf
 */
protected function _getPdf()
{
	if (!$this->_pdf instanceof Zend_Pdf) {
		Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using'));
	}

	return $this->_pdf;
}

/**
 * Create new page and assign to PDF object
 *
 * @param array $settings
 * @return Zend_Pdf_Page
 */
public function newPage(array $settings = array())
{
	$pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
	$page = $this->_getPdf()->newPage($pageSize);
	$this->_getPdf()->pages[] = $page;
	$this->y = 800;

	return $page;
}

/**
 * Draw lines
 *
 * draw items array format:
 * lines        array;array of line blocks (required)
 * shift        int; full line height (optional)
 * height       int;line spacing (default 10)
 *
 * line block has line columns array
 *
 * column array format
 * text         string|array; draw text (required)
 * feed         int; x position (required)
 * font         string; font style, optional: bold, italic, regular
 * font_file    string; path to font file (optional for use your custom font)
 * font_size    int; font size (default 7)
 * align        string; text align (also see feed parametr), optional left, right
 * height       int;line spacing (default 10)
 *
 * @param Zend_Pdf_Page $page
 * @param array $draw
 * @param array $pageSettings
 * @throws Mage_Core_Exception
 * @return Zend_Pdf_Page
 */
public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
{
	foreach ($draw as $itemsProp) {
		if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
			Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array'));
		}
		$lines  = $itemsProp['lines'];
		$height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

		if (empty($itemsProp['shift'])) {
			$shift = 0;
			foreach ($lines as $line) {
				$maxHeight = 0;
				foreach ($line as $column) {
					$lineSpacing = !empty($column['height']) ? $column['height'] : $height;
					if (!is_array($column['text'])) {
						$column['text'] = array($column['text']);
					}
					$top = 0;
					foreach ($column['text'] as $part) {
						$top += $lineSpacing;
					}

					$maxHeight = $top > $maxHeight ? $top : $maxHeight;
				}
				$shift += $maxHeight;
			}
			$itemsProp['shift'] = $shift;
		}

		if ($this->y - $itemsProp['shift'] < 15) {
			$page = $this->newPage($pageSettings);
		}

		foreach ($lines as $line) {
			$maxHeight = 0;
			foreach ($line as $column) {
				$fontSize  = empty($column['font_size']) ? 10 : $column['font_size'];
				if (!empty($column['font_file'])) {
					$font = Zend_Pdf_Font::fontWithPath($column['font_file']);
					$page->setFont($font);
				}
				else {
					$fontStyle = empty($column['font']) ? 'regular' : $column['font'];
					switch ($fontStyle) {
						case 'bold':
							$font = $this->_setFontBold($page, $fontSize);
							break;
						case 'italic':
							$font = $this->_setFontItalic($page, $fontSize);
							break;
						default:
							$font = $this->_setFontRegular($page, $fontSize);
							break;
					}
				}

				if (!is_array($column['text'])) {
					$column['text'] = array($column['text']);
				}

				$lineSpacing = !empty($column['height']) ? $column['height'] : $height;
				$top = 0;
				foreach ($column['text'] as $part) {
					$feed = $column['feed'];
					$textAlign = empty($column['align']) ? 'left' : $column['align'];
					$width = empty($column['width']) ? 0 : $column['width'];
					switch ($textAlign) {
						case 'right':
							if ($width) {
								$feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
							}
							else {
								$feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
							}
							break;
						case 'center':
							if ($width) {
								$feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
							}
							break;
					}
					$page->drawText($part, $feed, $this->y-$top, 'UTF-8');
					$top += $lineSpacing;
				}

				$maxHeight = $top > $maxHeight ? $top : $maxHeight;
			}
			$this->y -= $maxHeight;
		}
	}

	return $page;
}


protected function convertToBarcodeString($toBarcodeString)
{
	$str = $toBarcodeString;
	$barcode_data = str_replace(' ', chr(128), $str);

	$checksum = 104; # must include START B code 128 value (104) in checksum
	for($i=0;$i<strlen($str);$i++) {
		$code128 = '';
		if (ord($barcode_data{$i}) == 128) {
			$code128 = 0;
		} elseif (ord($barcode_data{$i}) >= 32 && ord($barcode_data{$i}) <= 126) {
			$code128 = ord($barcode_data{$i}) - 32;
		} elseif (ord($barcode_data{$i}) >= 126) {
			$code128 = ord($barcode_data{$i}) - 50;
		}
		$checksum_position = $code128 * ($i + 1);
		$checksum += $checksum_position;
	}
	$check_digit_value = $checksum % 103;
	$check_digit_ascii = '';
	if ($check_digit_value <= 94) {
		$check_digit_ascii = $check_digit_value + 32;
	} elseif ($check_digit_value > 94) {
		$check_digit_ascii = $check_digit_value + 50;
	}
	$barcode_str = chr(154) . $barcode_data . chr($check_digit_ascii) . chr(156);

	return $barcode_str;

}
}
