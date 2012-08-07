<?php
/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
function clean_method($dirty,$method) {

	$clean = '';
	if($method == 'shipping')
	{
		$shipping_method = trim(str_ireplace(array('Select Delivery Method','Method','Select postage','Select Shipping','- '),'',$dirty));

		$shipping_method = trim(str_ireplace(
			array("\n","\r","\t",'  ','Free Shipping Free','Free shipping␣ Free','Paypal Express Checkout','Pick up Pick up','Pick up at our ','Normal ','Registered',' Postage','Postage','Mail','Store Pickup Store Pickup','Cash on Delivery','Courier Service','Delivery Charge','International','  '),
			array(' ',' ',' ',' ','Free Shipping','Free Shipping','Paypal Express','Pick up','Pickup@','',"Reg'd",',','','','Store Pickup','COD','Courier','Charge','Int\'l',' '),
			$shipping_method));
			// TODO

		$shipping_method = preg_replace('~Charge:$~i','',$shipping_method);
		$shipping_method = preg_replace('~^\s*~','',$shipping_method);
		$shipping_method = preg_replace('~^\-~','',$shipping_method);
		$shipping_method = trim(preg_replace('~delivery~i','',$shipping_method));
		$shipping_method = trim(preg_replace('~\((.*)$~','',$shipping_method));

		// if same name has been configured as title and method, this will trim out the repetition
		$shipping_method_length = strlen($shipping_method);
		$s_a = trim(substr($shipping_method,0,(round($shipping_method_length/2))));
		$s_b = trim(substr($shipping_method,(round($shipping_method_length/2)),$shipping_method_length));

		if($s_a == $s_b) $shipping_method = $s_a;

		// preg_match('~shipping(.*)shipping~i',$shipping_method,$shipping_method_trim);
		// 							if(isset($shipping_method_trim[1])) $shipping_method = 'Shipping '.$shipping_method_trim[1];
		// 
		// 							preg_match('~verzenden(.*)verzenden~i',$shipping_method,$shipping_method_trim);
		// 							if(isset($shipping_method_trim[1])) $shipping_method = 'Verzenden '.$shipping_method_trim[1];
		// 							
		$clean = $shipping_method;
	}
	elseif($method == 'payment')
	{

		/* Payment */
		$paymentInfo = $dirty;
		/*
		Mage::helper('payment')->getInfoBlock($order->getPayment())
			    ->setIsSecureMode(true)
			    ->toPdf();
			   */ 
		$payment = explode('{{pdf_row_separator}}', $paymentInfo);
		foreach ($payment as $key=>$value)
		{
			if (strip_tags(trim($value))=='')
			{
				unset($payment[$key]);
			}
		}
		reset($payment);
	
		$payment_test = implode(',',$payment);
		$payment_test = trim(str_ireplace(
			array("\n","\r","\t",'  ',', credit card type:','credit card type:','- Account info','PayPal Secured Payment','credit card number','#: ','Credit Card','American Express',',','(Authorize.net)','Cash on Delivery','Number','Credit / Debit CardCC','Credit / Debit Card','CardCC','payment gateway by','n/a','cccc','cc cc','****','****','***','***','Card: Visa','  '),
			array(' ',' ',' ',' ',':',':','','Paypal','#','#','','Amex','','','COD','#','CC','Card','CC','','CC','CC','CC','**','**','**','**','Visa',''),$payment_test));
			
			$payment_test = trim(str_ireplace(
				array('Credit or Debit Card'),
				array('Card'),$payment_test));
		$payment_test = preg_replace('~^\s*~','',$payment_test);
		$payment_test = trim(preg_replace('~^:~','',$payment_test));
		$payment_test = preg_replace('~Paypal(.*)$~i','Paypal',$payment_test);
		$payment_test = preg_replace('~Account(.*)$~i','Account',$payment_test);
		$payment_test = preg_replace('~Processed Amount(.*)$~i','',$payment_test);
		$payment_test = preg_replace('~Payer Email(.*)$~i','',$payment_test);
		$payment_test = preg_replace('~Charge:$~i','',$payment_test);
		$payment_test = preg_replace('~^\-~','',$payment_test);
		$payment_test = preg_replace('~Check / Money order(.*)$~i','Check / Money order',$payment_test);
		$payment_test = str_ireplace(
			array('CardCC','CC Type','MasterCardCC','MasterCC'),
			array('CC','CC, Type','MasterCard','MasterCard'),$payment_test);

		$payment_test = trim($payment_test);

		// if same name has been configured as title and method, this will trim out the repetition
		$payment_method_length = strlen($payment_test);
		$p_a = trim(substr($payment_test,0,(round($payment_method_length/2))));
		$p_b = trim(substr($payment_test,(round($payment_method_length/2)),$payment_method_length));

		if($p_a == $p_b) $payment_test = $p_a;
	
		$clean = $payment_test;
	}

	return $clean;
}

function search($array, $key='', $value='')
	{
		$results = array();
		if(!$value) $value = '';
		
	    if (is_array($array))
	    {

			if (isset($array[$key]) && ($array[$key] == $value))
	        {
		    	$results[] = $array;
			}
	        foreach ($array as $subarray)
			{
	            $results = array_merge($results, search($subarray, $key, $value));
			}
	    }


	    return $results;
	}

class Mage_Sales_Model_Order_Pdf_Invoices extends Mage_Sales_Model_Order_Pdf_Aabstract
{    
	
	
	private $_nudgeY;
    private $_itemsY;    
       

	/***********************************************
        * Shifter Template - START
    ************************************************/
    public function getPdf2($orders=array(), $from_shipment = false)
	{
	    // die($order);
	// echo '$order:'.implode(',',$order);exit;
		$this->_beforeGetPdf();
		$this->_initRenderer('invoices');
		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
				
			/* Page Sizes */

		    //const SIZE_A4                = '595:842:'; //820
		    //const SIZE_LETTER            = '612:792:'; //770
			
		$page_size = $this->_getConfig('page_size', 'a4', false,'general');
		
		if($page_size == 'letter') 
		{
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
			$page_top = 770;$padded_right = 587;
		}
		elseif($page_size == 'a4') 
		{
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
			$page_top = 820;$padded_right = 570;
		}
		elseif($page_size == 'a5landscape') 
		{
			$page = $pdf->newPage('596:421');					
			$page_top = 395;$padded_right = 573;
		}
		
		$pdf->pages[] = $page;
	    
		$first_page_yn 			= 'y';

		foreach ($orders as $orderSingle) 
		{
			if($first_page_yn == 'n') $page = $this->newPage();
			else $first_page_yn = 'n';
			$this->y = $page->getHeight();
			
			$order = Mage::getModel('sales/order')->load($orderSingle);		
			$order_id  = $order->getRealOrderId();
			// $itemsCollection =  $order->getAllVisibleItems();

			$this->_beforeGetPdf();
			$this->_initRenderer('invoices');

			$pdf = new Zend_Pdf();
			$this->_setPdf($pdf);
			$style = new Zend_Pdf_Style();
			$this->_setFontBold($page, 10);
  
			// if ($order->getStoreId()) {
			// 		Mage::app()->getLocale()->emulate($order->getStoreId());
			// 	}
			
			$page_size = $this->_getConfig('page_size', 'a4', false,'general');

			if($page_size == 'letter') 
			{
				$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
				$page_top = 770;$padded_right = 587;
			}
			elseif($page_size == 'a4')
			{
				$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
				$page_top = 820;$padded_right = 570;
			}
			elseif($page_size == 'a5landscape') 
			{
				$page = $pdf->newPage('596:421');					
				$page_top = 395;$padded_right = 573;
			}
			
			$pdf->pages[] = $page;           
              
			/***********************************************************
			* CONFIGURATIONS
			***********************************************************/

			// all y values must be positive, all x positive
			// bottom left is 0,0

			## DEFAULT VALUES
			$columnYNudgeDefault        = 570;
			$barcodeXYDefault           = '450,800';
			$orderDateXYDefault         = '390,755';
			$orderIdXYDefault           = '390,730';
			$customerIdXYDefault        = '390,705';
			$customerNameXYDefault      = '130,648'; 
			$addressXYDefault           = '130,633';  
			$orderIdFooterXYDefault     = '106,76';
			$customerIdFooterXYDefault  = '106,47';            

			$cutoff_noDefault           = 10;
			$fontsize_overallDefault    = 9;
			$fontsize_productlineDefault= 9;
			$skuXDefault                = 90;
			$qtyXDefault                = 40;
			$productXDefault            = 230;
			$colorXDefault              = 400;
			$sizeXDefault               = 450;
			$priceXDefault              = 505; 

			$colorAttributeNameDefault 	= 'color';
			$sizeAttributeNameDefault	= 'size';

			$columnYNudge   		= $this->_getConfig('pack_column', $columnYNudgeDefault);
			$showBarCode    		= $this->_getConfig('pickpack_packbarcode', 0, false); 

			$barcodeXY      		= explode(",", $this->_getConfig('pack_barcode', $barcodeXYDefault));               
			$orderDateXY    		= explode(",", $this->_getConfig('pack_order', $orderDateXYDefault));
			$customerIdXY   		= explode(",", $this->_getConfig('pack_cid', $customerIdXYDefault));
			$orderIdXY      		= explode(",", $this->_getConfig('pack_oid', $orderIdXYDefault));                        

			$color_attribute_name 	= $this->_getConfig('color_attribute_name', $colorAttributeNameDefault,false);
			$size_attribute_name 	= $this->_getConfig('size_attribute_name', $sizeAttributeNameDefault,false);

			$customerNameXY 		= explode(",", $this->_getConfig('pack_customer', $customerNameXYDefault)); 
			$addressXY     			= explode(",", $this->_getConfig('pack_customeraddress', $addressXYDefault));

			$customerIdFooterXY 	=  explode(",", $this->_getConfig('footer_customer_id', $customerIdFooterXYDefault));
			$orderIdFooterXY    	=  explode(",", $this->_getConfig('footer_order_id', $orderIdFooterXYDefault));      

			$cutoff_no              = $this->_getConfig('pack_noofitems', $cutoff_noDefault, false);
			$fontsize_overall       = $this->_getConfig('pickpack_fontsizeoverall', $fontsize_overallDefault, false);
			$fontsize_productline   = $this->_getConfig('pickpack_fontsizeproductline', $fontsize_productlineDefault, false);
			
			$font_family_body_default = 'helvetica';
			$font_size_body_default = 10;
			$font_style_body_default = 'regular';
			$font_color_body_default = 'Black';
					
			$font_family_body = $this->_getConfig('font_family_body', $font_family_body_default, false,'general');
			$font_style_body = $this->_getConfig('font_style_body', $font_style_body_default, false,'general');
			$font_size_body = $this->_getConfig('font_size_body', $font_size_body_default, false,'general');
			$font_color_body = $this->_getConfig('font_color_body', $font_color_body_default, false,'general');

	     	$non_standard_characters 	= $this->_getConfig('non_standard_characters', 0, false,'general');

			$shipment_details_yn	= $this->_getConfig('shipment_details_yn', 0, false,'wonder');

			$skuX           		= $this->_getConfig('pack_sku', $skuXDefault);
			$qtyX           		= $this->_getConfig('pack_qty', $qtyXDefault);
			$productX       		= $this->_getConfig('pack_product', $productXDefault);
			$colorX         		= $this->_getConfig('pack_color', $colorXDefault);
			$sizeX          		= $this->_getConfig('pack_size', $sizeXDefault);
			$priceX         		= $this->_getConfig('pack_price', $priceXDefault);            

			$this->y				= $page->getHeight();

			/***********************************************************
			* CONFIGURATIONS
			***********************************************************/

			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->_setFontRegular($page, $fontsize_overall);         

			# BARCODE
			if(1 == $showBarCode)
			{
				$barcodeString = $this->convertToBarcodeString($order->getRealOrderId());
				$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 16);  
				$page->drawText($barcodeString, $barcodeXY[0], $barcodeXY[1], 'CP1252');
			}            
			if($shipment_details_yn == 1)
			{
				// $shipping_method = '';
				$shipping_method = clean_method($order->getShippingDescription(),'shipping');
				
				// $shipping_method = str_ireplace(array('Select Delivery Method','Method','Select postage','Select Shipping','- '),'',$order->getShippingDescription());
				// $shipping_method = str_ireplace(array('Select Delivery Method','Method'),'',$order->getShippingDescription());
				$this->_setFont($page, $font_style_body, ($fontsize_productline-2), $font_family_body, $non_standard_characters, $font_color_body);
				$page->drawText('Post: '.$shipping_method, $barcodeXY[0], ($barcodeXY[1]-6), 'UTF-8');
			}
			
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->_setFontRegular($page, $fontsize_overall);
			
			# HEADER 
			$page->drawText(Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), $orderDateXY[0], $orderDateXY[1], 'UTF-8');
			$page->drawText($order->getRealOrderId(), $orderIdXY[0], $orderIdXY[1], 'UTF-8');
			$page->drawText($order->getCustomerId(), $customerIdXY[0], $customerIdXY[1] , 'UTF-8');

			# NAME/DELIVERY
			$page->drawText($order->getCustomerName(), $customerNameXY[0], $customerNameXY[1], 'UTF-8');
			$shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));        
			$page->drawText(implode(', ',$shippingAddress), $addressXY[0], $addressXY[1], 'UTF-8');

			# FOOTER
			$page->drawText($order->getRealOrderId(), $orderIdFooterXY[0], $orderIdFooterXY[1], 'UTF-8');           
			$page->drawText($order->getCustomerId(), $customerIdFooterXY[0], $customerIdFooterXY[1] , 'UTF-8');

			# ITEMS
			$this->y         = $columnYNudge;  
			$this->_itemsY   = $this->y;  
		  
			$itemsCollection =  $order->getAllVisibleItems();
			$total_items     = count($itemsCollection);

			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->_setFontRegular($page, $fontsize_productline);          
			
			$counter = 1; 
			foreach ($itemsCollection as $item)
			{
          			     
				$size = '';
				$color = ''; 
				$size_id = '';
				$color_id = '';
				$color_attribute_id = '';
				$size_attribute_id = '';

				$options = $item->getProductOptions();

				if(Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $color_attribute_name)->getAttributeId())
				{
					$color_attribute_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $color_attribute_name)->getAttributeId();//272
				}

				if(Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $size_attribute_name)->getAttributeId())
				{
					$size_attribute_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $size_attribute_name)->getAttributeId();//525
				}


				$qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();

				if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
				{
					$sku = $item->getProductOptionByCode('simple_sku');
					if(isset($options['info_buyRequest']['super_attribute'][$size_attribute_id])) $size_id  = $options['info_buyRequest']['super_attribute'][$size_attribute_id]; #size
					if(isset($options['info_buyRequest']['super_attribute'][$color_attribute_id])) $color_id = $options['info_buyRequest']['super_attribute'][$color_attribute_id]; #color     

					$sizeCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')                
					->setStoreFilter(0)                   
					->setAttributeFilter($size_attribute_id)                    
					->load(); 

					foreach($sizeCollection as $_size)
					{                        
						if($_size->getOptionId() == $size_id)
						{
							$size = $_size->getValue();
							break;
						}
					}                    

					$colorCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')                
					->setStoreFilter(0)                   
					->setAttributeFilter($color_attribute_id)                    
					->load();         

					foreach($colorCollection as $_color)
					{                        
						if($_color->getOptionId() == $color_id)
						{
							$color = $_color->getValue();
							break;
						}
					}
				} 
				else 
				{
					// simple product
					// sku works, others not
					$sku = $item->getSku();
					$color = $item->getAttributeText($color_attribute_name);
					$size = $item->getAttributeText($size_attribute_name);

					if(!isset($color) && Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText($color_attribute_name)) $color = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText($color_attribute_name);
					if(!isset($size) && Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText($size_attribute_name)) $size = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText($size_attribute_name);

					if(!isset($color) && $item->getColor) $color = $item->getColor;
					if(!isset($size) && $item->getSize) $size = $item->getSize;				
				}
			
				if($from_shipment)
				{
					$qty_string = 's:'.(int)$item->getQtyShipped() . ' / o:'.$qty;
					#$skuX += 15;
				}
				else
				{
					$qty_string = $qty;
				}
       
				$page->drawText($qty_string, $qtyX, $this->y , 'UTF-8');
				$page->drawText($sku, $skuX, $this->y , 'UTF-8');
				$page->drawText($item->getName(), $productX, $this->y , 'UTF-8');
				$page->drawText($color, $colorX, $this->y , 'UTF-8');
				$page->drawText($size, $sizeX, $this->y , 'UTF-8');                    

				$price = $qty * $item->getPriceInclTax();

				$page->drawText(number_format($price, 2, '.', ','), $priceX, $this->y , 'UTF-8');
				$this->y -= 15;

				if(($counter % $cutoff_no) == 0 && $counter != $total_items)
				{
				    $page = $this->newPage2();
				}

				$counter++;
			}

			/***********************************************
			* Shifter Template - END
			************************************************/
			$this->_afterGetPdf();
		}
	return $pdf;
	}

   /***********************************************
    * Default Template - START
    ************************************************/

	public function getPdfDefault($orders=array(), $from_shipment = false, $invoice_or_pack = 'pack')
	{
		$wonder = 'wonder';
		if($invoice_or_pack == 'invoice') $wonder = 'wonder_invoice';


            /***********************************************************
            * CONFIGURATIONS
            ***********************************************************/

			// all y values must be positive, all x positive
			// bottom left is 0,0

			$page_size = $this->_getConfig('page_size', 'a4', false,'general');

			// $page_template	= $this->_getConfig('page_template', 0, false,$wonder);

			if($page_size == 'letter') 
			{
				$page_top = 770;$padded_right = 587;
			
				$columnYNudgeDefault        = 720;
	            $barcodeXYDefault           = '465,753';
	            $title2XYDefault           	= '465,733';
	            $orderDateXYDefault         = '160,695';
	            $orderIdXYDefault           = '30,695';
				$customerIdXYDefault        = '390,655';
	            $addressXYDefault           = '40,645';  
	            $addressFooterXYDefault     = '50,110'; // * new
	            $returnAddressFooterXYDefault = '320,90'; // * new
				$customerNameXYDefault		= '0,0';
				
				$giftMessageXYDefault		= '0,90';
				$notesXYDefault				= '25,90';
				$packedByXYDefault			= '520,20';
				$returnLogoXYDefault		= '320,40';
			}
			elseif($page_size == 'a4') 
			{
				$page_top = 820;$padded_right = 570;
				
				$columnYNudgeDefault        = 720;
	            $barcodeXYDefault           = '465,803';
	            $title2XYDefault           	= '465,783';
	            $orderDateXYDefault         = '160,745';
	            $orderIdXYDefault           = '30,745';
				$customerIdXYDefault        = '390,705';
	            $addressXYDefault           = '40,695';  
	            $addressFooterXYDefault     = '50,140'; // * new
	            $returnAddressFooterXYDefault = '320,120'; // * new
	//			$page->drawImage($image, $returnLogoXY[0], $returnLogoXY[1], ($returnLogoXY[0]+180), ($returnLogoXY[1]+120));
	
				$customerNameXYDefault		= '0,0';
				
				$giftMessageXYDefault		= '0,140';
				$notesXYDefault				= '25,140';
				$packedByXYDefault			= '520,20';
				$returnLogoXYDefault		= '320,70';//'320,140'; (minus the image size [180x120] from return address coords)
			}
			elseif($page_size == 'a5landscape') 
			{
				//$page_top = 395;$padded_right = 573;
				/*
				Letter		612x792 587x770 (-25 -22)
				A4		 	595x842 570x820 (-25 -22)
				A5		 	420x595 395x573 
				A5(L)		595x420 573x395 
				*/
				$page_top = 395;$padded_right = 573;
				
				$columnYNudgeDefault        = 720;
	            $barcodeXYDefault           = '465,378';//-425
	            $title2XYDefault           	= '465,358';
	            $orderDateXYDefault         = '160,320';
	            $orderIdXYDefault           = '30,320';
				$customerIdXYDefault        = '390,280';
	            $addressXYDefault           = '40,270';  
	            $addressFooterXYDefault     = '50,100'; // * new
	            $returnAddressFooterXYDefault = '320,80'; // * new	
				$customerNameXYDefault		= '0,0';
				$giftMessageXYDefault		= '0,80';
				$notesXYDefault				= '25,80';
				$packedByXYDefault			= '520,20';
				$returnLogoXYDefault		= '320,70';//'320,140'; (minus the image size [180x120] from return address coords)
			}
			
            ## DEFAULT VALUES
            
			
            $cutoff_noDefault           = 20;
            $fontsize_overallDefault    = 10;
            $fontsize_productlineDefault= 10;
            $fontsize_returnaddressDefault= 9;
            $fontsize_returnaddresstopDefault= 8;
            $fontsize_shipaddressDefault= 15;
			$subheader_start			= 0;

			$prices_yn					= $this->_getConfig('prices_yn', 0, false,$wonder);
			$tax_yn						= $this->_getConfig('tax_yn', 0, false,$wonder);
			$tax_col_yn					= $this->_getConfig('tax_col_yn', 0, false,$wonder);
			if($tax_yn == 0) $tax_col_yn = 0;
			$tax_label					= trim($this->_getConfig('tax_label', '', false,$wonder));
			$tax_line_or_subtotal		= $this->_getConfig('tax_line_or_subtotal', '', false,$wonder);
			$discount_line_or_subtotal	= $this->_getConfig('discount_line_or_subtotal', '', false,$wonder);
			
			$shelving_real_yn 			= $this->_getConfig('shelving_real_yn', 0, false,$wonder);
			$shelving_real_attribute 	= $this->_getConfig('shelving_real', 'shelf', false,$wonder);
			$shelving_real_title 		= $this->_getConfig('shelving_real_title', 'shelf', false,$wonder);
			$shelving_real_title 		= str_ireplace(array('blank',"'"),'',$shelving_real_title);
	     	$sku_title 					= $this->_getConfig('sku_title', 'codes', false,$wonder);
	     	$items_title = $this->_getConfig('items_title', 'items', false,$wonder);
	
	     	$invoice_title_yn 			= $this->_getConfig('pickpack_title_yn', 0, false,$wonder);
	     	$invoice_title 				= trim($this->_getConfig('pickpack_title', '', false,$wonder));
			if($invoice_title_yn == 0) $invoice_title = '';
			
	     	$invoice_title_2_yn 		= $this->_getConfig('pickpack_title_2_yn', 0, false,$wonder);
	     	$invoice_title_2 			= trim($this->_getConfig('pickpack_title_2', '', false,$wonder));
			if($invoice_title_2_yn == 0) $invoice_title_2 = '';
			$title2XY      				= explode(",", $this->_getConfig('pickpack_nudge_title', $title2XYDefault,true,$wonder));               

	     	$non_standard_characters 	= $this->_getConfig('non_standard_characters', 0, false,'general');

            $qtyXDefault                = 40;
            $productXDefault            = 80;
			$skuXDefault                = 300;
            $shelfXDefault           	= 434;
            $shelf2XDefault           	= 454;
            $shelf3XDefault           	= 464;
            $priceXDefault              = 500; 
            $priceEachXDefault          = 520; 
           
 			$colorXDefault              = 345;
            $sizeXDefault               = 430;

			$first_item_title_shift 	= 0;
			if($prices_yn != '0')
			{ 
				// product [0]   sku	shelf	qty	priceEach	price
				$productXDefault            = 40;
				$skuXDefault                = 230;
				$shelfXDefault           	= 350;
				$shelf2XDefault           	= 370;
				$shelf3XDefault           	= 380;
				$qtyXDefault                = 410;
				$priceEachXDefault          = 460;
				$priceXDefault              = 520; 
				$first_item_title_shift 	= -13;
			}
			
			if($shelving_real_yn == 0)
			{
				$skuXDefault += 50;
			}
			
			$gift_message_yn			= $this->_getConfig('gift_message_yn', 'yesunder', false,$wonder);
			$giftMessageXY      		= explode(",", $this->_getConfig('gift_message_nudge', $giftMessageXYDefault,true,$wonder));               
   
			$notes_yn_pre				= $this->_getConfig('notes_yn_pre', '0', false,$wonder);
			$notes_yn					= $this->_getConfig('notes_yn', 'yesemail', false,$wonder);
			if($notes_yn_pre == 0) $notes_yn = 'no';
			$notes_filter				= trim(strtolower($this->_getConfig('notes_filter', '', false,$wonder)));
			$notes_filter_options		= $this->_getConfig('notes_filter_options', '', false,$wonder);
			if($notes_filter_options != 'yestext') $notes_filter = '';
			
			$notesXY      				= explode(",", $this->_getConfig('notes_nudge', $notesXYDefault,true,$wonder));               
         
			$packed_by_yn				= $this->_getConfig('packed_by_yn', 0, false,$wonder);
			$packedByXY      			= explode(",", $this->_getConfig('packed_by_nudge', $packedByXYDefault,true,$wonder));               

			$colorAttributeNameDefault 	= 'color';
            $sizeAttributeNameDefault	= 'size';

			$showReturnLogoYNDefault	= 'Y';

			$showPriceYNDefault			= 'N';
			$showColorYNDefault			= 'N';
			$showSizeYNDefault			= 'N';
			$boldLastAddressLineYNDefault = 'Y';
			$fontsizeTitlesDefault		= 14;
			$fontColorGrey				= 0.6;
			$fontColorReturnAddressFooter = 0.2;
			$boxBkgColorDefault			= 0.7;
			
			$red_bkg_color 			= new Zend_Pdf_Color_Html('lightCoral');
			$grey_bkg_color			= new Zend_Pdf_Color_GrayScale(0.7);
			$dk_grey_bkg_color		= new Zend_Pdf_Color_GrayScale(0.3);//darkCyan
			$dk_cyan_bkg_color 		= new Zend_Pdf_Color_Html('darkCyan');//darkOliveGreen
			$dk_og_bkg_color 		= new Zend_Pdf_Color_Html('darkOliveGreen');//darkOliveGreen
			$black_color 			= new Zend_Pdf_Color_Rgb(0,0,0);
			$grey_color 			= new Zend_Pdf_Color_GrayScale(0.3);
			$greyout_color 			= new Zend_Pdf_Color_GrayScale(0.6);
			$white_color 			= new Zend_Pdf_Color_GrayScale(1);
			
			$showColorYN				= $showColorYNDefault;
			$showSizeYN					= $showSizeYNDefault;
			$boldLastAddressLineYN		= $boldLastAddressLineYNDefault;
 			$fontsize_returnaddresstop 	= $fontsize_returnaddresstopDefault;
			$fontsizeTitles 			= $fontsizeTitlesDefault;
			$boxBkgColor				= $boxBkgColorDefault;

			$font_family_header_default = 'helvetica';
			$font_size_header_default = 16;
			$font_style_header_default = 'bolditalic';
			$font_color_header_default = 'darkOliveGreen';
			
			$font_family_subtitles_default = 'helvetica';
			$font_style_subtitles_default = 'bold';
			$font_size_subtitles_default = 15;
			$font_color_subtitles_default = '#222222';
			$background_color_subtitles_default = '#999999';
			
			$font_family_message_default = 'helvetica';
			$font_style_message_default = 'italic';
			$font_size_message_default = 9;
			$font_color_message_default = '#222222';
			$background_color_message_default = 'lightGreen';
			
			$font_family_gift_message_default = 'helvetica';
			$font_style_gift_message_default = 'italic';
			$font_size_gift_message_default = 9;
			$font_color_gift_message_default = '#222222';
			$background_color_gift_message_default = 'lightGreen';
		
			$font_family_body_default = 'helvetica';
			$font_size_body_default = 10;
			$font_style_body_default = 'regular';
			$font_color_body_default = 'Black';

			$font_family_header = $this->_getConfig('font_family_header', $font_family_header_default, false,'general');
			$font_style_header = $this->_getConfig('font_style_header', $font_style_header_default, false,'general');
			$font_size_header = $this->_getConfig('font_size_header', $font_size_header_default, false,'general');
			$font_color_header = trim($this->_getConfig('font_color_header', $font_color_header_default, false,'general'));
			
			$font_family_subtitles = $this->_getConfig('font_family_subtitles', $font_family_subtitles_default, false,'general');
			$font_style_subtitles = $this->_getConfig('font_style_subtitles', $font_style_subtitles_default, false,'general');
			$font_size_subtitles = $this->_getConfig('font_size_subtitles', $font_size_subtitles_default, false,'general');
			$font_color_subtitles = trim($this->_getConfig('font_color_subtitles', $font_color_subtitles_default, false,'general'));
			$background_color_subtitles = trim($this->_getConfig('background_color_subtitles', $background_color_subtitles_default, false,'general'));
		
			$font_family_message 		= $this->_getConfig('font_family_message', $font_family_message_default, false,'general');
			$font_style_message 		= $this->_getConfig('font_style_message', $font_style_message_default, false,'general');
			$font_size_message 			= $this->_getConfig('font_size_message', $font_size_message_default, false,'general');
			$font_color_message 		= trim($this->_getConfig('font_color_message', $font_color_message_default, false,'general'));
			$background_color_message 	= trim($this->_getConfig('background_color_message', $background_color_message_default, false,'general'));
			$background_color_message_zend = new Zend_Pdf_Color_Html($background_color_message);

			$font_family_gift_message 	= $this->_getConfig('font_family_gift_message', $font_family_gift_message_default, false,'general');
			$font_style_gift_message 	= $this->_getConfig('font_style_gift_message', $font_style_gift_message_default, false,'general');
			$font_size_gift_message 	= $this->_getConfig('font_size_gift_message', $font_size_gift_message_default, false,'general');
			$font_color_gift_message 	= trim($this->_getConfig('font_color_gift_message', $font_color_gift_message_default, false,'general'));
			$background_color_gift_message = trim($this->_getConfig('background_color_gift_message', $background_color_gift_message_default, false,'general'));
			$background_color_gift_message_zend = new Zend_Pdf_Color_Html(''.$background_color_gift_message.'');
		
			$font_family_body 			= $this->_getConfig('font_family_body', $font_family_body_default, false,'general');
			$font_style_body 			= $this->_getConfig('font_style_body', $font_style_body_default, false,'general');
			$font_size_body 			= $this->_getConfig('font_size_body', $font_size_body_default, false,'general');
			$font_color_body 			= trim($this->_getConfig('font_color_body', $font_color_body_default, false,'general'));
		
			$font_size_options 			= $this->_getConfig('font_size_options', 8, false,'general');
			$background_color_product_temp = trim($this->_getConfig('background_color_product', '', false,'general'));
			$background_color_product = new Zend_Pdf_Color_Html($background_color_product_temp);
		
			$background_color_subtitles_zend = new Zend_Pdf_Color_Html($background_color_subtitles);	
			$font_color_header_zend 	= new Zend_Pdf_Color_Html($font_color_header);	
			$font_color_subtitles_zend 	= new Zend_Pdf_Color_Html($font_color_subtitles);	
			$font_color_body_zend 		= new Zend_Pdf_Color_Html($font_color_body);
		
			$shelving_yn 				= $this->_getConfig('shelving_yn', 0, false,$wonder);
			$shelving_attribute 		= $this->_getConfig('shelving', '', false,$wonder);			
			$shelving_title 			= $this->_getConfig('shelving_title', '', false,$wonder);
			$shelving_title 			= trim(str_ireplace(array('blank',"'"),'',$shelving_title));
		
			$shelving_2_yn 				= $this->_getConfig('shelving_2_yn', 0, false,$wonder);
			$shelving_2_attribute 		= $this->_getConfig('shelving_2', '', false,$wonder);			
			$shelving_2_title 			= $this->_getConfig('shelving_2_title', '', false,$wonder);
			$shelving_2_title 			= trim(str_ireplace(array('blank',"'"),'',$shelving_2_title));
	
			$shipment_details_yn		= $this->_getConfig('shipment_details_yn', 0, false,$wonder);
			$shipment_details_weight	= $this->_getConfig('shipment_details_weight', 0, false,$wonder);
			$shipment_details_weight_unit= $this->_getConfig('shipment_details_weight_unit', 0, false,$wonder);
			$shipment_details_carrier	= $this->_getConfig('shipment_details_carrier', 0, false,$wonder);
			$shipment_details_payment	= $this->_getConfig('shipment_details_payment', 0, false,$wonder);
			$shipment_details_custgroup	= $this->_getConfig('shipment_details_custgroup', 0, false,$wonder);
			$shipment_details_customer_id = $this->_getConfig('shipment_details_customer_id', 0, false,$wonder);
			$shipment_details_count		= $this->_getConfig('shipment_details_count', 0, false,$wonder);
			$customer_group_filter 		= $this->_getConfig('shipment_details_custgroup_filter', 0, false,$wonder);
			$customer_group_filter 		= trim(strtolower($customer_group_filter));
			$shipment_details_custom_attribute_yn	= $this->_getConfig('shipment_details_custom_attribute_yn', 0, false,$wonder);
			$shipment_details_custom_attribute		= trim($this->_getConfig('shipment_details_custom_attribute', '', false,$wonder));
			$shipment_details_custom_attribute_2_yn	= $this->_getConfig('shipment_details_custom_attribute_2_yn', 0, false,$wonder);
			$shipment_details_custom_attribute_2	= trim($this->_getConfig('shipment_details_custom_attribute_2', '', false,$wonder));
			if($shipment_details_custom_attribute_yn == 0)
			{
				$shipment_details_custom_attribute 		= '';
				$shipment_details_custom_attribute_2_yn = 0;
				$shipment_details_custom_attribute_2 	= '';
			}
			if($shipment_details_custom_attribute_2_yn == 0)
			{
				$shipment_details_custom_attribute_2 	= '';
			}
			
			$configurable_names 		= $this->_getConfig('pack_configname', 'simple', false,$wonder); //col/sku
			// $columnYNudge   			= $this->_getConfig('pack_column', $columnYNudgeDefault,true,$wonder);
			$columnYNudge   			= $columnYNudgeDefault;
            $showBarCode    			= $this->_getConfig('pickpack_packbarcode', 0, false,$wonder); 

            $barcodeXY      			= explode(",", $this->_getConfig('pack_barcode', $barcodeXYDefault,true,$wonder));               
            $orderDateXY    			= explode(",", $orderDateXYDefault);
            $orderIdXY      			= explode(",", $orderIdXYDefault);  
            // $title2XY      				= explode(",", $title2XYDefault);//,true,$wonder));               

			$color_attribute_name 		= $this->_getConfig('color_attribute_name', $colorAttributeNameDefault,false,$wonder);
			$size_attribute_name 		= $this->_getConfig('size_attribute_name', $sizeAttributeNameDefault,false,$wonder);

            // $customerNameXY 			= explode(",", $this->_getConfig('pack_customer', $customerNameXYDefault)); 
            // $addressXY     				= explode(",", $this->_getConfig('pack_customeraddress', $addressXYDefault,true,$wonder));
            $addressXY     				= explode(",", $addressXYDefault);
            $addressFooterXY 			= explode(",", $this->_getConfig('pickpack_shipaddress', $addressFooterXYDefault,true,$wonder));

            if($background_color_subtitles == '#FFFFFF') 
			{
				$orderIdXY[0] -= 11;
				$orderIdXY[1] += 11;
				$addressXY[0] -= 15;
				$addressXY[1] += 10;
			}
			$shipping_title 			= trim($this->_getConfig('shipping_title', '', false,$wonder));
			
			$billing_details_yn       	= $this->_getConfig('billing_details_yn', '',false,$wonder);  
			$billing_details_position	= $this->_getConfig('billing_details_position', '',false,$wonder); 
			if($billing_details_yn == 0) $billing_details_position = 0;
			$billing_title 				= trim($this->_getConfig('billing_title', '', false,$wonder));
			if($shipping_title == '' && ($billing_details_yn == 0 || $billing_title == '')) 
			{
				$addressXY[1] 			= ($addressXY[1] + 20);
			}
			
			$cutoff_no				= $cutoff_noDefault;
            $fontsize_returnaddress = $this->_getConfig('pickpack_returnfont', $fontsize_returnaddressDefault, false,$wonder);
            $fontsize_shipaddress   = $this->_getConfig('pickpack_shipfont', $fontsize_shipaddressDefault, false,$wonder);

			if($prices_yn == 1 && $tax_yn == 1 && $tax_col_yn == 1)
			{
           	 	$skuX           		= $this->_getConfig('pricesT_skuX', $skuXDefault, false,$wonder);
	            $productX       		= $this->_getConfig('pricesT_productX', $productXDefault, false,$wonder);
	      		$shelfX					= $this->_getConfig('pricesT_shelfX', $shelfXDefault, false,$wonder);
	      		$shelf2X				= $this->_getConfig('pricesT_shelf2X', $shelf2XDefault, false,$wonder);
	      		$shelf3X				= $this->_getConfig('pricesT_shelf2X', $shelf3XDefault, false,$wonder);
	      		$optionsX				= $this->_getConfig('pricesT_optionsX', 0, false,$wonder);
	            $qtyX           		= $this->_getConfig('pricesT_qty_priceX', $qtyXDefault, false,$wonder);
	            $priceX          		= $this->_getConfig('pricesT_priceX', $priceXDefault,false,$wonder);
	            $taxEachX          		= $this->_getConfig('pricesT_item_taxX', 0,false,$wonder);
	            $priceEachX          	= $this->_getConfig('pricesT_item_priceX', $priceEachXDefault,false,$wonder);
			}
			elseif($prices_yn == 1)
			{
           	 	$skuX           		= $this->_getConfig('pricesY_skuX', $skuXDefault, false,$wonder);
	            $productX       		= $this->_getConfig('pricesY_productX', $productXDefault, false,$wonder);
	      		$shelfX					= $this->_getConfig('pricesY_shelfX', $shelfXDefault, false,$wonder);
	      		$shelf2X				= $this->_getConfig('pricesY_shelf2X', $shelf2XDefault, false,$wonder);
	      		$shelf3X				= $this->_getConfig('pricesY_shelf3X', $shelf2XDefault, false,$wonder);
	      		$optionsX				= $this->_getConfig('pricesY_optionsX', 0, false,$wonder);
	            $qtyX           		= $this->_getConfig('pricesY_qty_priceX', $qtyXDefault, false,$wonder);
	            $priceX          		= $this->_getConfig('pricesY_priceX', $priceXDefault,false,$wonder);
	            $taxEachX          		= $this->_getConfig('pricesT_item_taxX', 0,false,$wonder);
	            $priceEachX          	= $this->_getConfig('pricesY_item_priceX', $priceEachXDefault,false,$wonder);
			}
			else
			{
           	 	$skuX           		= $this->_getConfig('pricesN_skuX', $skuXDefault, false,$wonder);
	            $productX       		= $this->_getConfig('pricesN_productX', $productXDefault, false,$wonder);
	      		$shelfX					= $this->_getConfig('pricesN_shelfX', $shelfXDefault, false,$wonder);
	      		$shelf2X				= $this->_getConfig('pricesN_shelf2X', $shelf2XDefault, false,$wonder);
	      		$shelf3X				= $this->_getConfig('pricesN_shelf3X', $shelf2XDefault, false,$wonder);
	          	$optionsX				= $this->_getConfig('pricesN_optionsX', 0, false,$wonder);
	      	  	$qtyX           		= $this->_getConfig('pricesN_qty_priceX', $qtyXDefault, false,$wonder);
			    $priceX          		= $this->_getConfig('pricesY_priceX', $priceXDefault,false,'wonder');
	        }

	      	// $colorX         			= $this->_getConfig('pack_color', $colorXDefault,true,$wonder);
	      	//             $sizeX          			= $this->_getConfig('pack_size', $sizeXDefault,true,$wonder);
			$colorX         			= $colorXDefault;
            $sizeX          			= $sizeXDefault;
			$orderdetailsX 				= 304;
		
            $product_options_yn       	= $this->_getConfig('product_options_yn', '',false,$wonder);            
            $product_options_title      = $this->_getConfig('product_options_title', '', false,$wonder);            
           
 			$qty_title       			= $this->_getConfig('qty_title', '', false,$wonder);            
 			$price_title       			= $this->_getConfig('price_title', '', false,$wonder);            
 			$tax_title       			= $this->_getConfig('tax_title', '', false,$wonder);            
 			$total_title       			= $this->_getConfig('total_title', '', false,$wonder);            
	    
	
			$show_top_logo_yn			= $this->_getConfig('pickpack_packlogo', 0, false,$wonder); 
  			$showReturnLogoYN       	= $this->_getConfig('pickpack_returnlogo', $showReturnLogoYNDefault, false,$wonder);            
            $returnLogoXY	        	= explode(",", $this->_getConfig('pickpack_nudgelogo', $returnLogoXYDefault,true,$wonder));            
            $returnAddressFooterXY		= explode(",", $this->_getConfig('pickpack_returnaddress', $returnAddressFooterXYDefault,true,$wonder));            
			// ($field, $default = '', $add_default = true, $group = 'wonder', $store = null)
			$split_supplier_yn_default 	= 0;
			$supplier_attribute_default = 'supplier';
			$supplier_options_default 	= 'filter';
			$supplier_login_default		= '';
			$tickbox_default			= 'no'; //no, pick, pickpack
			$supplier_ubermaster		= array();

			$split_supplier_yn_temp 	= $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
			$split_supplier_options 	= $this->_getConfig('pickpack_split_supplier_options', 'no', false,'general');
			$split_supplier_yn = 'no';
			if($split_supplier_yn_temp == 1)
			{
				$split_supplier_yn 		= $split_supplier_options;
			}
			// this means only picklists should be separated
			if($split_supplier_yn == 'pick') $split_supplier_yn = 'no';
			
			$supplier_attribute 		= $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false,'general');
			$supplier_options 			= $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false,'general');
		
			$userId 					= Mage::getSingleton('admin/session')->getUser()->getId();
		    $user 						= Mage::getModel('admin/user')->load($userId);
			$username					= $user['username'];

			$supplier_login_pre 		= $this->_getConfig('pickpack_supplier_login', '', false,'general');
			$supplier_login_pre 		= str_replace(array("\n",','),';',$supplier_login_pre);
			$supplier_login_pre 		= explode(';',$supplier_login_pre);

			foreach($supplier_login_pre as $key => $value)
			{
				$supplier_login_single = explode(':',$value);
				if(preg_match('~'.$username.'~i',$supplier_login_single[0])) 
				{
					if($supplier_login_single[1] != 'all') $supplier_login = trim($supplier_login_single[1]);
					else $supplier_login = '';
				}
			}
						
			$tickbox 					= $this->_getConfig('pickpack_tickbox', $tickbox_default, false,'general');
			// $picklogo  					= $this->_getConfig('pickpack_picklogo', 0, false,'general');
		
			$override_address_format_yn = 1;//$this->_getConfig('override_address_format_yn', 0,false,'general');
			$customer_email_yn 			= $this->_getConfig('customer_email_yn', 0,false,'general');
			$customer_phone_yn 			= $this->_getConfig('customer_phone_yn', 0,false,'general');

			$address_format 			= $this->_getConfig('address_format', '', false,'general'); //col/sku

			$address_countryskip  		= $this->_getConfig('address_countryskip', 0,false,'general');              
	
			$bottom_shipping_address_yn = $this->_getConfig('pickpack_bottom_shipping_address_yn', 0,false,$wonder);              
			$return_address_yn			= $this->_getConfig('pickpack_return_address_yn', 0,false,$wonder);              
	    	if($page_size == 'a5landscape')
			{         
				$bottom_shipping_address_yn = 0;
				$return_address_yn = 0;
			}
			if($return_address_yn == 0) $showReturnLogoYN = '0';
			//$return_address			    = $this->_getConfig('pickpack_return_address', '',false,$wonder,$order->getStore());               
			$return_address			    = $this->_getConfig('pickpack_return_address', '',false,$wonder);    
			$company_address_yn			= $this->_getConfig('pickpack_company_address_yn', 0,false,$wonder);              
			$company_address			= $this->_getConfig('pickpack_company_address', '',false,$wonder);               
			// $company_address			= $this->_getConfig('pickpack_company_address', '',false,$wonder,$order->getStore());               
	  	
			$capitalize_label_yn		= $this->_getConfig('capitalize_label_yn', 0,false,'general');   // o,usonly,1           
	
			//$image = Mage::getStoreConfig('pickpack_options/wonder/pickpack_logo', $order->getStore());
	
			$message_yn					= $this->_getConfig('custom_message_yn', '', false,$wonder);
	  		$message_filter				= trim($this->_getConfig('custom_message_filter', '', false,$wonder));
	  		$message					= $this->_getConfig('custom_message', '', false,$wonder);
	  		$messageA					= $this->_getConfig('custom_messageA', '', false,$wonder);
	  		$messageB					= $this->_getConfig('custom_messageB', '', false,$wonder);
			if($message_yn == 'yes2') $message = $messageA;

			$sort_packing_yn			= $this->_getConfig('sort_packing_yn', 1,false,'general');               
			$sort_packing			    = $this->_getConfig('sort_packing', 'sku',false,'general');               
			$sortorder_packing			= $this->_getConfig('sort_packing_order', 'ascending',false,'general');  
			$sort_packing_attribute		= trim($this->_getConfig('sort_packing_attribute', '',false,'general'));  
			             
			if($sort_packing_yn == 0) $sortorder_packing = 'none';
			elseif($sort_packing == 'Mcategory') $sort_packing = 'Mcategory';
			elseif($sort_packing_attribute != '') $sort_packing = $sort_packing_attribute;
			
			$total_price_taxed			= 0;
	
			/**
			 * get store id
			 */
			$storeId = Mage::app()->getStore()->getId();
			
	
             /***********************************************************
            * CONFIGURATIONS
            ***********************************************************/

			if(function_exists('sksort'))
			{}
			else
			{
				function sksort(&$array, $subkey, $sort_ascending=false) 
				{

				    if (count($array))
				        $temp_array[key($array)] = array_shift($array);

				    foreach($array as $key => $val){
				        $offset = 0;
				        $found = false;
				        foreach($temp_array as $tmp_key => $tmp_val)
				        {
				            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
				            {
				                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
				                                            array($key => $val),
				                                            array_slice($temp_array,$offset)
				                                          );
				                $found = true;
				            }
				            $offset++;
				        }
				        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
				    }

				    if ($sort_ascending) $array = array_reverse($temp_array);

				    else $array = $temp_array;
				}
			}
		
	        $sku_supplier_item_action = array();
			$sku_supplier_item_action_master = array();
			
			if($split_supplier_yn != 'no')
			{
				foreach ($orders as $orderSingle) 
				{
					$order = Mage::getModel('sales/order')->load($orderSingle);		
					$order_id  = $order->getRealOrderId();
					$itemsCollection =  $order->getAllVisibleItems();
								
					foreach ($itemsCollection as $item)
					{		
						if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
						{
							$sku = $item->getProductOptionByCode('simple_sku');
							$product_id = Mage::getModel('catalog/product')->getIdBySku($sku);
						} 
						else 
						{
							$sku = $item->getSku();
							$product_id = $item->getProductId(); // get it's ID			
						}	

						$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku, array('cost',$shelving_attribute,$shelving_real_attribute,'name','simple_sku','qty'));			
						if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
					
						$supplier = '';
						$sku_supplier_item_action_master[$order_id] = 'keep';
						$loop_supplier = 0;
				
						if($split_supplier_yn == 'pickpack')
						{
							$sku_supplier_item_action_master[$order_id] = 'hide';
							if(Mage::getModel('catalog/product')->load($product_id))
							{
								$_newProduct = Mage::getModel('catalog/product')->load($product_id);  
								if($_newProduct->getData($supplier_attribute)) $supplier = $_newProduct->getData(''.$supplier_attribute.'');
							}
							elseif($product->getData(''.$supplier_attribute.''))
							{
								 $supplier = $product->getData($supplier_attribute);
							}
							if(Mage::getModel('catalog/product')->load($product_id)->getAttributeText($supplier_attribute))
							{
								$supplier = Mage::getModel('catalog/product')->load($product_id)->getAttributeText($supplier_attribute);
							}
							elseif($product[$supplier_attribute]) $supplier = $product[$supplier_attribute];
							if(is_array($supplier)) $supplier = implode(',',$supplier);
							if(!$supplier) $supplier = '~Not Set~';	
							$supplier = trim(strtoupper($supplier));
							if(isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier) $sku_supplier[$sku] .= ','.$supplier;
							else $sku_supplier[$sku] = $supplier;
							$sku_supplier[$sku] = preg_replace('~,$~','',$sku_supplier[$sku]);
							if(!isset($supplier_master[$supplier]))
							{
								$supplier_master[$supplier] = $supplier;
								$supplier_ubermaster[] = $supplier;
							}
					
							$order_id_master[$order_id] .= ','.$supplier;
							$sku_supplier_item_action[$supplier][$sku] = 'keep';
							// if set to filter and a name and this is the name, then print
							if($supplier_options == 'filter' && isset($supplier_login) && ($sku_supplier[$sku] == strtoupper($supplier_login)) )//grey //split
							{
								$sku_supplier_item_action[$supplier][$sku] = 'keep';
							}
							elseif($supplier_options == 'filter' && isset($supplier_login) && ($supplier_login != '') && ($sku_supplier[$sku] != strtoupper($supplier_login)) )//grey //split
							{
								$sku_supplier_item_action[$supplier][$sku] = 'hide';
							}
							elseif($supplier_options == 'grey' && isset($supplier_login) && ($sku_supplier[$sku] == strtoupper($supplier_login)) )//grey //split
							{
								$sku_supplier_item_action[$supplier][$sku] = 'keep';
							}
							elseif($supplier_options == 'grey' && isset($supplier_login) && $supplier_login != '' && ($sku_supplier[$sku] != strtoupper($supplier_login)) )//grey //split
							{
								$sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
							}
							elseif($supplier_options == 'grey' && ($supplier_login == '') && ($sku_supplier[$sku] != strtoupper($supplier) ) )
							{
								$sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
							}
							elseif($supplier_options == 'filter' && ($supplier_login == '') && ($sku_supplier[$sku] != strtoupper($supplier) ) )
							{
								$sku_supplier_item_action[$supplier][$sku] = 'hide';
							}
							elseif($supplier_options == 'grey' && ($supplier_login == '') && ($sku_supplier[$sku] == strtoupper($supplier) ) )
							{
								$sku_supplier_item_action[$supplier][$sku] = 'keep';
							}
							elseif($supplier_options == 'filter' && ($supplier_login == '') && ($sku_supplier[$sku] == strtoupper($supplier) ) )
							{
								$sku_supplier_item_action[$supplier][$sku] = 'keep';
							}
							elseif($supplier_options == 'grey') $sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
							elseif($supplier_options == 'filter') $sku_supplier_item_action[$supplier][$sku] = 'hide';
						
							if($split_supplier_yn == 'no') $sku_supplier_item_action[$supplier][$sku] = 'keep';
					
							if(($sku_supplier_item_action_master[$order_id] != 'keep') && ($sku_supplier_item_action_master[$order_id] != 'keepGrey') && (($sku_supplier_item_action[$supplier][$sku] == 'keepGrey') || ($sku_supplier_item_action[$supplier][$sku] == 'keep'))) $sku_supplier_item_action_master[$order_id] = 'keep';
							$loop_supplier = 1;
						}
					}
				}
			}

				if(($split_supplier_yn != 'no' && $sku_supplier_item_action_master[$order_id] == 'keep') || ($split_supplier_yn == 'no'))
				{
					$this->_beforeGetPdf();
					$this->_initRenderer('invoices');
					$pdf = new Zend_Pdf();
					$this->_setPdf($pdf);
					$style = new Zend_Pdf_Style();
					
					/*
					Letter		612x792 587x770 (-25 -22)
					A4		 	595x842 570x820 (-25 -22)
					A5		 	420x595 395x573 
					A5(L)		595x420 573x395 
					*/
				
					if($page_size == 'letter') 
					{
						$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
						$page_top = 770;$padded_right = 587;
					}
					elseif($page_size == 'a4') 
					{
						$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
						$page_top = 820;$padded_right = 570;
					}
					elseif($page_size == 'a5landscape') 
					{
						$page = $pdf->newPage('596:421');					
						$page_top = 395;$padded_right = 573;
					}
					
					$pdf->pages[] = $page;
		            $this->y = $page_top;//$page->getHeight();
				}
				
				$s = 0;				
				$first_page_yn 			= 'y';
				$page_count				= 1;
				
				do
				{
					if($split_supplier_yn != 'no') $supplier = $supplier_ubermaster[$s];
					
					foreach ($orders as $orderSingle) 
					{
						$order = Mage::getModel('sales/order')->load($orderSingle);		
						$order_id  = $order->getRealOrderId();
						
						$itemsCollection =  $order->getAllVisibleItems();
						
						if((isset($sku_supplier_item_action_master[$order_id]) && $sku_supplier_item_action_master[$order_id] == 'keep') || ($split_supplier_yn == 'no'))
						{
							if($first_page_yn == 'n') 
							{
								$page = $this->newPage();
							}
							else $first_page_yn = 'n';
							
				            # BARCODE
				            if(1 == $showBarCode){
								$page->setFillColor($black_color);
								$this->_setFontBold($page, 10);
				        		$barcodeString = $this->convertToBarcodeString($order->getRealOrderId());
				        		$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 16);  
				        		$page->drawText($barcodeString, $barcodeXY[0], $barcodeXY[1], 'CP1252');
				                $page->setFillColor($black_color);
				                $this->_setFontRegular($page, $font_size_body);
				        	}     
				
				       		if($invoice_title_2_yn == 1 && $invoice_title_2 != '')
							{
								$this->_setFont($page, $font_style_subtitles, $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
								$page->drawText($invoice_title_2, $title2XY[0], $title2XY[1], 'UTF-8');
							}
				            # HEADER 
							// draw box behind order ID#
							$page->setFillColor($background_color_subtitles_zend);			
							$page->setLineColor($background_color_subtitles_zend);
							$page->setLineWidth(0.5);			
							$page->drawRectangle(20, ($orderIdXY[1]-($font_size_subtitles/2)), $padded_right, ($orderIdXY[1]+$font_size_subtitles+2));

							// header
							$this->_setFont($page, $font_style_subtitles, $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							// subtitles
							
							$order_date = '';
							if($order->getCreatedAtStoreDate()) $order_date = Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false);

							$order_number_display = '#'.$order->getRealOrderId();
							$order_number_display .= '    '.$order_date;
							if($invoice_title_yn == 1 && $invoice_title != '')
							{
								$order_number_display = $invoice_title.'    '.$order_number_display;
							}
							$page->drawText($order_number_display, $orderIdXY[0], $orderIdXY[1], 'UTF-8');
							//$page->drawText('   '.Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), $orderDateXY[0], $orderIdXY[1], 'UTF-8');
				         	if($split_supplier_yn == 'pickpack') $page->drawText('Supplier: '.$supplier, ($orderDateXY[0]+150), $orderIdXY[1], 'UTF-8');
				            
							if($show_top_logo_yn == 1)
							{
								// header logo
					   			$this->insertLogo($page, $page_size,$wonder,$order->getStore());
							}
							
				   			// HEADER STORE ADDRESS
							$this->y = $page_top;//820;
							if($company_address_yn == 1)
							{
								// store-specific
								$company_address			= $this->_getConfig('pickpack_company_address', '',false,$wonder,$order->getStore()); 
								$this->_setFont($page, $font_style_body, $fontsize_returnaddresstop, $font_family_body, $non_standard_characters, $font_color_body);
								$line_height = 1;

								foreach (explode("\n", $company_address) as $value)
								{
									if ($value!=='')
									{
										$page->drawText(trim(strip_tags($value)), 320, ($this->y-$line_height), 'UTF-8');
									}
									$line_height = ($line_height + $fontsize_returnaddresstop);
								}
								
								// HEADER LINES
								$page->setFillColor($background_color_subtitles_zend);			
								$page->setLineColor($background_color_subtitles_zend);
								$page->setLineWidth(0.5);			
								//820 / 770
								$page->drawRectangle(304, ($page_top-36), 316, ($page_top+5));
								$this->_setFont($page, $font_style_subtitles, $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							}
							else $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

							$customer_email = '';
							if($order->getCustomerEmail()) $customer_email = trim($order->getCustomerEmail());
						
							$customer_phone = '';
							if($order->getShippingAddress()->getTelephone()) $customer_phone = trim($order->getShippingAddress()->getTelephone());

							$customer_company = '';
							if($order->getShippingAddress()->getCompany()) $customer_company = trim($order->getShippingAddress()->getCompany());

							$customer_name = '';
							if($order->getShippingAddress()->getName()) $customer_name = trim($order->getShippingAddress()->getName());

							$customer_city = '';
							if($order->getShippingAddress()->getCity()) $customer_city = trim($order->getShippingAddress()->getCity());

							$customer_postcode = '';
							if($order->getShippingAddress()->getPostcode()) $customer_postcode = trim(strtoupper($order->getShippingAddress()->getPostcode()));

							$customer_region = '';
							if($order->getShippingAddress()->getRegion()) $customer_region = trim($order->getShippingAddress()->getRegion());

							$customer_region_code = '';
							if($order->getShippingAddress()->getRegionCode()) $customer_region_code = trim($order->getShippingAddress()->getRegionCode());

							$customer_prefix = '';
							if($order->getShippingAddress()->getPrefix()) $customer_prefix = trim($order->getShippingAddress()->getPrefix());
  	
							$customer_suffix = '';
							if($order->getShippingAddress()->getSuffix()) $customer_suffix = trim($order->getShippingAddress()->getSuffix());

							$customer_country = '';
							if(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId())) $customer_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()));

							if($billing_details_yn == 1)
							{
								$billing_email = '';
								// if($order->getCustomerEmail()) $billing_email = trim($order->getbillingEmail());

								$billing_phone = '';
								if($order->getBillingAddress()->getTelephone()) $billing_phone = trim($order->getBillingAddress()->getTelephone());

								$billing_company = '';
								if($order->getBillingAddress()->getCompany()) $billing_company = trim($order->getBillingAddress()->getCompany());

								$billing_name = '';
								if($order->getBillingAddress()->getName()) $billing_name = trim($order->getBillingAddress()->getName());

								$billing_city = '';
								if($order->getBillingAddress()->getCity()) $billing_city = trim($order->getBillingAddress()->getCity());

								$billing_postcode = '';
								if($order->getBillingAddress()->getPostcode()) $billing_postcode = trim(strtoupper($order->getBillingAddress()->getPostcode()));

								$billing_region = '';
								if($order->getBillingAddress()->getRegion()) $billing_region = trim($order->getBillingAddress()->getRegion());

								$billing_region_code = '';
								if($order->getBillingAddress()->getRegionCode()) $billing_region_code = trim($order->getBillingAddress()->getRegionCode());

								$billing_prefix = '';
								if($order->getBillingAddress()->getPrefix()) $billing_prefix = trim($order->getBillingAddress()->getPrefix());

								$billing_suffix = '';
								if($order->getBillingAddress()->getSuffix()) $billing_suffix = trim($order->getBillingAddress()->getSuffix());

								$billing_country = '';
								if(Mage::app()->getLocale()->getCountryTranslation($order->getBillingAddress()->getCountryId())) $billing_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getBillingAddress()->getCountryId()));
								
								$billing_addy 				= array();
								$if_contents 		= array();
								$billing_addy['company'] 	= $billing_company; //$order->getBillingAddress()->getCompany();
								$billing_addy['name'] 		= $billing_name; //$order->getBillingAddress()->getName();
								$billing_addy['city'] 		= $billing_city; //$order->getBillingAddress()->getCity();
								$billing_addy['postcode'] 	= $billing_postcode; //$order->getBillingAddress()->getPostcode();
								$billing_addy['region_full'] = $billing_region; // $order->getBillingAddress()->getRegion();
								$billing_addy['region_code'] = $billing_region_code; // $order->getBillingAddress()->getRegion();
								if($billing_region_code != '') 
								{
									$billing_addy['region']		= $billing_region_code; // $order->getShippingAddress()->getRegion();
								}
								else
								{
									$billing_addy['region']		= $billing_region; // $order->getShippingAddress()->getRegion();
								}
								$billing_addy['prefix'] 	= $billing_prefix; //$order->getBillingAddress()->getPrefix();
								$billing_addy['suffix'] 	= $billing_suffix; //$order->getBillingAddress()->getSuffix();
								$billing_addy['country'] 	= $billing_country; //Mage::app()->getLocale()->getCountryTranslation($order->getBillingAddress()->getCountryId());
								if(trim($address_countryskip) != '') $billing_addy['country'] = str_ireplace($address_countryskip,'',$billing_addy['country']);
													
								$i = 0;
								while($i < 10)
								{
									if($order->getBillingAddress()->getStreet($i) && !is_array($order->getBillingAddress()->getStreet($i)))
									{
										if(isset($billing_addy['street'])) $billing_addy['street'] .= ", \n";
										else $billing_addy['street'] = '';
										$value = $order->getBillingAddress()->getStreet($i);

										$max_chars = 20;
										$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
										$font_size_compare = ($font_size_body*0.8);
										$line_width = $this->parseString('1234567890',$font_temp,$font_size_compare); // bigger = left
										$char_width = $line_width/10;
										$max_chars = round(($orderdetailsX-160)/$char_width);
										// wordwrap characters
										$value = wordwrap($value, $max_chars, "\n", false);
										$token = strtok($value, "\n");
										while ($token != false) 
										{
											if(trim(str_replace(',','',$token)) != '')
											{
												$billing_addy['street'] .= trim($token)."\n";
											}
											$token = strtok("\n");
										}
								
										
									}
									$i ++;
								}
							
								$address_format_set = str_replace(array("\n",'<br />','<br/>',"\r"),'',$address_format);

								foreach($billing_addy as $key =>$value)
								{									
									$value = trim($value);
									$value = preg_replace('~,$~','',$value);
									$value = str_replace(',,',',',$value);
									
									if($value != '' && !is_array($value))
									{		
										preg_match('~{if '.$key.'}(.*){\/if '.$key.'}~ims',$address_format_set,$if_contents);
										
										if(isset($if_contents[1])) 
										{
											$if_contents[1] = str_replace('{'.$key.'}',$value,$if_contents[1]);
										}
										else $if_contents[1] = '';
										
										$address_format_set = preg_replace('~\{if '.$key.'\}(.*)\{/if '.$key.'\}~ims',$if_contents[1],$address_format_set);
										$address_format_set = str_replace('{'.$key.'}',$value,$address_format_set);
									}
									else
									{
										$address_format_set = preg_replace('~\{if '.$key.'\}(.*)\{/if '.$key.'\}~i','',$address_format_set);	
										$address_format_set = str_replace('{'.$key.'}','',$address_format_set);
									}	
								}
								
								$address_format_set = trim(str_replace(array('||','|'),"\n",trim($address_format_set)));

								$billingAddressArray = explode("\n",$address_format_set);
								$billing_line_count = (count($billingAddressArray)-1);
							}
							// end billing address

							$addy 				= array();
							$if_contents 		= array();
							$addy['company'] 	= $customer_company; //$order->getShippingAddress()->getCompany();
							$addy['name'] 		= $customer_name; //$order->getShippingAddress()->getName();
							$addy['city'] 		= $customer_city; //$order->getShippingAddress()->getCity();
							$addy['postcode'] 	= $customer_postcode; //$order->getShippingAddress()->getPostcode();
							$addy['region_full'] = $customer_region; // $order->getShippingAddress()->getRegion();
							$addy['region_code']= $customer_region_code; // $order->getShippingAddress()->getRegion();
							if($customer_region_code != '') 
							{
								$addy['region']		= $customer_region_code; // $order->getShippingAddress()->getRegion();
							}
							else
							{
								$addy['region']		= $customer_region; // $order->getShippingAddress()->getRegion();
							}
							$addy['prefix'] 	= $customer_prefix; //$order->getShippingAddress()->getPrefix();
							$addy['suffix'] 	= $customer_suffix; //$order->getShippingAddress()->getSuffix();
							$addy['country'] 	= $customer_country; //Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId());
							if(trim($address_countryskip) != '') $addy['country'] = str_ireplace($address_countryskip,'',$addy['country']);
							
							$i = 0;
							while($i < 10)
							{
								if($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i)))
								{
									if(isset($addy['street'])) $addy['street'] .= ", \n";
									else $addy['street'] = '';
									$value = $order->getShippingAddress()->getStreet($i);

									$max_chars = 20;
									$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
									$font_size_compare = ($font_size_body*0.8);
									$line_width = $this->parseString('1234567890',$font_temp,$font_size_compare); // bigger = left
									$char_width = $line_width/10;
									$max_chars = round(($orderdetailsX-160)/$char_width);
									// wordwrap characters
									$value = wordwrap($value, $max_chars, "\n", false);
									$token = strtok($value, "\n");
									while ($token != false) 
									{
										if(trim(str_replace(',','',$token)) != '')
										{
											$addy['street'] .= trim($token)."\n";
										}
										$token = strtok("\n");
									}									
								}
								$i ++;
							}
						
							$addy['name'] = trim(preg_replace('~^'.$addy['company'].'~i','',$addy['name']));
							if(isset($billing_addy)) $billing_addy['name'] = trim(preg_replace('~^'.$billing_addy['company'].'~i','',$billing_addy['name']));
				
							$address_format_set = str_replace(array("\n",'<br />','<br/>',"\r"),'',$address_format);

							foreach($addy as $key =>$value)
							{									
								$value = trim($value);
								$value = preg_replace('~,$~','',$value);
								$value = str_replace(',,',',',$value);
								
								if($value != '' && !is_array($value))
								{		
									preg_match('~\{if '.$key.'\}(.*)\{\/if '.$key.'\}~ims',$address_format_set,$if_contents);
									
									if(isset($if_contents[1])) 
									{
										$if_contents[1] = str_replace('{'.$key.'}',$value,$if_contents[1]);
									}
									else $if_contents[1] = '';
									
									$address_format_set = preg_replace('~\{if '.$key.'\}(.*)\{/if '.$key.'\}~ims',$if_contents[1],$address_format_set);
									$address_format_set = str_replace('{'.$key.'}',$value,$address_format_set);
								}
								else
								{
									$address_format_set = preg_replace('~\{if '.$key.'\}(.*)\{/if '.$key.'\}~i','',$address_format_set);	
									$address_format_set = str_replace('{'.$key.'}','',$address_format_set);
								}	
							}
							
							$address_format_set = trim(str_replace(array('||','|'),"\n",trim($address_format_set)));

							$shippingAddressArray = explode("\n",$address_format_set);
							
							$count = (count($shippingAddressArray)-1);
							
							$shipping_line_count = (count($shippingAddressArray)-1);
							
							if(isset($billing_line_count) && $billing_line_count > $shipping_line_count) $shipping_line_count = $billing_line_count;
							
							$address_left_Y 	= $addressXY[0];
							$address_right_Y	= $orderdetailsX;
							
							if($billing_details_position == 1)
							{
								$address_left_Y 	= $orderdetailsX;
								$address_right_Y	= $addressXY[0];
							}
							
							$this->_setFont($page, 'bold', ($font_size_body+2), $font_family_body, $non_standard_characters, $font_color_body);
							if($shipping_title != '') 
							{
								$page->drawText($shipping_title, $address_left_Y, ($addressXY[1]+20), 'UTF-8');
							}
							
							if($billing_details_yn == 1 && $billing_title != '')
							{
								$page->drawText($billing_title, $address_right_Y, ($addressXY[1]+20), 'UTF-8');
							}
							
							$this->_setFont($page, $font_style_body, ($font_size_body+2), $font_family_body, $non_standard_characters, $font_color_body);
							
							$line_height 		= 0;
							$addressLine 		= '';				           
							$i 					= 0;
							$line_height_top 	= (1.07*($font_size_body+2));
							$line_height_bottom = (1.05*$fontsize_shipaddress);
							$i_space 			= -1;
							
							foreach($shippingAddressArray as $i =>$value)
							{
								$skip 		= 0;
								$line_addon	= 0;
								$line_bold 	= 0;
								
								$value = trim($value);
								$value = preg_replace('~^,$~','',$value);
								
								if($i == $shipping_line_count) 
								{
									$line_bold = 1;
									$value = preg_replace('~,$~','',$value);
								}
								
								if($value != '')
								{
									$i_space = ($i_space+1);
									if($i > 0) 
									{
										if((trim($shippingAddressArray[($i-1)])) == '') $i_space = ($i_space-1);
									}
							
									$this->_setFont($page, $font_style_body, ($font_size_body+2), $font_family_body, $non_standard_characters, $font_color_body);
									$page->drawText($value, $address_left_Y, ($addressXY[1]-($line_height_top*$i_space)-$line_addon), 'UTF-8');
									
									$fontsize_adjust = 0
									;
									if($bottom_shipping_address_yn == 1) 
									{
										if(($capitalize_label_yn == 1) || (($capitalize_label_yn == 'usonly') && (strtolower($customer_country) == 'united states')))
										{
											$value = trim(strtoupper($value));
										
											// remove line-end commas for USPS
											if(strtolower($customer_country) == 'united states') $value = preg_replace('~,$~','',$value);
											$fontsize_adjust = 2;
										}
										
										if($line_bold == 1) 
										{
											$this->_setFont($page, 'bold', ($fontsize_shipaddress+2-$fontsize_adjust), $font_family_body, $non_standard_characters, $font_color_body);
											$line_addon = ($fontsize_shipaddress*0.1);
										}
										else $this->_setFont($page, $font_style_body, ($fontsize_shipaddress-$fontsize_adjust), $font_family_body, $non_standard_characters, $font_color_body);
										
										$page->drawText($value, $addressFooterXY[0], ($addressFooterXY[1]-($line_height_bottom*$i_space)-$line_addon), 'UTF-8');
									}
								}
							}
							$subheader_start = ceil(($addressXY[1]-($line_height_top*($i_space+1))-$line_addon));
							
							$billing_shipping_details_y = null;
							
							if($billing_details_yn == 1)
							{
								$i = 0;
								$line_height_top = (1.07*($font_size_body+2));
								$line_height_bottom = (1.05*$fontsize_shipaddress);
								$i_space = -1;
								
								foreach($billingAddressArray as $i =>$value)
								{
									$skip = 0;
									$line_addon=0;

									$value = trim($value);
									$value = preg_replace('~^,$~','',$value);
									
									if($i == $billing_line_count) 
									{
										$value = preg_replace('~,$~','',$value);
									}

									if($value != '')
									{
										$i_space = ($i_space+1);
										if($i > 0) 
										{
											if((trim($billingAddressArray[($i-1)])) == '') $i_space = ($i_space-1);
										}

										$this->_setFont($page, $font_style_body, ($font_size_body+2), $font_family_body, $non_standard_characters, $font_color_body);
										$page->drawText($value, $address_right_Y, ($addressXY[1]-($line_height_top*$i_space)-$line_addon), 'UTF-8');
									}
								}
								
								$billing_shipping_details_y = ceil(($addressXY[1]-($line_height_top*($i_space+2))-$line_addon));
								
								if($billing_shipping_details_y > $subheader_start) $billing_shipping_details_y = $subheader_start;
							}

							$top_phone_Y = 0;
							if($customer_phone_yn != 'no' && $customer_phone != '')
							{
								$i_space += 1;
								$value = 'T: '.$customer_phone;
								$line_addon=($font_size_body*0.5);
								$this->_setFont($page, $font_style_body, ($font_size_body), $font_family_body, $non_standard_characters, $font_color_body);
					
								$top_phone_Y = ceil($addressXY[1]-($line_height_top*$i_space)-$line_addon);

								if($top_phone_Y > $billing_shipping_details_y && $billing_shipping_details_y > 0) $top_phone_Y = ($billing_shipping_details_y-$line_addon);
								
								$bottom_phone_Y = ($addressFooterXY[1]-($line_height_bottom*$i_space)-$line_addon);
							
								if($customer_phone_yn != 'yeslabel') $page->drawText($value, $addressXY[0], ($top_phone_Y), 'UTF-8');
								$this->_setFont($page, 'regular', ($fontsize_shipaddress-2), $font_family_body, $non_standard_characters, 'Gray');
								if($bottom_shipping_address_yn == 1 && $customer_phone_yn != 'yesdetails') $page->drawText($value, $addressFooterXY[0], $bottom_phone_Y, 'UTF-8');
								
								$subheader_start -= ($subheader_start - $top_phone_Y);
								$this->y = $top_phone_Y - ($font_size_body*0.5);
								
							}
							
							if($customer_email_yn != 'no' && $customer_email != '')
							{
								$i_space += 1;
								$value = 'E: '.$customer_email;
								$line_addon=($font_size_body*0.5);
								$this->_setFont($page, $font_style_body, ($font_size_body), $font_family_body, $non_standard_characters, $font_color_body);
					
								$top_email_Y = ceil($addressXY[1]-($line_height_top*$i_space)-$line_addon);
								if($top_email_Y > $billing_shipping_details_y && $billing_shipping_details_y > 0) $top_email_Y = ($billing_shipping_details_y-$line_addon);
								if($top_phone_Y > 0 && $top_phone_Y == $top_email_Y) $top_email_Y = ceil($top_email_Y - $line_height_top);
								
								$bottom_email_Y = ($addressFooterXY[1]-($line_height_bottom*$i_space)-$line_addon);
								
								if($customer_email_yn != 'yeslabel') $page->drawText($value, $addressXY[0], ($top_email_Y), 'UTF-8');
								$this->_setFont($page, 'regular', ($fontsize_shipaddress-2), $font_family_body, $non_standard_characters, 'Gray');
								if($bottom_shipping_address_yn == 1 && $customer_email_yn != 'yesdetails') $page->drawText($value, $addressFooterXY[0], $bottom_email_Y, 'UTF-8');
							
								$subheader_start -= ($subheader_start - $top_email_Y);
								$this->y = $top_email_Y - ($font_size_body*0.5);
							}
							
						
							if($shipment_details_yn == 1)	
							{									
								$shipping_method = clean_method($order->getShippingDescription(),'shipping');

								$paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
								$payment_test = clean_method($paymentInfo,'payment');
					
								$customer_group = '';
								$customer_group = ucwords(strtolower(Mage::getModel('customer/group')->load((int)$order->getCustomerGroupId())->getCode()));

								$customer_id	= trim($order->getCustomerId());

								$line_height = 0;

								$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

								if(isset($billing_shipping_details_y) && $billing_shipping_details_y != null) $shipment_details_y = $billing_shipping_details_y;
								else $shipment_details_y = $addressXY[1];
								
								if($shipment_details_count == 1)
								{
									$items_count = $order['total_item_count'];
									if(!$items_count) 
									{
										// v3.1.2.4
										$items_count = count($order->getAllVisibleItems());
									}
									
									$page->drawText('Item count :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
									$page->drawText($items_count, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');

									$line_height = ($line_height+(1.15*$font_size_body));
									//total_item_count
								}

								if($shipment_details_weight == 1)
								{
									$total_shipping_weight = 0;
									$total_shipping_weight = round($order['weight'],2);
									$page->drawText('Weight :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
									$page->drawText($total_shipping_weight.' '.$shipment_details_weight_unit, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');
									$line_height = ($line_height+(1.15*$font_size_body));
								}

								if($shipment_details_carrier == 1 && $shipping_method != '')	
								{
									$page->drawText('Shipping :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
									$page->drawText($shipping_method, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');

									$line_height = ($line_height+(1.15*$font_size_body));
								}

								if($shipment_details_payment == 1)	
								{
									$page->drawText('Payment :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
									$page->drawText($payment_test, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');
									$line_height = ($line_height+(1.15*$font_size_body));
								}

								if($shipment_details_custgroup == 1 && $customer_group != '')	
								{
									$customer_group_filtered = $customer_group;
									$customer_group_filter_array = array();
									$customer_group_filter_array = explode(',',$customer_group_filter);
									foreach($customer_group_filter_array as $customer_group_filter_single)
									{
										$customer_group_filtered = trim(str_ireplace(trim($customer_group_filter_single),'',$customer_group_filtered));
									}

									if($customer_group_filtered != '')
									{
										$page->drawText('Customer group :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
										$page->drawText($customer_group, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');
										$line_height = ($line_height+(1.15*$font_size_body));
									}									
								}

								if($shipment_details_customer_id == 1 && $customer_id != '')
								{
										$page->drawText('Customer ID :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
										$page->drawText($customer_id, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');
										$line_height = ($line_height+(1.15*$font_size_body));
								}
								
								
								if($shipment_details_custom_attribute_yn == 1 && $shipment_details_custom_attribute != '' && Mage::getModel('amorderattr/attribute'))
								{
									unset($shipment_custom_attribute_label);
									unset($shipment_custom_attribute);
									$shipment_custom_attribute_label = array();
									$shipment_custom_attribute = array();
									
									$collection = Mage::getModel('eav/entity_attribute')->getCollection();
							        $collection->addFieldToFilter('is_visible_on_front', 1);
							        $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
							        // $collection->getSelect()->order('checkout_step');
							        $attributes = $collection->load();
							        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');

							        if ($attributes->getSize())
							        {
							            foreach ($attributes as $attribute)
							            {
							                $currentStore = $order->getStoreId();
							                $storeIds = explode(',', $attribute->getData('store_ids'));
							                if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds))
							                {
							                    continue;
							                }

							                $value_a = '';
							                switch ($attribute->getFrontendInput())
							                {
							                    case 'select':
							                        $options_a = $attribute->getSource()->getAllOptions(true, true);

													foreach ($options_a as $option_a)
							                        {
							                            if ($option_a['value'] == $orderAttributes->getData($attribute->getAttributeCode()))
							                            {
							                                $value_a = $option_a['label'];
							                                break;
							                            }
							                        }
							                        break;
							                    default:
							                        $value_a = $orderAttributes->getData($attribute->getAttributeCode());
							                        break;
							                }
											
											if(!isset($shipment_custom_attribute_label[$attribute->getAttributeCode()]))
											{
												$shipment_custom_attribute_label[$attribute->getAttributeCode()] = trim(str_replace(array(':','Ship Order When','Information'),array('','Ship Order','Info'),$attribute->getFrontendLabel()));
											}
											
							                $shipment_custom_attribute[$attribute->getAttributeCode()] = $value_a;
							            }
							        }
								
									if(isset($shipment_custom_attribute_label[$shipment_details_custom_attribute]) && $shipment_custom_attribute[$shipment_details_custom_attribute] != '') 
									{
										$display_cust_attr_label_1 = $shipment_custom_attribute_label[$shipment_details_custom_attribute];
										$display_cust_attr_1 = $shipment_custom_attribute[$shipment_details_custom_attribute];
									
										$page->drawText($display_cust_attr_label_1.' :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
										$page->drawText($display_cust_attr_1, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');
										$line_height = ($line_height+(1.15*$font_size_body));
									}
								}
						
								if($shipment_details_custom_attribute_2_yn == 1 && $shipment_details_custom_attribute_2 != '')
								{
									if(isset($shipment_custom_attribute_label[$shipment_details_custom_attribute_2]) && $shipment_custom_attribute[$shipment_details_custom_attribute_2] != '') 
									{
										$display_cust_attr_label_2 = $shipment_custom_attribute_label[$shipment_details_custom_attribute_2];
										$display_cust_attr_2 = $shipment_custom_attribute[$shipment_details_custom_attribute_2];
									
										$page->drawText($display_cust_attr_label_2.' :', $orderdetailsX, ($shipment_details_y-$line_height), 'UTF-8');
										$page->drawText($display_cust_attr_2, ($orderdetailsX+80), ($shipment_details_y-$line_height), 'UTF-8');
										$line_height = ($line_height+(1.15*$font_size_body));
									}
								}
					
								if($message_yn == 'yes2')
								{
									if(preg_match('~'.$message_filter.'~i',$customer_group)) $message = $messageB;
								}
								
								if(isset($billing_shipping_details_y) && $billing_shipping_details_y != null) 
								{										
									$subheader_start = round(($shipment_details_y-$line_height));
								}
							}

	/* Add Order Notes*/    
							if($notes_yn == 'yesemail')
							{ 
								/*
									[entity_id] => 17
									                            [parent_id] => 8
									                            [is_customer_notified] => 0
									                            [is_visible_on_front] => 1
									                            [comment] => New note two ('visible on frontend')
									                            [status] => pending
									                            [created_at] => 2011-02-08 01:21:55
									                            [store_id] => 1
								*/
								
	
								if($order->getStatusHistoryCollection(true))
								{
							        $notes = $order->getStatusHistoryCollection(true);								
									$note_line = array();
									$note_comment_count = 0;

									$i = 0;
									foreach ($notes as $_item)
									{
										$_item['comment'] = trim($_item['comment']);
										// if(!preg_match('~'.$notes_filter.'~i',$_item['comment']) && ($_item['comment'] != ''))
										// 									{
										if((($notes_filter_options == 'yesfrontend' && $_item['is_visible_on_front'] == 1) || $notes_filter_options == 'no' || ($notes_filter_options == 'yestext' && !preg_match('~'.$notes_filter.'~i',$_item['comment'])))  && ($_item['comment'] != ''))
										{
											$_item['created_at'] 		= date('m/d/y',strtotime($_item['created_at']));
											$_item['comment'] 			= $_item['created_at'].' : '.$_item['comment'];
											$_item['comment'] 			= wordwrap($_item['comment'], 114, "\n", false);

											$note_line[$i]['date'] 		= $_item['created_at'];
											$note_line[$i]['comment'] 	= $_item['comment'];
											if($note_line[$i]['comment'] != '') $note_comment_count = 1;
											$note_line[$i]['is_visible_on_front'] = $_item['is_visible_on_front'];
											$i ++;
										}
										// }
									}
	
									if($note_comment_count > 0)
									{
										$this->y -= ($font_size_body + 4);
	
										//$addressFooterXY[0] = 25;	
									
										//$this->y -= 13;
	
										// $page->setFillColor($background_color_message_zend);			
										// page->setLineColor($background_color_message_zend);			
										// $page->setLineWidth(0.5);				
										// $page->drawRectangle(20, ($this->y + $font_size_body_message + 10), $padded_right, ($this->y + $font_size_body_message + 7));
										// 	
										$this->_setFont($page, 'bold', ($font_size_body-1), $font_family_body, $non_standard_characters, $font_color_body);
										$page->drawText('Order notes', ($addressXY[0]), $this->y);
										$this->y -= ($font_size_body+3);
										$this->_setFont($page, $font_style_body, ($font_size_body-1), $font_family_body, $non_standard_characters, $font_color_body);
	
	
										sksort($note_line, 'date', true);
	
										$i = 0;
										$line_count = 0;
										while(isset($note_line[$i]['date']))
										{
											// wordwrap characters
											$token = strtok($note_line[$i]['comment'], "\n");
											while ($token != false) 
											{
												$page->drawText(trim($token), ($addressXY[0]), $this->y);
												$this->y-=10;
												$token = strtok("\n");
												$line_count ++;
											}		
											$order_notes_was_set = true;
											// $line_count ++;
											$i ++;					
									    }
										$subheader_start -= (($font_size_body) * ($line_count+2));
									
	
									}
	
									unset($note_line);
								}
							}
							// 					//echo '$addressFooterXY[1]+20:'.$addressFooterXY[1]+20;exit;
						
							$shipping_address_header_lines = (count($shippingAddressArray) + 1);
							$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-3), $font_family_subtitles, $non_standard_characters, 'Gray');
							if($bottom_shipping_address_yn == 1) $page->drawText('#'.$order->getRealOrderId(), $addressFooterXY[0], ($addressFooterXY[1]+20), 'UTF-8');           
							
							$this->_setFont($page, $font_style_body, $fontsize_shipaddress, $font_family_body, $non_standard_characters, $font_color_body);
							
							$line_height = 0;
							$item_prev = '';
							$i = 0;
							$stop_address = FALSE;
							// if($override_address_format_yn != 1)
							// 							{
							// 								foreach($shippingAddressArray as $i =>$value)
							// 								{
							// 									$skip_entry = FALSE;
							// 								
							// 									if(isset($value) && (($addressFooterXY[1]-$line_height) > 0) && ($addressFooterXY[0] > 0) && trim($value) != '~')
							// 									{
							// 										// remove fax
							// 										$value = preg_replace('!<(.*)$!','',$value);
							// 										if(preg_match('~T:~',$value))
							// 										{	
							// 											$value = trim(str_replace('~','',$value));							
							// 											$line_height = ($line_height + ($fontsize_shipaddress/2));
							// 											$this->_setFont($page, 'regular', ($fontsize_shipaddress-2), $font_family_body, 'Gray');
							// 											$value = '[ '.$value.' ]';
							// 											$stop_address = TRUE;
							// 										}
							// 										elseif($stop_address === FALSE)
							// 										{
							// 											$this->_setFont($page, 'regular', $fontsize_shipaddress, $font_family_body, $non_standard_characters, $font_color_body);
							// 										 
							// 											if(!isset($shippingAddressArray[($i+1)]) || preg_match('~T:~',$shippingAddressArray[($i+1)]))
							// 											{
							// 												// last line, lets bold it and make it a bit bigger
							// 												$value = trim(str_replace('~','',$value));
							// 												if($boldLastAddressLineYN == 'Y')
							// 												{
							// 													$this->_setFont($page, 'bold', ($fontsize_shipaddress+2), $font_family_body, $non_standard_characters, $font_color_body);
							// 													$line_height = ($line_height + 5);
							// 												}
							// 											}
							// 											else
							// 											{
							// 												if((!isset($shippingAddressArray[($i+2)]) || preg_match('~T:~',$shippingAddressArray[($i+2)])))
							// 												{
							// 													$value = trim(str_replace('~','',$value));
							// 												}
							// 												else $value = trim(str_replace('~',',',$value));
							// 											}
							// 											$page->setFillColor($black_color);
							// 										}
							// 										
							// 										if($skip_entry === FALSE && $bottom_shipping_address_yn == 1) 
							// 										{
							// 											$page->drawText($value, $addressFooterXY[0], ($addressFooterXY[1]-$line_height), 'UTF-8');
							// 										}
							// 										
							// 										$line_height = ($line_height + $fontsize_shipaddress);
							// 									}
							// 									$i ++;
							// 								}    
							// 							}
							
							$page->setFillColor($black_color);

							// footer return address logo
							if($showReturnLogoYN == 1)
							{
								$image = Mage::getStoreConfig('pickpack_options/wonder/pickpack_logo', $order->getStore());
								if ($image) 
								{
									$image = Mage::getStoreConfig('system/filesystem/media', $order->getStore()) . '/sales/store/logo_third/' . $image;
									if (is_file($image)) 
									{
										$image = Zend_Pdf_Image::imageWithPath($image);
										$page->drawImage($image, $returnLogoXY[0], $returnLogoXY[1], ($returnLogoXY[0]+180), ($returnLogoXY[1]+120));
									}
								}
							}

							if($return_address_yn == 1)
							{
								// store-specific
								$return_address			    = $this->_getConfig('pickpack_return_address', '',false,$wonder,$order->getStore());
								// footer store address
								$return_address_title_fontsize = -2;
								if($fontsize_returnaddress > 10) $return_address_title_fontsize = 2;
								$this->_setFontRegular($page, ($fontsize_returnaddress-$return_address_title_fontsize));  
								$page->setFillColor(new Zend_Pdf_Color_Rgb($fontColorReturnAddressFooter,$fontColorReturnAddressFooter,$fontColorReturnAddressFooter));       
								$page->drawText('From :', $returnAddressFooterXY[0], $returnAddressFooterXY[1], 'UTF-8');
								$this->_setFontRegular($page, $fontsize_returnaddress);  
								$line_height = 20;
								foreach (explode("\n", $return_address) as $value)
								{
									if ($value!=='')
									{
										$page->drawText(trim(strip_tags($value)), $returnAddressFooterXY[0], ($returnAddressFooterXY[1]-$line_height), 'UTF-8');	
										$line_height = ($line_height + ($fontsize_returnaddress+1));
									}
								}
							}
				
							$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
							
				            $this->y         = ($subheader_start-($font_size_body*3));
				
							$page->setFillColor($background_color_subtitles_zend);			
							$page->setLineColor($background_color_subtitles_zend);	
							$page->setLineWidth(0.5);				
							$page->drawRectangle(20, ($this->y-($font_size_subtitles/2)), $padded_right, ($this->y+$font_size_subtitles+2));
			
							$this->_setFont($page, $font_style_subtitles, $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							
                            if($from_shipment){
                                $productXInc = 25;
                            }else{
                                $productXInc = 0;
                            }
							
							$first_item_title_shift_sku = 0;
							$first_item_title_shift_items = 0;
							
							if($qtyX > 50)
							{
								if($productX < $skuX) $first_item_title_shift_items = $first_item_title_shift;
								elseif($skuX < $productX) $first_item_title_shift_sku = $first_item_title_shift;
							}
	
							$page->drawText(Mage::helper('sales')->__($qty_title), $qtyX, $this->y, 'UTF-8');
							$page->drawText(Mage::helper('sales')->__($items_title), ($productX+$productXInc+$first_item_title_shift_items), $this->y, 'UTF-8');
							$page->drawText(Mage::helper('sales')->__($sku_title), ($skuX+$first_item_title_shift_sku), $this->y, 'UTF-8');

							if($product_options_yn == 'yescol')
							{
								$page->drawText(Mage::helper('sales')->__($product_options_title), ($optionsX), $this->y, 'UTF-8');
							}
							
							if($shelving_real_yn == 1)
							{
								$page->drawText(Mage::helper('sales')->__($shelving_real_title), ($shelfX), $this->y, 'UTF-8');
							}
							
							if($shelving_yn == 1)
							{
								$page->drawText(Mage::helper('sales')->__($shelving_title), ($shelf2X), $this->y, 'UTF-8');
							}
							
							if($shelving_2_yn == 1)
							{
								$page->drawText(Mage::helper('sales')->__($shelving_2_title), ($shelf3X), $this->y, 'UTF-8');
							}
							
							if($prices_yn != '0') 
							{
								$page->drawText(Mage::helper('sales')->__($price_title), $priceEachX, $this->y, 'UTF-8');
								$page->drawText(Mage::helper('sales')->__($total_title), $priceX, $this->y, 'UTF-8');
							}
							
							if($tax_col_yn == 1 && $tax_yn == 1) 
							{
								$page->drawText(Mage::helper('sales')->__($tax_title), $taxEachX, $this->y, 'UTF-8');
							}
							
							$this->y = ($this->y - 28);
							$items_y_start = $this->y;
							
							// number of lines of products to show
							$cutoff_no = round((($this->y - ($addressFooterXY[1]-30))/15));
							// if no bottom labels, can show more products
							if(($bottom_shipping_address_yn == 0) && ($return_address_yn == 0) ) $cutoff_no = round(($this->y - 30)/15);
				            $counter = 1;   
				            $itemsCollection =  $order->getAllVisibleItems();//$order->getItemsCollection();

				            $total_items     = count($itemsCollection);
				      
							$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
							$total_qty 			= 0;
							$total_price 		= 0;
							$total_price_ex_vat = 0;
							$total_weight		= 0;
							$total_price_taxed	= 0;
							$total_price_ex_vat = 0;
							$price_unit_taxed	= 0;
							$price_unit_tax		= 0;
							$price_discount_unrounded = 0;
							
							$max_name_length 	= 0;
							$test_name	= 'abcdefghij';//10
							$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
							$test_name_length = round($this->parseString($test_name,$font_temp,($font_size_body)));//*0.77)); // bigger = left

							$nextColX = 0;
							if($skuX > $productX) $nextColX = $skuX;
							elseif($qtyX > $skuX) $nextColX = $qtyX;
							elseif(($productX > $skuX) && ($shelving_yn == 0) && ($shelving_2_yn == 0) && ($shelving_real_yn == 0) && ($prices_yn == 0)) $nextColX = $padded_right;
							
							if(($shelving_real_yn == 1) && ($shelfX > $productX) && ($shelfX < $qtyX)) $nextColX = $shelfX;
							
							$pt_per_char = ($test_name_length/10);
							// $pt_per_char_name = ($test_name_length/10);
							$max_name_length = ($nextColX - ($productX+$productXInc) - 27);
							$breakpoint = round($max_name_length/$pt_per_char);
							$custom_options_output = '';		
							$bundle_options_output = array();
							$bundle_options_part = '';
							
							$product_build = array();
							$product_build_item = array();
							
							// 							$product_organise[$sku] = $sku;
							// 							if($product_sort_col == 'shelf'){}
							$coun = 1;
							
							foreach ($itemsCollection as $item)
							{
								$custom_options_output = '';
								
								if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
								{
									// any products actually go thru here?
									$sku = trim($item->getProductOptionByCode('simple_sku'));
									$product_id = Mage::getModel('catalog/product')->getIdBySku($sku);
								} 
								else 
								{
									$sku = trim($item->getSku());
									$product_id = $item->getProductId(); // get it's ID			
								}	
								
								// unique item id
								$product_build_item[] = $sku.'-'.$coun;	
								
								$product_build[$sku.'-'.$coun]['sku'] = $sku;
								
								$product_sku = $sku;
								
								$sku = $sku.'-'.$coun;
								$product_build[$sku]['sku'] = $product_sku;
								
								$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku, array('cost',$shelving_attribute,$shelving_real_attribute,'name','simple_sku','qty'));			
								if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
								
								if(!isset($supplier)) $supplier = '~Not Set~';	
								
								if(!isset($sku_supplier_item_action[$supplier][$sku]))
								{
									if($supplier_options == 'filter') $sku_supplier_item_action[$supplier][$sku] = 'hide';
									elseif($supplier_options == 'grey') $sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
									if($split_supplier_yn == 'no') $sku_supplier_item_action[$supplier][$sku] = 'keep';
								}
								
								

			                    $options = $item->getProductOptions();			
			
				                $size = '';
			                    $color = '';
								if($showColorYN == 'Y' || $showSizeYN == 'Y')
								{
			                   	 $color_attribute_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $color_attribute_name)->getAttributeId();//272
				                    $size_attribute_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $size_attribute_name)->getAttributeId();//525
				                    $size_id  = $options['info_buyRequest']['super_attribute'][$size_attribute_id]; #size
				                    $color_id = $options['info_buyRequest']['super_attribute'][$color_attribute_id]; #color                    
				                    $sizeCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')                
				                    ->setStoreFilter(0)                   
				                    ->setAttributeFilter($size_attribute_id)                    
				                    ->load(); 
				                    foreach($sizeCollection as $_size){                        
				                        if($_size->getOptionId() == $size_id){
				                            $size = $_size->getValue();
				                            break;
				                        }
				                    }                    

				                    $colorCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')                
				                    ->setStoreFilter(0)                   
				                    ->setAttributeFilter($color_attribute_id)                    
				                    ->load();         
				                    foreach($colorCollection as $_color){                        
				                        if($_color->getOptionId() == $color_id){
				                            $color = $_color->getValue();
				                            break;
				                        }
				                    }       
				            	}

								
								if($sku_supplier_item_action[$supplier][$sku] != 'hide')
								{
								
			                   	 	$qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();

									$shelving = '';
									$shelving_real = '';
									
									// $sku_print = $sku;
									$sku_print = $product_build[$sku]['sku'];
									
									$category_label = '';
									$product = Mage::getModel('catalog/product')->load($product_id);
									# Get product's category collection object
									$catCollection = $product->getCategoryCollection();
									# export this collection to array so we could iterate on it's elements
									$categs = $catCollection->exportToArray();
									$categsToLinks = array();
									# Get categories names
									foreach($categs as $cat)
									{
										$categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
									}
									foreach($categsToLinks as $ind=>$cat)
									{
										if($category_label != '') $category_label = $category_label.', '.$cat;
										else $category_label = $cat;
									}
									$product_build[$sku]['%%category%%'] = $category_label;
									unset($category_label);

									$product_build[$sku]['shelving'] = '';
									if($shelving_yn == 1 && $shelving_attribute != '')
									{
										$attributeName = $shelving_attribute;
									
										if($attributeName == '%%category%%')
										{
											$product_build[$sku]['shelving'] = $product_build[$sku]['%%category%%'];//$category_label;
										}
										elseif(Mage::getModel('catalog/product')->load($product_id)->getData($shelving_attribute))
										{
											
											$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($attributeName);

											$product = Mage::getModel('catalog/product');
											$collection = Mage::getResourceModel('eav/entity_attribute_collection')
											->setEntityTypeFilter($product->getResource()->getTypeId())
											->addFieldToFilter('attribute_code', $attributeName);

											$attribute = $collection->getFirstItem()->setEntity($product->getResource());

											$attributeOptions = $attribute->getSource()->getAllOptions(false);
											if(!empty($attributeOptions))
											{						
												$result = search($attributeOptions,'value',$attributeValue);

												if(isset($result[0]['label'])) 
												{
													$product_build[$sku]['shelving'] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $result[0]['label']);
												}
											}
											else 
											{
												$product_build[$sku]['shelving'] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $attributeValue);
											}
										}
									}
									
									$product_build[$sku]['shelving2'] = '';
									if($shelving_2_yn == 1 && $shelving_2_attribute != '')
									{
										
										$attributeName = $shelving_2_attribute;

										if($attributeName == '%%category%%')
										{
											$product_build[$sku]['shelving2'] = $product_build[$sku]['%%category%%'];//$category_label;											
										}
										elseif(Mage::getModel('catalog/product')->load($product_id)->getData($attributeName))
										{
											
											$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($attributeName);

											$product = Mage::getModel('catalog/product');
											$collection = Mage::getResourceModel('eav/entity_attribute_collection')
											->setEntityTypeFilter($product->getResource()->getTypeId())
											->addFieldToFilter('attribute_code', $attributeName);

											$attribute = $collection->getFirstItem()->setEntity($product->getResource());

											$attributeOptions = $attribute->getSource()->getAllOptions(false);
											if(!empty($attributeOptions))
											{						
												$result = search($attributeOptions,'value',$attributeValue);

												if(isset($result[0]['label'])) 
												{
													$product_build[$sku]['shelving2'] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $result[0]['label']);
												}
											}
											else 
											{
												$product_build[$sku]['shelving2'] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $attributeValue);
											}
										}
									}
									
									if($sort_packing != 'none' && $sort_packing != '')
									{
										$product_build[$sku][$sort_packing] = '';
									
											$attributeName = $sort_packing;


											if($attributeName == 'Mcategory')
											{
												// $category_label = '';
												// 										 	
												// 												$product = Mage::getModel('catalog/product')->load($product_id);
												// 												
												// 												# Get product's category collection object
												// 												$catCollection = $product->getCategoryCollection();
												// 												# export this collection to array so we could iterate on it's elements
												// 												$categs = $catCollection->exportToArray();
												// 												$categsToLinks = array();
												// 												# Get categories names
												// 												foreach($categs as $cat)
												// 												{
												// 													$categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
												// 												}
												// 												foreach($categsToLinks as $ind=>$cat)
												// 												{
												// 													$category_label = $category_label.$cat;
												// 												}
												
												$product_build[$sku][$sort_packing] = $product_build[$sku]['%%category%%'];//$category_label;
												
											}
											else
											{
											
												$product = Mage::getModel('catalog/product');
											
												if(Mage::getModel('catalog/product')->load($product_id)->getData($attributeName))
												{
											
													$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($attributeName);

													$collection = Mage::getResourceModel('eav/entity_attribute_collection')
													->setEntityTypeFilter($product->getResource()->getTypeId())
													->addFieldToFilter('attribute_code', $attributeName);

													$attribute = $collection->getFirstItem()->setEntity($product->getResource());

													$attributeOptions = $attribute->getSource()->getAllOptions(false);
													if(!empty($attributeOptions))
													{						
														$result = search($attributeOptions,'value',$attributeValue);

														if(isset($result[0]['label'])) 
														{
															$product_build[$sku][$sort_packing] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $result[0]['label']);
														}
													}
													else 
													{
														$product_build[$sku][$sort_packing] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $attributeValue);
													}
												}
											}
											unset($attributeName);
											unset($attribute);
											unset($attributeOptions);
											unset($result);
											
									}
									
									
									$product_build[$sku]['shelving_real'] = '';
									if($shelving_real_yn == 1 && $shelving_real_attribute != '')
									{
										
										$attributeName = $shelving_real_attribute;
									
										if($attributeName == '%%category%%')
										{
											$product_build[$sku]['shelving_real'] = $product_build[$sku]['%%category%%'];//$category_label;
										}
										elseif(Mage::getModel('catalog/product')->load($product_id)->getData($attributeName))
										{
											
											$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($attributeName);

											$product = Mage::getModel('catalog/product');
											$collection = Mage::getResourceModel('eav/entity_attribute_collection')
											->setEntityTypeFilter($product->getResource()->getTypeId())
											->addFieldToFilter('attribute_code', $attributeName);

											$attribute = $collection->getFirstItem()->setEntity($product->getResource());

											$attributeOptions = $attribute->getSource()->getAllOptions(false);
											if(!empty($attributeOptions))
											{						
												$result = search($attributeOptions,'value',$attributeValue);

												if(isset($result[0]['label'])) 
												{
													$product_build[$sku]['shelving_real'] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $result[0]['label']);
												}
											}
											else 
											{
												$product_build[$sku]['shelving_real'] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $attributeValue);
											}
										}
										// if(Mage::getModel('catalog/product')->load($product_id))
										// 									{
										// 										$_newProduct = Mage::getModel('catalog/product')->load($product_id);  
										// 										if($_newProduct->getData($shelving_real_attribute)) $shelving_real = $_newProduct->getData(''.$shelving_real_attribute.'');
										// 									}
										// 									elseif($product->getData($shelving_real_attribute))
										// 									{
										// 										 $shelving = $product->getData($shelving_real_attribute);
										// 									}
										// 									if(Mage::getModel('catalog/product')->load($product_id)->getAttributeText($shelving_real_attribute))
										// 									{
										// 										$shelving_real = Mage::getModel('catalog/product')->load($product_id)->getAttributeText($shelving_real_attribute);
										// 									}
										// 									elseif($product[$shelving_real_attribute]) $shelving_real = $product[$shelving_real_attribute];
										// 									if(is_array($shelving_real)) $shelving_real = implode(',',$shelving_real);
										// 									$shelving_real = trim($shelving_real);
										// 									if($shelving_real != '') $product_build[$sku]['shelving_real'] = $shelving_real;
									}
									
									if(isset($options['options']) && $product_options_yn != 'no')
									{
										if(is_array($options['options']))
										{
											$i = 0;
							// print_r($options['options']);exit;
											if(isset($options['options'][$i])) $continue = 1;
											else $continue = 0;
											
											while($continue == 1)
											{
												if(trim($options['options'][$i]['label'].$options['options'][$i]['value']) != '')
												{
													if($i > 0) $custom_options_output .= ' ';
													
													if($product_options_yn == 'yescol')
													{
														$custom_options_output .= htmlspecialchars_decode($options['options'][$i]['value']);
													}
													else 
													{
														$custom_options_output .= htmlspecialchars_decode('[ '.$options['options'][$i]['label'].' : '.$options['options'][$i]['value'].' ]');
													}
												}
												$i ++;
												if(isset($options['options'][$i])) $continue = 1;
												else $continue = 0;
											}
										}
									}
									
									if(isset($options['bundle_options']))
									{
										if(is_array($options['bundle_options']))
										{
											$bundle_options_sku = 'SKU : '.$sku_print;
											$sku_print = '(Bundle)';
											$bundle_sku_test = $sku;
										}
									}
								
									$name = '';
									if(Mage::getModel('catalog/product')->load($product_id) && $configurable_names == 'simple')
									{
										$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
										// $_newProduct = Mage::getModel('catalog/product')->load($product_id);  
										if($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
									}
									else $name = trim($item->getName());
	        											
                                    if($from_shipment){
                                        $qty_string = 's:'.(int)$item->getQtyShipped() . ' / o:'.$qty;
										$price_qty = (int)$item->getQtyShipped();
                                        $productXInc = 25;
                                    }else{
                                        $qty_string = $qty;
										$price_qty = $qty;
                                        $productXInc = 0;
                                    }                                    
                                    
									$display_name = '';
									$name_length = 0;
	
									$test_name	= $name;//'abcdefghij';//10
									switch ($font_family_body) {
										case 'helvetica':
											$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
										break;
											
										case 'times':
											$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
										break;
												
										case 'courier':
											$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
										break;
										
										default:
											$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
										break;
									}
									// $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
									// $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
									$test_name_length = round($this->parseString($test_name,$font_temp,($font_size_body)));//*0.77)); // bigger = left									
									$pt_per_char_name = ($test_name_length/strlen($test_name));
									// $max_name_length = ($nextColX - ($productX+$productXInc) - 27);
									$breakpoint_name = round($max_name_length/$pt_per_char_name);
									// echo 'breakpoint_name = round($max_name_length/$pt_per_char_name);:'.$breakpoint_name.' = round('.$max_name_length.'/'.$pt_per_char_name.');';
									// 																							exit;
								
								
									if(strlen($name) > $breakpoint_name)
									{
										$display_name = substr($name, 0, $breakpoint_name).'…';
									}
									else $display_name = $name;
									
									$product_build[$sku]['display_name'] = $display_name;
									$product_build[$sku]['qty_string'] = $qty_string;
									$product_build[$sku]['sku_print'] = $sku_print;
						// print_r($product_build[$sku]);exit;			
									$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
									
									
									
									if($showColorYN == 'Y')
									{
										$page->drawText($color, ($colorX), $this->y , 'UTF-8');
									}
									if($showSizeYN == 'Y')
									{
				                    	$page->drawText($size, ($sizeX), $this->y , 'UTF-8');                    
									}
									if($prices_yn != '0')
									{
										$item_discount = 0;
										if($discount_line_or_subtotal == 'line')
										{
											$item_discount = $item->getDiscountAmount();
										}
										
										// $price_unit_unrounded = $item->getPriceInclTax();
										
										//	qty		unit-price		unit-tax		combined-subtotal
										//	1		10				5				10		<< this would have tax added in combined subtotal
										//	1		10				5				15
											
										// price_unit = price for one item
										$price_unit_unrounded 		= ($item->getPrice()-$item_discount);
										
										if ($item->getPriceInclTax()) 
										{
											$price_unit_unrounded_taxed = ($item->getPriceInclTax()-$item_discount);
										}
										else
										{
											// v.1.3.2.4
											$qty = ($item->getQty() ? $item->getQty() : ($item->getQtyOrdered() ? $item->getQtyOrdered() : 1));
											$price = (floatval($qty)) ? ($item->getRowTotal() + $item->getTaxAmount())/$qty : 0;
											$price_unit_unrounded_taxed = ($price-$item_discount);//Mage::app()->getStore()->roundPrice($price);
										}
										
										//echo '$price_unit_unrounded_taxed:'.$price_unit_unrounded_taxed.' $item->getPriceInclTax():'.$item->getPriceInclTax().' $item->getPrice():'.$item->getPrice();exit;
										
										$price_discount_unrounded	= ($price_discount_unrounded + $item->getDiscountAmount());
										// echo '$order->getDiscountInvoiced():'.$order->getDiscountInvoiced();
										// 									echo '<br />$order->getBaseDiscountInvoiced():'.$order->getBaseDiscountInvoiced();
										// 									echo '<br />$item->getDiscountAmount():'.$item->getDiscountAmount();
										// 									echo '<br />$item->getBaseDiscountAmount():'.$item->getBaseDiscountAmount();
										// 									exit;
								
									
										
										$price_unrounded 			= ($price_qty * ($price_unit_unrounded));
										$price_unrounded_taxed 		= ($price_qty * ($price_unit_unrounded_taxed));
										
										$price_unit 				= number_format($price_unit_unrounded, 2, '.', ',');
										$price_unit_plus_tax 		= number_format($price_unit_unrounded_taxed, 2, '.', ',');
										$price_plus_tax 			= number_format($price_unrounded_taxed, 2, '.', ',');
										$price 						= number_format($price_unrounded, 2, '.', ',');

										
										// $price_unit_taxed 			= number_format(($price_unrounded_taxed - $price_unit_unrounded), 2, '.', ',');
										
										$price_ex_vat_unrounded 	= ($price_qty * $item->getPrice());
										$price_ex_vat 				= number_format($price_ex_vat_unrounded, 2, '.', ',');
										
										
										$tax_percent_temp 			= trim($item->getTaxPercent()); 
										if($tax_percent_temp > 0)
										{
											$tax_percent_temp 			= number_format($tax_percent_temp,2,'.',''); 
										}
										elseif($tax_percent_temp != 0) $tax_percent_temp = 'Other';
										
										if($tax_percent_temp != 0)
										{
											$price_unit_tax 			= (($price_unit_unrounded_taxed - $item->getPrice()) * $price_qty);
											$price_unit_taxed 			= number_format(($price_unit_unrounded_taxed-$price_unit_unrounded), 2, '.', ',');
									
										}
										// elseif($tax_percent_temp == 0)
										// 									{
										// 										$price_unit_tax = 0;
										// 										$price_unit_taxed = 
										// 									}
										
										if(!isset($tax_percents[$tax_percent_temp])) $tax_percents[$tax_percent_temp] = $price_unit_tax;
										else $tax_percents[$tax_percent_temp] = ($price_unit_tax + $tax_percents[$tax_percent_temp]);
										
										$product_build[$sku]['tax_each'] 			= $order->formatPriceTxt($price_unit_taxed);
										$product_build[$sku]['price_each'] 			= $order->formatPriceTxt($price_unit);
										$product_build[$sku]['price_each_plus_tax'] = $order->formatPriceTxt($price_unit_plus_tax); // single item price
										$product_build[$sku]['price_notax_each'] 	= $order->formatPriceTxt($price_unit-$price_unit_taxed);
										$product_build[$sku]['price'] 				= $order->formatPriceTxt($price);
										$product_build[$sku]['price_plus_tax'] 		= $order->formatPriceTxt($price_plus_tax); //total item price
					
										$total_qty 			= ($total_qty + $price_qty);
										$total_price		= ($total_price + $price_unrounded);
										$total_price_taxed	= ($total_price_taxed + $price_unrounded_taxed);
										$total_price_ex_vat = ($total_price_ex_vat + $price_ex_vat_unrounded);
									}
								
									if($custom_options_output != '')
									{
										$custom_options_title = '';
										$product_build[$sku]['custom_options_title_output'] = $custom_options_title.$custom_options_output;
									}
									
																		
									if(isset($options['bundle_options']) && is_array($options['bundle_options']))
									{
										$product_build[$sku]['bundle_options_sku'] = $bundle_options_sku;	
										$product_build[$sku]['bundle_children'] = $item->getChildrenItems();
										$product_build[$sku]['bundle_qty_shipped'] = (int)$item->getQtyShipped();
									}									
										
									if($shipment_details_yn == 1)
									{
										$weight = ($price_qty * $item->getWeight());
										$total_weight = ($total_weight + $weight);
									}
				                    $this->y -= 15;
				                 
				                    $counter++;
								} // end if hide
								
								unset($options);
								// unset($sku);
								$coun ++;
				        	}  // end items loop
			      	
							//sort_packing
							//sortorder_packing
						
							
							// sort items and loop
							if($sort_packing != 'none')
							{
								if($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
								sksort($product_build,$sort_packing,$sortorder_packing_bool);
								sksort($product_build_item,$sort_packing,$sortorder_packing_bool);
	
	// echo 'sort_packing:'.$sort_packing;
	// print_r($product_build);
	// print_r($product_build_item);
	// exit;
								//$product_build_item[] = $sku;
							}
							
							$this->y = $items_y_start;
							foreach($product_build as $key => $product_build_value)
							{
								
								$sku = $product_build_value['sku'];
								// check for item list overflow
								if ($this->y<60)
								{
									if($page_count == 1)
									{
										$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
										$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - 15), 'UTF-8');
									}
									$page = $this->newPage();
									$page_count ++;
									$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
									$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
									$this->y = ($this->y - 20);
									$page->setFillColor($background_color_subtitles_zend);			
									$page->setLineColor($background_color_subtitles_zend);	
									$page->setLineWidth(0.5);				
									$page->drawRectangle(20, ($this->y-($font_size_subtitles/2)), $padded_right, ($this->y+$font_size_subtitles+2));

									$this->_setFont($page, $font_style_subtitles, $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
									$page->drawText(Mage::helper('sales')->__($qty_title), $qtyX, $this->y, 'UTF-8');
									$page->drawText(Mage::helper('sales')->__($items_title), ($productX+$productXInc+$first_item_title_shift), $this->y, 'UTF-8');
									$page->drawText(Mage::helper('sales')->__($sku_title), $skuX, $this->y, 'UTF-8');
									
									if($product_options_yn == 'yescol')
									{
										$page->drawText(Mage::helper('sales')->__($product_options_title), ($optionsX), $this->y, 'UTF-8');
									}
									
									if($shelving_real_yn == 1)
									{
										$page->drawText(Mage::helper('sales')->__('shelf'), $shelfX, $this->y, 'UTF-8');
									}

									if($prices_yn != '0') 
									{
										$page->drawText(Mage::helper('sales')->__($price_title), $priceEachX, $this->y, 'UTF-8');
										$page->drawText(Mage::helper('sales')->__($total_title), $priceX, $this->y, 'UTF-8');
									}


									$this->y = ($this->y - 28);
									$items_y_start = $this->y;
									$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
								}
								// end for item list overflow
								
								// if($sku_supplier_item_action[$supplier][$sku] != 'hide')
								// 								{
								// 									if($sku_supplier_item_action[$supplier][$sku] == 'keepGrey')
								// 									{
								// 										$page->setFillColor($greyout_color);
								// 									}
								// 									else
									if($tickbox == 'pickpack')
									{
										$page->setLineWidth(0.5);
										$page->setFillColor($white_color);			
										$page->setLineColor($black_color);	
										$page->drawRectangle(27, $this->y, 34, ($this->y+7));
										$page->setFillColor($black_color);
									}
								// }
								
								$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
								
								if(isset($product_build_value['shelving']) &&$product_build_value['shelving'] != '') $page->drawText(Mage::helper('sales')->__($product_build_value['shelving']), $shelf2X, $this->y, 'UTF-8');
								if(isset($product_build_value['shelving_real']) && $product_build_value['shelving_real'] != '') $page->drawText(Mage::helper('sales')->__($product_build_value['shelving_real']), $shelfX, $this->y, 'UTF-8');
								if(isset($product_build_value['shelving2']) && $product_build_value['shelving2'] != '') $page->drawText(Mage::helper('sales')->__($product_build_value['shelving2']), $shelf3X, $this->y, 'UTF-8');
								
								$page->drawText($product_build_value['qty_string'], ($qtyX+8), $this->y , 'UTF-8');
								$page->drawText($product_build_value['sku_print'], $skuX, $this->y , 'UTF-8');
								$page->drawText($product_build_value['display_name'], ($productX+$productXInc), $this->y , 'UTF-8');
					
								if($prices_yn != '0')
								{
									// $page->drawText($product_build_value['price'], $priceX, $this->y , 'UTF-8');
									
									
									
									if($tax_col_yn == 1 && $tax_yn == 1) 
									{
										if($tax_line_or_subtotal == 'order')
										{
											$page->drawText($product_build_value['price'], $priceX, $this->y , 'UTF-8');
										}
										elseif($tax_line_or_subtotal == 'line')
										{
											$page->drawText($product_build_value['price_plus_tax'], $priceX, $this->y , 'UTF-8');
										}
										
										// $page->drawText($product_build_value['price_notax_each'], $priceEachX, $this->y , 'UTF-8');
										$page->drawText($product_build_value['price_each'], $priceEachX, $this->y , 'UTF-8');
										$page->drawText($product_build_value['tax_each'], $taxEachX, $this->y , 'UTF-8');
									}
									elseif($tax_yn == 1)
									{
										// tax set to 'yes', not in column
										if($tax_line_or_subtotal == 'order')
										{
											$page->drawText($product_build_value['price_each'], $priceEachX, $this->y , 'UTF-8');
											$page->drawText($product_build_value['price'], $priceX, $this->y , 'UTF-8');
										}
										elseif($tax_line_or_subtotal == 'line')
										{	
											$page->drawText($product_build_value['price_each_plus_tax'], $priceEachX, $this->y , 'UTF-8');
											$page->drawText($product_build_value['price_plus_tax'], $priceX, $this->y , 'UTF-8');
										}
										// $page->drawText($product_build_value['price_each'], $priceEachX, $this->y , 'UTF-8');
										// 										$page->drawText($product_build_value['price'], $priceX, $this->y , 'UTF-8');
									}
									else 
									{
										// tax set to 'no' so we must show full taxed price on lines
										$page->drawText($product_build_value['price_each_plus_tax'], $priceEachX, $this->y , 'UTF-8');
										$page->drawText($product_build_value['price_plus_tax'], $priceX, $this->y , 'UTF-8');
										
										// $page->drawText($product_build_value['price_each'], $priceEachX, $this->y , 'UTF-8');
										// 										$page->drawText($product_build_value['price'], $priceX, $this->y , 'UTF-8');
									}
									
								}
								
								if($product_options_yn == 'yescol' && isset($product_build_value['custom_options_title_output']))
								{
									$page->drawText($product_build_value['custom_options_title_output'], $optionsX, $this->y, 'UTF-8');
								}
								elseif($product_options_yn == 'yes' && isset($product_build_value['custom_options_title_output']) && $product_build_value['custom_options_title_output'] != '')
								{
									$offset = 20;
									$this->y -= $font_size_options;
									$this->_setFont($page, $font_style_body, ($font_size_options), $font_family_body, $non_standard_characters, $font_color_body);
								    $page->drawText($product_build_value['custom_options_title_output'], ($productX+$productXInc+$offset) , $this->y , 'UTF-8');
								}
												
							
								if(isset($product_build_value['bundle_options_sku']))
								{
									$offset = 10;
									$line_height = ($font_size_body);
									$this->y -= $line_height;

									$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
									if($skuX < 800) 
									{
										$display_bundle_sku = $product_build_value['bundle_options_sku'];
										if(strlen($display_bundle_sku) > $breakpoint)
										{
											$display_bundle_sku = substr($display_bundle_sku, 0, $breakpoint).'…';
										}
										
										$page->drawText($display_bundle_sku, ($productX+$productXInc+$offset) , $this->y , 'UTF-8');						                    
										$this->y -= $line_height;
									}
									else $offset = 0;
									
									$page->drawText('Bundle Options : ', ($productX+$productXInc+$offset) , $this->y , 'UTF-8');						                    

									// $children = $item->getChildrenItems();
							        if(isset($product_build_value['bundle_children']) && count($product_build_value['bundle_children']))
							        {
										if($sort_packing != 'none')
										{
											if($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
											sksort($product_build_value['bundle_children'],$sort_packing,$sortorder_packing_bool);
										}
										
							            foreach($product_build_value['bundle_children'] as $child)
							            {
							                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId());
											$sku = $child->getSku();
											$price = $child->getPriceInclTax();
											$qty = (int)$child->getQtyOrdered();
											$name = $child->getName();
											
											$this->y -= $line_height;
											// $offset = 0;
											
											$shelving_real = '';
											if($shelving_real_yn == 1)
											{
												if($product->getData($shelving_real_attribute))
												{
													$shelving_real = $product->getData($shelving_real_attribute);
												}
												elseif(Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId())->getAttributeText($shelving_real_attribute))
												{
													$shelving_real = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId())->getAttributeText($shelving_real_attribute);
												}
												elseif($product[$shelving_real_attribute]) $shelving_real = $product[$shelving_real_attribute];
												if(is_array($shelving_real)) $shelving_real = implode(',',$shelving_real);
												$shelving_real = trim($shelving_real);
												$page->drawText($shelving_real, $shelfX, $this->y , 'UTF-8');
											}
											
											if($from_shipment)
											{
												$qty_string = 's:'.$product_build[$sku]['bundle_qty_shipped'] . ' / o:'.$qty;
												$price_qty = $product_build[$sku]['bundle_qty_shipped'];
												$productXInc = 25;
											}
											else
											{
												$qty_string = $qty;
												$price_qty = $qty;
												$productXInc = 0;
											}
			
											$display_name = '';
											$name_length = 0;
											if(strlen($name) > ($breakpoint+2))
											{
												$display_name = substr(htmlspecialchars_decode($name), 0, ($breakpoint)).'…';
											}
											else $display_name = htmlspecialchars_decode($name);
										
											$page->drawText($sku, $skuX, $this->y , 'UTF-8');
											$page->drawText($qty_string, ($qtyX+8), $this->y , 'UTF-8');
											if($prices_yn != '0')
											{

												$bundle_options_part_price_total = ($price_qty * $price);

												$page->drawText('('.$order->formatPriceTxt($price).')', $priceEachX, $this->y , 'UTF-8');
							                    $page->drawText('('.$order->formatPriceTxt($bundle_options_part_price_total).')', $priceX, $this->y , 'UTF-8');

											}

											$page->drawText($display_name, ($productX+$productXInc+$offset+12) , $this->y , 'UTF-8');								
						                    unset($bundle_options_output);

											if($tickbox == 'pickpack')
											{
												$box_x = ($productX+$productXInc+$offset);
												$page->setLineWidth(0.5);
												$page->setFillColor($white_color);			
												$page->setLineColor($black_color);	
												$page->drawRectangle(28, ($this->y-1), 34, ($this->y+5));												
												$page->setFillColor($black_color);
											}
										}
									}
								}
								
								if($background_color_product != '#FFFFFF')
								{
									$this->y -= ($font_size_body/2);
									$page->setLineWidth(0.5);
									$page->setFillColor($background_color_product);			
									$page->setLineColor($background_color_product);	
									$page->drawLine(20, ($this->y), $padded_right, ($this->y));
																					
									$page->setFillColor($black_color);
									$this->y -= (($font_size_body));
									
								}
								else $this->y -= 15;
							}
							unset($product_build);
							unset($product_build_value);
							unset($sku);
							// end sorted items output loop

							if($prices_yn != '0')
							{
								$shipping_cost 		= 0;
								$tax_amount 		= 0;
								// $shipping_cost 		= $order->getShippingAmount();
	// echo '
	// 		$order->getShippingAmount():'.$order->getShippingAmount().' 
	// 		$order->getShippingInclTax(): '.$order->getShippingInclTax(); exit;
		 
								// work out if shipping is taxed
								$shipping_ex_tax 	= $order->getShippingAmount();
								$shipping_plus_tax	= $order->getShippingInclTax();
								
								$shipping_includes_tax = true;
								if(($shipping_plus_tax - $shipping_ex_tax) > 0 && $tax_yn == 1) 
								{
									// shipping is not incuded in the shipping amount
									$shipping_includes_tax 	= false;
									$shipping_cost 			= $shipping_ex_tax;
									
									
									
									$tax_percent_temp 			= trim( (($shipping_plus_tax - $shipping_ex_tax)/$shipping_ex_tax) * 100 );
									if($tax_percent_temp > 0)
									{
										$tax_percent_temp 			= number_format($tax_percent_temp,2,'.',''); 
									}
									elseif($tax_percent_temp != 0) $tax_percent_temp = 'Other';
									
									if($tax_percent_temp != 0)
									{
										$price_unit_tax 			= ($shipping_plus_tax - $shipping_ex_tax);
										// $price_unit_taxed 			= number_format(($price_unit_unrounded_taxed-$price_unit_unrounded), 2, '.', ',');
								
									}
									// elseif($tax_percent_temp == 0)
									// 									{
									// 										$price_unit_tax = 0;
									// 										$price_unit_taxed = 
									// 									}
									
									if(!isset($tax_percents[$tax_percent_temp])) $tax_percents[$tax_percent_temp] = $price_unit_tax;
									else $tax_percents[$tax_percent_temp] = ($price_unit_tax + $tax_percents[$tax_percent_temp]);
									
									
									
									
								}
								elseif($tax_yn == 1)
								{
									$shipping_cost 			= $shipping_plus_tax;
								}
								else
								{
									$shipping_includes_tax	= true;
									if($shipping_plus_tax) $shipping_cost = $shipping_plus_tax;
									else
									{
										// v.1.3.2.4
										 $shipping_cost = $shipping_ex_tax;
									}
								}
								
								$tax_amount 		= ($total_price_taxed - $total_price_ex_vat);
								$total_price 		= ($total_price + $shipping_cost);
								$total_price_taxed 	= $order->getGrandTotal();//($total_price_taxed + $shipping_cost);
								$total_price_ex_vat = ($total_price_ex_vat + $shipping_cost);
								
					            $this->y -=5;
								$page->setFillColor($background_color_subtitles_zend);			
								$page->setLineColor($background_color_subtitles_zend);	
								$page->setLineWidth(0.5);				
								$page->drawRectangle(20, ($this->y-1), $padded_right, ($this->y+1));
								
								if($background_color_subtitles != '#FFFFFF') $this->y -=20;
								else $this->y -=10;
									
								$priceTextX = 410;
								
								$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
								if($prices_yn == 1 && $tax_yn == 1)
								{
									if($shipping_includes_tax == false)
									{
										// show shipping before tax
										// $page->drawText('Shipping (excl. tax)', $priceTextX, $this->y , 'UTF-8');
										$page->drawText('Shipping', $priceTextX, $this->y , 'UTF-8');
										$page->drawText($order->formatPriceTxt(number_format($shipping_cost, 2, '.', ',')), $priceX, $this->y , 'UTF-8');
										$this->y -=15;
									}
									
									$page->drawText('Subtotal', $priceTextX, $this->y , 'UTF-8');
									
									if($tax_line_or_subtotal == 'line')
									{
										$page->drawText($order->formatPriceTxt(number_format(($total_price_taxed-$shipping_cost), 2, '.', ',')), $priceX, $this->y , 'UTF-8');
									}
									else
									{
										$page->drawText($order->formatPriceTxt(number_format(($total_price_ex_vat-$shipping_cost), 2, '.', ',')), $priceX, $this->y , 'UTF-8');
										
									}
									
									$this->y -=15;
								}
								
							
								if($prices_yn == 1 && $tax_yn == 1 && $tax_line_or_subtotal == 'order')
								{
									foreach($tax_percents as $tax_percent => $tax_percent_amount)
									{
											$tax_percent = trim(str_replace('.00','',$tax_percent));
											if($tax_percent > 0 && $tax_percent != '')
											{
												$page->drawText($tax_label.' @ '.$tax_percent.'%', $priceTextX, $this->y , 'UTF-8');
												$page->drawText($order->formatPriceTxt(number_format($tax_percent_amount, 2, '.', ',')), $priceX, $this->y , 'UTF-8');
												$this->y -=15;
											}
									}
									
									unset($tax_percents);
									unset($tax_percent);
									unset($tax_percent_amount);
								}
							
								if($shipping_includes_tax == true)
								{
									$page->drawText('Shipping', $priceTextX, $this->y , 'UTF-8');
									$page->drawText($order->formatPriceTxt(number_format($shipping_cost, 2, '.', ',')), $priceX, $this->y , 'UTF-8');
									$this->y -=15;
								}
							
								if($prices_yn == 1)
								{
									// echo '$order->getSubtotal():'.$order->getSubtotal().' $order->getGrandTotal():'.$order->getGrandTotal();exit;
									
									if($price_discount_unrounded > 0)
									{
										if($discount_line_or_subtotal == 'subtotal')
										{
											$page->drawText('Discount', $priceTextX, $this->y , 'UTF-8');
											$page->drawText('-'.$order->formatPriceTxt(number_format($price_discount_unrounded, 2, '.', ',')), ($priceX-($font_size_body/3)), $this->y , 'UTF-8');
										}
										
										$total_price_taxed = $order->getGrandTotal();//($total_price_taxed-$price_discount_unrounded);
										$total_price_ex_vat = ($total_price_ex_vat-$price_discount_unrounded);
										$this->y -=15;
									}
									$page->setFillColor($background_color_subtitles_zend);			
									$page->setLineColor($background_color_subtitles_zend);	
									$page->setLineWidth(0.5);				
									$page->drawRectangle(($priceTextX-($font_size_body*2)), ($this->y-1), $padded_right, ($this->y+1));
									if($background_color_subtitles != '#FFFFFF') $this->y -=20;
									else $this->y -=10;

									$this->_setFont($page, 'bold', $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
									
									if($tax_yn == 1)
									{
										// $page->drawText('Total price with '.$tax_label, $priceTextX, $this->y , 'UTF-8');
										$page->drawText('Grand Total', $priceTextX, $this->y , 'UTF-8');
										$page->drawText($order->formatPriceTxt(number_format($total_price_taxed, 2, '.', ',')), $priceX, $this->y , 'UTF-8');
									}
									elseif($tax_yn == 0)
									{
										// $page->drawText('Total price', $priceTextX, $this->y , 'UTF-8');
										$page->drawText('Grand Total', $priceTextX, $this->y , 'UTF-8');
										// $page->drawText($order->formatPriceTxt(number_format($total_price_ex_vat, 2, '.', ',')), $priceX, $this->y , 'UTF-8');
										$page->drawText($order->formatPriceTxt(number_format($total_price_taxed, 2, '.', ',')), $priceX, $this->y , 'UTF-8');
									}
									
									$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
									
								}
								unset($total_price_taxed);
							}
							
							// if($shipment_details_yn == 1 && $shipment_details_weight == 1)
							// 						{
							// 							$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
							// 							if($page_count == 1)
							// 							{				
							// 								$line_height = 0;
							// 								$orderdetailsX = 0;
							// 								$orderdetailsX = 304;
							// 								$page->drawText('Weight :', $orderdetailsX, ($addressXY[1]-$line_height), 'UTF-8');
							// 								$page->drawText(round($total_weight,2).' '.$shipment_details_weight_unit, ($orderdetailsX+80), ($addressXY[1]-$line_height), 'UTF-8');
							// 								$line_height = ($line_height+(1.15*$font_size_body));
							// 							}
							// 							else
							// 							{		
							// 								$this->y -=30;
							// 								$page->drawText('Total weight :', ($padded_right-100), $this->y , 'UTF-8');
							// 								$line_height = 0;
							// 								$orderdetailsX = 0;
							// 								$orderdetailsX = 304;
							// 								$page->drawText($total_weight.' '.$shipment_details_weight_unit, ($padded_right-25), $this->y, 'UTF-8');
							// 							}
							// 
							// 						}	
							
							if($message && $message_yn != 'no')
							{
								$this->y -= 30;

								$page->setFillColor($background_color_message_zend);			
								$page->setLineColor($background_color_message_zend);			
								$page->setLineWidth(0.5);				
					
								$message_array = explode("\n", $message);
								$line_count = count($message_array);
								$page->drawRectangle(20, ($this->y-($line_count*($font_size_message+2))), $padded_right, ($this->y+11));
								$this->_setFont($page, $font_style_message, $font_size_message, $font_family_message, $non_standard_characters, $font_color_message);
								
								$this->y -= (($font_size_subtitles-4)/2);
								foreach ($message_array as $value)
								{
											if($non_standard_characters == 0) 
											{
												$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
											}
											else
											{												
												$font_temp = Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'arial.ttf');  
											}
											$line_width = ceil($this->parseString($value,$font_temp,($font_size_message*0.96)));//*0.77)); // bigger = left
											
											$left_margin = ceil((($padded_right-$line_width)/2));
											if($left_margin < 0) $left_margin = 0;
											
											if($line_width == 0) // some issue with non-standard fonts
											{
												$left_margin = 25;
											}
											// echo 'font_temp:'.$font_temp.' <br />padded_right:'.$padded_right.' left_margin:'.$left_margin.' non_standard_characters:'.$non_standard_characters.' line_width:'.$line_width;exit;
											
											if(isset($value) && isset($left_margin) && ($this->y > 9)) $page->drawText($value,$left_margin, $this->y, 'UTF-8');
											$this->y -= ($font_size_message + 2);
											if($this->y < 10) $this->y = 10;
								}
							}
							
							$order_notes_was_set = false;
						
							/* Add Order Notes*/    
							if($notes_yn != 'no' && $notes_yn != 'yesemail')
							{ 
								/*
									[entity_id] => 17
									                            [parent_id] => 8
									                            [is_customer_notified] => 0
									                            [is_visible_on_front] => 1
									                            [comment] => New note two ('visible on frontend')
									                            [status] => pending
									                            [created_at] => 2011-02-08 01:21:55
									                            [store_id] => 1
								*/
								$notesX = 0;
								
								if($order->getStatusHistoryCollection(true))
								{
							        $notes = $order->getStatusHistoryCollection(true);								
									$note_line = array();
									$note_comment_count = 0;
									
									$i = 0;
									foreach ($notes as $_item)
									{
										$_item['created_at'] = date('m/d/y',strtotime($_item['created_at']));
										if(trim($_item['comment']) != '') $_item['comment'] = $_item['created_at'].' : '.$_item['comment'];
										
										if($notes_yn == 'yesbox')
										{
											$_item['comment'] = wordwrap($_item['comment'], 50, "\n", false);
										}
										elseif($notes_yn == 'yesunder')
										{
											$_item['comment'] = wordwrap($_item['comment'], 114, "\n", false);
										}
										
										$note_line[$i]['date'] = $_item['created_at'];
										$note_line[$i]['comment'] = $_item['comment'];
										if($note_line[$i]['comment'] != '') $note_comment_count = 1;
										$note_line[$i]['is_visible_on_front'] = $_item['is_visible_on_front'];
										$i ++;
									}
									
									if($note_comment_count > 0)
									{
										$this->y -= ($font_size_gift_message + 5);

										if($notes_yn == 'yesbox')
										{
											$this->y = $notesXY[1];
											$notesX = $notesXY[0];
										}
										elseif($notes_yn == 'yesunder')
										{
											$notesX = 25;	
											if(!$message || ($message && ($background_color_message_zend == '#FFFFFF' || $background_color_message_zend == '')))
											{	
												$this->y -= 13;

												$page->setFillColor($background_color_message_zend);			
												$page->setLineColor($background_color_message_zend);			
												$page->setLineWidth(0.5);				
												$page->drawRectangle(20, ($this->y + $font_size_gift_message + 10), $padded_right, ($this->y + $font_size_gift_message + 7));
											}
										}
										
										$this->_setFont($page, 'bold', $font_size_gift_message, $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
										$page->drawText('Order notes', ($notesX), $this->y);
										$this->y -= ($font_size_gift_message + 4);
										$this->_setFont($page, $font_style_gift_message, $font_size_gift_message, $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
									
									
										sksort($note_line, 'date', true);
									
										$i = 0;
										while(isset($note_line[$i]['date']))
										{
											// wordwrap characters
											$token = strtok($note_line[$i]['comment'], "\n");
											while ($token != false) 
											{
												$page->drawText(trim($token), ($notesX), $this->y);
												$this->y-=10;
												$token = strtok("\n");
											}		
											$order_notes_was_set = true;
										
											$i ++;					
									    }
									
									}
								
									unset($note_line);
								}
							}
						
							/* Add QC Message*/    
							if($packed_by_yn == 1)
							{ 
									$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
									$page->drawText(Mage::helper('sales')->__('Packed by: ____'), $packedByXY[0], $packedByXY[1], 'UTF-8');
							}
						
						
							/* Add Gift Message*/    
							if($gift_message_yn != 'no')
							{ 
								$gift_message_item = Mage::getModel('giftmessage/message');
								$gift_message_id = $order->getGiftMessageId();
								if(!is_null($gift_message_id)) 
								{
									$gift_msg_array = array();
									
									$this->y -= 30;

									$notesX = 25;	
									
									$gift_message_item->load((int)$gift_message_id);
									$gift_sender 	= $gift_message_item->getData('sender');
									$gift_recipient = $gift_message_item->getData('recipient');
									$gift_message 	= $gift_message_item->getData('message');

									$to_from = '';
									if(isset($gift_recipient)){
										if($gift_message_yn != 'yesnewpage') $to_from .= 'Message to '.$gift_recipient;
										else $to_from .= 'To '.$gift_recipient;
										if(isset($gift_sender)) $to_from .= ', from '.$gift_sender.' :';
									}
									elseif(isset($gift_sender)) $to_from .= 'From '.$gift_sender.' :';

									if($gift_message_yn == 'yesbox')
									{
										$this->y = $giftMessageXY[1];
										$msgX = $giftMessageXY[0];
										$gift_message = wordwrap($gift_message, 50, "\n", false);
									}
									elseif($gift_message_yn == 'yesunder')
									{
										$msgX = 35;
										$gift_message = wordwrap($gift_message, 115, "\n", false);
									}
									elseif($gift_message_yn == 'yesnewpage')
									{
										$msgX = 35;
										$gift_message = wordwrap($gift_message, 115, "\n", false);
									}

									// wordwrap characters
									$token = strtok($gift_message, "\n");
									// $y = 740; 
									$msg_line_count = 2.5;
									while ($token != false) 
									{
										$gift_msg_array[] = $token;
										$msg_line_count ++;
										$token = strtok("\n");
									}
									
									if($order_notes_was_set)
									{	
										// $this->y -= 13;
																
										$page->setFillColor($white_color);			
										$page->setLineColor($background_color_message_zend);			
										$page->setLineWidth(0.5);				
										// $page->drawRectangle(20, ($this->y + $font_size_gift_message + 10), 570, ($this->y + $font_size_gift_message + 7));
										$page->drawRectangle(20, ($this->y + $font_size_gift_message + 10), $padded_right, ($this->y - ((($font_size_gift_message) * $msg_line_count) + 7)));
									}

								
									
									if($gift_message_yn == 'yesnewpage')
									{
										$page = $this->newPage();
										$page_count ++;
										
										$page->setFillColor($background_color_message_zend);			
										$page->setLineColor($background_color_message_zend);	
										$page->setLineWidth(0.5);				
										$page->drawRectangle(20, ($this->y-($font_size_gift_message/2)), $padded_right, ($this->y+$font_size_gift_message+2));
											
										$this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
										
										$page->drawText('Gift Message for Order #'.$order->getRealOrderId(), ($msgX), $this->y, 'UTF-8');
										
										// $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
										// $page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
										$this->y = ($this->y - 10 - $font_size_gift_message);
									}
									else $this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
									
									$page->drawText(Mage::helper('sales')->__($to_from), ($msgX), $this->y, 'UTF-8');
									// $page->drawText(Mage::helper('sales')->__($gift_sender), ($msgX+80), $this->y, 'UTF-8');
									// $page->drawText(Mage::helper('sales')->__('Message to:'), ($msgX+147), $this->y, 'UTF-8');
									// $page->drawText(Mage::helper('sales')->__($gift_recipient), ($msgX+185), $this->y, 'UTF-8');
									$this->y -=($font_size_gift_message+8);
									$this->_setFont($page, $font_style_gift_message, ($font_size_gift_message-1), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
									
									foreach($gift_msg_array as $gift_msg_line)
									{
										$page->drawText(trim($gift_msg_line), ($msgX), $this->y);
										$this->y-=($font_size_gift_message+3);
									}
									unset($gift_msg_array);
								}
							}
						
						
							$page_count	= 1;
							
			        	}
					}
					
					if((!isset($supplier_ubermaster[($s+1)])) || ($split_supplier_yn == 'no')) 
					{
						$loop_supplier = 0;
					}
					$s ++;
				}
				while($loop_supplier != 0);
				
				$this->_afterGetPdf();
				return $pdf;
    }

	 	/***********************************************
        * Default Template - END
        ************************************************/



 /**
   getPickSeparated 2222222  Template - START
    ************************************************/

public function getPickSeparated2($orders=array(), $from_shipment = false)
{
	
	if(function_exists('sksort'))
	{}
	else
	{
		function sksort(&$array, $subkey, $sort_ascending=false) 
		{

		    if (count($array))
		        $temp_array[key($array)] = array_shift($array);

		    foreach($array as $key => $val){
		        $offset = 0;
		        $found = false;
		        foreach($temp_array as $tmp_key => $tmp_val)
		        {
		            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
		            {
		                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
		                                            array($key => $val),
		                                            array_slice($temp_array,$offset)
		                                          );
		                $found = true;
		            }
		            $offset++;
		        }
		        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
		    }

		    if ($sort_ascending) $array = array_reverse($temp_array);

		    else $array = $temp_array;
		}
	}
	
    #die($from_shipment);
	$debug = 0;
	$this->_beforeGetPdf();
	$this->_initRenderer('invoices');

	$pdf = new Zend_Pdf();
	$this->_setPdf($pdf);
	$style = new Zend_Pdf_Style();
	
	$page_size = $this->_getConfig('page_size', 'a4', false,'general');
	
	if($page_size == 'letter') 
	{
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
		$page_top = 770;$padded_right = 587;
	}
	elseif($page_size == 'a4')
	{
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$page_top = 820;$padded_right = 570;
	}
	elseif($page_size == 'a5landscape') 
	{
		$page = $pdf->newPage('596:421');					
		$page_top = 395;$padded_right = 573;
	}
	
	$pdf->pages[] = $page;
  
	$page_width = $padded_right;
	$skuX = 67;
	$qtyX = 40;
	$productX = 250;
	$fontsize_overall = 15;
	$fontsize_productline = 9;
    $total_quantity = 0;
    $total_cost = 0;
	$red_bkg_color 			= new Zend_Pdf_Color_Html('lightCoral');
	$grey_bkg_color			= new Zend_Pdf_Color_GrayScale(0.7);
	$lt_grey_bkg_color		= new Zend_Pdf_Color_GrayScale(0.9);
	$dk_grey_bkg_color		= new Zend_Pdf_Color_GrayScale(0.3);//darkCyan
	$dk_cyan_bkg_color 		= new Zend_Pdf_Color_Html('darkCyan');//darkOliveGreen
	$dk_og_bkg_color 		= new Zend_Pdf_Color_Html('darkOliveGreen');//darkOliveGreen
	$white_bkg_color 		= new Zend_Pdf_Color_Html('white');
	$orange_bkg_color 		= new Zend_Pdf_Color_Html('Orange');
	$black_color 			= new Zend_Pdf_Color_Rgb(0,0,0);
	$grey_color 			= new Zend_Pdf_Color_GrayScale(0.3);
	$greyout_color 			= new Zend_Pdf_Color_GrayScale(0.6);
	$white_color 			= new Zend_Pdf_Color_GrayScale(1);
	$dk_green_color 		= new Zend_Pdf_Color_Html('darkGreen');//darkOliveGreen
	
	
	$font_family_header_default = 'helvetica';
	$font_size_header_default = 16;
	$font_style_header_default = 'bolditalic';
	$font_color_header_default = 'darkOliveGreen';
	$font_family_subtitles_default = 'helvetica';
	$font_style_subtitles_default = 'bold';
	$font_size_subtitles_default = 15;
	$font_color_subtitles_default = 'darkOliveGreen';//'#222222';
	$background_color_subtitles_default = 'lightGrey';//'#999999';
	$font_family_body_default = 'helvetica';
	$font_size_body_default = 10;
	$font_style_body_default = 'regular';
	$font_color_body_default = 'Black';

	$font_family_header = $this->_getConfig('font_family_header', $font_family_header_default, false,'general');
	$font_style_header = $this->_getConfig('font_style_header', $font_style_header_default, false,'general');
	$font_size_header = $this->_getConfig('font_size_header', $font_size_header_default, false,'general');
	$font_color_header = $this->_getConfig('font_color_header', $font_color_header_default, false,'general');
	$font_family_subtitles = $this->_getConfig('font_family_subtitles', $font_family_subtitles_default, false,'general');
	$font_style_subtitles = $this->_getConfig('font_style_subtitles', $font_style_subtitles_default, false,'general');
	$font_size_subtitles = $this->_getConfig('font_size_subtitles', $font_size_subtitles_default, false,'general');
	$font_color_subtitles = $this->_getConfig('font_color_subtitles', $font_color_subtitles_default, false,'general');
	$background_color_subtitles = $this->_getConfig('background_color_subtitles', $background_color_subtitles_default, false,'general');
	$font_family_body = $this->_getConfig('font_family_body', $font_family_body_default, false,'general');
	$font_style_body = $this->_getConfig('font_style_body', $font_style_body_default, false,'general');
	$font_size_body = $this->_getConfig('font_size_body', $font_size_body_default, false,'general');
	$font_color_body = $this->_getConfig('font_color_body', $font_color_body_default, false,'general');
	
		$background_color_subtitles_zend = new Zend_Pdf_Color_Html(''.$background_color_subtitles.'');	
	$font_color_header_zend = new Zend_Pdf_Color_Html(''.$font_color_header.'');	
	$font_color_subtitles_zend = new Zend_Pdf_Color_Html(''.$font_color_subtitles.'');	
	$font_color_body_zend = new Zend_Pdf_Color_Html(''.$font_color_body.'');	
	
	$non_standard_characters 	= $this->_getConfig('non_standard_characters', 0, false,'general');
 	
	$shelvingpos = $this->_getConfig('shelvingpos', 'col', false,'general'); //col/sku
	// $address_country_yn = $this->_getConfig('address_country_yn', 1, false,'general'); //col/sku
	$order_address_yn = $this->_getConfig('pickpack_order_address_yn', 0, false,'picks'); //col/sku

	$configurable_names = $this->_getConfig('pickpack_configname_separated', 'simple', false,'picks'); //col/sku
	$configurable_names_attribute = trim($this->_getConfig('pickpack_configname_attribute_separated', '', false,'picks')); //col/sku
	if($configurable_names != 'custom') $configurable_names_attribute = '';
	
	$barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
	
	
	$product_id = NULL; // get it's ID
	$stock = NULL;
	$sku_stock = array();
	// pickpack_cost
	$showcost_yn_default		= 0;
	$showcount_yn_default		= 0;
	$currency_default			= 'USD';
	
	$shelving_yn_default		= 0;
	$shelving_attribute_default = 'shelf';
	$shelvingX_default 			= 200;
	$supplier_yn_default 		= 0;
	$supplier_attribute_default = 'supplier';
	$namenudgeYN_default		= 0;
	$stockcheck_yn_default 		= 0;
	$stockcheck_default 		= 1;
	$shipping_method_x			= 0;
	
	/////// from 1	
	$product_id 				= NULL; // get it's ID
	$stock 						= NULL;
	///////// /from 1
	$split_supplier_yn_default 	= 'no';
	$supplier_attribute_default = 'supplier';
	$supplier_options_default 	= 'filter';
	$supplier_login_default		= '';
	$tickbox_default			= 'no'; //no, pick, pickpack
	
	// $split_supplier_yn 			= $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
	$split_supplier_yn_temp 	= $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
	$split_supplier_options 	= $this->_getConfig('pickpack_split_supplier_options', 'no', false,'general');
	$split_supplier_yn = 'no';
	if($split_supplier_yn_temp == 1)
	{
		$split_supplier_yn = $split_supplier_options;
	}

	$supplier_attribute 		= $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false,'general');
	$supplier_options 			= $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false,'general');
	//$supplier_login 			= $this->_getConfig('pickpack_supplier_login', $supplier_login_default, false,'general');

	$userId 					= Mage::getSingleton('admin/session')->getUser()->getId();
    $user 						= Mage::getModel('admin/user')->load($userId);
	$username					= $user['username'];

	$supplier_login_pre 		= $this->_getConfig('pickpack_supplier_login', '', false,'general');
	$supplier_login_pre 		= str_replace(array("\n",','),';',$supplier_login_pre);
	$supplier_login_pre 		= explode(';',$supplier_login_pre);

	foreach($supplier_login_pre as $key => $value)
	{
		$supplier_login_single = explode(':',$value);
		if(preg_match('~'.$username.'~i',$supplier_login_single[0])) 
		{
			if($supplier_login_single[1] != 'all') $supplier_login = trim($supplier_login_single[1]);
			else $supplier_login = '';
		}
	}
	
	$tickbox 					= $this->_getConfig('pickpack_tickbox', $tickbox_default, false,'general');
	$picklogo 					= $this->_getConfig('pickpack_picklogo', 0, false,'general');
	
	$showcount_yn 		= $this->_getConfig('pickpack_count', $showcount_yn_default, false,'picks');

	$showcost_yn 		= $this->_getConfig('pickpack_cost', $showcost_yn_default, false,'picks');
	$currency	 		= $this->_getConfig('pickpack_currency', $currency_default, false,'picks');
	$currency_symbol 	= Mage::app()->getLocale()->currency($currency)->getSymbol();
	
	$stockcheck_yn 		= $this->_getConfig('pickpack_stock_yn_separated', $stockcheck_yn_default,false,'picks');
	$stockcheck 		= $this->_getConfig('pickpack_stock_separated', $stockcheck_default,false,'picks');	

	$shelving_yn 		= $this->_getConfig('pickpack_shelving_yn_separated', $shelving_yn_default, false,'picks');
	$shelving_attribute = $this->_getConfig('pickpack_shelving_separated', $shelving_attribute_default, false,'picks');
	$shelvingX 			= intval($this->_getConfig('shelving_nudge', $shelvingX_default, true,'general'));

	$nameyn 			= $this->_getConfig('pickpack_name_yn_separated', $namenudgeYN_default, false,'picks');
	$namenudge 			= intval($this->_getConfig('pickpack_namenudge_separated', 0, false,'picks'));
	
	
	$override_address_format_yn = 1;//$this->_getConfig('override_address_format_yn', 0,false,'general');
	// $address_country_yn 	= $this->_getConfig('address_country_yn', 1, false,'general'); //col/sku

	$address_format_default = '{if company}{company},|{/if company}
{if name}{name},|{/if name}
{if street}{street},|{/if street}
{if city}{city}, |{/if city}
{if postcode}{postcode}{/if postcode} {if region}{region},{/if region}|
{country}';

	$address_format 		= $this->_getConfig('address_format', $address_format_default, false,'general'); //col/sku
	$address_countryskip  	= $this->_getConfig('address_countryskip', 0,false,'general');              
	
	$shmethod = Mage::getStoreConfig('pickpack_options/picks/pickpack_shipmethod');
	
	$options_yn_base 		= $this->_getConfig('separated_options_yn', 0, false,'picks'); // no, inline, newline
	$options_yn 			= $this->_getConfig('separated_options_yn', 0, false,'picks'); // no, inline, newline
	if($options_yn_base == 0) $options_yn = 0;
	$pickpack_options_filter_yn = $this->_getConfig('separated_options_filter_yn', 0, false,'picks');
	$pickpack_options_filter 	= $this->_getConfig('separated_options_filter', 0, false,'picks');
	$pickpack_options_filter_array = array();
	if($pickpack_options_filter_yn == 0) $pickpack_options_filter = '';
	elseif(trim($pickpack_options_filter) != '')
	{
		$pickpack_options_filter_array = explode(',',$pickpack_options_filter);
		foreach($pickpack_options_filter_array as $key => $value)
		{
			$pickpack_options_filter_array[$key] = trim($value);
		}
	}
	$pickpack_options_count_filter_array = array();
	$pickpack_options_count_filter 	= $this->_getConfig('separated_options_count_filter', 0, false,'picks');
	if($pickpack_options_filter_yn == 0) $pickpack_options_count_filter = '';
	elseif(trim($pickpack_options_count_filter) != '')
	{
		$pickpack_options_count_filter_array = explode(',',$pickpack_options_count_filter);
		foreach($pickpack_options_count_filter_array as $key => $value)
		{
			$pickpack_options_count_filter_array[$key] = trim($value);
		}
	}
	
	$sort_packing_yn			= $this->_getConfig('sort_packing_yn', 1,false,'general');               
	$sort_packing			    = $this->_getConfig('sort_packing', 'sku',false,'general');               
	$sortorder_packing			= $this->_getConfig('sort_packing_order', 'ascending',false,'general');  
	$sort_packing_attribute		= trim($this->_getConfig('sort_packing_attribute', '',false,'general'));  
	             
	if($sort_packing_yn == 0) $sortorder_packing = 'none';
	elseif($sort_packing == 'Mcategory') $sort_packing = 'Mcategory';
	elseif($sort_packing_attribute != '') $sort_packing = $sort_packing_attribute;
	
	$skuXInc = 0;
	
	/**
	 * get store id
	 */
	$storeId = Mage::app()->getStore()->getId();

	$order_id_master = array();
	$sku_order_suppliers = array();
	$sku_shelving = array();
	$sku_shipping_address = array();
	$sku_order_id_options = array();
	$sku_bundle = array();
	$product_build_item = array();
	$product_build = array();
	
	foreach ($orders as $orderSingle) 
	{
		$order = Mage::getModel('sales/order')->load($orderSingle);		
		$putOrderId = $order->getRealOrderId();
		$order_id  = $putOrderId;

		$sku_shipping_address_temp = '';
		$sku_shipping_address[$order_id] = '';
		// $sku_shipping_address[$order_id] = str_replace(array('<br />','<br/>',"\n","\p"),'',implode(',',$this->_formatAddress($order->getShippingAddress()->format('pdf')))); 
		$shippingAddressFlat = implode(',',$this->_formatAddress($order->getShippingAddress()->format('pdf'))); 
		$shippingAddressArray = explode(',',str_replace(',','~,',$shippingAddressFlat));
	
		// if($override_address_format_yn == 1)
		// 		{
			//
			$shippingAddressArray = array();
			$addy = array();
			$if_contents = array();
			$addy['company'] = $order->getShippingAddress()->getCompany();
			$addy['name'] = $order->getShippingAddress()->getName();
			$addy['first_name'] = $order->getShippingAddress()->getFirstName();
			$addy['last_name'] = $order->getShippingAddress()->getLastName();
			$addy['telephone'] = $order->getShippingAddress()->getTelephone();
			// $addy['email'] = $order->getBillingAddress()->getEmail();
			$i = 0;
			while($i < 10)
			{
				if($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i)))
				{
					if(isset($addy['street'])) $addy['street'] .= ", \n";
					else $addy['street'] = '';
					$addy['street'] .= $order->getShippingAddress()->getStreet($i);
				}
				$i ++;
			}
			$addy['city'] = $order->getShippingAddress()->getCity();
			$addy['postcode'] = $order->getShippingAddress()->getPostcode();
			$addy['region'] = $order->getShippingAddress()->getRegion();
			$addy['prefix'] = $order->getShippingAddress()->getPrefix();
			$addy['suffix'] = $order->getShippingAddress()->getSuffix();
			$addy['country'] = Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId());					
			$address_format_set = str_replace(array("\n",'<br />','<br/>',"\r"),'',$address_format);

			if(trim($address_countryskip) != '') $addy['country'] = str_ireplace($address_countryskip,'',$addy['country']);

			foreach($addy as $key =>$value)
			{
				$value = trim($value);
				$value = preg_replace('~,$~','',$value);
				$value = str_replace(',,',',',$value);
				if($value != '' && !is_array($value))
				{
					$pre_value = '';
					// if($key == 'country') $pre_value = '##country##';
					// 					elseif($key == 'telephone') $pre_value = '##telephone##';
					
					preg_match('~\{if '.$key.'\}(.*)\{\/if '.$key.'\}~ims',$address_format_set,$if_contents);
					if(isset($if_contents[1])) $if_contents[1] = str_replace('{'.$key.'}',$value,$if_contents[1]);
					else $if_contents[1] = '';
					$address_format_set = preg_replace('~\{if '.$key.'\}(.*)\{/if '.$key.'\}~ims',$pre_value.$if_contents[1],$address_format_set);
					$address_format_set = str_ireplace('{'.$key.'}',$pre_value.$value,$address_format_set);
					$address_format_set = str_ireplace('{/'.$key.'}','',$address_format_set);
					$address_format_set = str_ireplace('{/if '.$key.'}','',$address_format_set);
				}
				else
				{
					$address_format_set = preg_replace('~\{if '.$key.'\}(.*)\{/if '.$key.'\}~i','',$address_format_set);	
					$address_format_set = str_ireplace('{'.$key.'}','',$address_format_set);
					$address_format_set = str_ireplace('{/'.$key.'}','',$address_format_set);
					$address_format_set = str_ireplace('{/if '.$key.'}','',$address_format_set);
				}	
			}
						
			// $address_format_set = str_replace(array('##telephone##|','##country##|'),array('|##telephone##','|##country##'),$address_format_set);
			// 			$address_format_set = str_replace(array('##telephone##','##country##'),'',$address_format_set);
			$address_format_set = str_replace(array('||','|'),"\n",trim($address_format_set));
			
			$shippingAddressArray = explode("\n",$address_format_set);
		// }
		
		
		$i = 0;
		$stop_address = FALSE;
		$skip_entry = FALSE;
		
		foreach($shippingAddressArray as $i =>$value)
		{
			$value = trim($value);
			
			$skip_entry = FALSE;
			if(isset($value) && $value != '~')
			{
				// remove fax
				$value = preg_replace('!<(.*)$!','',$value);
				if(preg_match('~T:~',$value))
				{	
					// if($show_phone_yn == 1)
					// 				{
						$value = str_replace('~','',$value);							
						$value = '[ '.$value.' ]';
				}
				elseif($stop_address === FALSE)
				{
					if(!isset($shippingAddressArray[($i+1)]) || preg_match('~T:~',$shippingAddressArray[($i+1)]))
					{
						// last line, lets bold it and make it a bit bigger
						$value = str_replace('~','',$value);
					}
					else
					{
						if((!isset($shippingAddressArray[($i+2)]) || preg_match('~T:~',$shippingAddressArray[($i+2)])))
						{
							$value = str_replace('~','',$value);
						}
						else $value = str_replace('~',',',$value);
					}
					$page->setFillColor($black_color);
				}
				if($stop_address === FALSE && $skip_entry === FALSE) 	$sku_shipping_address_temp .= ','.$value;
			}
			$i ++;		
		}    
		$sku_shipping_address_temp = str_replace(
			array('  ',',,','<br />','<br/>',"\n","\p",',,',',,',','),
			array(' ',',','','','','',',',',',', '),$sku_shipping_address_temp);
		$sku_shipping_address_temp = preg_replace('~, $~','',$sku_shipping_address_temp); 
		$sku_shipping_address[$order_id] = preg_replace('~^\s?,\s?~','',$sku_shipping_address_temp);  
//


		if(!isset($order_id_master[$order_id])) $order_id_master[$order_id] = 0;		
		
		$itemsCollection =  $order->getAllVisibleItems();//$order->getItemsCollection();
        // $total_items     = count($itemsCollection);
	
		$max_name_length 	= 0;
		$test_name	= 'abcdefghij';//10
		$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$test_name_length = round($this->parseString($test_name,$font_temp,($font_size_body)));//*0.77)); // bigger = left

		$pt_per_char = ($test_name_length/10);
		$max_name_length = (550 - $skuX);
		if(!isset($productXInc)) $productXInc = 0;
		$max_sku_length = (($productX+$productXInc) - 27 - $skuX);
		$breakpoint_name = round($max_name_length/$pt_per_char);
		$breakpoint_sku = round($max_sku_length/$pt_per_char);
		// $product_build_item = array();
		// 	$product_build = array();
		$sku_category = array();
		
		$coun = 1;

		foreach ($itemsCollection as $item)
		{			
			if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
			{
				// any products actually go thru here?
				$sku = $item->getProductOptionByCode('simple_sku');
				$product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
			} 
			else 
			{
				$sku = $item->getSku();
				$product_id = $item->getProductId(); // get it's ID			
			}	
			
			
		
			#nu
			// if show options as counted groups
			if($options_yn == 1)
			{
				$full_sku = trim($item->getSku());	
				$parent_sku = preg_replace('~\-(.*)$~','',$full_sku);
				$sku = $parent_sku;
				// $product_build[$sku]['sku'] = $sku;
				// 				$product_build[$sku.'-'.$coun]['sku'] = $sku;	
				$product_build_item[] = $sku;//.'-'.$coun;	
				// $product_build[$sku]['sku'] = $sku;			
				$product_sku = $sku;
				// $sku = $sku.'-'.$coun;
				// $product_build[$sku]['sku'] = $product_sku;	
				$product_build[$order_id][$sku]['sku'] = $product_sku;
					
			}
			else
			{
				// unique item id
				$product_build_item[] = $sku.'-'.$coun;	
				// $product_build[$sku.'-'.$coun]['sku'] = $sku;			
				$product_sku = $sku;
				$sku = $sku.'-'.$coun;
				// $product_build[$sku]['sku'] = $product_sku;
				$product_build[$order_id][$sku]['sku'] = $product_sku;
			}
			
			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku', $sku, array('cost',$shelving_attribute,'name','simple_sku','qty'));			
			if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
			
		    $options = $item->getProductOptions();
        


			// $sku_bundle = array();
			if(isset($options['bundle_options']) && is_array($options['bundle_options']))
			{
			/*
				$offset = 10;
				$this->y -= 10;

				$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
				$page->drawText($bundle_options_sku, ($productX+$productXInc+$offset) , $this->y , 'UTF-8');						                    
				$this->y -= 10;
				$page->drawText('Bundle Options : ', ($productX+$productXInc+$offset) , $this->y , 'UTF-8');						                    
			*/

				$children = $item->getChildrenItems();
		        if(count($children))
		        {
		            foreach($children as $child)
		            {
		                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId());
						$sku_b = $child->getSku();
						$price_b = $child->getPriceInclTax();
						$qty_b = (int)$child->getQtyOrdered();
						$name_b = $child->getName();

						$this->y -= 10;
						$offset = 20;

						$shelving_real = '';
						$shelving_real_b = '';
						if(isset($shelving_real_yn) && $shelving_real_yn == 1)
						{
							if($product->getData($shelving_real_attribute))
							{
								$shelving_real_b = $product->getData($shelving_real_attribute);
							}
							elseif(Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId())->getAttributeText($shelving_real_attribute))
							{
								$shelving_real_b = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId())->getAttributeText($shelving_real_attribute);
							}
							elseif($product[$shelving_real_attribute]) $shelving_real_b = $product[$shelving_real_attribute];
							else $shelving_real_b = '';
							
							if(is_array($shelving_real)) $shelving_real_b = implode(',',$shelving_real_b);
							if(isset($shelving_real_b)) $shelving_real_b = trim($shelving_real_b);
							// $page->drawText($shelving_real, $shelfX, $this->y , 'UTF-8');
						}

						 if($from_shipment){
                                $qty_string_b = 's:'.(int)$item->getQtyShipped() . ' / o:'.$qty;
								$price_qty_b = (int)$item->getQtyShipped();
                                $productXInc = 25;
                            }else{

                                $qty_string_b = $qty_b;
								$price_qty_b = $qty_b;
                                $productXInc = 0;
                            }
// echo 'name_b:'.$name_b.' $breakpoint:'.$breakpoint.' strlen($name_b):'.strlen($name_b);exit;
						$display_name_b = '';
						if(strlen($name_b) > ($breakpoint_name+2))
						{
							$display_name_b = substr(htmlspecialchars_decode($name_b), 0, ($breakpoint_name)).'…';
						}
						else $display_name_b = htmlspecialchars_decode($name_b);

						$sku_bundle[$order_id][$sku][] = $sku_b.'##'.$display_name_b.'##'.$shelving_real_b.'##'.$qty_string_b;
					}
				}
			}
			else 
			{
				$sku_bundle[$order_id][$sku] = '';
			}
			
		
			
			$category_label = '';
			$product = Mage::getModel('catalog/product')->load($product_id);
			# Get product's category collection object
			$catCollection = $product->getCategoryCollection();
			# export this collection to array so we could iterate on it's elements
			$categs = $catCollection->exportToArray();
			$categsToLinks = array();
			# Get categories names
			foreach($categs as $cat)
			{
				$categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
			}
			foreach($categsToLinks as $ind=>$cat)
			{
				if($category_label != '') $category_label = $category_label.', '.$cat;
				else $category_label = $cat;
			}
			$sku_category[$sku] = $category_label;
			unset($category_label);


			$shelving = '';
			$supplier = '';
			
			if($shelving_yn == 1)
			{
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
						$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($shelving_attribute)) $shelving = $_newProduct->getData(''.$shelving_attribute.'');
				}
				elseif($product->getData(''.$shelving_attribute.''))
				{
						 $shelving = $product->getData($shelving_attribute);
				}
				else $shelving = '';
				
				if(trim($shelving) != '')
				{
					if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($shelving_attribute))
					{
						$shelving = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($shelving_attribute);
					}
					elseif($product[$shelving_attribute]) $shelving = $product[$shelving_attribute];
					else $shelving = '';
				}
								
				if(trim($shelving) != '')
				{
					
					if(is_array($shelving)) $shelving = implode(',',$shelving);
				
					if(isset($sku_shelving[$sku]) && trim(strtoupper($sku_shelving[$sku])) != trim(strtoupper($shelving))) $sku_shelving[$sku] .= ','.trim($shelving);
					else $sku_shelving[$sku] = trim($shelving);
					$sku_shelving[$sku] = preg_replace('~,$~','',$sku_shelving[$sku]);
				}
				else $sku_shelving[$sku] = '';
				
			}
			
			if($sort_packing != 'none' && $sort_packing != '')
			{
				$product_build[$order_id][$sku][$sort_packing] = '';
			
				$attributeName = $sort_packing;

				if($attributeName == 'Mcategory')
				{						
					$product_build[$order_id][$sku][$sort_packing] = $sku_category[$sku];//$product_build[$sku]['%%category%%'];//$category_label;							
				}
				else
				{
				
					$product = Mage::getModel('catalog/product');
				
					if(Mage::getModel('catalog/product')->load($product_id)->getData($attributeName))
					{
						$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($attributeName);

						$collection = Mage::getResourceModel('eav/entity_attribute_collection')
						->setEntityTypeFilter($product->getResource()->getTypeId())
						->addFieldToFilter('attribute_code', $attributeName);

						$attribute = $collection->getFirstItem()->setEntity($product->getResource());

						$attributeOptions = $attribute->getSource()->getAllOptions(false);
						if(!empty($attributeOptions))
						{						
							$result = search($attributeOptions,'value',$attributeValue);

							if(isset($result[0]['label'])) 
							{
								$product_build[$order_id][$sku][$sort_packing] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $result[0]['label']);
							}
						}
						else 
						{
							$product_build[$order_id][$sku][$sort_packing] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $attributeValue);
						}
					}
				}
				unset($attributeName);
				unset($attribute);
				unset($attributeOptions);
				unset($result);
					
			}
			
			
// print_r($product_build);exit;

			if($split_supplier_yn != 'no')
			{
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
						$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($supplier_attribute)) $supplier = $_newProduct->getData(''.$supplier_attribute.'');
				}
				elseif($product->getData(''.$supplier_attribute.''))
				{
						 $supplier = $product->getData($supplier_attribute);
				}
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($supplier_attribute))
				{
					$supplier = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($supplier_attribute);
				}
				elseif($product[$supplier_attribute]) $supplier = $product[$supplier_attribute];
				
				if(is_array($supplier)) $supplier = implode(',',$supplier);
				if(!$supplier) $supplier = '~Not Set~';	
				
				$supplier = trim(strtoupper($supplier));
				
				if(isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier) $sku_supplier[$sku] .= ','.$supplier;
				else $sku_supplier[$sku] = $supplier;
				$sku_supplier[$sku] = preg_replace('~,$~','',$sku_supplier[$sku]);
				
				if(!isset($supplier_master[$supplier])) $supplier_master[$supplier] = $supplier;

				$order_id_master[$order_id] .= ','.$supplier;
			}
			
			
			if($shmethod == 1)
			{
				// $sku_shipping_method[$order_id] = $order->getShippingDescription();
				$sku_shipping_method[$order_id] = clean_method($order->getShippingDescription(),'shipping');
				
				// $sku_shipping_method[$order_id] = preg_replace('/[^A-Za-z0-9 _\-\+\&\(\{\}\)]/','',$sku_shipping_method[$order_id]);
				// 				
				// 				$sku_shipping_method[$order_id] = str_ireplace(array('Select Delivery Method','Method','Select postage','- '),'',$sku_shipping_method[$order_id]);
				// 				$sku_shipping_method[$order_id] = str_ireplace(
				// 					array("\n","\r",'  ','Free Shipping Free','Free shipping␣ Free','Paypal Express Checkout','Pick up Pick up','Pick up at our ','Cash on delivery','Courier Service','Delivery Charge','  '),
				// 					array(' ',' ',' ','Free Shipping','Free Shipping','Paypal Express','Pick up','Pickup@','COD','Courier','Charge',' '),
				// 				$sku_shipping_method[$order_id]);
				// 				$sku_shipping_method[$order_id] = preg_replace('~Charge:$~i','',$sku_shipping_method[$order_id]);
					
				// $sku_shipping_method[$order_id] = str_ireplace('Pick up Pick up','Pick up',$sku_shipping_method[$order_id]);
				//Pick up at our
				//Pick up Pick up
				// $sku_shipping_method[$order_id] = trim(str_ireplace('Paypal Express Checkout','Paypal Express',$sku_shipping_method[$order_id]));
				if($sku_shipping_method[$order_id] != '') $sku_shipping_method[$order_id] = 'Ship: '.$sku_shipping_method[$order_id];
				// $sku_shipping_method[$order_id] = str_ireplace(array('Select Delivery Method','Method'),array('Delivery',''),$order->getShippingDescription());
				
				$shipping_method_x = 260;
				if($barcodes == 1) $shipping_method_x = 300;//$barcodeWidth = 250;
				$max_shipping_description_length = 41;//37
				if(strlen($sku_shipping_method[$order_id]) > $max_shipping_description_length)
				{
					$sku_shipping_method[$order_id] = trim(substr(htmlspecialchars_decode($sku_shipping_method[$order_id]), 0, ($max_shipping_description_length)).'…');
				}
				$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
				$font_size_compare = ($font_size_subtitles - 2);
				$line_width = $this->parseString($sku_shipping_method[$order_id],$font_temp,$font_size_compare); // bigger = left
				
				// 			
				/*
				$minXship = (240+($barcodeWidth*2));
								$maxLineWidth = ($page_width-$minXship);
								
								$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
								$font_size_compare = ($font_size_subtitles - 2);
								$line_width = $this->parseString($sku_shipping_method[$order_id],$font_temp,$font_size_compare); // bigger = left
							
								if($shipping_method_x == 0) $shipping_method_x = 360;
								if(($line_width+$shipping_method_x+20)>$page_width)
								{
									$shipping_method_x = ($page_width-$line_width-20);
								}
								if(($line_width+$shipping_method_x+20)>$page_width)
								{
									$shipping_method_x = ($shipping_method_x-$line_width-20);
								}
								if($shipping_method_x>$minXship)
								{
									$shipping_method_x = $minXship;
								}*/
				
			}
		
			/********************************************************/
			// qty in this order of this sku
			$qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();
            $sqty = $item->getIsQtyDecimal() ? $item->getQtyShipped() : (int) $item->getQtyShipped();
			// total qty in all orders for this sku
			if(isset($sku_qty[$sku])) $sku_qty[$sku] = ($sku_qty[$sku]+$qty);
			else $sku_qty[$sku] = $qty;
			$total_quantity = $total_quantity + $qty;

			$cost= $qty*(is_object($product)?$product->getCost():0);
			$total_cost = $total_cost + $cost;

			$sku_master[$sku] = $sku;
			
			if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id) && $configurable_names == 'simple')
			{
				$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
				if($_newProduct->getData('name')) $sku_name[$sku] = $_newProduct->getData('name');
			}
			elseif($configurable_names == 'configurable') $sku_name[$sku] = $item->getName();
			elseif($configurable_names == 'custom' && $configurable_names_attribute != '')
			{
				$customname = '';
				
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
					$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($configurable_names_attribute)) $customname = $_newProduct->getData(''.$configurable_names_attribute.'');
				}
				elseif($product->getData(''.$configurable_names_attribute.''))
				{
					$customname = $product->getData($configurable_names_attribute);
				}
				else $customname = '';

				if(trim($customname) != '')
				{
					if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($configurable_names_attribute))
					{
						$customname = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($configurable_names_attribute);
					}
					elseif($product[$configurable_names_attribute]) $customname = $product[$configurable_names_attribute];
					else $customname = '';
				}

				if(trim($customname) != '')
				{
					if(is_array($customname)) $customname = implode(',',$customname);
					$customname = preg_replace('~,$~','',$customname);
				}
				else $customname = '';
				
				 $sku_name[$sku] = $customname;
			}
			
			if(isset($options['options']) && is_array($options['options']))
			{
				$i = 0;
				if(isset($options['options'][$i])) $continue = 1;
				while($continue == 1)
				{
					$options_name_temp[$i] = trim(htmlspecialchars_decode($options['options'][$i]['label'].' : '.$options['options'][$i]['value']));
					$options_name_temp[$i] = preg_replace('~^select ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^enter ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^would you Like to ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^please enter ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^your ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~\((.*)\)~i','',$options_name_temp[$i]);
						
					$i ++;
					$continue = 0;
					if(isset($options['options'][$i])) $continue = 1;
				}
				//print_r($options_name_temp);exit;
				
				
				$i = 0;
				$continue = 0;
				$opt_count = 0;
				
				if(isset($options['options'][$i])) $continue = 1;
				
			
				$sku_order_id_options[$order_id][$sku] = array();
				
				while($continue == 1)// && isset($sku_order_id_options[$order_id][$sku]))
				{
					// echo 'options_yn:'.$options_yn.' here';exit;	
					
					if($i > 0) $sku_order_id_options[$order_id][$sku] .= ' ';
					$sku_order_id_options[$order_id][$sku] .= htmlspecialchars_decode('[ '.$options['options'][$i]['label'].' : '.$options['options'][$i]['value'].' ]');
					
						// if show options as a group
						if($options_yn != 0 && $opt_count == 0)
						{
							$full_sku 	= trim($item->getSku());	
							$parent_sku = preg_replace('~\-(.*)$~','',$full_sku);
							$full_sku 	= preg_replace('~^'.$parent_sku.'\-~','',$full_sku);
							$options_sku_array = array();
							$options_sku_array = explode('-',$full_sku);
							if(!isset($options_sku_parent[$order_id])) $options_sku_parent[$order_id] = array();
							if(!isset($options_sku_parent[$order_id][$sku])) $options_sku_parent[$order_id][$sku] = array();
							
							$opt_count = 0;
							foreach ($options_sku_array as $k => $options_sku_single)
							{	
								if(!isset($options_sku_parent[$order_id][$sku][$options_sku_single])) $options_sku_parent[$order_id][$sku][$options_sku_single] = '';
								
								// if(!isset($options_name_count[$options_sku_single])) $options_name_count[$options_sku_single] = 0;
								// 								else $options_name_count[$options_sku_single] ++;
								$options_name[$order_id][$sku][$options_sku_single] = $options_name_temp[$opt_count];
								
								if(isset($options_sku[$order_id][$options_sku_single]) && (!in_array($options_sku_single,$pickpack_options_filter_array)))
								{
									$options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku] + $options_sku[$order_id][$options_sku_single]);
									$options_sku_parent[$order_id][$sku][$options_sku_single] = ($qty + $options_sku_parent[$order_id][$sku][$options_sku_single]);
									// if($options_name_temp[$opt_count]) $sku_name[$options_sku_single] =  $options_name_temp[$opt_count];
								}
								elseif(!in_array($options_sku_single,$pickpack_options_filter_array))
								{
									$options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku]);
									$options_sku_parent[$order_id][$sku][$options_sku_single] = $qty;//($sku_qty[$sku]);
									// if($options_name_temp[$opt_count]) $sku_name[$options_sku_single] =  $options_name_temp[$opt_count];
								}

								$opt_count ++;
							}		
							unset($options_name_temp);	
						}

					// }
					
					$i ++;
					$continue = 0;
					if(isset($options['options'][$i])) $continue = 1;
				}
			}
			unset($options_name_temp);
// print_r($options_name);exit;
			$sku_stock[$sku] = $stock;
		    
			if(isset($sku_order_id_qty[$order_id][$sku])){
                $sku_order_id_qty[$order_id][$sku] = ($sku_order_id_qty[$order_id][$sku]+$qty); 
                $sku_order_id_sqty[$order_id][$sku] = ($sku_order_id_sqty[$order_id][$sku]+$sqty); 
	            $sku_order_id_sku[$order_id][$sku] = $product_sku; 
	
				$product_build[$order_id][$sku]['qty'] = ($sku_order_id_qty[$order_id][$sku]+$qty); 
                $product_build[$order_id][$sku]['sqty'] = ($sku_order_id_sqty[$order_id][$sku]+$sqty); 
	            // $sku_order_id_sku[$order_id][$sku] = $product_sku;
    		}else{
                $sku_order_id_qty[$order_id][$sku] = $qty;
                $sku_order_id_sqty[$order_id][$sku] = $sqty; 
                $sku_order_id_sku[$order_id][$sku] = $product_sku; 

				$product_build[$order_id][$sku]['qty'] = $qty; 
                $product_build[$order_id][$sku]['sqty'] = $sqty;
			} 

			if(!isset($max_qty_length)) $max_qty_length = 2;
			if(strlen($sku_order_id_qty[$order_id][$sku]) > $max_qty_length) $max_qty_length = strlen($sku_order_id_qty[$order_id][$sku]);
			if(strlen($sku_order_id_sqty[$order_id][$sku]) > $max_qty_length) $max_qty_length = strlen($sku_order_id_sqty[$order_id][$sku]);


			if($split_supplier_yn != 'no')
			{
				// if(!in_array($supplier,$sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
				if(!isset($sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
				elseif(!in_array($supplier,$sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
			}
			$coun ++;
			
		}
		
		
// echo 'order_id:'.$order_id.' sku:'.$sku;
	ksort($sku_bundle[$order_id]);
	}
// print_r($sku_bundle);exit;

	$sku_test = array();
	
	ksort($order_id_master);
	ksort($sku_order_id_qty);
	// ksort($sku_bundle);
	ksort($sku_master);
	ksort($product_build);
	if(isset($supplier_master))	ksort($supplier_master);
	$supplier_previous 		= '';
	$supplier_item_action 	= '';
	$first_page_yn 			= 'y';

	// if($sort_packing != 'none')
	// {
	// 	if($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
	// 	sksort($product_build,$sort_packing,$sortorder_packing_bool);
	// 	// sksort($product_build_item,$sort_packing,$sortorder_packing_bool);
	// }
	
	// foreach($sku_order_id_qty[$order_id] as $sku =>$qty)
	
 // print_r($product_build);exit;
	// print_r($sku_order_id_qty);exit;
    /*print_r($order_id_master);
    print_r($supplier_master);
    print_r($sku_order_id_qty);
    print_r($sku_master);
    exit;*/
	


	if($split_supplier_yn != 'no')
	{
		foreach($supplier_master as $key =>$supplier)
		{
			if( (isset($supplier_login) && ($supplier_login != '') && (strtoupper($supplier) == strtoupper($supplier_login) ) ) || !isset($supplier_login) || $supplier_login == '')
			{
				
				if($first_page_yn == 'n') $page = $this->newPage();
				else $first_page_yn = 'n';
			
				if($picklogo == 1)
				{					
					$packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $store);
					if($packlogo)
					{
						$packlogo = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_pack/' . $packlogo;
						if (is_file($packlogo)) {	
							$packlogo = Zend_Pdf_Image::imageWithPath($packlogo);			
							$page->drawImage($packlogo, 27, ($page_top-31), 296, ($page_top+10));
						}
					}
					//770 / 820 ($page_top-5)
					$this->_setFont($page, $font_style_header, $font_size_header, $font_family_header, $non_standard_characters, $font_color_header);	
					$page->drawText('Order-separated Pick List', 325, ($page_top-5), 'UTF-8');		
					$page->drawText(Mage::helper('sales')->__('Supplier : '.$supplier), 325, ($page_top-25), 'UTF-8');
					$page->setFillColor($font_color_header_zend);			
					$page->setLineColor($font_color_header_zend);
					$page->setLineWidth(0.5);			
					$page->drawRectangle(304, ($page_top-31), 316, ($page_top+10));
				
					$this->y = 777;
				}
				else
				{
		   			$this->_setFont($page, $font_style_header, ($font_size_header+2), $font_family_header, $non_standard_characters, $font_color_header);
					$page->drawText(Mage::helper('sales')->__('Order-separated Pick List, Supplier : '.$supplier), 31, ($page_top-5), 'UTF-8');
					//$page->drawText(Mage::helper('sales')->__('Supplier : '.$supplier), 325, ($page_top-25), 'UTF-8');
					$page->setLineColor($font_color_header_zend);
					$page->setFillColor($font_color_header_zend);
					$page->drawRectangle(27, ($page_top-12), $padded_right, ($page_top-11));
				
					$this->y = 800;
				}
			
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
	     	
		        $sku_supplier_item_action = array();
				$sku_supplier_item_action_master = array();
				
				$page_count = 1;
				foreach($order_id_master as $order_id =>$value)
				{
					// if ($this->y<40) $page = $this->newPage();
					if ($this->y<60)
					{
						if($page_count == 1)
						{
							$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - 15), 'UTF-8');
						}
						$page = $this->newPage();
						$page_count ++;
						$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
						$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
					}
				
					$grey_title = FALSE;
					$supplier_array = explode(',',$value);
					$supplier_skip_order = TRUE;
					if(in_array(strtoupper(trim($supplier)),$supplier_array)) $supplier_skip_order = FALSE;
					elseif($supplier_options == 'grey')
					{
						$supplier_skip_order = FALSE;
						$grey_title = TRUE;
					} // hide only 'filter' settings, if 'grey' show non-fulfileld orders greyed out
				
				
					if($supplier_skip_order == FALSE)
					{
						$page->setFillColor($background_color_subtitles_zend);
						$page->setLineColor($background_color_subtitles_zend);
						if($grey_title == TRUE)
						{
							$page->setFillColor($lt_grey_bkg_color);
							$page->setLineColor($lt_grey_bkg_color);	
						}
						$page->drawRectangle(27, $this->y, $padded_right, ($this->y - 20));
		   			
						$this->_setFont($page, 'bold', $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles);

						// order #
						if($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
						$page->drawText(Mage::helper('sales')->__('#') .$order_id, 31, ($this->y - 15), 'UTF-8');
				
						if($tickbox != 'no' && $grey_title != TRUE)
						{
							$page->setFillColor($white_color);			
							$page->setLineColor($black_color);	
							$page->drawRectangle(552, ($this->y-2), 568, ($this->y-18));
						}
				
						// $barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
						if($barcodes==1)
						{
							$barcodeString = $this->convertToBarcodeString($order_id);
							$barcodeWidth = $this->parseString($order_id,Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 24);
							$page->setFillColor($white_color);
							$page->setLineColor($white_color);
							$page->drawRectangle(($barcodeWidth + 38), ($this->y-2), (40+$barcodeWidth+$barcodeWidth+10), ($this->y - 18));
							$page->setFillColor($black_color);
							if($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
							$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 16);
							$page->drawText($barcodeString, ($barcodeWidth + 50), ($this->y - 20), 'CP1252');
						}

						if($shmethod == 1)
						{	
							if($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));						
							$this->_setFont($page, 'bold', ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							$page->drawText($sku_shipping_method[$order_id], $shipping_method_x, ($this->y - 15), 'UTF-8');
						}
					
						if($order_address_yn == 1)
						{
							$this->y -= 30;
						
							if($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
							$this->_setFont($page, 'regular', ($font_size_subtitles-4), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);						
							$page->drawText($sku_shipping_address[$order_id], 60, ($this->y - 15), 'UTF-8');
						}

						$this->y -= 30;
					
					 
						foreach($sku_order_id_qty[$order_id] as $sku =>$qty)
						{
							$supplier_item_action = 'keep';
							// if set to filter and a name and this is the name, then print
							if($supplier_options == 'filter' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login)) )//grey //split
							{
								$supplier_item_action = 'keep';
							}
							elseif($supplier_options == 'filter' && isset($supplier_login) && ($supplier_login != '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login)) )//grey //split
							{
								$supplier_item_action = 'hide';
							}
							elseif($supplier_options == 'grey' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login)) )//grey //split
							{
								$supplier_item_action = 'keep';
							}
							elseif($supplier_options == 'grey' && isset($supplier_login) && $supplier_login != '' && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login)) )//grey //split
							{
								$supplier_item_action = 'keepGrey';
							}
							elseif($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier) ) )
							{
								$supplier_item_action = 'keepGrey';
							}
							elseif($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier) ) )
							{
								$supplier_item_action = 'hide';
							}
							elseif($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier) ) )
							{
								$supplier_item_action = 'keep';
							}
							elseif($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier) ) )
							{
								$supplier_item_action = 'keep';
							}
							elseif($supplier_options == 'grey') $supplier_item_action = 'keepGrey';
							elseif($supplier_options == 'filter') $supplier_item_action = 'hide';
						
							$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

							if($supplier_item_action != 'hide' && trim($supplier_item_action) != '')
							{
								if ($this->y<60)
								{
									if($page_count == 1)
									{
										$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
										$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - 15), 'UTF-8');
									}
									$page = $this->newPage();
									$page_count ++;
									$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
									$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
								}
							
								// if ($this->y<15) $page = $this->newPage();
								if($supplier_item_action == 'keepGrey')
								{
									$page->setFillColor($greyout_color);
								}
								elseif($tickbox != 'no')
								{
									$page->setFillColor($white_color);			
									$page->setLineColor($black_color);	
									$page->drawRectangle(27, ($this->y), 34, ($this->y+7));
									$page->setFillColor($font_color_body_zend);
								}
							

								//
								$sku_addon = '';
								if($shelving_yn == 1 && trim($sku_shelving[$sku]) != '') 
								{
									if($shelvingpos == 'col')
									{
										$page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
									}
									else
									{
										$sku_addon = ' / ['.$sku_shelving[$sku].']';
									}
								}

								$max_qty_length_display = 0;

	                             if($from_shipment){
									$max_qty_length_display = ((($max_qty_length+5)*($font_size_body*1.1))-57);
									$qty_string = 's:'.(int)$sku_order_id_sqty[$order_id][$sku] . ' / o:'.$qty; 
	                             }else{
	                                 $qty_string = $qty;
	                             }                            

								$page->drawText($qty_string, $qtyX, $this->y , 'UTF-8');
								$page->drawText(' x  '.$sku.$sku_addon, $skuX+$max_qty_length_display, $this->y , 'UTF-8');
								if($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y , 'UTF-8');
								//
							
								if(($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1 && $supplier_item_action != 'keepGrey') 
								{
									$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
								
									$this->y -= 12;
									$page->setFillColor($red_bkg_color);			
									$page->setLineColor(new Zend_Pdf_Color_Rgb(1,1,1));	
									$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
									$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));				
									$warning = 'Stock Warning      SKU: '.$sku.'    Net Stock After All Picks : ';
									if(isset($sku_stock[$sku])) $warning .= $sku_stock[$sku];
									$page->drawText($warning, 60, $this->y , 'UTF-8');
									$this->y -= 4;
								}
						
								if(isset($sku_order_id_options[$order_id][$sku]) && $sku_order_id_options[$order_id][$sku] != '')
								{
									$this->y -= 10;
									$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
					                if($options_yn == 0) $page->drawText('Options: '.$sku_order_id_options[$order_id][$sku], ($namenudge + 20) , $this->y , 'UTF-8');
									else
									{
										
									}
								}
							
								if($sku_bundle[$order_id][$sku])
								{
									$offset = 10;
									$box_x = ($qtyX-$offset);
									$this->y -= 10;
									$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
					                $page->drawText('Bundle Options : ', $box_x , $this->y , 'UTF-8');

									foreach($sku_bundle[$order_id][$sku] as $key =>$value)
									{
										$sku			= '';
										$name			= '';
										$shelf			= '';
										$qty			= '';
										$sku_bundle_array = explode('##',$value);
										$sku 			= $sku_bundle_array[0];
										$name 			= $sku_bundle_array[1];
										$shelf 			= $sku_bundle_array[2];
										$qty 			= $sku_bundle_array[3];

										$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
						                $this->y -= 10;
										$page->drawText($sku, ($skuX+$skuXInc+10) , $this->y , 'UTF-8');
										$page->drawText($name, intval($namenudge+10) , $this->y , 'UTF-8');
										$page->drawText($shelf, $shelvingX , $this->y , 'UTF-8');
										$page->drawText($qty.'  x', $qtyX , $this->y , 'UTF-8');

										if(($sku_supplier_item_action[$supplier][$sku] != 'keepGrey') && ($tickbox == 'pickpack'))
										{
											$page->setLineWidth(0.5);
											$page->setFillColor($white_color);			
											$page->setLineColor($black_color);	
											$page->drawRectangle($box_x, ($this->y-1), ($box_x + 7), ($this->y+6));
											$page->setFillColor($black_color);
										}
									}
								}
							
								$this->y -= 12;
							}
						}
					} //end if in supplier_array
				}// end roll_Order
			}// end hide/grey sheets for suppliers
		}
	}
	else
	{
			

			if($picklogo == 1)
			{					
				$packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $store);
				if($packlogo)
				{
					$packlogo = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_pack/' . $packlogo;
					if (is_file($packlogo)) {	
						$packlogo = Zend_Pdf_Image::imageWithPath($packlogo);			
						$page->drawImage($packlogo, 27, ($page_top-31), 296, ($page_top+10));
					}
				}
				$this->_setFont($page, $font_style_header, $font_size_header, $font_family_header, $non_standard_characters, $font_color_header);	
				if($from_shipment === true)
				{
					$page->drawText('Shipment-separated Pick List', 325, ($page_top-5), 'UTF-8');		
				}
				else
				{
					$page->drawText('Order-separated Pick List', 325, ($page_top-5), 'UTF-8');		
				}
				// $page->drawText(Mage::helper('sales')->__('Supplier : '.$supplier), 325, 790, 'UTF-8');
				$page->setFillColor($font_color_header_zend);			
				$page->setLineColor($font_color_header_zend);
				$page->setLineWidth(0.5);			
				$page->drawRectangle(304, ($page_top-31), 316, ($page_top+10));
				
				$this->y = ($page_top-43);//777;
			}
			else
			{
	   			$this->_setFont($page, $font_style_header, ($font_size_header+2), $font_family_header, $non_standard_characters, $font_color_header);
				if($from_shipment === true)
				{
					$page->drawText(Mage::helper('sales')->__('Shipment-separated Pick List'), 31, ($page_top-5), 'UTF-8');
				}
				else
				{
					$page->drawText(Mage::helper('sales')->__('Order-separated Pick List'), 31, ($page_top-5), 'UTF-8');
				}
				$page->setLineColor($font_color_header_zend);
				$page->setFillColor($font_color_header_zend);
				$page->drawRectangle(27, ($page_top-12), $padded_right, ($page_top-11));
				
				$this->y = ($page_top-20);//800;
			}
			$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
			
			
			// roll_orderID
			$page_count = 1;
			// foreach($order_id_master as $order_id =>$value)
			// 			{	
			foreach($product_build as $order_id => $order_build) 
			{			
				if ($this->y<60)
				{
					if($page_count == 1)
					{
						$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
						$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - 15), 'UTF-8');
					}
					$page = $this->newPage();
					$page_count ++;
					$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
					$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
				}
				
				$page->setFillColor($background_color_subtitles_zend);
				$page->setLineColor($background_color_subtitles_zend);
				$page->drawRectangle(27, $this->y, $padded_right, ($this->y - 20));

				// order #
				$this->_setFont($page, 'bold', $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
				$page->drawText(Mage::helper('sales')->__('#') .$order_id, 31, ($this->y - 15), 'UTF-8');

				$barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
			
			//587 / 570 ($padded_right-2)
				if($tickbox != 'no')
				{
					$page->setFillColor($white_color);			
					$page->setLineColor($black_color);	
					$page->drawRectangle(($padded_right-2-16), ($this->y-2), ($padded_right-2), ($this->y-18));
					$page->setFillColor($black_color);
				}
			
				if($barcodes==1)
				{
					$barcodeString = $this->convertToBarcodeString($order_id);
					$barcodeWidth = $this->parseString($order_id,Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 24);
					$page->setFillColor($white_color);
					$page->setLineColor($white_color);
					$page->drawRectangle(($barcodeWidth + 38), ($this->y-2), (40+$barcodeWidth+$barcodeWidth+10), ($this->y - 18));
					$page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
					$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 16);
					$page->drawText($barcodeString, ($barcodeWidth + 50), ($this->y - 20), 'CP1252');
				}
 
				if($shmethod == 1)
				{	
					$this->_setFont($page, 'bold', ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
					$page->drawText($sku_shipping_method[$order_id], $shipping_method_x, ($this->y - 15), 'UTF-8');
				}
		
				if($order_address_yn == 1)
				{
					$page->setFillColor($background_color_subtitles_zend);
					$page->setLineColor($background_color_subtitles_zend);
					$page->drawRectangle(27, ($this->y-31), $padded_right, ($this->y - 20));

					// order #
					$this->_setFont($page, 'regular', ($font_size_subtitles*0.6), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
					
					$this->y -= 28;
					$page->drawText($sku_shipping_address[$order_id], 31, $this->y, 'UTF-8');
					// $this->y += 5;
					$this->y -= 16;
				}
				else $this->y -= ($font_size_body+20);

				
// 				echo 'sort_packing:'.$sort_packing;
// print_r($product_build);exit;
		       	// foreach($product_build as $order_id => $order_build) 
		       	// 				{

					if($sort_packing != 'none')
					{
						if($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
						sksort($order_build,$sort_packing,$sortorder_packing_bool);
						// sksort($product_build_item,$sort_packing,$sortorder_packing_bool);
					}
					// print_r($order_build);exit;
		
					foreach($order_build as $sku => $value)
					{
						// $sku = $key;
						$dsku = $value['sku'];
						$qty = $value['qty'];
						$sqty = $value['sqty'];
						// echo 'sku:'.$sku.'dsku:'.$dsku;exit;
				
				
						// if ($this->y<15) $page = $this->newPage();
						if ($this->y<60)
						{
							if($page_count == 1)
							{
								$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
								$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - 15), 'UTF-8');
							}
							$page = $this->newPage();
							$page_count ++;
							$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
						}
					
						$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
					
						if($tickbox != 'no')
						{
							$page->setLineWidth(1);
							$page->setFillColor($white_color);			
							$page->setLineColor($black_color);	
							$page->drawRectangle(27, ($this->y), (27+($font_size_body*0.65)), ($this->y+($font_size_body*0.65)));
						
									// $page->setLineWidth(0.5);
									// 					$page->setFillColor($white_color);			
									// 					$page->setLineColor($black_color);	
									// 					$page->drawRectangle(27, $this->y, 34, ($this->y+7));
									// 					$page->setFillColor($black_color);
							
						}
						$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
					
							//
						$sku_addon = '';
						if($shelving_yn == 1 && trim($sku_shelving[$sku]) != '') 
						{
							if($shelvingpos == 'col')
							{
								$page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
							}
							else
							{
								$sku_addon = ' / ['.$sku_shelving[$sku].']';
							}
						}

						$max_qty_length_display = 0;
					
						if($from_shipment){
							$max_qty_length_display = ((($max_qty_length+5)*($font_size_body*1.1))-57);
							$qty_string = 's:'.(int)$sku_order_id_sqty[$order_id][$sku]. ' / o:'.$qty;
						}else{
							$qty_string = $qty;
						}                            

						$page->drawText($qty_string, $qtyX, $this->y , 'UTF-8');
					
						$display_sku = '';
						if(strlen($sku) > ($breakpoint_sku+2))
						{
							// $display_sku = substr(htmlspecialchars_decode($sku_order_id_sku[$order_id][$sku]), 0, ($breakpoint_sku)).'…';
							$display_sku = substr(htmlspecialchars_decode($dsku), 0, ($breakpoint_sku)).'…';
						}
						// else $display_sku = htmlspecialchars_decode($sku_order_id_sku[$order_id][$sku]);
						else $display_sku = htmlspecialchars_decode($dsku);
				
				
						// $page->drawText(' x  '.$display_sku.$sku_addon, $skuX+$max_qty_length_display, $this->y , 'UTF-8');
						$page->drawText($display_sku.$sku_addon, $skuX+$max_qty_length_display, $this->y , 'UTF-8');
						if($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y , 'UTF-8');
		
						if(($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1) 
						{
							$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
						
							$this->y -= ($font_size_body+2);
							$page->setFillColor($red_bkg_color);			
							$page->setLineColor(new Zend_Pdf_Color_Rgb(1,1,1));	
							$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
							$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));				
							$warning = 'Stock Warning      SKU: '.$sku.'    Net Stock After All Picks : '.$sku_stock[$sku];
							$page->drawText($warning, 60, $this->y , 'UTF-8');
							$this->y -= 4;
						}
					
						if(isset($sku_order_id_options[$order_id][$sku]) && $sku_order_id_options[$order_id][$sku] != '')
						{
							$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
			                if($options_yn == 0) 
							{
								$this->y -= ($font_size_body);
								$page->drawText('Options: '.$sku_order_id_options[$order_id][$sku], ($namenudge + 20) , $this->y , 'UTF-8');
							}
							else
							{
								ksort($options_sku_parent[$order_id][$sku]);
							
								// print_r($options_sku_parent[$order_id][$sku]);exit;
								foreach($options_sku_parent[$order_id][$sku] as $options_sku => $options_qty)
								{
							
								
									if(!in_array($options_sku,$pickpack_options_filter_array))
									{
										$this->y -= ($font_size_body-2);
								
										if($tickbox != 'no')
										{
											$page->setFillColor($white_color);			
											$page->setLineColor($black_color);	
											$page->setLineWidth(0.5);			
											$page->drawRectangle(30, ($this->y-1), 36, ($this->y+5));
											$page->setLineWidth(1);			
										}
										$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
								
										if(!in_array($options_sku,$pickpack_options_count_filter_array))
										{
											$page->drawText($options_qty, ($qtyX + 4) , $this->y , 'UTF-8');
											// $page->drawText('x [ '.$options_sku.' ]', ($skuX+$max_qty_length_display + 4) , $this->y , 'UTF-8');
											$page->drawText(' [ '.$options_sku.' ]', ($skuX+$max_qty_length_display + 6) , $this->y , 'UTF-8');
										}
										else
										{
											$page->drawText('   [ '.$options_sku.' ]', ($skuX+$max_qty_length_display + 6) , $this->y , 'UTF-8');
										}
									
								
										if(isset($options_name[$order_id][$sku][$options_sku]) && $nameyn == 1)
										{
											$page->drawText($options_name[$order_id][$sku][$options_sku], ($namenudge+4), $this->y , 'UTF-8');
										}
									}
								}
							}
						}

						if($sku_bundle[$order_id][$sku])
						{
							$offset = 10;
							$box_x = ($qtyX-$offset);
							$this->y -= ($font_size_body);
							$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
			                $page->drawText('Bundle Options : ', $box_x , $this->y , 'UTF-8');
		
							foreach($sku_bundle[$order_id][$sku] as $key =>$value)
							{
								$sku			= '';
								$name			= '';
								$shelf			= '';
								$qty			= '';
								$sku_bundle_array = explode('##',$value);
								$sku 			= $sku_bundle_array[0];
								$name 			= $sku_bundle_array[1];
								$shelf 			= $sku_bundle_array[2];
								$qty 			= $sku_bundle_array[3];
						
					
							
								$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
				                $this->y -= ($font_size_body-2);
								$page->drawText($sku, ($skuX+$skuXInc+10) , $this->y , 'UTF-8');
								$page->drawText($name, intval($namenudge+10) , $this->y , 'UTF-8');
								$page->drawText($shelf, $shelvingX , $this->y , 'UTF-8');
								// $page->drawText($qty.'  x', $qtyX , $this->y , 'UTF-8');
								$page->drawText($qty, $qtyX , $this->y , 'UTF-8');
							
								if($tickbox != 'no')
								{
									$page->setFillColor($white_color);			
									$page->setLineColor($black_color);	
									$page->setLineWidth(0.5);			
									$page->drawRectangle(30, ($this->y-1), 36, ($this->y+5));
									$page->setLineWidth(1);			
								}
							
								// if(((isset($sku_supplier_item_action[$supplier][$sku]) && $sku_supplier_item_action[$supplier][$sku] != 'keepGrey')) && ($tickbox == 'pickpack'))
								// 						{
								// 							$page->setLineWidth(0.5);
								// 							$page->setFillColor($white_color);			
								// 							$page->setLineColor($black_color);	
								// 							$page->drawRectangle($box_x, ($this->y), ($box_x + ($font_size_body-2-4)), ($this->y+($font_size_body-2-4)));
								// 							$page->setFillColor($black_color);
								// 						}
							}
						}
					
					$this->y -= ($font_size_body+1);
				// }
				}// end roll_SKU
				
			}// end roll_Order

			$this->y -= 30;
			// $this->_setFontItalic($page,12);
			if(($showcount_yn == 1) || $showcost_yn == 1)
			{
				$page->setFillColor($white_bkg_color);			
				$page->setLineColor($orange_bkg_color);	
				$page->setLineWidth(4);	
				$page->drawRectangle(355, ($this->y-34), 568, ($this->y+10+$font_size_body));
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
			}
			
			if($showcount_yn == 1)
			{
				$page->drawText('Total quantity : '.$total_quantity, 375, $this->y , 'UTF-8');
			}

			$this->y -= 20;

			if($showcost_yn == 1)
			{
				$page->drawText('Total cost : '.$currency_symbol."  ".($total_cost), 375, $this->y , 'UTF-8');
			}

			$printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_packprint');
			if($printdates!=1)
			{
				$this->_setFontBold($page);
$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time())), 210, 18, 'UTF-8');
			}
	}
	
	$this->_afterGetPdf();

	return $pdf;	
	/**
	getPickseparated2 222222222 Template - END
	*********************************************
	************************************************/
}






 /**
   getPickCOMBINED Template - START
    ************************************************/

public function getPickCombined($orders=array(), $shipments = array(), $from_shipment = false)
{

// 	echo '$orders = array()';
// 	print_r($orders);
// 	echo '$shipments = array()';
// print_r($shipments);
// echo '<br />';
// exit;


	/**
	 * get store id
	 */
	$storeId = Mage::app()->getStore()->getId();


	$this->_beforeGetPdf();
	$this->_initRenderer('invoices');

	$pdf = new Zend_Pdf();
	$this->_setPdf($pdf);
	$style = new Zend_Pdf_Style();
	
	$page_size = $this->_getConfig('page_size', 'a4', false,'general');
	
	if($page_size == 'letter') 
	{
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
		$page_top = 770;$padded_right = 587;
	}
	elseif($page_size == 'a4') 
	{
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$page_top = 820;$padded_right = 570;
	}
	elseif($page_size == 'a5landscape') 
	{
		$page = $pdf->newPage('596:421');					
		$page_top = 395;$padded_right = 573;
	}
	// ($padded_right-10) 570 / 587       ($page_top-5) 820 / 770
	
	$pdf->pages[] = $page;
  

	// $skuX = 50;
	$qtyX = 40;
	$productX = 250;
	$fontsize_overall = 15;
	$fontsize_productline = 9;
    $total_quantity = 0;
    $total_cost = 0;
	$red_bkg_color 			= new Zend_Pdf_Color_Html('lightCoral');
	$grey_bkg_color			= new Zend_Pdf_Color_GrayScale(0.7);
	$dk_grey_bkg_color		= new Zend_Pdf_Color_GrayScale(0.3);//darkCyan
	$dk_cyan_bkg_color 		= new Zend_Pdf_Color_Html('darkCyan');//darkOliveGreen
	$dk_og_bkg_color 		= new Zend_Pdf_Color_Html('darkOliveGreen');//darkOliveGreen
	
	$white_bkg_color 		= new Zend_Pdf_Color_Html('white');
	$orange_bkg_color 		= new Zend_Pdf_Color_Html('Orange');
	
	$black_color 			= new Zend_Pdf_Color_Rgb(0,0,0);
	$grey_color 			= new Zend_Pdf_Color_GrayScale(0.3);
	$greyout_color 			= new Zend_Pdf_Color_GrayScale(0.6);
	$white_color 			= new Zend_Pdf_Color_GrayScale(1);
	
	$font_family_header_default = 'helvetica';
	$font_size_header_default = 16;
	$font_style_header_default = 'bolditalic';
	$font_color_header_default = 'darkOliveGreen';
	$font_family_subtitles_default = 'helvetica';
	$font_style_subtitles_default = 'bold';
	$font_size_subtitles_default = 15;
	$font_color_subtitles_default = '#222222';
	$background_color_subtitles_default = '#999999';
	$font_family_body_default = 'helvetica';
	$font_size_body_default = 10;
	$font_style_body_default = 'regular';
	$font_color_body_default = 'Black';

	$font_family_header = $this->_getConfig('font_family_header', $font_family_header_default, false,'general');
	$font_style_header = $this->_getConfig('font_style_header', $font_style_header_default, false,'general');
	$font_size_header = $this->_getConfig('font_size_header', $font_size_header_default, false,'general');
	$font_color_header = $this->_getConfig('font_color_header', $font_color_header_default, false,'general');
	$font_family_subtitles = $this->_getConfig('font_family_subtitles', $font_family_subtitles_default, false,'general');
	$font_style_subtitles = $this->_getConfig('font_style_subtitles', $font_style_subtitles_default, false,'general');
	$font_size_subtitles = $this->_getConfig('font_size_subtitles', $font_size_subtitles_default, false,'general');
	$font_color_subtitles = $this->_getConfig('font_color_subtitles', $font_color_subtitles_default, false,'general');
	$background_color_subtitles = $this->_getConfig('background_color_subtitles', $background_color_subtitles_default, false,'general');
	$font_family_body = $this->_getConfig('font_family_body', $font_family_body_default, false,'general');
	$font_style_body = $this->_getConfig('font_style_body', $font_style_body_default, false,'general');
	$font_size_body = $this->_getConfig('font_size_body', $font_size_body_default, false,'general');
	$font_color_body = $this->_getConfig('font_color_body', $font_color_body_default, false,'general');
		$background_color_subtitles_zend = new Zend_Pdf_Color_Html(''.$background_color_subtitles.'');	
	$font_color_header_zend = new Zend_Pdf_Color_Html(''.$font_color_header.'');	
	$font_color_subtitles_zend = new Zend_Pdf_Color_Html(''.$font_color_subtitles.'');	
	$font_color_body_zend = new Zend_Pdf_Color_Html(''.$font_color_body.'');

 	$non_standard_characters 	= $this->_getConfig('non_standard_characters', 0, false,'general');

	$product_id = NULL; // get it's ID
	$stock = NULL;
	$sku_stock = array();
	$sku_qty = array();
	$sku_cost = array();
	// pickpack_cost
	$showcost_yn_default		= 0;
	$showcount_yn_default		= 0;
	$currency_default			= 'USD';
	
	$shelving_yn_default		= 0;
	$shelving_attribute_default = '';
	$shelvingX_default 			= 200;
	$supplier_yn_default 		= 0;
	$supplier_attribute_default = 'supplier';
	$namenudgeYN_default		= 0;
	$stockcheck_yn_default 		= 0;
	$stockcheck_default 		= 1;
	
	$split_supplier_yn_default 	= 'no';
	$supplier_attribute_default = 'supplier';
	$supplier_options_default 	= 'filter';
	$supplier_login_default		= '';
	$tickbox_default			= 'no'; //no, pick, pickpack
	
	// $split_supplier_yn 			= $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
	$split_supplier_yn_temp 	= $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
	$split_supplier_options 	= $this->_getConfig('pickpack_split_supplier_options', 'no', false,'general');
	$split_supplier_yn = 'no';
	if($split_supplier_yn_temp == 1)
	{
		$split_supplier_yn = $split_supplier_options;
	}

	$supplier_attribute 		= $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false,'general');
	$supplier_options 			= $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false,'general');

	//	$supplier_login 			= $this->_getConfig('pickpack_supplier_login', $supplier_login_default, false,'general');
	$userId 					= Mage::getSingleton('admin/session')->getUser()->getId();
	$user 						= Mage::getModel('admin/user')->load($userId);
	$username					= $user['username'];

	$supplier_login_pre 		= $this->_getConfig('pickpack_supplier_login', '', false,'general');
	$supplier_login_pre 		= str_replace(array("\n",','),';',$supplier_login_pre);
	$supplier_login_pre 		= explode(';',$supplier_login_pre);

	foreach($supplier_login_pre as $key => $value)
	{
		$supplier_login_single = explode(':',$value);
		if(preg_match('~'.$username.'~i',$supplier_login_single[0])) 
		{
			if($supplier_login_single[1] != 'all') $supplier_login = trim($supplier_login_single[1]);
			else $supplier_login = '';
		}
	}

	$tickbox 					= $this->_getConfig('pickpack_tickbox', $tickbox_default, false,'general');
	$picklogo 					= $this->_getConfig('pickpack_picklogo', 0, false,'general');
	
	$shelvingpos = $this->_getConfig('shelvingpos', 'col', false,'general'); //col/sku
	// $address_country_yn = $this->_getConfig('address_country_yn', 1, false,'general'); //col/sku
	
	$showcount_yn 		= $this->_getConfig('pickpack_count', $showcount_yn_default, false,'messages');

	$showcost_yn 		= $this->_getConfig('pickpack_cost', $showcost_yn_default, false,'messages');
	$currency	 		= $this->_getConfig('pickpack_currency', $currency_default, false,'messages');
	$currency_symbol 	= Mage::app()->getLocale()->currency($currency)->getSymbol();
	
	$stockcheck_yn 		= $this->_getConfig('pickpack_showstock_yn_combined', $stockcheck_yn_default, false,'messages');
	$stockcheck 		= $this->_getConfig('pickpack_stock_combined', $stockcheck_default, false,'messages');

	$shelving_yn 		= $this->_getConfig('pickpack_shelving_yn', 0, false,'messages');
	$shelving_attribute = $this->_getConfig('pickpack_shelving', '', false,'messages');
	$shelvingX 			= $this->_getConfig('pickpack_shelving_nudge', 0, false,'messages');

	$extra_yn 			= $this->_getConfig('pickpack_extra_yn', 0, false,'messages');
	$extra_attribute 	= $this->_getConfig('pickpack_extra', '', false,'messages');
	$extraX 			= $this->_getConfig('pickpack_extra_nudge', 0, false,'messages');

	$nameyn 			= $this->_getConfig('pickpack_name_yn_combined', $namenudgeYN_default, false,'messages');
	$namenudge 			= $this->_getConfig('pickpack_namenudge_combined', $productX, false,'messages');
	
	$skuX 				= $this->_getConfig('pickpack_sku_nudge', 0, false,'messages');
	// $configurable_names = $this->_getConfig('pickpack_configname_combined', 'simple', false,'messages'); //col/sku
	
	$configurable_names = $this->_getConfig('pickpack_configname_combined', 'simple', false,'messages'); //col/sku
	$configurable_names_attribute = trim($this->_getConfig('pickpack_configname_attribute_separated', '', false,'picks')); //col/sku
	if($configurable_names != 'custom') $configurable_names_attribute = '';
	
	
	$pickpack_options_filter_array = array();
	$pickpack_options_count_filter_array = array();
	
	$options_yn 		= $this->_getConfig('pickpack_options_yn', 0, false,'messages'); // no, inline, newline
	$pickpack_options_filter_yn = $this->_getConfig('pickpack_options_filter_yn', 0, false,'messages');
	$pickpack_options_filter 	= $this->_getConfig('pickpack_options_filter', 0, false,'messages');
	if($pickpack_options_filter_yn == 0) $pickpack_options_filter = '';
	elseif(trim($pickpack_options_filter) != '')
	{
		$pickpack_options_filter_array = explode(',',$pickpack_options_filter);
		foreach($pickpack_options_filter_array as $key => $value)
		{
			$pickpack_options_filter_array[$key] = trim($value);
		}
	}
	$pickpack_options_count_filter 	= $this->_getConfig('pickpack_options_count_filter', 0, false,'messages');
	if($pickpack_options_filter_yn == 0) $pickpack_options_count_filter = '';
	elseif(trim($pickpack_options_count_filter) != '')
	{
		$pickpack_options_count_filter_array = explode(',',$pickpack_options_count_filter);
		foreach($pickpack_options_count_filter_array as $key => $value)
		{
			$pickpack_options_count_filter_array[$key] = trim($value);
		}
	}
	
	$sku_master = array();
	$process_list = array();



	if($from_shipment === true)
	{
		$process_list = $shipments;
// echo 'in shipments<br />';
	}
	else
	{
		$process_list = $orders;
	}
// print_r($process_list);exit;
	$shipment_list = array();
	unset($shipment_list);
	
	
	if(function_exists('sksort'))
	{}
	else
	{
		function sksort(&$array, $subkey, $sort_ascending=false) 
		{

		    if (count($array))
		        $temp_array[key($array)] = array_shift($array);

		    foreach($array as $key => $val){
		        $offset = 0;
		        $found = false;
		        foreach($temp_array as $tmp_key => $tmp_val)
		        {
		            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
		            {
		                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
		                                            array($key => $val),
		                                            array_slice($temp_array,$offset)
		                                          );
		                $found = true;
		            }
		            $offset++;
		        }
		        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
		    }

		    if ($sort_ascending) $array = array_reverse($temp_array);

		    else $array = $temp_array;
		}
	}
	
	$options_sku_array = array();
	$options_sku = array();
	unset($options_sku);
	unset($options_sku_array);
	
	$count = 0;
	$count_all = 0;
	$_count = 0;
	$i =0;
	foreach ($process_list as $orderSingle) 
	{
// echo '$orderSingle (shipment db ID):'.$orderSingle.'<br />';
		
		if($from_shipment === true)
		{	
			unset($_items);
			unset($_item);
			
			$_shipment = Mage::getModel('sales/order_shipment')->load($orderSingle);	

			$_items = $_shipment->getAllItems();//getAllVisibleItems();//getAllItems();
		
			$itemsCollection = $_items;
			
			$order 	= Mage::getModel('sales/order')->load($orders[$i]);	
			$order_items = $order->getAllVisibleItems();
			$shipment_list[] 	= $_shipment->getIncrementId();//$shipment['increment_id'];
			$order_list[]		= $order->getRealOrderId();
			if(strlen($order->getShippingAddress()->getName()) > 20)
			{
				$name_list[] = substr(htmlspecialchars_decode($order->getShippingAddress()->getName()), 0, 20).'…';
			}
			else $name_list[] 		= $order->getShippingAddress()->getName();
		}
		else
		{
			$order 				= Mage::getModel('sales/order')->load($orderSingle);	
			$itemsCollection 	=  $order->getAllVisibleItems();//$order->getItemsCollection();	
		}
		
        // $total_items     = count($itemsCollection);
		$product_build_item = array();
		$product_build = array();
		$options_name_temp = array();
		unset($options_name_temp);
		
		$coun = 1;
		
		foreach ($itemsCollection as $item)
		{		
			// if ($item->getOrderItem()->getParentItem()) continue;
			
			if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
			{
				$sku = $item->getProductOptionByCode('simple_sku');
				$product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
			} 
			else 
			{
				$sku = $item->getSku();
				$product_id = $item->getProductId(); // get it's ID			
			}	
			
			if($options_yn == 'newskuparent')
			{
				$full_sku = trim($item->getSku());	
				$parent_sku = preg_replace('~\-(.*)$~','',$full_sku);
				$sku = $parent_sku;
			}
			
			// unique item id
			$product_build_item[] = $sku.'-'.$coun;	
			$product_build[$sku.'-'.$coun]['sku'] = $sku;			
			$product_sku = $sku;
			
			// if(isset($options['options']) && is_array($options['options']))
			// 			{
			// 				$sku = $sku.'-'.$coun;
			// 			}
			$product_build[$sku]['sku'] = $product_sku;
			
			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku', $sku, array('cost',$shelving_attribute,'name','simple_sku','qty'));			
			if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
			
		
		
			
			$shelving = '';
			$supplier = '';
			
			if($shelving_yn == 1)
			{ 
				
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
						$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($shelving_attribute)) $shelving = $_newProduct->getData(''.$shelving_attribute.'');
				}
				elseif($product->getData(''.$shelving_attribute.''))
				{
						 $shelving = $product->getData($shelving_attribute);
				}
				else $shelving = '';
				
				
				if(trim($shelving) != '')
				{
					if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($shelving_attribute))
					{
						$shelving = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($shelving_attribute);
					}
					elseif($product[$shelving_attribute]) $shelving = $product[$shelving_attribute];

					if(is_array($shelving)) $shelving = implode(',',$shelving);

					if(isset($sku_shelving[$sku]) && trim(strtoupper($sku_shelving[$sku])) != trim(strtoupper($shelving))) $sku_shelving[$sku] .= ','.trim($shelving);
					else $sku_shelving[$sku] = trim($shelving);
					$sku_shelving[$sku] = preg_replace('~,$~','',$sku_shelving[$sku]);
				}
				else $sku_shelving[$sku] = '';
			}			
			
			if($extra_yn == 1)
			{ 
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
					$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($extra_attribute)) $extra = $_newProduct->getData(''.$extra_attribute.'');
				}
				elseif($product->getData(''.$extra_attribute.''))
				{
						 $extra = $product->getData($extra_attribute);
				}
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($extra_attribute))
				{
					$extra = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($extra_attribute);
				}
				elseif($product[$extra_attribute]) $extra = $product[$extra_attribute];

				if(is_array($extra)) $extra = implode(',',$extra);

				if(isset($sku_extra[$sku]) && trim(strtoupper($sku_extra[$sku])) != trim(strtoupper($extra))) $sku_extra[$sku] .= ','.trim($extra);
				else $sku_extra[$sku] = trim($extra);
				$sku_extra[$sku] = preg_replace('~,$~','',$sku_extra[$sku]);
			}
			
			if($split_supplier_yn != 'no')
			{
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
						$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($supplier_attribute)) $supplier = $_newProduct->getData(''.$supplier_attribute.'');
				}
				elseif($product->getData(''.$supplier_attribute.''))
				{
						 $supplier = $product->getData($supplier_attribute);
				}
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($supplier_attribute))
				{
					$supplier = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($supplier_attribute);
				}
				elseif($product[$supplier_attribute]) $supplier = $product[$supplier_attribute];
				
				if(is_array($supplier)) $supplier = implode(',',$supplier);
				if(!$supplier) $supplier = '~Not Set~';	
				
				$supplier = trim(strtoupper($supplier));
				
				if(isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier) $sku_supplier[$sku] .= ','.$supplier;
				else $sku_supplier[$sku] = $supplier;
				$sku_supplier[$sku] = preg_replace('~,$~','',$sku_supplier[$sku]);
				
				if(!isset($supplier_master[$supplier])) $supplier_master[$supplier] = $supplier;
				
				if(isset($order_id))
				{
					if(!isset($order_id_master[$order_id])) $order_id_master[$order_id] = $supplier;
					else $order_id_master[$order_id] .= ','.$supplier;
				}
			}
			
			// qty in this order of this sku
			
			$sqty = 0;
            if($from_shipment)
			{
				$qty = (int) $item->getOrderItem()->getQtyShipped();
				$sqty = $item->getIsQtyDecimal() ? $item->getQty() : (int) $item->getQty();//$item->getQty();
				
				$qty_shipped_now = (int) $item->getQty();
				$qty_shipped_total = (int) $item->getOrderItem()->getQtyShipped();
				$qty_ordered = (int) $order_items[($coun-1)]['qty_ordered'];
				
				$count = ($count + $qty_shipped_now);
				$count_all = ($count_all + $qty_shipped_total);
				$tqty = $qty_shipped_total;
				$sqty = $qty_shipped_now;
				$qty = $qty_ordered;
			}
			else
			{
				$qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();
			}
			
			// total qty in all orders for this sku
			if(isset($sku_qty[$sku])){
			  $sku_qty[$sku] = ($sku_qty[$sku]+$qty);
              $sku_sqty[$sku] = ($sku_sqty[$sku]+$sqty);
              if(isset($tqty)) $sku_tqty[$sku] = ($sku_tqty[$sku]+$tqty);
				else $sku_tqty[$sku] = 0;
              $sku_sku[$sku] = $product_sku;
			}else{
			 $sku_qty[$sku] = $qty;
             $sku_sqty[$sku] = $sqty;
             if(isset($tqty)) $sku_tqty[$sku] = $tqty;
				else $sku_tqty[$sku] = 0;
             $sku_sku[$sku] = $product_sku;
			} 
			
			if(!isset($max_qty_length)) $max_qty_length = 2;
			if(strlen($sku_qty[$sku]) > $max_qty_length) $max_qty_length = strlen($sku_qty[$sku]);
			if(strlen($sku_sqty[$sku]) > $max_qty_length) $max_qty_length = strlen($sku_sqty[$sku]);
			
			$options = $item->getProductOptions();
			//$optionsSku =  $item->getOptionSku($options['options'][$j]['value'].'-');
			
			if(isset($options['options']) && is_array($options['options']))
			{
				$j = 0;
				$continue = 0;
				if(isset($options['options'][$j])) $continue = 1;
				
				while($continue == 1)
				{
					if(!isset($sku_order_id_options[$sku])) $sku_order_id_options[$sku] = '';
					if($j > 0) $sku_order_id_options[$sku] .= ' ';
					if($options_yn == 'inline' || $options_yn == 'newline') $sku_order_id_options[$sku] .= htmlspecialchars_decode('[ '.$options['options'][$j]['label'].' : '.$options['options'][$j]['value'].' ]');
					//else
					
					if($options_yn == 'newsku' || $options_yn == 'newskuparent') $options_name_temp[] = htmlspecialchars_decode('[ '.$options['options'][$j]['label'].' : '.$options['options'][$j]['value'].' ]');
					// $sku_order_id_options[$sku] .= htmlspecialchars_decode('[ '.$options['options'][$j]['label'].' : '.$options['options'][$j]['value'].' ]');
					
					$j ++;
					$continue = 0;
					if(isset($options['options'][$j])) $continue = 1;
				}

				if($options_yn == 'newsku' || $options_yn == 'newskuparent')
				{
					$full_sku 	= trim($item->getSku());	
					$parent_sku = preg_replace('~\-(.*)$~','',$full_sku);
					$full_sku 	= preg_replace('~^'.$parent_sku.'\-~','',$full_sku);
						
					$options_sku_array = explode('-',$full_sku);

					$opt_count = 0;
					foreach ($options_sku_array as $k => $options_sku_single)
					{				
						if(isset($options_sku[$options_sku_single]) && (!in_array($options_sku_single,$pickpack_options_filter_array)))
						{
							$options_sku[$options_sku_single] = ($sku_qty[$sku] + $options_sku[$options_sku_single]);
							$options_sku_parent[$sku][$options_sku_single] = ($qty + $options_sku_parent[$sku][$options_sku_single]);
							if($options_name_temp[$opt_count]) $sku_name[$options_sku_single] =  $options_name_temp[$opt_count];
						}
						elseif(!in_array($options_sku_single,$pickpack_options_filter_array))
						{
							$options_sku[$options_sku_single] = ($sku_qty[$sku]);
							$options_sku_parent[$sku][$options_sku_single] = $qty;//($sku_qty[$sku]);
							if($options_name_temp[$opt_count]) $sku_name[$options_sku_single] =  $options_name_temp[$opt_count];
						}
						
						$opt_count ++;
					}		
					unset($options_name_temp);	
				}
				
			}
// print_r($options_sku_parent);exit;
			$sku_cost[$sku] = (is_object($product)?$product->getCost():0);

			$sku_master[$sku] = $sku;
			
			
			// if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id) && $configurable_names == 'simple')
			// 		{
			// 			$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
			// 			if($_newProduct->getData('name')) $sku_name[$sku] = $_newProduct->getData('name');
			// 		}
			// 		else $sku_name[$sku] = $item->getName();
			// 		
			
			
			
			
			if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id) && isset($configurable_names) && $configurable_names == 'simple')
			{
				$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
				if($_newProduct->getData('name')) $sku_name[$sku] = $_newProduct->getData('name');
			}
			elseif(isset($configurable_names) && $configurable_names == 'configurable') $sku_name[$sku] = $item->getName();
			elseif(isset($configurable_names) && $configurable_names == 'custom' && $configurable_names_attribute != '')
			{
				$customname = '';
				
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
					$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($configurable_names_attribute)) $customname = $_newProduct->getData(''.$configurable_names_attribute.'');
				}
				elseif($product->getData(''.$configurable_names_attribute.''))
				{
					$customname = $product->getData($configurable_names_attribute);
				}
				else $customname = '';

				if(trim($customname) != '')
				{
					if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($configurable_names_attribute))
					{
						$customname = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($configurable_names_attribute);
					}
					elseif($product[$configurable_names_attribute]) $customname = $product[$configurable_names_attribute];
					else $customname = '';
				}

				if(trim($customname) != '')
				{
					if(is_array($customname)) $customname = implode(',',$customname);
					$customname = preg_replace('~,$~','',$customname);
				}
				else $customname = '';
				
				 $sku_name[$sku] = $customname;
			}
						
			$sku_stock[$sku] = $stock;
			$sku_type[$sku] = 'normal';
			
			// bundle SKUs
			$options = $item->getProductOptions();
			if(isset($options['bundle_options']) && is_array($options['bundle_options']))
			{
				$children = $item->getChildrenItems();
		        if(count($children))
		        {
		            foreach($children as $child)
		            {
		                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId());
						$sku_b = $child->getSku();
						$price_b = $child->getPriceInclTax();
						$qty_b = (int)$child->getQtyOrdered();
						$name_b = $child->getName();

						$this->y -= 10;
						$offset = 20;

						$shelving_real = '';
						$shelving_real_b = '';
						if(isset($shelving_real_yn) && $shelving_real_yn == 1)
						{
							if($product->getData($shelving_real_attribute))
							{
								$shelving_real_b = $product->getData($shelving_real_attribute);
							}
							elseif(Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId())->getAttributeText($shelving_real_attribute))
							{
								$shelving_real_b = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId())->getAttributeText($shelving_real_attribute);
							}
							elseif($product[$shelving_real_attribute]) $shelving_real_b = $product[$shelving_real_attribute];
							else $shelving_real_b = '';

							if(is_array($shelving_real)) $shelving_real_b = implode(',',$shelving_real_b);
							if(isset($shelving_real_b)) $shelving_real_b = trim($shelving_real_b);
							// $page->drawText($shelving_real, $shelfX, $this->y , 'UTF-8');
						}

						 if($from_shipment){
                                $qty_string_b = 's:'.(int)$item->getQtyShipped() . ' / o:'.$qty;
								$price_qty_b = (int)$item->getQtyShipped();
                                $productXInc = 25;
                            }else{

                                $qty_string_b = $qty_b;
								$price_qty_b = $qty_b;
                                $productXInc = 0;
                            }
// echo 'name_b:'.$name_b.' $breakpoint:'.$breakpoint.' strlen($name_b):'.strlen($name_b);exit;
						$display_name_b = '';
						if(strlen($name_b) > ($breakpoint_name+2))
						{
							$display_name_b = substr(htmlspecialchars_decode($name_b), 0, ($breakpoint_name)).'…';
						}
						else $display_name_b = htmlspecialchars_decode($name_b);

						// $sku_bundle[$order_id][$sku][] = $sku_b.'##'.$display_name_b.'##'.$shelving_real_b.'##'.$qty_string_b;
						//
						$sku_cost[$sku_b] = (is_object($product)?$product->getCost():0);
						$sku_master[$sku_b] = $sku_b;
						$sku_name[$sku_b] = $name_b;
						if($sku_qty[$sku_b] > 0) $sku_qty[$sku_b] = ($sku_qty[$sku_b] + $qty_b);
						else $sku_qty[$sku_b] = $qty_b;
						if($sku_sqty[$sku_b] > 0) $sku_sqty[$sku_b] = ($sku_sqty[$sku_b] + $price_qty_b);
						else $sku_sqty[$sku_b] = $price_qty_b;
		             	$sku_sku[$sku_b] = $sku_b;
						$sku_type[$sku] = 'bundle';
					}
				}
			}
			// else 
			// 		{
			// 			$sku_bundle[$order_id][$sku] = '';
			// 		}
			
			$coun ++;
		}
		$i ++;
	}

//print_r($options_sku);exit;
	if($options_yn == 'newsku')
	{
		foreach($options_sku as $sku_option_key => $sku_option_value)
		{
			$sku_master[$sku_option_key] = $sku_option_key;
			$qty = $sku_option_value;
			$sqty = $sku_option_value;

				if(isset($sku_qty[$sku_option_key]))
				{
					$sku_qty[$sku_option_key] = ($sku_qty[$sku_option_key]+$qty);
					$sku_sqty[$sku_option_key] = ($sku_sqty[$sku_option_key]+$sqty);
					if(isset($tqty)) $sku_tqty[$sku_option_key] = ($sku_tqty[$sku_option_key]+$tqty);
					else $sku_tqty[$sku_option_key] = 0;
					$sku_sku[$sku_option_key] = $sku_option_key;
				}
				else
				{
					$sku_qty[$sku_option_key] = $qty;
					$sku_sqty[$sku_option_key] = $sqty;
					if(isset($tqty)) $sku_tqty[$sku_option_key] = $tqty;
					else $sku_tqty[$sku_option_key] = 0;
					$sku_sku[$sku_option_key] = $sku_option_key;
				}
		}
	}
	// elseif($options_yn == 'newskuparent')
	// {
	// 	foreach($options_sku as $sku_option_key => $sku_option_value)
	// 	{
	// 		$qty = $sku_option_value;
	// 		$sqty = $sku_option_value;
	// 
	// 		if(isset($sku_qty[$sku_option_key]))
	// 		{
	// 			$sku_qty[$sku_option_key] = ($sku_qty[$sku_option_key]+$qty);
	// 			$sku_sqty[$sku_option_key] = ($sku_sqty[$sku_option_key]+$sqty);
	// 			if(isset($tqty) && !in_array($sku_option_key,$pickpack_options_count_filter_array)) $sku_tqty[$sku_option_key] = ($sku_tqty[$sku_option_key]+$tqty);
	// 			else $sku_tqty[$sku_option_key] = 0;
	// 			$sku_sku[$sku_option_key] = $sku_option_key;
	// 		}
	// 		else
	// 		{
	// 			$sku_qty[$sku_option_key] = $qty;
	// 			$sku_sqty[$sku_option_key] = $sqty;
	// 			if(isset($tqty) && !in_array($sku_option_key,$pickpack_options_count_filter_array)) $sku_tqty[$sku_option_key] = $tqty;
	// 			else $sku_tqty[$sku_option_key] = 0;
	// 			$sku_sku[$sku_option_key] = $sku_option_key;
	// 		}
	// 	}
	// }
	
	if(isset($sku_master) && is_array($sku_master)) 
	{
		sort($sku_master);
		// sksort($sku_master, 'date', true);
	}

	if(isset($supplier_master) && is_array($supplier_master)) ksort($supplier_master);

	$supplier_previous 		= '';
	$supplier_item_action 	= '';
	$first_page_yn 			= 'y';
	

// print_r($options_sku);exit;
	
	if($split_supplier_yn != 'no')
	{
		foreach($supplier_master as $key =>$supplier)
		{
			//if(($supplier_options == 'filter' && isset($supplier_login) && ($supplier_login != '') && (strtoupper($supplier) == strtoupper($supplier_login)))||$supplier_options == 'grey')
			if( (isset($supplier_login) && ($supplier_login != '') && (strtoupper($supplier) == strtoupper($supplier_login) ) ) || !isset($supplier_login) || $supplier_login == '')
			{
				
				// new page
				$total_quantity = 0;	
				$total_cost = 0;	
			
				if($first_page_yn == 'n') $page = $this->newPage();
				else $first_page_yn = 'n';

				$this->y = ($page_top-43);
			
				if($picklogo == 1)
				{					
					$packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $store);
					if($packlogo)
					{
						$packlogo = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_pack/' . $packlogo;
						if (is_file($packlogo)) {	
							$packlogo = Zend_Pdf_Image::imageWithPath($packlogo);			
							$page->drawImage($packlogo, 27, 784, 296, 825);
						}
					}
					$this->_setFont($page, $font_style_header, $font_size_header, $font_family_header, $non_standard_characters, $font_color_header);	
					if($from_shipment === true)
					{
						$page->drawText('Shipment-combined Pick List', 325, 810, 'UTF-8');		
					}
					else
					{
						$page->drawText('Order-combined Pick List', 325, 810, 'UTF-8');		
					}
					$page->drawText(Mage::helper('sales')->__('Supplier : '.$supplier), 325, 790, 'UTF-8');
					$page->setFillColor($font_color_header_zend);			
					$page->setLineColor($font_color_header_zend);
					$page->setLineWidth(0.5);			
					$page->drawRectangle(304, 784, 316, 825);
					// $this->y -= 10;
					$page->drawRectangle(27, $this->y, $padded_right, ($this->y - 1));
					$this->y -= 40;
				}
				else
				{
		   			$this->_setFont($page, $font_style_header, ($font_size_header+2), $font_family_header, $non_standard_characters, $font_color_header);
					if($from_shipment === true)
					{
						$page->drawText(Mage::helper('sales')->__('Shipment-combined Pick List, Supplier : '.$supplier), 31, 810, 'UTF-8');
					}
					else
					{
						$page->drawText(Mage::helper('sales')->__('Order-combined Pick List, Supplier : '.$supplier), 31, 810, 'UTF-8');
					}
					$page->setLineColor($font_color_header_zend);
					$page->setFillColor($font_color_header_zend);
					$page->drawRectangle(27, 803, $padded_right, 804);
				}

				$sku_test = array();
				// roll_SKU
				$page_count = 1;
				$grey_next_line = 0;
	
		// if(isset($roll) && $roll == 0) $roll = 1;
		// print_r($supplier_master);
		// print_r($sku_master);
		// print_r($sku_supplier);
		// echo '$supplier_login:'.$supplier_login;
		// if($roll == 1) exit;
		
				foreach($sku_master as $key =>$sku)
				{						
					if(!in_array($sku,$sku_test))
					{
						$sku_test[] = $sku;
		
						$supplier_item_action = 'keep';
					
						// if set to filter and a name and this is the name, then print
						if($supplier_options == 'filter' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login)) )//grey //split
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'filter' && isset($supplier_login) && ($supplier_login != '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login)) )//grey //split
						{
							$supplier_item_action = 'hide';
						}
						elseif($supplier_options == 'grey' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login)) )//grey //split
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'grey' && isset($supplier_login) && $supplier_login != '' && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login)) )//grey //split
						{
							// grey/login and this product is not same supplier as login
							$supplier_item_action = 'keepGrey';
							// echo '$supplier_options == grey && isset($supplier_login) && $supplier_login != \'\' && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login)) ::'.$supplier_options.' == \'grey\' && isset('.$supplier_login.') && '.$supplier_login.' != \'\' && (strtoupper('.$sku_supplier[$sku].') != strtoupper('.$supplier_login.')) ';
							// 					exit;
						}
						elseif($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier) ) )
						{
							$supplier_item_action = 'keepGrey';
						}
						elseif($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier) ) )
						{
							$supplier_item_action = 'hide';
						}
						elseif($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier) ) )
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier) ) )
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'grey') $supplier_item_action = 'keepGrey';
						elseif($supplier_options == 'filter') $supplier_item_action = 'hide';

	echo '$supplier:'.$supplier.' $supplier_options:'.$supplier_item_action.' sku:'.$sku.'<br />';

						if($supplier_item_action != 'hide' && $supplier_item_action != '')
						{
							// if ($this->y<15) $page = $this->newPage();
						
							if ($this->y<60)
							{
								if($page_count == 1)
								{
									$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
									$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - 15), 'UTF-8');
								}
								$page = $this->newPage();
								$page_count ++;
								$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
								$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
							}

							if($grey_next_line == 1)
							{
								$page->setFillColor($grey_bkg_color);			
								$page->setLineColor($grey_bkg_color);	
								$page->drawRectangle(25, ($this->y-2), $padded_right, ($this->y+9));
								$grey_next_line = 0;
							}
							else $grey_next_line = 1;
							$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

							// $page->setFillColor($black_color);
							if($supplier_item_action == 'keepGrey') 
							{
								$page->setFillColor($greyout_color);
							}
							else
							{
								$total_quantity = $total_quantity + $sku_qty[$sku] + $sku_qty_options[$sku];	
								if($from_shipment)
								{
									$total_cost = $total_cost + ($sku_qty[$sku] * $sku_cost[$sku]);	
								}
								else
								{
									$total_cost = $total_cost + ($sku_sqty[$sku] * $sku_cost[$sku]);	
								}
							
								if($tickbox != 'no')
								{
									$page->setFillColor($white_color);			
									$page->setLineColor($black_color);	
									if($sku_type[$sku] != 'bundle') $page->drawRectangle(27, ($this->y), 34, ($this->y+7));
									else $page->drawCircle(27, ($this->y), 7);
									$page->setFillColor($font_color_body_zend);
								}
							}
		
							$max_qty_length_display = 0;
						
	                        if($from_shipment){
								$max_qty_length_display = ((($max_qty_length+5)*($font_size_body*1.1)*1.35)-57);
	                            $qty_string = 's:'.(int)$sku_sqty[$sku] . ' / ts:'.$sku_tqty[$sku]. ' / o:'.$sku_qty[$sku];
	                        }else{
	                            $qty_string = $sku_qty[$sku];
								$max_qty_length_display = (($max_qty_length-1)*$font_size_body);
	                        }

							//
							$sku_addon = '';
							if($shelving_yn == 1 && $sku_shelving[$sku]) 
							{
								if($shelvingpos == 'col')
								{
									$page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
								}
								else
								{
									$sku_addon = ' / ['.$sku_shelving[$sku].']';
								}
							}
                        
							// shift sku over for big qty string
							//$max_qty_length
						
							$page->drawText($qty_string, $qtyX, $this->y , 'UTF-8');
							$page->drawText(' x   '.$sku.$sku_addon, ($skuX+$max_qty_length_display), $this->y , 'UTF-8');
							if($extra_yn == 1) $page->drawText($sku_extra[$sku], $extraX, $this->y , 'UTF-8');
							if($nameyn == 1)
							{
								$page->drawText($sku_name[$sku], intval($namenudge), $this->y , 'UTF-8');
							}
							// echo 'extra_attribute:'.$extra_attribute.' extra_yn:'.$extra_yn.' extraX:'.$extraX;exit;
						
							//
						
							// $page->drawText($sku_qty[$sku]. ' x ', $qtyX, $this->y , 'UTF-8');
							// $page->drawText($sku, $skuX, $this->y , 'UTF-8');
							// if($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y , 'UTF-8');
							// if($shelving_yn == 1 && $sku_shelving[$sku]) $page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
							if(($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1 && $supplier_item_action != 'keepGrey') 
							{
								$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
							
								$this->y -= 12;
								$page->setFillColor($red_bkg_color);			
								$page->setLineColor(new Zend_Pdf_Color_Rgb(1,1,1));	
								$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
								$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));				
								$warning = 'Stock Warning      SKU: '.$sku.'    Net Stock After All Picks : '.$sku_stock[$sku];
								$page->drawText($warning, 60, $this->y , 'UTF-8');
								$this->y -= 4;
							}
							$this->y -= ($font_size_body+2);
						}
					}
				}
			
				if($from_shipment === true)
				{
					$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
					$this->y -= 30;
					$page->drawText('Shipments included:', 30, $this->y , 'UTF-8');

					foreach ($shipment_list as $k => $value)
					{
						$this->y -= $font_size_body;
						$page->drawText('#'.$value.' (order #'.$order_list[$k].' : '.$name_list[$k].')', 30, $this->y , 'UTF-8');
					}
				}
			
				// end roll_SKU
				$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
				// $page->setFillColor(new Zend_Pdf_Color_Rgb(0,0,0));
				$this->y -= 30;
				// $this->_setFontItalic($page,12);
		
				if(($showcount_yn == 1) || $showcost_yn == 1)
				{
					$page->setFillColor($white_bkg_color);			
					$page->setLineColor($orange_bkg_color);	
					$page->setLineWidth(4);	
					$shipYbox = 0;
					if($from_shipment === true) $shipYbox = 10;
				
					$page->drawRectangle(355, ($this->y-34-$shipYbox), 568, ($this->y+10+$font_size_body));
					$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
				}
			
		
			
				if($showcount_yn == 1)
				{
					if($from_shipment === true)
					{
						$page->drawText('Total quantity ordered : '.$total_quantity, 375, $this->y , 'UTF-8');
						$this->y -= 20;
						$page->drawText('Total quantity shipped this time : '.$count, 375, $this->y , 'UTF-8');
					}
					else
					{
						$page->drawText('Total quantity : '.$total_quantity, 375, $this->y , 'UTF-8');
					}
				}
				$this->y -= 20;
				if($showcost_yn == 1)
				{
					if($from_shipment) $page->drawText('Total cost these shipments : '.$currency_symbol."  ".$total_cost, 375, $this->y , 'UTF-8');
					else $page->drawText('Total cost : '.$currency_symbol."  ".$total_cost, 375, $this->y , 'UTF-8');
				}
				$printdates = Mage::getStoreConfig('pickpack_options/messages/pickpack_packprint');
				if($printdates == 1)
				{
					$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-3), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
				
					// $this->_setFontBold($page);
					// $page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time()+(14*60*60)+(10*60))), 160, 18, 'UTF-8');
					$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time())), 160, 18, 'UTF-8');
				}
			}
			unset($sku_test);
		}
		//exit;
	}
	else
	{
			$this->y = ($page_top-43);//777;
			
			$total_quantity 	= 0;
			$total_cost 		= 0;	
			$sheet_has_bundles 	= false;
	
	
			if($picklogo == 1)
			{					
				$packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $store);
				if($packlogo)
				{
					$packlogo = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_pack/' . $packlogo;
					if (is_file($packlogo)) {	
						$packlogo = Zend_Pdf_Image::imageWithPath($packlogo);			
						$page->drawImage($packlogo, 27, ($page_top-36), 296, ($page_top+5));
					}
				}
				$this->_setFont($page, $font_style_header, $font_size_header, $font_family_header, $non_standard_characters, $font_color_header);	
				if($from_shipment === true)
				{
					$page->drawText('Shipment-combined Pick List', 325, ($page_top-10), 'UTF-8');		
				}
				else
				{
					$page->drawText('Order-combined Pick List', 325, ($page_top-10), 'UTF-8');		
				}
				// $page->drawText(Mage::helper('sales')->__('Supplier : '.$supplier), 325, 790, 'UTF-8');
				$page->setFillColor($font_color_header_zend);			
				$page->setLineColor($font_color_header_zend);
				$page->setLineWidth(0.5);			
				$page->drawRectangle(304, ($page_top-36), 316, ($page_top+5));
				// $this->y -= 10;
				$page->drawRectangle(27, $this->y, $padded_right, ($this->y - 1));
				$this->y -= 40;
			}
			else
			{
	   			$this->_setFont($page, $font_style_header, ($font_size_header+2), $font_family_header, $non_standard_characters, $font_color_header);
				if($from_shipment === true)
				{
					$page->drawText(Mage::helper('sales')->__('Shipment-combined Pick List'), 31, ($page_top-10), 'UTF-8');
				}
				else
				{
					$page->drawText(Mage::helper('sales')->__('Order-combined Pick List'), 31, ($page_top-10), 'UTF-8');
				}
				$page->setLineColor($font_color_header_zend);
				$page->setFillColor($font_color_header_zend);
				$page->drawRectangle(27, ($page_top-17), $padded_right, ($page_top-16));
			}


			// roll sku foreach
			// roll_SKU

			
			$max_qty_length_display = 0;

			if(!$from_shipment)
			{
				$max_qty_length_display = (($max_qty_length-2)*$font_size_body);
            }
			else
			{
				$max_qty_length_display = ((($max_qty_length+5)*($font_size_body*1.1)*1.4)-57);
			}
			
			// make sure values don't overlap qty and ' x '
			$skuXreal	= ($skuX+$max_qty_length_display);
			$xX = ($qtyX+10);
			if($skuXreal < ($xX + (2 * $font_size_body) + 5)) $skuXreal = ($xX + (2 * $font_size_body) + 5);
			if($extraX < ($xX + (2 * $font_size_body) + 5)) $extraX = ($xX + (2 * $font_size_body) + 5);
			if($namenudge < ($xX + (2 * $font_size_body) + 5)) $namenudge = ($xX + (2 * $font_size_body) + 5);
			if($skuXreal > $extraX)
			{
				$maxWidthSku = ($padded_right - $skuXreal + 20);
				
				// $max_chars = 20;
				$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
				$font_size_compare = ($font_size_body);
				$line_width = $this->parseString('1234567890',$font_temp,$font_size_compare); // bigger = left
				$char_width = $line_width/10;
				$max_chars = round($maxWidthSku/$char_width);
			}
			
		
			// if(!isset($sku_master)) $sku_master[] = 'Bundle';
			$sku_test = array();
			// print_r($sku_master);exit;
			
			$page_count = 1;
			$grey_next_line = 0;
			$thisYnext = 0;
			$thisYorig = 0;
			
			foreach($sku_master as $key =>$sku)
			{		
				// if($thisYnext != 0)
				// 				{
				// 					$this->y = $thisYnext;
				// 					//$thisYnext = 0;
				// 				}
				
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
				
				if(!in_array($sku,$sku_test))
				{
					$sku_test[] = $sku;
				
			
					$total_quantity = $total_quantity + $sku_qty[$sku];	
					if($from_shipment === true)
					{
						$total_cost = $total_cost + ($sku_cost[$sku]*$sku_sqty[$sku]);	
					}
					else
					{
						$total_cost = $total_cost + ($sku_cost[$sku]*$sku_qty[$sku]);	
					}
				
					if ($this->y<60)
					{
						if($page_count == 1)
						{
							$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - 15), 'UTF-8');
						}
						$page = $this->newPage();
						$page_count ++;
						$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
						$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
					}
					
					if($grey_next_line == 1)
					{
						$page->setFillColor($grey_bkg_color);			
						$page->setLineColor($grey_bkg_color);	
						$page->drawRectangle(25, ($this->y-($font_size_body/5)), $padded_right, ($this->y+($font_size_body*0.85)));
						$grey_next_line = 0;
					}
					else $grey_next_line = 1;
					$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
					
					// if ($this->y<15) $page = $this->newPage();
					if($tickbox != 'no' && !in_array($sku,$pickpack_options_count_filter_array))
					{
						$page->setFillColor($white_color);			
						$page->setLineColor($black_color);	
						if($sku_type[$sku] != 'bundle') $page->drawRectangle(27, ($this->y), 34, ($this->y+7));
						else 
						{
							$page->drawCircle(30.5, ($this->y+3.5), 3.5);
							$sheet_has_bundles = true;
						}
						// $page->drawRectangle(27, ($this->y), 34, ($this->y+7));
						$page->setFillColor($font_color_body_zend);
					}
					// $page->setFillColor(new Zend_Pdf_Color_Rgb(0,0,0));					

                    if($from_shipment){
                        // $qty_string = 's:'.(int)$sku_sqty[$sku] . ' / o:'.$sku_qty[$sku];
                        $qty_string = 's:'.(int)$sku_sqty[$sku] . ' / ts:'.$sku_tqty[$sku]. ' / o:'.$sku_qty[$sku];

                        // $skuXInc = 25;
                    }else{
                        $qty_string = $sku_qty[$sku];
                        // $skuXInc = 0;
                    }                   
									
					//
					$sku_addon = '';
					if($shelving_yn == 1 && $sku_shelving[$sku]) 
					{
						if($shelvingpos == 'col')
						{
							$page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
						}
						else
						{
							$sku_addon = ' / ['.$sku_shelving[$sku].']';
						}
					}
					
					$display_sku = '';
					$display_sku = htmlspecialchars_decode($sku_sku[$sku]);
					
					if(isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && ($options_yn == 'inline'))
					{
		                $display_sku .= ' '.$sku_order_id_options[$sku];
					}
				
				//	if(($options_yn == 'inline') || ($options_yn == 'newline')) $sku_order_id_options[$sku] .= htmlspecialchars_decode('[ '.$options['options'][$j]['label'].' : '.$options['options'][$j]['value'].' ]');
				
					// $skuXreal	= ($skuX+$max_qty_length_display);
					// 					$xX = ($qtyX+10);
					// 					if($skuXreal < ($xX + (2 * $font_size_body))) $skuXreal = ($xX + (2 * $font_size_body));
					
					$page->drawText($qty_string, $qtyX, $this->y , 'UTF-8');
					$page->drawText(' x', ($xX+$max_qty_length_display), $this->y , 'UTF-8');
					
					$displaySkuReal = $display_sku.$sku_addon;
					
					// if sku and extra attribute switched pos
					if(($extra_yn == 1) && $skuXreal > $extraX)
					{
						if(strlen($displaySkuReal) > $max_chars)
						{
							$chunks = str_split($displaySkuReal,$max_chars);
							
							$thisYorig = $this->y;
							$lines = 0;
							foreach($chunks as $key => $chunk)
							{
								if($grey_next_line == 0 && $lines > 0)
								{
									$page->setFillColor($grey_bkg_color);			
									$page->setLineColor($grey_bkg_color);	
									$page->drawRectangle(25, ($this->y-($font_size_body/5)), $padded_right, ($this->y+($font_size_body*1.1)));
									$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
									$page->drawText($chunk, ($skuXreal+8), $this->y , 'UTF-8');
								}
								else $page->drawText($chunk.'...', $skuXreal, $this->y , 'UTF-8');
								$this->y -= ($font_size_body+3);
								$lines ++;
							}
							$this->y = $thisYorig;
							
							unset($chunks);
						}
						else $page->drawText($displaySkuReal, $skuXreal, $this->y , 'UTF-8');
						
					}
					else $page->drawText($displaySkuReal, $skuXreal, $this->y , 'UTF-8');
		
					if($extra_yn == 1) $page->drawText($sku_extra[$sku], $extraX, $this->y , 'UTF-8');
					if($nameyn == 1)
					{
						if(isset($options_sku_parent) && is_array($options_sku_parent[$sku]))
						{
							$this->y -= ($font_size_body+3);
							if($grey_next_line == 0)
							{
								$page->setFillColor($grey_bkg_color);			
								$page->setLineColor($grey_bkg_color);	
								$page->drawRectangle(25, ($this->y-($font_size_body/5)), $padded_right, ($this->y+($font_size_body*1.1)));
								$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
							}
							$page->drawText('( '.$sku_name[$sku].' )', ($skuXreal+4), $this->y , 'UTF-8');
						}
						else $page->drawText($sku_name[$sku], $namenudge, $this->y , 'UTF-8');
					}
					
					$options_splits = array();
					if($options_yn == 'newline') 
					{
						$options_splits = explode('[',$sku_order_id_options[$sku]);
						$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
						
						foreach($options_splits as $key => $options_split)
						{
							$options_split = trim($options_split);
							if($options_split != '') $page->drawText('[ '.$options_split, ($skuXreal+4), $this->y , 'UTF-8');
							$this->y -= 12;
						}
						$this->_setFont($page, $font_style_body, ($font_size_body), $font_family_body, $non_standard_characters, $font_color_body);
						
					}
					elseif($options_yn == 'newskuparent')
					{
						$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
						
						//$options_sku_parent[$sku][$options_sku_single] = ($sku_qty[$sku]);
						if(is_array($options_sku_parent[$sku]))
						{
							ksort($options_sku_parent[$sku]);
						
							foreach($options_sku_parent[$sku] as $options_sku_single => $options_sku_qty)
							{
								$this->y -= 12;
								
								if ($this->y<70)
								{
									if($page_count == 1)
									{
										$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
										$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - ($font_size_subtitles * 2)), 'UTF-8');
									}
									$page = $this->newPage();
									$page_count ++;
									$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
									$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
								}
								
								$options_sku_single = trim($options_sku_single);
								$options_sku_qty = trim($options_sku_qty);
								
								if(!in_array($options_sku_single,$pickpack_options_count_filter_array)) $total_quantity = ($total_quantity + $options_sku_qty);
								
								if($grey_next_line == 0)
								{
									$page->setFillColor($grey_bkg_color);			
									$page->setLineColor($grey_bkg_color);	
									$page->drawRectangle(25, ($this->y-(($font_size_body-2)/2)), $padded_right, ($this->y+(($font_size_body-1)*1.1)));
									$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
								}
								
								if(!in_array($options_sku_single,$pickpack_options_count_filter_array))
								{
									if($tickbox != 'no' && !in_array($sku,$pickpack_options_count_filter_array))
									{
										$page->setFillColor($white_color);			
										$page->setLineColor($black_color);	
										$page->setLineWidth(0.5);			
										$page->drawRectangle(28, ($this->y-1), 34, ($this->y+5));
										$page->setFillColor($font_color_body_zend);
										$page->setLineWidth(1);			
									}
									
									$page->drawText($options_sku_qty, ($qtyX+4), $this->y , 'UTF-8');
									$page->drawText(' x', ($xX+$max_qty_length_display+4), $this->y , 'UTF-8');
								}
								if($options_sku_single != '') $page->drawText('[ '.$options_sku_single.' ]', ($skuXreal+4), $this->y , 'UTF-8');
								
								$sku_name[$options_sku_single] = trim(str_replace(array('[',']'),'',$sku_name[$options_sku_single]));
								$sku_name[$options_sku_single] = preg_replace('~^select ~i','',$sku_name[$options_sku_single]);
								$sku_name[$options_sku_single] = preg_replace('~^enter ~i','',$sku_name[$options_sku_single]);
								$sku_name[$options_sku_single] = preg_replace('~^would you Like to ~i','',$sku_name[$options_sku_single]);
								$sku_name[$options_sku_single] = preg_replace('~\((.*)\)~i','',$sku_name[$options_sku_single]);
								 
								if($nameyn == 1) $page->drawText($sku_name[$options_sku_single], ($namenudge+4), $this->y , 'UTF-8');
								
							}
						}
						$this->_setFont($page, $font_style_body, ($font_size_body), $font_family_body, $non_standard_characters, $font_color_body);
						
					}
					
					if(($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1) 
					{
						$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
						
						$this->y -= 12;
						$page->setFillColor($red_bkg_color);			
						$page->setLineColor(new Zend_Pdf_Color_Rgb(1,1,1));	
						$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
						$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));				
						$warning = 'Stock Warning      SKU: '.$sku.'    Net Stock After All Picks : '.$sku_stock[$sku];
						$page->drawText($warning, 60, $this->y , 'UTF-8');
						$this->y -= 4;
					}
					if(isset($lines) && $lines != 0)
					{
						$this->y -= ($lines*($font_size_body+3));
						$lines = 0;
						
						// $this->y = $thisYnext;
						// 					//$thisYnext = 0;
					}
					else $this->y -= ($font_size_body+3);
					
				}
			}
			// end roll_SKU
			$thisYbase = ($this->y-50);
				
			if($from_shipment === true)
			{
				
				$thisYbase = ($thisYbase + ($font_size_body*3));
				$this->y = $thisYbase;
				
				$box_top = $this->y;//($this->y+10+$font_size_body+1);
				$box_bottom = ($this->y-(($font_size_body+1)*(count($shipment_list)+1))-($font_size_body*2));
				$box_bottom_page2 = 0;
				
				if($box_bottom < 60) 
				{
					//echo '$page_top + (($box_bottom:'.$page_top.' + (('.$box_bottom;exit;
					// total height = box_top - box_bottom;
					if((($box_bottom * -1)+$box_bottom) < 0) $box_bottom_page2 = $box_top + ($box_bottom - 60);
					else $box_bottom_page2 = ($box_top - $box_bottom + 60);
	
					$box_bottom = 50;
				}
				$page->setFillColor($white_bkg_color);			
				$page->setLineColor($orange_bkg_color);	
				$page->setLineWidth(4);
				$page->drawRectangle(25, $box_bottom, 320, $box_top);
				
				$this->y -= ($font_size_body*2);
				
				$this->_setFont($page, 'bold', $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
				$page->drawText('Shipments included:', 35, $this->y , 'UTF-8');
				$this->y -= 5;
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

				$i = 0;
				foreach ($shipment_list as $k => $value)
				{
					if ($this->y<70)
					{
						if($page_count == 1)
						{
							$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
							$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y - ($font_size_subtitles * 2)), 'UTF-8');
						}
						$page = $this->newPage();
						$page_count ++;
						$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
						$page->drawText('-- '.Mage::helper('sales')->__('Page').' '.$page_count.' --', 250, ($this->y + 15), 'UTF-8');
												
						if($page_count > 1)
						{
							$page->setFillColor($white_bkg_color);			
							$page->setLineColor($orange_bkg_color);	
							$page->setLineWidth(4);
							$page->drawRectangle(25, $box_bottom_page2, 320, ($page_top-($font_size_body*2)));
							$this->y = ($page_top-($font_size_body*3));
						}					
					}
					
					$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);	
					
					$this->y -= ($font_size_body+1);
					
					$page->drawText('#'.$value.' ['.$name_list[$k].'] (order #'.$order_list[$k].')', 35, $this->y , 'UTF-8');
					$i ++;
				}
				$this->y += 30;
				$this->y += (15*$i);
			}
			
			$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
			
			if(($showcount_yn == 1) || ($showcost_yn == 1))
			{
				if($page_count == 1) $this->y = $thisYbase;
				// else $this->y = ($page_top-($font_size_body*2));
				
				$page->setFillColor($white_bkg_color);			
				$page->setLineColor($orange_bkg_color);	
				$page->setLineWidth(4);
				$shipYbox = 0;	
				if($from_shipment) $shipYbox = 45;
				if($sheet_has_bundles === true)  $shipYbox += ($font_size_subtitles+4);
				$page->drawRectangle(355, ($this->y-34-$shipYbox), ($padded_right-2), $this->y);
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
			}
			
			if($showcount_yn == 1)
			{			
				$this->y -= ($font_size_body*2);
				
				if($from_shipment === true)
				{
					//echo '$page_count:'.$page_count.' count:'.$count.' $this->y:'.$this->y;exit;
					$this->_setFont($page, 'bold', $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
					$page->drawText('Total quantity shipped this time : '.$count, 375, $this->y , 'UTF-8');
					$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
					$this->y -= 12;
					$page->drawText('Total quantity shipped all time : '.$count_all, 375, $this->y , 'UTF-8');
					$this->y -= 12;
					$page->drawText('Total quantity ordered : '.$total_quantity, 375, $this->y , 'UTF-8');
					$this->y -= 8;
					$page->drawText('---------------------------------------------------', 375, $this->y , 'UTF-8');
					$this->y -= 8;
					$page->drawText('Qty remaining to be shipped : '.($total_quantity-$count_all), 375, $this->y , 'UTF-8');
					
				}
				else
				{
					$page->drawText('Total quantity : '.$total_quantity, 375, $this->y , 'UTF-8');
				}
				
			
				
			}
			$this->y -= 20;
			if($showcost_yn == 1)
			{
				if($from_shipment) $page->drawText('Total cost these shipments : '.$currency_symbol."  ".$total_cost, 375, $this->y , 'UTF-8');
				else $page->drawText('Total cost : '.$currency_symbol."  ".($total_cost), 375, $this->y , 'UTF-8');
			}
		
			if($sheet_has_bundles === true)
			{
				// $this->y -= 8;
				$page->setFillColor($white_color);			
				$page->setLineColor($black_color);	
				$page->setLineWidth(0.5);
				$page->drawCircle(378.5, ($this->y+3.5), 3.5);
				$page->setFillColor($black_color);			
				$page->drawText('Key : Bundle Parent', 388.5, $this->y , 'UTF-8');
			}
			
			$printdates = Mage::getStoreConfig('pickpack_options/messages/pickpack_packprint');
			if($printdates==1)
			{
				$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles-3), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
				
				// $this->_setFontBold($page);
				// $page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time()+(14*60*60)+(10*60))), 160, 18, 'UTF-8');
				$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time())), 160, 18, 'UTF-8');
			}
	}
	

	$this->_afterGetPdf();

	return $pdf;	
	/**
	getPickCOMBINED Template - END
	*********************************************
	************************************************/
}












 /**
   getPickCSTOCK 22222 Template - START
    ************************************************/

public function getPickStock2($orders=array())
{
	
	
	/**
	 * get store id
	 */
	$storeId = Mage::app()->getStore()->getId();

	$this->_beforeGetPdf();
	$this->_initRenderer('invoices');

	$pdf = new Zend_Pdf();
	$this->_setPdf($pdf);
	$style = new Zend_Pdf_Style();
	
	$page_size = $this->_getConfig('page_size', 'a4', false,'general');
	
	if($page_size == 'letter') 
	{
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
		$page_top = 770;$padded_right = 587;
	}
	elseif($page_size == 'a4') 
	{
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$page_top = 820;$padded_right = 570;
	}
	elseif($page_size == 'a5landscape') 
	{
		$page = $pdf->newPage('596:421');					
		$page_top = 395;$padded_right = 573;
	}
	
	$pdf->pages[] = $page;
  

	$skuX = 67;
	$qtyX = 40;
	$productX = 250;
	$fontsize_overall = 15;
	$fontsize_productline = 9;
    $total_quantity = 0;
    $total_cost = 0;
	$red_bkg_color 			= new Zend_Pdf_Color_Html('lightCoral');
	$green_bkg_color = new Zend_Pdf_Color_Html('lightGreen');
	$grey_bkg_color			= new Zend_Pdf_Color_GrayScale(0.7);
	$dk_grey_bkg_color		= new Zend_Pdf_Color_GrayScale(0.3);//darkCyan
	$dk_cyan_bkg_color 		= new Zend_Pdf_Color_Html('darkCyan');//darkOliveGreen
	$dk_og_bkg_color 		= new Zend_Pdf_Color_Html('darkOliveGreen');//darkOliveGreen
	
	$white_bkg_color 		= new Zend_Pdf_Color_Html('white');
	$orange_bkg_color 		= new Zend_Pdf_Color_Html('Orange');
	
	$black_color 			= new Zend_Pdf_Color_Rgb(0,0,0);
	$grey_color 			= new Zend_Pdf_Color_GrayScale(0.3);
	$greyout_color 			= new Zend_Pdf_Color_GrayScale(0.6);
	$white_color 			= new Zend_Pdf_Color_GrayScale(1);
	
	$font_family_header_default = 'helvetica';
	$font_size_header_default = 16;
	$font_style_header_default = 'bolditalic';
	$font_color_header_default = 'darkOliveGreen';
	$font_family_subtitles_default = 'helvetica';
	$font_style_subtitles_default = 'bold';
	$font_size_subtitles_default = 15;
	$font_color_subtitles_default = '#222222';
	$background_color_subtitles_default = '#999999';
	$font_family_body_default = 'helvetica';
	$font_size_body_default = 10;
	$font_style_body_default = 'regular';
	$font_color_body_default = 'Black';

	$font_family_header = $this->_getConfig('font_family_header', $font_family_header_default, false,'general');
	$font_style_header = $this->_getConfig('font_style_header', $font_style_header_default, false,'general');
	$font_size_header = $this->_getConfig('font_size_header', $font_size_header_default, false,'general');
	$font_color_header = $this->_getConfig('font_color_header', $font_color_header_default, false,'general');
	$font_family_subtitles = $this->_getConfig('font_family_subtitles', $font_family_subtitles_default, false,'general');
	$font_style_subtitles = $this->_getConfig('font_style_subtitles', $font_style_subtitles_default, false,'general');
	$font_size_subtitles = $this->_getConfig('font_size_subtitles', $font_size_subtitles_default, false,'general');
	$font_color_subtitles = $this->_getConfig('font_color_subtitles', $font_color_subtitles_default, false,'general');
	$background_color_subtitles = $this->_getConfig('background_color_subtitles', $background_color_subtitles_default, false,'general');
	$font_family_body = $this->_getConfig('font_family_body', $font_family_body_default, false,'general');
	$font_style_body = $this->_getConfig('font_style_body', $font_style_body_default, false,'general');
	$font_size_body = $this->_getConfig('font_size_body', $font_size_body_default, false,'general');
	$font_color_body = $this->_getConfig('font_color_body', $font_color_body_default, false,'general');
		$background_color_subtitles_zend = new Zend_Pdf_Color_Html(''.$background_color_subtitles.'');	
	$font_color_header_zend = new Zend_Pdf_Color_Html(''.$font_color_header.'');	
	$font_color_subtitles_zend = new Zend_Pdf_Color_Html(''.$font_color_subtitles.'');	
	$font_color_body_zend = new Zend_Pdf_Color_Html(''.$font_color_body.'');
	
	$non_standard_characters 	= $this->_getConfig('non_standard_characters', 0, false,'general');
 	
	$shelvingpos = $this->_getConfig('shelvingpos', 'col', false,'general'); //col/sku
	// $address_country_yn = $this->_getConfig('address_country_yn', 1, false,'general'); //col/sku

	
	// header
	// _setFont($page, $font_style_header, $font_size_header, $font_family_header, $non_standard_characters, $font_color_header)	
	// subtitles
	// _setFont($page, $font_style_subtitles, $font_size_subtitles, $font_family_subtitles, $non_standard_characters, $font_color_subtitles)	
	// body
	// _setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body)	
	
	$product_id = NULL; // get it's ID
	$stock = NULL;
	$sku_stock = array();
	$sku_qty = array();
	$sku_cost = array();
	// pickpack_cost
	$showcost_yn_default		= 0;
	$showcount_yn_default		= 0;
	$currency_default			= 'USD';
	
	$shelving_yn_default		= 0;
	$shelving_attribute_default = 'shelf';
	$shelvingX_default 			= 200;
	$supplier_yn_default 		= 0;
	$supplier_attribute_default = 'supplier';
	$namenudgeYN_default		= 0;
	$stockcheck_yn_default 		= 0;
	$stockcheck_default 		= 1;
	
	$split_supplier_yn_default 	= 'no';
	$supplier_attribute_default = 'supplier';
	$supplier_options_default 	= 'filter';
	$supplier_login_default		= '';
	$tickbox_default			= 'no'; //no, pick, pickpack
	
	// $split_supplier_yn 			= $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
	$split_supplier_yn_temp 	= $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
	$split_supplier_options 	= $this->_getConfig('pickpack_split_supplier_options', 'no', false,'general');
	$split_supplier_yn = 'no';
	if($split_supplier_yn_temp == 1)
	{
		$split_supplier_yn = $split_supplier_options;
	}
	$supplier_attribute 		= $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false,'general');
	$supplier_options 			= $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false,'general');
	//$supplier_login 			= $this->_getConfig('pickpack_supplier_login', $supplier_login_default, false,'general');
	
	$userId 					= Mage::getSingleton('admin/session')->getUser()->getId();
    $user 						= Mage::getModel('admin/user')->load($userId);
	$username					= $user['username'];

	$supplier_login_pre 		= $this->_getConfig('pickpack_supplier_login', '', false,'general');
	$supplier_login_pre 		= str_replace(array("\n",','),';',$supplier_login_pre);
	$supplier_login_pre 		= explode(';',$supplier_login_pre);

	foreach($supplier_login_pre as $key => $value)
	{
		$supplier_login_single = explode(':',$value);
		if(preg_match('~'.$username.'~i',$supplier_login_single[0])) 
		{
			if($supplier_login_single[1] != 'all') $supplier_login = trim($supplier_login_single[1]);
			else $supplier_login = '';
		}
	}
	
	$tickbox 					= $this->_getConfig('pickpack_tickbox', $tickbox_default, false,'general');
	$picklogo 					= $this->_getConfig('pickpack_picklogo', 0, false,'general');
	
	$showcount_yn 		= $this->_getConfig('pickpack_count', $showcount_yn_default, false,'messages');

	$showcost_yn 		= $this->_getConfig('pickpack_cost', $showcost_yn_default, false,'messages');
	$currency	 		= $this->_getConfig('pickpack_currency', $currency_default, false,'messages');
	$currency_symbol 	= Mage::app()->getLocale()->currency($currency)->getSymbol();
	
	$stockcheck_yn 		= 1;//$this->_getConfig('pickpack_showstock_yn_combined', $stockcheck_yn_default, false,'messages');
	$stockcheck 		= $this->_getConfig('stock_stock', '0', false,'stock');

	$shelving_yn 		= $this->_getConfig('pickpack_shelving_yn_stock', $shelving_yn_default, false,'stock');
	$shelving_attribute = $this->_getConfig('pickpack_shelving_stock', $shelving_attribute_default, false,'stock');
	$shelvingX 			= $this->_getConfig('shelving_nudge', $shelvingX_default, true,'general');

	$nameyn 			= $this->_getConfig('pickpack_name_yn_combined', $namenudgeYN_default, false,'messages');
	$namenudge 			= $this->_getConfig('pickpack_namenudge_combined', $productX, true,'messages');
	
	foreach ($orders as $orderSingle) 
	{
		$order = Mage::getModel('sales/order')->load($orderSingle);	
			
		$order_id  = $order->getRealOrderId();

		if(!isset($order_id_master[$order_id])) $order_id_master[$order_id] = 0;		

		$itemsCollection =  $order->getAllVisibleItems();//$order->getItemsCollection();
        // $total_items     = count($itemsCollection);
		
		foreach ($itemsCollection as $item)
		{		
			if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
			{
				// any products actually go thru here?
				$sku = $item->getProductOptionByCode('simple_sku');
				$product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
			} 
			else 
			{
				$sku = $item->getSku();
				$product_id = $item->getProductId(); // get it's ID			
			}	
			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku', $sku, array('cost',$shelving_attribute,'name','simple_sku','qty'));			
			if(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
			
			
			
			$shelving = '';
			$supplier = '';
			
			if($shelving_yn == 1)
			{
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
						$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($shelving_attribute)) $shelving = $_newProduct->getData(''.$shelving_attribute.'');
				}
				elseif($product->getData(''.$shelving_attribute.''))
				{
						 $shelving = $product->getData($shelving_attribute);
				}
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($shelving_attribute))
				{
					$shelving = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($shelving_attribute);
				}
				elseif($product[$shelving_attribute]) $shelving = $product[$shelving_attribute];

				if(is_array($shelving)) $shelving = implode(',',$shelving);

				if(isset($sku_shelving[$sku]) && trim(strtoupper($sku_shelving[$sku])) != trim(strtoupper($shelving))) $sku_shelving[$sku] .= ','.trim($shelving);
				else $sku_shelving[$sku] = trim($shelving);
				$sku_shelving[$sku] = preg_replace('~,$~','',$sku_shelving[$sku]);
			}
			

			if($split_supplier_yn != 'no')
			{
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
						$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($supplier_attribute)) $supplier = $_newProduct->getData(''.$supplier_attribute.'');
				}
				elseif($product->getData(''.$supplier_attribute.''))
				{
						 $supplier = $product->getData($supplier_attribute);
				}
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($supplier_attribute))
				{
					$supplier = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($supplier_attribute);
				}
				elseif($product[$supplier_attribute]) $supplier = $product[$supplier_attribute];
				
				if(is_array($supplier)) $supplier = implode(',',$supplier);
				if(!$supplier) $supplier = '~Not Set~';	
				
				$supplier = trim(strtoupper($supplier));
				
				if(isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier) $sku_supplier[$sku] .= ','.$supplier;
				else $sku_supplier[$sku] = $supplier;
				$sku_supplier[$sku] = preg_replace('~,$~','',$sku_supplier[$sku]);
				
				if(!isset($supplier_master[$supplier])) $supplier_master[$supplier] = $supplier;

				$order_id_master[$order_id] .= ','.$supplier;
			}

						
			// qty in this order of this sku
			$qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();

			$sku_qty[$sku] = $qty;
		
			$sku_cost[$sku] = (is_object($product)?$product->getCost():0);
			// $total_cost = $total_cost + $cost;

			$sku_master[$sku] = $sku;
			if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id) && isset($configurable_names) && $configurable_names == 'simple')
			{
				$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
				if($_newProduct->getData('name')) $sku_name[$sku] = $_newProduct->getData('name');
			}
			else $sku_name[$sku] = $item->getName();
			
			$sku_stock[$sku] = $stock;
			
			if($sku_stock[$sku] < $stockcheck)
			{
				if(!isset($supplier_master[$supplier])) $supplier_master[$supplier] = $supplier;
				else $supplier_master[$supplier] .= ','.$supplier;
			}
	
		
		}
		
	}

	// $nitems = array();
	
	ksort($sku_master);
	
	ksort($supplier_master);
	$supplier_previous 		= '';
	$supplier_item_action 	= '';
	$first_page_yn 			= 'y';
	
	
	if($split_supplier_yn == 1)
	{
		foreach($supplier_master as $supplier =>$value)
		{
			$supplier_skip_order = FALSE;
		
			if($supplier_skip_order == FALSE)
			{
				// new page
	
				$total_quantity = 0;	
				$total_cost = 0;	
			
				if($first_page_yn == 'n') $page = $this->newPage();
				else $first_page_yn = 'n';

				// $page->setFillColor($dk_og_bkg_color);
				// 		$this->_setFontBold($page, 10);
				// 		$this->_setFontItalic($page,20);
				// 		$page->drawText(Mage::helper('sales')->__('Out-of-stock Pick List, Supplier : '.$supplier), 31, 810, 'UTF-8');
				// 		$page->setLineColor($dk_og_bkg_color);
				// 		$page->setFillColor($dk_og_bkg_color);
				// 		$page->drawRectangle(27, 803, $padded_right, 804);
			
				if($picklogo == 1)
				{					
					$packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $store);
					if($packlogo)
					{
						$packlogo = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_pack/' . $packlogo;
						if (is_file($packlogo)) {	
							$packlogo = Zend_Pdf_Image::imageWithPath($packlogo);			
							$page->drawImage($packlogo, 27, 784, 296, 825);
						}
					}
					$this->_setFont($page, $font_style_header, $font_size_header, $font_family_header, $non_standard_characters, $font_color_header);	
					$page->drawText('Out-of-stock Pick List', 325, 810, 'UTF-8');		
					$page->drawText(Mage::helper('sales')->__('Supplier : '.$supplier), 325, 790, 'UTF-8');
					$page->setFillColor($font_color_header_zend);			
					$page->setLineColor($font_color_header_zend);
					$page->setLineWidth(0.5);			
					$page->drawRectangle(304, 784, 316, 825);
					// $this->y -= 10;
					$page->drawRectangle(27, $this->y, $padded_right, ($this->y + 1));
					$this->y -= 40;
				}
				else
				{
		   			$this->_setFont($page, $font_style_header, ($font_size_header+2), $font_family_header, $non_standard_characters, $font_color_header);
					$page->drawText(Mage::helper('sales')->__('Out-of-stock Pick List, Supplier : '.$supplier), 31, 810, 'UTF-8');
					$page->setLineColor($font_color_header_zend);
					$page->setFillColor($font_color_header_zend);
					$page->setLineWidth(0.5);			
					$page->drawRectangle(27, 803, $padded_right, 804);
				}
			
		
				$this->y = 777;	        
				$sku_test = array();
				$outofstock_check = false;
			
				// roll_SKU
				foreach($sku_master as $key =>$sku)
				{		
					if(!in_array($sku,$sku_test) && ($sku_stock[$sku] < $stockcheck))
					{
						$sku_test[] = $sku;
						$outofstock_check = true;
					
						$supplier_item_action = 'keep';
						// if set to filter and a name and this is the name, then print
						if($supplier_options == 'filter' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login)) )//grey //split
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'filter' && isset($supplier_login) && ($supplier_login != '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login)) )//grey //split
						{
							$supplier_item_action = 'hide';
						}
						elseif($supplier_options == 'grey' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login)) )//grey //split
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'grey' && isset($supplier_login) && $supplier_login != '' && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login)) )//grey //split
						{
							$supplier_item_action = 'keepGrey';
						}
						elseif($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier) ) )
						{
							$supplier_item_action = 'keepGrey';
						}
						elseif($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier) ) )
						{
							$supplier_item_action = 'hide';
						}
						elseif($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier) ) )
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier) ) )
						{
							$supplier_item_action = 'keep';
						}
						elseif($supplier_options == 'grey') $supplier_item_action = 'keepGrey';
						elseif($supplier_options == 'filter') $supplier_item_action = 'hide';

						$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

						if($supplier_item_action != 'hide' && trim($supplier_item_action) != '')
						{
							if ($this->y<15) $page = $this->newPage();
							$page->setFillColor($black_color);
							if($supplier_item_action == 'keepGrey') 
							{
								$page->setFillColor($greyout_color);
							}
							else
							{
								
								$total_quantity = $total_quantity + ($stockcheck - $sku_stock[$sku]);//$sku_qty[$sku];	
								$total_cost = $total_cost + (($stockcheck - $sku_stock[$sku]) * $sku_cost[$sku]);	
							
							}
							// $page->drawText($sku_qty[$sku]. ' x ', $qtyX, $this->y , 'UTF-8');
							// 							$page->drawText($sku, $skuX, $this->y , 'UTF-8');
							// 							if($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y , 'UTF-8');
							// 							if($shelving_yn == 1 && $sku_shelving[$sku]) $page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
							// 							if(($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1 && $supplier_item_action != 'keepGrey') 
							// 							{
							// 								$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
							// 								
							// 								$this->y -= 12;
							// 								$page->setFillColor($red_bkg_color);			
							// 								$page->setLineColor(new Zend_Pdf_Color_Rgb(1,1,1));	
							// 								$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
							// 								$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));				
							// 								$warning = 'Stock Warning      SKU: '.$sku.'    Net Stock After All Picks : '.$sku_stock[$sku];
							// 								$page->drawText($warning, 60, $this->y , 'UTF-8');
							// 								$this->y -= 4;
							// 							}
							if ($this->y<15) $page = $this->newPage();
							$page->setFillColor($black_color);
							$page->drawText(($stockcheck - $sku_stock[$sku]). ' x ', $qtyX, $this->y , 'UTF-8');

							$page->drawText($sku, $skuX, $this->y , 'UTF-8');
							if($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y , 'UTF-8');
							if($shelving_yn == 1 && $sku_shelving[$sku]) $page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
							if(($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1) 
							{
								$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);

								$this->y -= 14;
								$page->setFillColor($red_bkg_color);			
								$page->setLineColor($red_bkg_color);	
								$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
								$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));				
								$warning = 'Stock Warning      SKU: '.$sku.'    Net Stock After All Picks : '.$sku_stock[$sku];
								$page->drawText($warning, 60, $this->y , 'UTF-8');

								$need = 'Need '.($stockcheck - $sku_stock[$sku]).' to restock to '.$stockcheck.' . . ('.$sku_qty[$sku].' going out in all picks)';
								$this->y -= 14;
								$page->setFillColor($green_bkg_color);	
								$page->setLineColor($green_bkg_color);
								$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
								$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));		
								$page->drawText($need, 60, $this->y , 'UTF-8');

								$this->y -= 4;

							}
							$this->y -= 12;
						}
					}// end if supplier_skip
				}
			}
			// end roll_SKU
			$page->setFillColor(new Zend_Pdf_Color_Rgb(0,0,0));
			$this->y -= 30;
			$this->_setFontItalic($page,12);
			
			if(($showcount_yn == 1) || $showcost_yn == 1)
			{
				$page->setFillColor($white_bkg_color);			
				$page->setLineColor($orange_bkg_color);	
				$page->setLineWidth(4);	
				$page->drawRectangle(355, ($this->y-34), 568, ($this->y+10+$font_size_body));
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
			}
			
			if($showcount_yn == 1)
			{
				$page->drawText('Total quantity : '.$total_quantity, 375, $this->y , 'UTF-8');
			}
			$this->y -= 20;
			if($showcost_yn == 1)
			{
				$page->drawText('Total cost : '.$currency_symbol."  ".$total_cost, 375, $this->y , 'UTF-8');
			}
			$printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_packprint');
			if($printdates!=1)
			{
				$this->_setFontBold($page);
				//$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time()+(14*60*60)+(10*60))), 210, 18, 'UTF-8');
$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time())), 210, 18, 'UTF-8');
			}
		}
	}
	else
	{
			$this->y = 777;
			
			$total_quantity = 0;
			$total_cost = 0;	
	
			if($picklogo == 1)
			{					
				$packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $store);
				if($packlogo)
				{
					$packlogo = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_pack/' . $packlogo;
					if (is_file($packlogo)) {	
						$packlogo = Zend_Pdf_Image::imageWithPath($packlogo);			
						$page->drawImage($packlogo, 27, 784, 296, 825);
					}
				}
				$this->_setFont($page, $font_style_header, ($font_size_header+4), $font_family_header, $non_standard_characters, $font_color_header);	
				$page->drawText('Out-of-stock Pick List', 325, 810, 'UTF-8');		
				$page->setFillColor($font_color_header_zend);			
				$page->setLineColor($font_color_header_zend);
				$page->setLineWidth(0.5);			
				$page->drawRectangle(304, 784, 316, 825);
				$page->drawRectangle(27, $this->y, $padded_right, ($this->y + 1));
				$this->y -= 40;
			}
			else
			{
	   			$this->_setFont($page, $font_style_header, ($font_size_header+4), $font_family_header, $non_standard_characters, $font_color_header);
				$page->drawText(Mage::helper('sales')->__('Out-of-stock Pick List'), 31, 810, 'UTF-8');
				$page->setLineColor($font_color_header_zend);
				$page->setFillColor($font_color_header_zend);
				$page->drawRectangle(27, 803, $padded_right, 804);
			}
			
			// roll sku foreach
			// roll_SKU
			foreach($sku_master as $key =>$sku)
			{		
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
				
				if(!in_array($sku,$sku_test) && ($sku_stock[$sku] < $stockcheck))
				{
					$sku_test[] = $sku;
				
					$total_quantity = $total_quantity + ($stockcheck - $sku_stock[$sku]);//$sku_qty[$sku];	
					$total_cost = $total_cost + (($stockcheck - $sku_stock[$sku]) * $sku_cost[$sku]);	
				
					if ($this->y<15) $page = $this->newPage();
					$page->setFillColor($black_color);
					$page->drawText(($stockcheck - $sku_stock[$sku]). ' x ', $qtyX, $this->y , 'UTF-8');
					
					$page->drawText($sku, $skuX, $this->y , 'UTF-8');
					if($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y , 'UTF-8');
					if($shelving_yn == 1 && $sku_shelving[$sku]) $page->drawText('['.$sku_shelving[$sku].']', $shelvingX, $this->y , 'UTF-8');
					if(($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1) 
					{
						$this->_setFont($page, $font_style_body, ($font_size_body-2), $font_family_body, $non_standard_characters, $font_color_body);
						
						$this->y -= 14;
						$page->setFillColor($red_bkg_color);			
						$page->setLineColor($red_bkg_color);	
						$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
						$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));				
						$warning = 'Stock Warning      SKU: '.$sku.'    Net Stock After All Picks : '.$sku_stock[$sku];
						$page->drawText($warning, 60, $this->y , 'UTF-8');
						
						$need = 'Need '.($stockcheck - $sku_stock[$sku]).' to restock to '.$stockcheck.' . . ('.$sku_qty[$sku].' going out in all picks)';
						$this->y -= 14;
						$page->setFillColor($green_bkg_color);	
						$page->setLineColor($green_bkg_color);
						$page->drawRectangle(55, ($this->y-4), $padded_right, ($this->y+10));
						$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));		
						$page->drawText($need, 60, $this->y , 'UTF-8');
						
						$this->y -= 4;
						
					}
					$this->y -= 12;
				}
			}
			// end roll_SKU
			$page->setFillColor(new Zend_Pdf_Color_Rgb(0,0,0));
			$this->y -= 30;
			$this->_setFontItalic($page,12);
			
			if(($showcount_yn == 1) || $showcost_yn == 1)
			{
				$page->setFillColor($white_bkg_color);			
				$page->setLineColor($orange_bkg_color);	
				$page->setLineWidth(4);	
				$page->drawRectangle(355, ($this->y-34), 568, ($this->y+10+$font_size_body));
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
			}
			
			if($showcount_yn == 1)
			{
				$page->drawText('Total quantity : '.$total_quantity, 375, $this->y , 'UTF-8');
			}
			$this->y -= 20;
			if($showcost_yn == 1)
			{
				$page->drawText('Total cost : '.$currency_symbol."  ".$total_cost, 375, $this->y , 'UTF-8');
			}
			$printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_packprint');
			if($printdates!=1)
			{
				$this->_setFontBold($page);
				//$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time()+(14*60*60)+(10*60))), 210, 18, 'UTF-8');
$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time())), 210, 18, 'UTF-8');
			}
	}
	
	$this->_afterGetPdf();

	return $pdf;	
	/**
	getPickSTOCK 22222 Template - END
	*********************************************
	************************************************/
}






 /**
   getLabel Template - START
    ************************************************/

	public function getLabel($orders=array())
	{
		
		
		/**
		 * get store id
		 */
		$storeId = Mage::app()->getStore()->getId();

        
/*
		Paper Keywords and paper size in points

		Letter		 612x792
		LetterSmall	 612x792
		Tabloid		 792x1224
		Ledger		1224x792
		Legal		 612x1008
		Statement	 396x612
		Executive	 540x720
		A0               2384x3371
		A1              1685x2384
		A2		1190x1684
		A3		 842x1190
		A4		 595x842
		A4Small		 595x842
		A5		 420x595
		B4		 729x1032
		B5		 516x729
		Envelope	 ???x???
		Folio		 612x936
		Quarto		 610x780
		10x14		 720x1008
*/
		$paper_width_default 					= 595;
		$paper_height_default 					= 842;

		// $top_left_x_default	 					= 40;
		// 	$top_left_y_default 					= 820; //1
		
		// top, right, bottom, left
		$paper_margin_default					= '22,30,22,30';
		$label_padding_default					= '5,5,5,5';

		$label_width_default					= 190; //3
		$label_height_default					= 168; //5

        $paper_margin = explode(",", $this->_getConfig('paper_margin', $paper_margin_default,false,'label'));               
        $label_padding = explode(",", $this->_getConfig('label_padding', $label_padding_default,false,'label'));               
	
		$paper_width = $this->_getConfig('paper_width', $paper_width_default, false,'label');
		$paper_height = $this->_getConfig('paper_height', $paper_height_default, false,'label');
	
		$label_height = $this->_getConfig('label_height', $label_height_default, false,'label');
		$label_width = $this->_getConfig('label_width', $label_width_default, false,'label');

		$top_left_x 							= ($paper_margin[1]+$label_padding[1]);//40;
		$top_left_y 							= ($paper_height-($paper_margin[0]+$label_padding[0])); //1

		$available_width = $label_width - $label_padding[1] - $label_padding[3];

		$show_order_id_yn = $this->_getConfig('label_show_order_id_yn', 1, false,'label');
		// $show_phone_yn = $this->_getConfig('label_show_phone_yn', 1, false,'label');

	
		$font_family_label_default = 'helvetica';
		$font_size_label_default = 15;
		$font_style_label_default = 'regular';
		$font_color_label_default = 'Black';

		$font_family_label = $this->_getConfig('font_family_label', $font_family_label_default, false,'label');
		$font_style_label = $this->_getConfig('font_style_label', $font_style_label_default, false,'label');
		$font_size_label = $this->_getConfig('font_size_label', $font_size_label_default, false,'label');
		$font_color_label = $this->_getConfig('font_color_label', $font_color_label_default, false,'label');

		$font_family_return_label_default = 'helvetica';
		$font_size_return_label_default = 9;
		$font_style_return_label_default = 'regular';
		$font_color_return_label_default = 'Black';

		$font_family_return_label = $this->_getConfig('font_family_return_label', $font_family_return_label_default, false,'label');
		$font_style_return_label = $this->_getConfig('font_style_return_label', $font_style_return_label_default, false,'label');
		$font_size_return_label = $this->_getConfig('font_size_return_label', $font_size_return_label_default, false,'label');
		$font_color_return_label = $this->_getConfig('font_color_return_label', $font_color_return_label_default, false,'label');
	
	 	$non_standard_characters 	= $this->_getConfig('non_standard_characters', 0, false,'general');
    
// 		$override_address_format_yn = $this->_getConfig('override_address_format_yn', 0,false,'general');
// 		// $address_country_yn 	= $this->_getConfig('address_country_yn', 1, false,'general'); //col/sku
// 
// 		$address_format_default = '{if company}{company},|{/if company}
// {if name}{name},|{/if name}
// {if street}{street},|{/if street}
// {if city}{city},|{/if city}
// {if region}{region},|{/if region}
// {if postcode}{postcode}{/if postcode}
// {country}
// {if telephone}|[ T: {telephone} ]{/if telephone}';
// 		
// 		$address_format 	= $this->_getConfig('address_format', '', false,'general'); //col/sku
// 		if($address_format == '') $address_format = $address_format_default;

		$override_address_format_yn = 1;//$this->_getConfig('override_address_format_yn', 0,false,'general');
		$customer_email_yn 			= $this->_getConfig('customer_email_yn', 0,false,'general');
		$customer_phone_yn 			= $this->_getConfig('customer_phone_yn', 0,false,'general');
		$address_format 			= $this->_getConfig('address_format', '', false,'general'); //col/sku
		$address_countryskip  		= $this->_getConfig('address_countryskip', 0,false,'general');
   		$return_address_yn     		= $this->_getConfig('label_return_address_yn', 0,false,'label');               
   		$return_address     		= $this->_getConfig('label_return_address', '',false,'label');    

		$capitalize_label_yn		= $this->_getConfig('capitalize_label_yn', 0,false,'general');   // o,usonly,1           

		$label_logo_yn       	= $this->_getConfig('label_logo_yn2', 0,false,'label');            
		$label_logo_image  		= $this->_getConfig('label_logo', '',false,'label');            
		$label_logo_xy	        = explode(",", $this->_getConfig('label_nudgelogo', '0,0',false,'label'));     //0,0
		$label_logo_text_xy		= explode(",", $this->_getConfig('label_nudgelogo_text', '0,0',false,'label'));     //0,0
		// $labellogo = Mage::getStoreConfig('pickpack_options/label/logo_label', $store);

		$boldLastAddressLineYNDefault 	= 'Y';
		$fontsizeTitlesDefault			= 14;
		$fontColorGrey					= 0.6;
		$fontColorReturnAddressFooter 	= 0.2;
		$boxBkgColorDefault				= 0.7;
		
		$red_bkg_color 			= new Zend_Pdf_Color_Html('lightCoral');
		$grey_bkg_color			= new Zend_Pdf_Color_GrayScale(0.7);
		$dk_grey_bkg_color		= new Zend_Pdf_Color_GrayScale(0.3);//darkCyan
		$dk_cyan_bkg_color 		= new Zend_Pdf_Color_Html('darkCyan');//darkOliveGreen
		$dk_og_bkg_color 		= new Zend_Pdf_Color_Html('darkOliveGreen');//darkOliveGreen
		$black_color 			= new Zend_Pdf_Color_Rgb(0,0,0);
		$grey_color 			= new Zend_Pdf_Color_GrayScale(0.3);
		$greyout_color 			= new Zend_Pdf_Color_GrayScale(0.6);
		$white_color 			= new Zend_Pdf_Color_GrayScale(1);

		$boldLastAddressLineYN		= $boldLastAddressLineYNDefault;
		$fontsizeTitles 			= $fontsizeTitlesDefault;
		$boxBkgColor				= $boxBkgColorDefault;

		$split_supplier_yn_default 	= 0;
		$supplier_attribute_default = 'supplier';
		$supplier_options_default 	= 'filter';
		$supplier_login_default		= '';

		$this->_beforeGetPdf();
		$this->_initRenderer('invoices');
		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		
		$page_size = $this->_getConfig('page_size', 'a4', false,'general');

		if($page_size == 'letter') 
		{
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
			$page_top = 770;$padded_right = 587;
		}
		elseif($page_size == 'a4') 
		{
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
			$page_top = 820;$padded_right = 570;
		}
		elseif($page_size == 'a5landscape') 
		{
			$page = $pdf->newPage('596:421');					
			$page_top = 395;$padded_right = 573;
		}
		
		$pdf->pages[] = $page;
           // $current_y = $page->getHeight();
	
		$current_column = 0;
		$current_row 	= 0;
		$address_count	= 0;
		$count			= 0;
		
		$current_x = $top_left_x;
		$current_y = $top_left_y;
		
		$temp_y = $current_y;
		$temp_x = $current_x;
		
	
			foreach ($orders as $orderSingle) 
			{
				$fontsize_adjust = 0;
					
				if($address_count > 0)
				{
					// going top left down, then across
					// if last label bigger than 1 label, start on fresh
					if(($current_y - $temp_y) > ($label_height-$label_padding[0]-$label_padding[3])) $current_y = ($current_y - $label_height);
					if(($temp_y - $label_height - $paper_margin[3]) < 0) 
					{
						$current_y = $top_left_y;
						if(($current_x + $label_width + $paper_margin[2]) > $paper_width)
						{
							$page = $this->newPage();
							$current_x = $top_left_x;
						}
						else {$current_x += $label_width;}
					}
					else {$current_y -= $label_height;}
				}
				$address_count ++;
				
				$temp_y = $current_y;
				$temp_x = $current_x;
				
				$order = Mage::getModel('sales/order')->load($orderSingle);		
				$order_id  = $order->getRealOrderId();
				$itemsCollection =  $order->getAllVisibleItems();
			
				$customer_email = '';
				if($order->getCustomerEmail()) $customer_email = trim($order->getCustomerEmail());
				$customer_phone = '';
				if($order->getShippingAddress()->getTelephone()) $customer_phone = trim($order->getShippingAddress()->getTelephone());
			
				// if($override_address_format_yn == 1)
				// 				{
					$addy = array();
					$addy['company'] = $order->getShippingAddress()->getCompany();
					$addy['prefix'] = $order->getShippingAddress()->getPrefix();
					$addy['suffix'] = $order->getShippingAddress()->getSuffix();
					$addy['name'] = $order->getShippingAddress()->getName();
					$i = 0;
					while($i < 10)
					{
						if($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i)))
						{
							if(isset($addy['street'])) $addy['street'] .= ", \n";
							else $addy['street'] = '';
							$addy['street'] .= $order->getShippingAddress()->getStreet($i);
						}
						$i ++;
					}
					if($addy['street'] != '') $addy['street'] .= ',';
					
					$addy['city'] = $order->getShippingAddress()->getCity();
					$addy['postcode'] = strtoupper($order->getShippingAddress()->getPostcode());
					$addy['region'] = $order->getShippingAddress()->getRegion();
					$addy_country = Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId());
					$addy['country'] = $addy_country;					
					if(trim($address_countryskip) != '') $addy['country'] = trim(str_ireplace($address_countryskip,'',$addy['country']));
					$address_format_set = str_replace(array("\n",'<br />','<br/>',"\r"),'',$address_format);

					foreach($addy as $key =>$value)
					{
						$value = trim($value);
						$key = trim($key);

						$value = preg_replace('~\,$~','',$value);
						$value = str_replace(',,',',',$value);
						
						if(($capitalize_label_yn == 1) || (($capitalize_label_yn == 'usonly') && (strtolower($addy_country) == 'united states')))
						{
							$value = trim(strtoupper($value));
							// remove line-end commas for USPS
							if(strtolower($addy_country) == 'united states') $value = preg_replace('~,$~','',$value);
							$fontsize_adjust = 2;
						}
						
						if($value != '' && !is_array($value))
						{					
							preg_match('~\{if '.$key.'\}(.*)\{\/if '.$key.'\}~Uis',$address_format_set,$if_contents);
							if(isset($if_contents[1])) $if_contents[1] = str_replace('{'.$key.'}',$value,$if_contents[1]);
							$address_format_set = str_replace($if_contents[0],$if_contents[1],$address_format_set);
							$address_format_set = str_replace('{'.$key.'}',$value,$address_format_set);
						}
						elseif(!is_array($value))
						{
							$address_format_set = preg_replace('~\{if '.$key.'\}(.*)\{/if '.$key.'\}~Uis','',$address_format_set);	
							$address_format_set = str_replace('{'.$key.'}','',$address_format_set);
						}	
					}
					
					$address_format_set = str_replace(array('||','|'),'|',trim($address_format_set));
					$address_format_set = str_replace(
						array("\t",'	','	','	','   ','  ',',.','.,','␣,',' ,',',,','ENGLAND'),
						array(', ',', ',' ',' ',' ',' ',',',',',',',',',',',''),
						trim($address_format_set));
					
					$address_format_set = str_replace('|,|','|',$address_format_set);
					$address_format_set = trim(str_replace('|',"\n",trim($address_format_set)));

					$shippingAddressArray = explode("\n",$address_format_set);
					$count = (count($shippingAddressArray)-1);
			

				$addressLine = '';
				$line_height = (1*$font_size_label);
				
				$stop_address = FALSE;
				
				
				if($label_logo_yn == 1)
				{
					// 						{
					if ($order->getStoreId()) {$store=$order->getStoreId();}
					else $store = null;
					
					if($label_logo_image)
					{
						$labellogo = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo_label/' . $label_logo_image;
						if (is_file($labellogo)) 
						{	
							$labellogo = Zend_Pdf_Image::imageWithPath($labellogo);	
							
							$height = ($label_height*0.9);
							$width  = ($label_width*0.9);
							
							$page->drawImage($labellogo, ($current_x+$label_logo_xy[0]), ($current_y+$label_logo_xy[1]-$height), ($current_x+$label_logo_xy[0]+$width), ($current_y+$label_logo_xy[1]));
						
						}
					}
				}
				
				if($show_order_id_yn == 1)
				{
					$this->_setFont($page, $font_style_label, ($font_size_label-2), $font_family_label, $non_standard_characters, 'lightGrey');
					$page->drawText('#'.$order_id, $temp_x, $temp_y, 'UTF-8');
					$temp_y -= ($line_height*0.2);
					$page->setLineWidth(1);
					$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));
					$page->drawLine($temp_x, $temp_y, ($temp_x + ($label_width*0.9)), ($temp_y-1));
					$temp_y -= ($line_height*0.2);
				}
				$this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);
				
					$i = 0;
					$line_height_top = (1.07*$font_size_label);
					$line_height_bottom = (1.05*$font_size_label);
					$i_space = 0;
					
					
				
					$shipping_line_count = (count($shippingAddressArray)-1);
					$line_addon=0;
					$token_addon = 0;
					
					foreach($shippingAddressArray as $i =>$value)
					{
						$skip = 0;
						
						$line_bold = 0;
						if($i == $shipping_line_count) 
						{
							$line_bold = 1;
							$value = preg_replace('~,$~','',$value);
						}
						$value = trim($value);
				

						$i_space = ($i_space+1);
						if($i > 0) 
						{
							if((trim($shippingAddressArray[($i-1)])) == '') $i_space = ($i_space-1);
						}
						$this->_setFont($page, $font_style_label, ($font_size_label-$fontsize_adjust), $font_family_label, $non_standard_characters, $font_color_label);
						if($line_bold == 1) 
						{
							$this->_setFont($page, 'bold', ($font_size_label+2-$fontsize_adjust), $font_family_label, $non_standard_characters, $font_color_label);
							
							$line_addon = ($font_size_label*0.1);
						}
						// else $this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);


						$max_chars = 20;
						$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
						$font_size_compare = ($font_size_label);
						$line_width = $this->parseString('1234567890',$font_temp,$font_size_compare); // bigger = left
						$char_width = $line_width/10;
						$max_chars = round(($label_width*0.9)/$char_width);
						
						// wordwrap characters
						$value = wordwrap($value, $max_chars, "\n", false);
					
						$address_line_x = $temp_x;
						$address_line_y = $temp_y;

						if($label_logo_yn == 1)
						{
							$address_line_x = ($temp_x + $label_logo_text_xy[0]);
							$address_line_y = ($temp_y + $label_logo_text_xy[1]);					
						}
						
						$token = strtok($value, "\n");
						while ($token != false) 
						{
							$page->drawText(trim($token), $address_line_x, ($address_line_y-($line_height_top*$i_space)-$line_addon-$token_addon), 'UTF-8');
							$token_addon += $font_size_label;
							$token = strtok("\n");
						}
						$i_space = ($i_space-1);
					
						$i ++;
					}
					$subheader_start = round(($temp_y-($line_height_top*($i_space+1))-$line_addon));

					if($customer_phone_yn != 'no' && $customer_phone != '')
					{
						$i_space += 1;
						$value = 'T: '.$customer_phone;
						$line_addon=($font_size_label*0.5);								

						if($override_address_format_yn != 1)
						{
							$top_phone_Y = $subheader_start;
							$bottom_phone_Y = $subfooter_start;
						}
						else 
						{
							$top_phone_Y = ($temp_y-($line_height_top*$i_space)-$line_addon-$token_addon);
							$bottom_phone_Y = ($temp_y-($line_height_bottom*$i_space)-$line_addon-$token_addon);
						}
						
						$address_line_x = $temp_x;
						$address_line_y = $bottom_phone_Y;

						if($label_logo_yn == 1)
						{
							$address_line_x = ($address_line_x + $label_logo_text_xy[0]);
							$address_line_y = ($address_line_y + $label_logo_text_xy[1]);					
						}

						$this->_setFont($page, 'regular', ($font_size_label-2), $font_family_label, $non_standard_characters, 'Gray');
						if($customer_phone_yn != 'yesdetails') $page->drawText($value, $address_line_x, $address_line_y, 'UTF-8');

						$subheader_start -= ($subheader_start - $top_phone_Y);
					}

					if($customer_email_yn != 'no' && $customer_email != '')
					{
						$i_space += 1;
						$value = 'E: '.$customer_email;
						$line_addon=($font_size_label*0.5);								

						if($override_address_format_yn != 1)
						{
							$top_email_Y = $subheader_start-($font_size_label*1.1);
							$bottom_email_Y = $subfooter_start-($font_size_label*1.1);
						}
						else 
						{
							$top_email_Y = ($temp_y-($line_height_top*$i_space)-$line_addon-$token_addon);
							$bottom_email_Y = ($temp_y-($line_height_bottom*$i_space)-$line_addon-$token_addon);
						}

						$address_line_x = $temp_x;
						$address_line_y = $bottom_email_Y;

						if($label_logo_yn == 1)
						{
							$address_line_x = ($address_line_x + $label_logo_text_xy[0]);
							$address_line_y = ($address_line_y + $label_logo_text_xy[1]);					
						}

						$this->_setFont($page, 'regular', ($font_size_label-2), $font_family_label, $non_standard_characters, 'Gray');
						if($customer_email_yn != 'yesdetails') $page->drawText($value, $address_line_x, $address_line_y, 'UTF-8');

						$subheader_start -= ($subheader_start - $top_email_Y);
					}
				// }								
		}	
				
		$i = 0;
		if($return_address_yn == 1)
		{
			while($i < $address_count)
			{
					// going top left down, then across
					// if last label bigger than 1 label, start on fresh
			
				if(($current_y - $temp_y) > $label_height) $current_y = ($current_y - $label_height);
				if(($temp_y - $label_height) < 0) 
				{
					$current_y = $top_left_y;
					if(($current_x + $label_width) > $paper_width)
					{
						$page = $this->newPage();
						$current_x = $top_left_x;
					}
					else {$current_x += $label_width;}
				}
				else {$current_y -= $label_height;}
				
				
				$temp_y = $current_y;
				$temp_x = $current_x; 
				
				// footer store address
				$this->_setFont($page, $font_style_return_label, ($font_size_return_label+2), $font_family_return_label, $non_standard_characters, $font_color_return_label);
				$page->drawText('From :', $temp_x, $temp_y, 'UTF-8');
				$this->_setFont($page, $font_style_return_label, $font_size_return_label, $font_family_return_label, $non_standard_characters, $font_color_return_label);
				$line_height = 15;
				foreach (explode("\n", $return_address) as $value)
				{
					if ($value!=='')
					{
						$page->drawText(trim(strip_tags($value)), $temp_x, ($temp_y-$line_height), 'UTF-8');	
						$line_height = ($line_height + $font_size_return_label);
					}
				}
			
			
				$i ++;
			}
		}
							
		$this->_afterGetPdf();
		return $pdf;
    }

		
	/**
	getLabel Template - END
	*********************************************
	************************************************/





 /**
   getCSVPickSeparated 2222222  Template - START
    ************************************************/

public function getCsvPickSeparated2($orders=array(), $from_shipment = false)
{
    #die($from_shipment);
	$debug = 0;

    $total_quantity = 0;
    $total_cost = 0;
	
	$column_headers = '';
	$column_separator = ',';
	$column_map = array();

	$column_mapping_pre = $this->_getConfig('column_mapping', '', false,'csvpick'); //col/sku
	
	$column_mapping_pre = trim(str_ireplace(array("\n","\r","\t"),'',$column_mapping_pre));
	
	$column_mapping_array = explode(';',$column_mapping_pre);
	
	foreach($column_mapping_array as $key => $value)
	{
		$column_mapping_sub_array = explode(':',$value);
		
		if(trim($key) != '')
		{
			$key = trim($column_mapping_sub_array[0]);
			if(isset($column_mapping_sub_array[1])) $value = trim($column_mapping_sub_array[1]);
			
			$column_map[$key] = $value;
			$column_headers .= $key.$column_separator;
		}
		
		$key = '';
		$value = '';
	}
		
	$column_headers = preg_replace('~,$~',"\n",$column_headers);
	
	$csv_output = $column_headers;
		
	$configurable_names = $this->_getConfig('pickpack_configname_separated', 'simple', false,'picks'); //col/sku
	$configurable_names_attribute = trim($this->_getConfig('pickpack_configname_attribute_separated', '', false,'picks')); //col/sku
	if($configurable_names != 'custom') $configurable_names_attribute = '';
	
	$product_id = NULL; // get it's ID
	$stock = NULL;
	$sku_stock = array();

	$product_id 				= NULL; // get it's ID
	$stock 						= NULL;
	
	$options_yn_base 		= $this->_getConfig('separated_options_yn', 0, false,'picks'); // no, inline, newline
	$options_yn 		= $this->_getConfig('separated_options_yn', 0, false,'picks'); // no, inline, newline
	if($options_yn_base == 0) $options_yn = 0;
	$pickpack_options_filter_yn = $this->_getConfig('separated_options_filter_yn', 0, false,'picks');
	$pickpack_options_filter 	= $this->_getConfig('separated_options_filter', 0, false,'picks');
	$pickpack_options_filter_array = array();
	if($pickpack_options_filter_yn == 0) $pickpack_options_filter = '';
	elseif(trim($pickpack_options_filter) != '')
	{
		$pickpack_options_filter_array = explode(',',$pickpack_options_filter);
		foreach($pickpack_options_filter_array as $key => $value)
		{
			$pickpack_options_filter_array[$key] = trim($value);
		}
	}
	$pickpack_options_count_filter_array = array();
	$pickpack_options_count_filter 	= $this->_getConfig('separated_options_count_filter', 0, false,'picks');
	if($pickpack_options_filter_yn == 0) $pickpack_options_count_filter = '';
	elseif(trim($pickpack_options_count_filter) != '')
	{
		$pickpack_options_count_filter_array = explode(',',$pickpack_options_count_filter);
		foreach($pickpack_options_count_filter_array as $key => $value)
		{
			$pickpack_options_count_filter_array[$key] = trim($value);
		}
	}
	
	$skuXInc = 0;
	
	/**
	 * get store id
	 */
	$storeId = Mage::app()->getStore()->getId();

	$order_id_master = array();
	$sku_order_suppliers = array();
	$sku_shelving = array();
	$sku_shipping_address = array();
	$sku_order_id_options = array();
	$sku_bundle = array();
	$address_data = array();
	
	foreach ($orders as $orderSingle) 
	{
		$order = Mage::getModel('sales/order')->load($orderSingle);		
		$putOrderId = $order->getRealOrderId();
		$order_id  = $putOrderId;

			$if_contents = array();

			$address_data[$order_id]['order_date'] = '';
			if($order->getCreatedAtStoreDate()) 
			{
				$address_data[$order_id]['order_date'] 		= date('m/d/Y',strtotime($order->getCreatedAtStoreDate()));
				$address_data[$order_id]['order_date_plus48h'] = date('m/d/Y',( strtotime($order->getCreatedAtStoreDate()) + (60*60*48)) );
			}

			// $websiteName = Mage::app()->getWebsite()->getName();
			// $storeName   = Mage::app()->getStore()->getName();
			$address_data[$order_id]['store'] 				= $order->getStore()->getGroup()->getName();
			$address_data[$order_id]['website'] 			= $order->getStore()->getWebsite()->getName();//->getStore()->getGroup()->getName();

			if(trim($order->getCustomerId()) != '' && !isset($address_data[$order_id]['customer_id'])) $address_data[$order_id]['customer_id'] 		= trim($order->getCustomerId());
			
			$address_data[$order_id]['ship-firstname'] 		= $order->getShippingAddress()->getFirstname();
			$address_data[$order_id]['ship-lastname'] 		= $order->getShippingAddress()->getLastname();
			$address_data[$order_id]['ship-companyname'] 	= $order->getShippingAddress()->getCompany();
			$address_data[$order_id]['ship-street1'] 		= '';
			if($order->getShippingAddress()->getStreet(1)) 
			{
				$address_data[$order_id]['ship-street1'] 	= $order->getShippingAddress()->getStreet(1);
			}
			$address_data[$order_id]['ship-street2'] 		= '';
			if($order->getShippingAddress()->getStreet(2)) 
			{
				$address_data[$order_id]['ship-street2'] 	= $order->getShippingAddress()->getStreet(2);
			}				

			$address_data[$order_id]['ship-city'] 			= $order->getShippingAddress()->getCity();
			$address_data[$order_id]['ship-region'] 		= $order->getShippingAddress()->getRegion();
			$address_data[$order_id]['ship-region-code'] 	= $order->getShippingAddress()->getRegionCode();
			$address_data[$order_id]['ship-postcode'] 		= strtoupper($order->getShippingAddress()->getPostcode());
			$address_data[$order_id]['ship-country'] 		= $order->getShippingAddress()->getCountry();//	= $addy['country'];
			$address_data[$order_id]['ship-region-filtered'] = '';
			if(trim(strtolower($address_data[$order_id]['ship-country'])) != 'us') 
			{
				$address_data[$order_id]['ship-region-filtered'] 	= $address_data[$order_id]['ship-region-code'];//'Y';
			}
			$address_data[$order_id]['ship-telephone'] 		= $order->getShippingAddress()->getTelephone();
			$address_data[$order_id]['ship-email'] 			= '';//trim($order->getCustomerEmail());
			
			//
			$address_data[$order_id]['bill-firstname'] 		= $order->getBillingAddress()->getFirstname();
			$address_data[$order_id]['bill-lastname'] 		= $order->getBillingAddress()->getLastname();
			$address_data[$order_id]['bill-companyname'] 	= $order->getBillingAddress()->getCompany();
			$address_data[$order_id]['bill-street1'] 		= '';
			if($order->getBillingAddress()->getStreet(1)) 
			{
				$address_data[$order_id]['bill-street1'] 	= $order->getBillingAddress()->getStreet(1);
			}
			$address_data[$order_id]['bill-street2'] 		= '';
			if($order->getBillingAddress()->getStreet(2)) 
			{
				$address_data[$order_id]['bill-street2'] 	= $order->getBillingAddress()->getStreet(2);
			}					
			$address_data[$order_id]['bill-city'] 			= $order->getBillingAddress()->getCity();
			$address_data[$order_id]['bill-region'] 		= $order->getBillingAddress()->getRegion();
			$address_data[$order_id]['bill-region-code'] 	= $order->getBillingAddress()->getRegionCode();
			$address_data[$order_id]['bill-postcode'] 		= strtoupper($order->getBillingAddress()->getPostcode());
			$address_data[$order_id]['bill-country'] 		= $order->getBillingAddress()->getCountry();//	= $addy['country'];
			$address_data[$order_id]['bill-region-filtered'] = '';
			if(trim(strtolower($address_data[$order_id]['bill-country'])) != 'us') 
			{
				$address_data[$order_id]['bill-region-filtered'] 	= $address_data[$order_id]['bill-region-code'];//'Y';
			}
			$address_data[$order_id]['bill-telephone'] 		= $order->getBillingAddress()->getTelephone();
			$address_data[$order_id]['bill-email'] 			= trim($order->getCustomerEmail());
			
		if(!isset($order_id_master[$order_id])) $order_id_master[$order_id] = 0;		
		
		$itemsCollection =  $order->getAllVisibleItems();//$order->getItemsCollection();
       
		if(!isset($productXInc)) $productXInc = 0;
	
		$product_build_item = array();
		$product_build = array();
		$coun = 1;

		foreach ($itemsCollection as $item)
		{			
			if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
			{
				// any products actually go thru here?
				$sku = $item->getProductOptionByCode('simple_sku');
				$product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
			} 
			else 
			{
				$sku = $item->getSku();
				$product_id = $item->getProductId(); // get it's ID			
			}	
		
			// if show options as counted groups
			if($options_yn == 1)
			{
				$full_sku = trim($item->getSku());	
				$parent_sku = preg_replace('~\-(.*)$~','',$full_sku);
				$sku = $parent_sku;	
				$product_build_item[] = $sku;//.'-'.$coun;	
				$product_build[$sku]['sku'] = $sku;			
				$product_sku = $sku;
				$product_build[$sku]['sku'] = $product_sku;		
			}
			else
			{
				// unique item id
				$product_build_item[] = $sku.'-'.$coun;	
				$product_build[$sku.'-'.$coun]['sku'] = $sku;			
				$product_sku = $sku;
				$sku = $sku.'-'.$coun;
				$product_build[$sku]['sku'] = $product_sku;
			}
			
			// 		
		    $options = $item->getProductOptions();

			$shelving = '';
			$supplier = '';
			$sku_colors_sizes = array();
			
			// if(!function_exists('search'))
			// 		{
			// 			function search($array, $key='', $value='')
			// 			{
			// 				$results = array();
			// 				
			// 			    if (is_array($array))
			// 			    {
			// 			        
			// 					if ($array[$key] == $value)
			// 			        {
			// 				    	$results[] = $array;
			// 					}
			// 			        foreach ($array as $subarray)
			// 					{
			// 			            $results = array_merge($results, search($subarray, $key, $value));
			// 					}
			// 			    }
			// 				
			// 
			// 			    return $results;
			// 			}
			// 		}
			
			$attribute_array = array('color','size','style','siennastyle','season');
			foreach($attribute_array as $key => $value)
			{
				$shelving_attribute = $value;
				$sku_color[$order_id][$sku][$shelving_attribute] = '';

				if(Mage::getModel('catalog/product')->load($product_id)->getData($shelving_attribute))
				{
					$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($shelving_attribute);
				
					$attributeName = $shelving_attribute;
					$product = Mage::getModel('catalog/product');
					$collection = Mage::getResourceModel('eav/entity_attribute_collection')
					->setEntityTypeFilter($product->getResource()->getTypeId())
					->addFieldToFilter('attribute_code', $attributeName);

					$attribute = $collection->getFirstItem()->setEntity($product->getResource());

					$attributeOptions = $attribute->getSource()->getAllOptions(false);
					if(!empty($attributeOptions))
					{						
						$result = search($attributeOptions,'value',$attributeValue);
				
						if(isset($result[0]['label'])) 
						{
							$sku_color[$order_id][$sku][$shelving_attribute] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $result[0]['label']);
						}
					}
					else 
					{
						$sku_color[$order_id][$sku][$shelving_attribute] = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $attributeValue);
					}
				}
							
			}	

			// print_r($sku_color);exit;
			$sku_shipping_method[$order_id] = clean_method($order->getShippingDescription(),'shipping');
			
				// $sku_shipping_method[$order_id] = $order->getShippingDescription();
				// 			$sku_shipping_method[$order_id] = preg_replace('/[^A-Za-z0-9 _\-\+\&\(\{\}\)]/','',$sku_shipping_method[$order_id]);
				// 			
				// 			$sku_shipping_method[$order_id] = str_ireplace(array('Select Delivery Method','Method','Select postage','- '),'',$sku_shipping_method[$order_id]);
				// 			$sku_shipping_method[$order_id] = str_ireplace(
				// 				array("\n","\r",'  ','Free Shipping Free','Free shipping␣ Free','Paypal Express Checkout','Pick up Pick up','Pick up at our ','Cash on delivery'),
				// 				array(' ',' ',' ','Free Shipping','Free Shipping','Paypal Express','Pick up','Pickup@','COD'),
				// 				$sku_shipping_method[$order_id]);
			
				$address_data[$order_id]['shipping_description'] = $sku_shipping_method[$order_id];
				
				// if($sku_shipping_method[$order_id] != '') $sku_shipping_method[$order_id] = 'Ship: '.$sku_shipping_method[$order_id];
			
			/********************************************************/
			// qty in this order of this sku
			$qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();		
            $sqty = $item->getIsQtyDecimal() ? $item->getQtyShipped() : (int) $item->getQtyShipped();
			// total qty in all orders for this sku
			if(isset($sku_qty[$sku])) $sku_qty[$sku] = ($sku_qty[$sku]+$qty);
			else $sku_qty[$sku] = $qty;
			$total_quantity = $total_quantity + $qty;

			$cost= $qty*(is_object($product)?$product->getCost():0);
			$total_cost = $total_cost + $cost;

			$sku_master[$sku] = $sku;
			
			$price = $item->getPrice();
			// $price = $item->getPriceInclTax();
			
			$address_data[$sku]['sku_price'] = $price;
			
			if(isset($address_data[$order_id]['order_qty'])) $address_data[$order_id]['order_qty'] = ($address_data[$order_id]['order_qty']+$qty);
			else $address_data[$order_id]['order_qty'] = $qty;

			// if(isset($address_data[$order_id]['order_price'])) $address_data[$order_id]['order_price'] = ($address_data[$order_id]['order_price']+$price);
			// 		else $address_data[$order_id]['order_price'] = $price;
			
			// $address_data[$order_id]['order_price'] = (float)($order->getData('grand_total')-$order->getShippingAmount());//$order->getSubtotal();//$order->getGrandTotal(); // <- includes shipping
			$address_data[$order_id]['order_price'] = (float)($order->getSubtotal());//-$order->getShippingAmount());//$order->getSubtotal();//$order->getGrandTotal(); // <- includes shipping
			
			
			
			if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id) && $configurable_names == 'simple')
			{
				$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
				if($_newProduct->getData('name')) $sku_name[$sku] = $_newProduct->getData('name');
			}
			elseif($configurable_names == 'configurable') $sku_name[$sku] = $item->getName();
			elseif($configurable_names == 'custom' && $configurable_names_attribute != '')
			{
				$customname = '';
				
				if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id))
				{
					$_newProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);  
					if($_newProduct->getData($configurable_names_attribute)) $customname = $_newProduct->getData(''.$configurable_names_attribute.'');
				}
				elseif($product->getData(''.$configurable_names_attribute.''))
				{
					$customname = $product->getData($configurable_names_attribute);
				}
				else $customname = '';

				if(trim($customname) != '')
				{
					if(Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($configurable_names_attribute))
					{
						$customname = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id)->getAttributeText($configurable_names_attribute);
					}
					elseif($product[$configurable_names_attribute]) $customname = $product[$configurable_names_attribute];
					else $customname = '';
				}

				if(trim($customname) != '')
				{
					if(is_array($customname)) $customname = implode(',',$customname);
					$customname = preg_replace('~,$~','',$customname);
				}
				else $customname = '';
				
				 $sku_name[$sku] = $customname;
			}
			
			$address_data[$sku]['sku_name'] = $item->getName();//$sku_name[$sku];
			$address_data[$sku]['sku_name'] = preg_replace('/[^a-zA-Z0-9\s]/', '', $address_data[$sku]['sku_name']);
			
			if(isset($options['options']) && is_array($options['options']))
			{
				$i = 0;
				if(isset($options['options'][$i])) $continue = 1;
				while($continue == 1)
				{
					$options_name_temp[$i] = trim(htmlspecialchars_decode($options['options'][$i]['label'].' : '.$options['options'][$i]['value']));
					$options_name_temp[$i] = preg_replace('~^select ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^enter ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^would you Like to ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^please enter ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~^your ~i','',$options_name_temp[$i]);
					$options_name_temp[$i] = preg_replace('~\((.*)\)~i','',$options_name_temp[$i]);
						
					$i ++;
					$continue = 0;
					if(isset($options['options'][$i])) $continue = 1;
				}
				
				
				$i = 0;
				$continue = 0;
				$opt_count = 0;
				
				if(isset($options['options'][$i])) $continue = 1;
				
			
				$sku_order_id_options[$order_id][$sku] = array();
				
				while($continue == 1)// && isset($sku_order_id_options[$order_id][$sku]))
				{
					// echo 'options_yn:'.$options_yn.' here';exit;	
					
					if($i > 0) $sku_order_id_options[$order_id][$sku] .= ' ';
					$sku_order_id_options[$order_id][$sku] .= htmlspecialchars_decode('[ '.$options['options'][$i]['label'].' : '.$options['options'][$i]['value'].' ]');
					
						// if show options as a group
						if($options_yn != 0 && $opt_count == 0)
						{
							$full_sku 	= trim($item->getSku());	
							$parent_sku = preg_replace('~\-(.*)$~','',$full_sku);
							$full_sku 	= preg_replace('~^'.$parent_sku.'\-~','',$full_sku);
							$options_sku_array = array();
							$options_sku_array = explode('-',$full_sku);
							if(!isset($options_sku_parent[$order_id])) $options_sku_parent[$order_id] = array();
							if(!isset($options_sku_parent[$order_id][$sku])) $options_sku_parent[$order_id][$sku] = array();
							
							$opt_count = 0;
							foreach ($options_sku_array as $k => $options_sku_single)
							{	
								if(!isset($options_sku_parent[$order_id][$sku][$options_sku_single])) $options_sku_parent[$order_id][$sku][$options_sku_single] = '';
						
								$options_name[$order_id][$sku][$options_sku_single] = $options_name_temp[$opt_count];
								
								if(isset($options_sku[$order_id][$options_sku_single]) && (!in_array($options_sku_single,$pickpack_options_filter_array)))
								{
									$options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku] + $options_sku[$order_id][$options_sku_single]);
									$options_sku_parent[$order_id][$sku][$options_sku_single] = ($qty + $options_sku_parent[$order_id][$sku][$options_sku_single]);
								}
								elseif(!in_array($options_sku_single,$pickpack_options_filter_array))
								{
									$options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku]);
									$options_sku_parent[$order_id][$sku][$options_sku_single] = $qty;//($sku_qty[$sku]);
								}

								$opt_count ++;
							}		
							unset($options_name_temp);	
						}

					// }
					
					$i ++;
					$continue = 0;
					if(isset($options['options'][$i])) $continue = 1;
				}
			}
			unset($options_name_temp);
// print_r($options_name);exit;
			$sku_stock[$sku] = $stock;
		    
			if(isset($sku_order_id_qty[$order_id][$sku])){
                $sku_order_id_qty[$order_id][$sku] = ($sku_order_id_qty[$order_id][$sku]+$qty); 
                $sku_order_id_sqty[$order_id][$sku] = ($sku_order_id_sqty[$order_id][$sku]+$sqty); 
	            $sku_order_id_sku[$order_id][$sku] = $product_sku; 
    		}else{
                $sku_order_id_qty[$order_id][$sku] = $qty;
                $sku_order_id_sqty[$order_id][$sku] = $sqty; 
                $sku_order_id_sku[$order_id][$sku] = $product_sku; 
			} 

			if(!isset($max_qty_length)) $max_qty_length = 2;
			if(strlen($sku_order_id_qty[$order_id][$sku]) > $max_qty_length) $max_qty_length = strlen($sku_order_id_qty[$order_id][$sku]);
			if(strlen($sku_order_id_sqty[$order_id][$sku]) > $max_qty_length) $max_qty_length = strlen($sku_order_id_sqty[$order_id][$sku]);


			if(isset($split_supplier_yn) && $split_supplier_yn != 'no')
			{
				// if(!in_array($supplier,$sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
				if(!isset($sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
				elseif(!in_array($supplier,$sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
			}
			$coun ++;
			
		}
		
		
		if(isset($sku_bundle[$order_id]) && is_array($sku_bundle[$order_id])) ksort($sku_bundle[$order_id]);
	}

	$sku_test = array();
	
	ksort($order_id_master);
	ksort($sku_order_id_qty);
	// ksort($sku_bundle);
	ksort($sku_master);
	if(isset($supplier_master))	ksort($supplier_master);
	$supplier_previous 		= '';
	$supplier_item_action 	= '';

	// roll_orderID
	// $page_count = 1;
	//print_r($order_id_master);exit;
	foreach($order_id_master as $order_id =>$value)
	{				
		foreach($sku_order_id_qty[$order_id] as $sku =>$qty)
		{
			unset($csv_data);
	// print_r($address_data);exit;
			$sku_addon = '';
			$max_qty_length_display = 0;
			$display_sku = '';
			$display_sku = htmlspecialchars_decode($sku_order_id_sku[$order_id][$sku]);
	
			$csv_data['qty'] 				= $qty;
			$csv_data['sku'] 				= $display_sku;
			$csv_data['product_name'] 		= $address_data[$sku]['sku_name'];
			$csv_data['sku_price'] 			= round($address_data[$sku]['sku_price'],2);//round($item->getPriceInclTax(),2);//total_price_taxed
			$csv_data['order_price'] 		= round($address_data[$order_id]['order_price'],2);//round($total_price_taxed,2) $order->getPriceInclTax();//
			$csv_data['order_qty'] 			= $address_data[$order_id]['order_qty'];//$total_qty;//total_price_taxed//$order->getPriceInclTax();//
			$csv_data['description'] 		= trim($item->getDescription());
			$csv_data['short_description'] 	= trim($item->getShortDescription());
			$csv_data['website'] 			= $address_data[$order_id]['website'];
			$csv_data['store'] 				= $address_data[$order_id]['store'];
			$csv_data['order_id'] 			= $order_id;
			
			$csv_data['shipping_description'] = $address_data[$order_id]['shipping_description'];
			$csv_data['customer_id'] 		= $address_data[$order_id]['customer_id'];
			$csv_data['order_date'] 		= $address_data[$order_id]['order_date'];
			$csv_data['order_date_plus48h'] = $address_data[$order_id]['order_date_plus48h'];
		
			// $csv_data['shipping_address'] 	= $sku_shipping_address[$order_id];
			$csv_data['ship-firstname'] 	= $address_data[$order_id]['ship-firstname'];//$order->getShippingAddress()->getFirstName();
			$csv_data['ship-lastname'] 		= $address_data[$order_id]['ship-lastname'];//$order->getShippingAddress()->getLastName();
			$csv_data['ship-companyname'] 	= $address_data[$order_id]['ship-companyname'];//$order->getShippingAddress()->getCompany();
			$csv_data['ship-street1'] 		= $address_data[$order_id]['ship-street1'];//$order->getShippingAddress()->getStreet(0);
			$csv_data['ship-street2'] 		= $address_data[$order_id]['ship-street2'];//$order->getShippingAddress()->getStreet(1);
			$csv_data['ship-city'] 			= $address_data[$order_id]['ship-city'];//$order->getShippingAddress()->getCity();
			$csv_data['ship-region'] 		= $address_data[$order_id]['ship-region'];//$order->getShippingAddress()->getRegion();
			$csv_data['ship-region-code'] 	= $address_data[$order_id]['ship-region-code'];//$order->getShippingAddress()->getRegion();
			$csv_data['ship-postcode'] 		= $address_data[$order_id]['ship-postcode'];//$order->getShippingAddress()->getPostcode();
			$csv_data['ship-country'] 		= $address_data[$order_id]['ship-country'];//$order->getShippingAddress()->getCountry();//	= $addy['country'];
			$csv_data['ship-region-filtered'] = $address_data[$order_id]['ship-region-filtered'];
			$csv_data['ship-telephone'] 	= $address_data[$order_id]['ship-telephone'];//
		
			$csv_data['bill-firstname'] 	= $address_data[$order_id]['bill-firstname'];//$order->getShippingAddress()->getFirstName();
			$csv_data['bill-lastname'] 		= $address_data[$order_id]['bill-lastname'];//$order->getShippingAddress()->getLastName();
			$csv_data['bill-companyname'] 	= $address_data[$order_id]['bill-companyname'];//$order->getShippingAddress()->getCompany();
			$csv_data['bill-street1'] 		= $address_data[$order_id]['bill-street1'];//$order->getShippingAddress()->getStreet(0);
			$csv_data['bill-street2'] 		= $address_data[$order_id]['bill-street2'];//$order->getShippingAddress()->getStreet(1);
			$csv_data['bill-city'] 			= $address_data[$order_id]['bill-city'];//$order->getShippingAddress()->getCity();
			$csv_data['bill-region'] 		= $address_data[$order_id]['bill-region'];//$order->getShippingAddress()->getRegion();
			$csv_data['bill-region-code'] 	= $address_data[$order_id]['bill-region-code'];//$order->getShippingAddress()->getRegion();
			$csv_data['bill-postcode'] 		= $address_data[$order_id]['bill-postcode'];//$order->getShippingAddress()->getPostcode();
			$csv_data['bill-country'] 		= $address_data[$order_id]['bill-country'];//$order->getShippingAddress()->getCountry();//	= $addy['country'];
			$csv_data['bill-region-filtered'] = $address_data[$order_id]['bill-region-filtered'];
			$csv_data['bill-telephone'] 	= $address_data[$order_id]['bill-telephone'];//
			$csv_data['bill-email'] 		= $address_data[$order_id]['bill-email'];//
		
			// custom mucking about
			if(preg_match('~UPS~',$address_data[$order_id]['shipping_description']))
			{
				$address_data[$order_id]['shipping_description_filtered'] 	= 'UPS';
				$address_data[$order_id]['billing_option_filtered'] 		= 'Prepaid';
				$address_data[$order_id]['ship_service_filtered'] 			= 'ups ground - residential';
				$csv_data['shipping_description_filtered'] 					= $address_data[$order_id]['shipping_description_filtered'];
				$csv_data['billing_option_filtered'] 						= $address_data[$order_id]['billing_option_filtered'];
				$csv_data['ship_service_filtered'] 							= $address_data[$order_id]['ship_service_filtered'];
			}
			else
			{
				$address_data[$order_id]['shipping_description_filtered'] 	= 'Fedex';
				$address_data[$order_id]['billing_option_filtered'] 		= 'Sender';
				$address_data[$order_id]['ship_service_filtered'] 			= 'fedex ground - residential';
				$csv_data['shipping_description_filtered'] 					= $address_data[$order_id]['shipping_description_filtered'];
				$csv_data['billing_option_filtered'] 						= $address_data[$order_id]['billing_option_filtered'];
				$csv_data['ship_service_filtered'] 							= $address_data[$order_id]['ship_service_filtered'];

				// BillingOption:[]; for ups its prepaid and for FedEx its sender	
			}
			
		// print_r($csv_data);exit;
			// %%description%%
				// $permittable_values = array('qty','sku','product_name','shipping_address','order_id');
			// print_r($column_map);exit;
			foreach($column_map as $column_header => $column_attribute)
			{
				// if value needs filling
				$attribute_base = strtolower(trim(str_replace('%','',$column_attribute)));
				if(preg_match('~%%~',$column_attribute))// && in_array($attribute_base,$permittable_values))
				{
					if(!isset($csv_data[$attribute_base])) $csv_data[$attribute_base] = '';
					$csv_output .= $csv_data[$attribute_base].$column_separator;
				}
				elseif(preg_match('~\[~',$column_attribute))
				{
					$csv_output .= str_replace(array('[',']'),'',$column_attribute).$column_separator;
				}
				else 
				{
					if(isset($sku_color[$order_id][$sku][$column_attribute]))
					{
						$csv_output .= $sku_color[$order_id][$sku][$column_attribute].$column_separator;
					}
					else $csv_output .= $column_separator;
				}
			}
		
			$csv_output .= "\n";
		}// end roll_SKU
	
// print_r($sku_color);exit;
	
	}// end roll_Order

	return $csv_output;
		
	/**
	getCSVPickseparated2 222222222 Template - END
	*********************************************
	************************************************/
}










	public function getPdfPicklist($order){
		
		
		/**
		 * get store id
		 */
		$storeId = Mage::app()->getStore()->getId();

        $this->_beforeGetPdf();
		$this->_initRenderer('invoices');

		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($page, 10);
        /***********************************************
        * PickList Template - START
        ************************************************/ 
            
        //foreach ($invoices as $invoice)
		{    		  
           
			if ($order->getStoreId()) {
				Mage::app()->getLocale()->emulate($order->getStoreId());
			}
			
			$page_size = $this->_getConfig('page_size', 'a4', false,'general');

			if($page_size == 'letter') 
			{
				$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
				$page_top = 770;$padded_right = 587;
			}
			elseif($page_size == 'a4') 
			{
				$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
				$page_top = 820;$padded_right = 570;
			}
			elseif($page_size == 'a5landscape') 
			{
				$page = $pdf->newPage('596:421');					
				$page_top = 395;$padded_right = 573;
			}
			
			$pdf->pages[] = $page;           
			
                        
            /***********************************************************
            * CONFIGURATIONS
            ***********************************************************/
            
			// all y values must be positive, all x positive
			// bottom left is 0,0
			
            ## DEFAULT VALUES
            $columnYNudgeDefault        = 600;
            $barcodeXYDefault           = '468,800';
			$customerIdXYDefault        = '390,705';
            $orderDateXYDefault         = '160,755';
            $orderIdXYDefault           = '40,755';
            $addressXYDefault           = '40,705';  
            $addressFooterXYDefault     = '50,105'; // * new
            $returnAddressFooterXYDefault = '320,105'; // * new
            
            $cutoff_noDefault           = 10;
            $fontsize_overallDefault    = 9;
            $fontsize_productlineDefault= 9;
            $fontsize_returnaddressDefault= 9;
            $fontsize_returnaddresstopDefault= 7;
            $fontsize_shipaddressDefault= 13;

            $skuXDefault                = 300;
            $qtyXDefault                = 40;
            $productXDefault            = 80;
            $colorXDefault              = 345;
            $sizeXDefault               = 430;
            $priceXDefault              = 505; 
           
			$colorAttributeNameDefault 	= 'color';
            $sizeAttributeNameDefault	= 'size';

			$showReturnLogoYNDefault	= 'Y';
			$returnLogoXYDefault		= '312,115';
			
			// hard-coded (not an option in config yet)
			
			$showPriceYNDefault			= 'N';
			$showColorYNDefault			= 'N';
			$showSizeYNDefault			= 'N';
			$boldLastAddressLineYNDefault = 'Y';
			$fontsizeTitlesDefault		= 14;
			$fontColorGrey				= 0.8;
			$fontColorReturnAddressFooter = 0.2;
			//
			$showPriceYN				= $showPriceYNDefault;
			$showColorYN				= $showColorYNDefault;
			$showSizeYN					= $showSizeYNDefault;
			$boldLastAddressLineYN		= $boldLastAddressLineYNDefault;
 			$fontsize_returnaddresstop 	= $fontsize_returnaddresstopDefault;
			$fontsizeTitles 			= $fontsizeTitlesDefault;


			// $page->drawImage($image, $left [x1], $bottom[y1], $right[x2], $top[y2]);
				
            ## DEFAULT VALUES from top left
            /*
            $columnYNudgeDefault        = 570;
            $barcodeXYDefault           = '450,800';
            $orderDateXYDefault         = '390,755';
            $orderIdXYDefault           = '390,730';
            $customerIdXYDefault        = '390,705'; 
            $customerNameXYDefault      = '130,648'; 
            $addressXYDefault           = '130,633';           
            $orderIdFooterXYDefault     = '106,76';
            $customerIdFooterXYDefault  = '106,47';            

            $cutoff_noDefault           = 10;
            $fontsize_overallDefault    = 9;
            $fontsize_productlineDefault= 9;
            $skuXDefault                = 90;
            $qtyXDefault                = 40;
            $productXDefault            = 230;
            $colorXDefault              = 345;
            $sizeXDefault               = 430;
            $priceXDefault              = 505;          
            */

            $columnYNudge   		= $this->_getConfig('pack_column', $columnYNudgeDefault);
            $showBarCode    		= $this->_getConfig('pickpack_packbarcode', 0, false); 
                         
            $barcodeXY      		= explode(",", $this->_getConfig('pack_barcode', $barcodeXYDefault));               
            $orderDateXY    		= explode(",", $this->_getConfig('pack_order', $orderDateXYDefault));
            $customerIdXY   		= explode(",", $this->_getConfig('pack_cid', $customerIdXYDefault));
            $orderIdXY      		= explode(",", $this->_getConfig('pack_oid', $orderIdXYDefault));                        

			$color_attribute_name 	= $this->_getConfig('color_attribute_name', $colorAttributeNameDefault,false);
			$size_attribute_name 	= $this->_getConfig('size_attribute_name', $sizeAttributeNameDefault,false);
            
            $customerNameXY 		= explode(",", $this->_getConfig('pack_customer', $customerNameXYDefault)); 
            $addressXY     			= explode(",", $this->_getConfig('pack_customeraddress', $addressXYDefault));
            
            // $customerIdFooterXY 	=  explode(",", $this->_getConfig('footer_customer_id', $customerIdFooterXYDefault));
            $addressFooterXY 		=  explode(",", $this->_getConfig('footer_address', $addressFooterXYDefault));
            // $orderIdFooterXY    	=  explode(",", $this->_getConfig('footer_order_id', $orderIdFooterXYDefault));      
            
            $cutoff_no              = $this->_getConfig('pack_noofitems', $cutoff_noDefault, false);
            // $fontsize_overall       = $this->_getConfig('pickpack_fontsizeoverall', $fontsize_overallDefault, false);
            //            $fontsize_productline   = $this->_getConfig('pickpack_fontsizeproductline', $fontsize_productlineDefault, false);
            //            $fontsize_returnaddress = $this->_getConfig('pickpack_returnfont', $fontsize_returnaddressDefault, false);
            //            $fontsize_shipaddress   = $this->_getConfig('pickpack_shipfont', $fontsize_shipaddressDefault, false);
			$fontsize_overall       = $fontsize_overallDefault;
            $fontsize_productline   = $fontsize_productlineDefault;
            $fontsize_returnaddress = $fontsize_returnaddressDefault;
            $fontsize_shipaddress   = $fontsize_shipaddressDefault;
            
            // $skuX           		= $this->_getConfig('pack_sku', $skuXDefault);
            $skuX           		= $skuXDefault;
            // $qtyX           		= $this->_getConfig('pack_qty', $qtyXDefault);
            $qtyX           		= $qtyXDefault;
            $productX       		= $this->_getConfig('pack_product', $productXDefault);
            $colorX         		= $this->_getConfig('pack_color', $colorXDefault);
            $sizeX          		= $this->_getConfig('pack_size', $sizeXDefault);
            $priceX         		= $this->_getConfig('pack_price', $priceXDefault);            
                  
	     	$non_standard_characters 	= $this->_getConfig('non_standard_characters', 0, false,'general');

            // $showReturnLogoYN       = $this->_getConfig('pickpack_returnlogo', $showReturnLogoYNDefault, 0, false);            
            //            $returnLogoXY	        = explode(",", $this->_getConfig('pickpack_nudgelogo', $returnLogoXYDefault));            
            //            $returnAddressFooterXY	= explode(",", $this->_getConfig('pickpack_returnaddress', $returnAddressFooterXYDefault));            

			

            $this->y        = $page->getHeight();
             /***********************************************************
            * CONFIGURATIONS
            ***********************************************************/
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page, $fontsize_overall);         
             
            # BARCODE
            if(1 == $showBarCode){
        		$barcodeString = $this->convertToBarcodeString($order->getRealOrderId());
        		#$page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
        		$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 16);  
                #$this->y += $barcodeXY[1];    
        		$page->drawText($barcodeString, $barcodeXY[0], $barcodeXY[1], 'CP1252');
               
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                $this->_setFontRegular($page, $fontsize_overall);
        	}            
            
                        
            # HEADER 

			// draw box behind order ID#
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.92));			
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));			
			$page->drawRectangle(20, ($orderIdXY[1]-5), $padded_right, ($orderIdXY[1]+18));
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));			

			$this->_setFontBold($page,($fontsizeTitles+4));
			
			$order_date = '';
			if($order->getCreatedAtStoreDate()) $order_date = Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false);
		
            $page->drawText('/   '.$order_date, $orderDateXY[0], $orderDateXY[1], 'UTF-8');
			$page->drawText('#'.$order->getRealOrderId(), $orderIdXY[0], $orderIdXY[1], 'UTF-8');
			$this->_setFontRegular($page, $fontsize_overall);

			// header logo
   			$this->insertLogo($page, $page_size,'pack',$order->getStore());
   
   			// HEADER STORE ADDRESS
   			// $store_address = $this->insertAddress($page, 'top', $order->getStore());
			$this->y = 820;
			$this->_setFontRegular($page, $fontsize_returnaddresstop);
			foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $order->getStore())) as $value)
			{
				if ($value!=='')
				{
					$page->drawText(trim(strip_tags($value)), 320, $this->y, 'UTF-8');
					$this->y -=6;
				}
			}
            $this->_setFontRegular($page, $fontsize_overall);         

			// HEADER LINES
			// return address
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$page->setLineWidth(12);
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));
			$page->drawLine(310, 795, 310, 825);
			// $page->setLineWidth(0);
            
            # HEADER SHIPPING ADDRESS
            $shippingAddressFlat = implode(',',$this->_formatAddress($order->getShippingAddress()->format('pdf'))); 
       		$shippingAddressArray = explode(',',str_replace(',','~,',$shippingAddressFlat));

			$this->_setFontBold($page,$fontsizeTitles);
			$page->drawText('shipping address', $addressXY[0], ($addressXY[1]+20), 'UTF-8');
			$this->_setFontRegular($page, $fontsize_overall);
			
			$line_height = 0;

            foreach($shippingAddressArray as $item)
			{
				if((($addressXY[1]-$line_height) > 0) && $addressXY[0] > 0)
				{
					$item = trim(str_replace('~',',',$item));
					$page->drawText($item, $addressXY[0], ($addressXY[1]-$line_height), 'UTF-8');
					$line_height = ($line_height + $fontsize_overall);
				}
			}

            
            # FOOTER

			// order ID / address
			$page->setFillColor(new Zend_Pdf_Color_Rgb($fontColorGrey, $fontColorGrey, $fontColorGrey));
			$this->_setFontBold($page,($fontsizeTitles-3));
			$page->drawText('#'.$order->getRealOrderId(), $addressFooterXY[0], ($addressFooterXY[1]+20), 'UTF-8');           
			$this->_setFontRegular($page, $fontsize_overall);         
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0,0,0));
			
			$line_height = 0;
			$item_prev = '';
			$this->_setFontRegular($page, $fontsize_shipaddress);  
            
			$i = 0;
			while($shippingAddressArray[$i])
			{
				
				if(isset($shippingAddressArray[$i]) && (($addressFooterXY[1]-$line_height) > 0) && $addressFooterXY[0] > 0)
				{
					
					
					if(preg_match('~T:~',$shippingAddressArray[$i]))
					{	
						// phone line, grey it bracket it
						$shippingAddressArray[$i] = trim(str_replace('~','',$shippingAddressArray[$i]));
						$line_height = ($line_height + ($fontsize_shipaddress/2));
						$this->_setFontRegular($page, ($fontsize_shipaddress-2));  
						$page->setFillColor(new Zend_Pdf_Color_Rgb(($fontColorGrey-0.2), ($fontColorGrey-0.2), ($fontColorGrey-0.2)));
						$shippingAddressArray[$i] = '[ '.$shippingAddressArray[$i].' ]';
					}
					else
					{
						// regular line or last line
						$this->_setFontRegular($page, $fontsize_shipaddress); 
						
						if(!isset($shippingAddressArray[($i+1)]) || preg_match('~T:~',$shippingAddressArray[($i+1)]))
						{
							// last line, lets bold it and make it a bit bigger
							$shippingAddressArray[$i] = trim(str_replace('~','',$shippingAddressArray[$i]));
							if($boldLastAddressLineYN == 'Y')
							{
								$this->_setFontBold($page, ($fontsize_shipaddress+2));  
								$line_height = ($line_height + 2);
							}
						}
						else
						{
							$shippingAddressArray[$i] = trim(str_replace('~',',',$shippingAddressArray[$i]));
						}
						$page->setFillColor(new Zend_Pdf_Color_Rgb(0,0,0));
					}
					$page->drawText($shippingAddressArray[$i], $addressFooterXY[0], ($addressFooterXY[1]-$line_height), 'UTF-8');
					$line_height = ($line_height + $fontsize_shipaddress);
				}
				$i ++;
			}    
			$page->setFillColor(new Zend_Pdf_Color_Rgb(0,0,0));
	        
			
			// footer return address logo
			if($showReturnLogoYN == 'Y')
			{
				$image = Mage::getStoreConfig('pickpack_options/wonder/pickpack_logo', $order->getStore());
				if ($image) 
				{
					$image = Mage::getStoreConfig('system/filesystem/media', $order->getStore()) . '/sales/store/logo_third/' . $image;
					if (is_file($image)) 
					{
						$image = Zend_Pdf_Image::imageWithPath($image);
						// $page->drawImage($image, 315+$newlogo[1], 180+$newlogo[0], 500+$newlogo[0], 200+$newlogo[0]);
						$page->drawImage($image, $returnLogoXY[0], $returnLogoXY[1], ($returnLogoXY[0]+185), ($returnLogoXY[1]+20));
					}
				}
			}
			
			// footer store address
			$this->_setFontRegular($page, $fontsize_returnaddress);  
			$page->setFillColor(new Zend_Pdf_Color_Rgb($fontColorReturnAddressFooter,$fontColorReturnAddressFooter,$fontColorReturnAddressFooter));       
   			// $store_address = $this->insertAddress($page, 'bottom', $order->getStore());
			// $this->y = 105;
			$page->drawText('From :', $returnAddressFooterXY[0], $returnAddressFooterXY[1], 'UTF-8');
			// $this->y -=18;
			$line_height = 15;
			foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $order->getStore())) as $value)
			{
				if ($value!=='')
				{
					$page->drawText(trim(strip_tags($value)), $returnAddressFooterXY[0], ($returnAddressFooterXY[1]-$line_height), 'UTF-8');	
					$line_height = ($line_height + $fontsize_returnaddress);
				}
				
			}
			$this->_setFontRegular($page, $fontsize_overall);         
            
			# ITEMS
            $this->y         = $columnYNudge;  
            $this->_itemsY   = $this->y;  

			// draw box behind table headers
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.92));			
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));			
			$page->drawRectangle((20), ($columnYNudge-2), ($padded_right), ($columnYNudge+10));

			/* Add table head */
			$page->setFillColor(new Zend_Pdf_Color_Rgb(($fontColorGrey-0.5),($fontColorGrey-0.5),($fontColorGrey-0.5)));
			$this->_setFontBold($page,($fontsizeTitles));
			$page->drawText(Mage::helper('sales')->__('qty'), $qtyX, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('items'), $productX, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('codes'), $skuX, $this->y, 'UTF-8');
			if($showPriceYN == 'Y') $page->drawText(Mage::helper('sales')->__('Price'), $priceX, $this->y, 'UTF-8');
			
		
			$this->y = ($this->y - 28);

            $counter = 1;   
            $itemsCollection =  $order->getAllVisibleItems();//$order->getItemsCollection();
            $total_items     = count($itemsCollection);
            
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($page, $fontsize_productline);                      
			foreach ($itemsCollection as $item)
			{
			     #if(!$item->isDummy())
                 {               
                    			     
                    $size = '';
                    $color = ''; 
                    $options = $item->getProductOptions();
                    $color_attribute_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $color_attribute_name)->getAttributeId();//272
                    $size_attribute_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $size_attribute_name)->getAttributeId();//525
                    
                    $size_id  = $options['info_buyRequest']['super_attribute'][$size_attribute_id]; #size
                    $color_id = $options['info_buyRequest']['super_attribute'][$color_attribute_id]; #color                    
                    $sizeCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')                
                    ->setStoreFilter(0)                   
                    ->setAttributeFilter($size_attribute_id)                    
                    ->load(); 
                    foreach($sizeCollection as $_size){                        
                        if($_size->getOptionId() == $size_id){
                            $size = $_size->getValue();
                            break;
                        }
                    }                    
                   
                    $colorCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')                
                    ->setStoreFilter(0)                   
                    ->setAttributeFilter($color_attribute_id)                    
                    ->load();         
                    foreach($colorCollection as $_color){                        
                        if($_color->getOptionId() == $color_id){
                            $color = $_color->getValue();
                            break;
                        }
                    }                   
                                   
        		                
                    $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();
                    
                    ###########MAGENTOPYCHO##########
                    $qty_string = $from_shipment ? 's:'.(int)$item->getQtyShipped() . ' / o:'.$qty : $qty;
                    ###########MAGENTOPYCHO##########                                  
        			$page->drawText($qty_string, ($qtyX+8), $this->y , 'UTF-8');
                    $page->drawText($item->getSku(), $skuX, $this->y , 'UTF-8');
                    $page->drawText($item->getName(), $productX, $this->y , 'UTF-8');
                    
					if($showColorYN == 'Y')
					{
						$page->drawText($color, $colorX, $this->y , 'UTF-8');
					}
					
					if($showSizeYN == 'Y')
					{
                    	$page->drawText($size, $sizeX, $this->y , 'UTF-8');                    
					}
					
					// show price if set to show
					if($showPriceYN == 'Y')
					{
						$price = $qty * $item->getPriceInclTax();
	                    $page->drawText(number_format($price, 2, '.', ','), $priceX, $this->y , 'UTF-8');
					}
					
                    $this->y -= 15;
                    
                    if(($counter % $cutoff_no) == 0 && $counter != $total_items){
                        $page = $this->newPage2();
                    }
                    
                    $counter++;
                }
			} 
            #exit;
            
        }    		
        /***********************************************
        * PickList Template - END
        ************************************************/
        $this->_afterGetPdf();
		return $pdf;
    }


    
	public function getPdf($invoices = array())
	{
		
		/**
		 * get store id
		 */
		$storeId = Mage::app()->getStore()->getId();

		$show_price = 0;
		$this->_beforeGetPdf();
		$this->_initRenderer('invoices');

		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($page, 10);
        
         
    		foreach ($invoices as $invoice)
    		{
    			if ($invoice->getStoreId()) {
    				Mage::app()->getLocale()->emulate($invoice->getStoreId());
    			}
				
				$page_size = $this->_getConfig('page_size', 'a4', false,'general');

				if($page_size == 'letter') 
				{
					$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
					$page_top = 770;$padded_right = 587;
				}
				elseif($page_size == 'a4') 
				{
					$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
					$page_top = 820;$padded_right = 570;
				}
				elseif($page_size == 'a5landscape') 
				{
					$page = $pdf->newPage('596:421');					
					$page_top = 395;$padded_right = 573;
				}
				
				$pdf->pages[] = $page;
    
    			$order = $invoice->getOrder();
    
    			/* Add image */
    			$this->insertLogo($page, $page_size,'pack',$order->getStore());
    
    			/* Add address */
    			$store_address = $this->insertAddress($page, 'top', $order->getStore());
    
    			/* Add head */
    			$this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));
    
    
    			$barcode = Mage::getStoreConfig('pickpack_options/wonder/pickpack_packbarcode');
    			if($barcode==1){
    				$barcodeString = $this->convertToBarcodeString($order->getRealOrderId());
    				$page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
    				$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 18);
    				$page->drawText($barcodeString, 452, 800, 'CP1252');
    			}
    
    			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
    			$this->_setFontRegular($page);
    			//$page->drawText(Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId(), 35, 780, 'UTF-8');
    
    			/* Add products list table */		
    			$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
    			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));			
    			$this->_setFontItalic($page,14);
    		
    
    			//                   ? xpos header rectangle  ? width            ? height header rectangle
    			$page->drawRectangle(25, $this->y, $padded_right, $this->y -20);
    
    			// HEIGHT PRODUCTS HEADER lINE
    			$this->y -=15;
    
    			/* Add table head */
    			$page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
    			$page->drawText(Mage::helper('sales')->__('items'), 35, $this->y, 'UTF-8');
    			$page->drawText(Mage::helper('sales')->__('codes'), 255, $this->y, 'UTF-8');
    			if($show_price > 0) $page->drawText(Mage::helper('sales')->__('Price'), 370, $this->y, 'UTF-8');
    			$page->drawText(Mage::helper('sales')->__('numbers'), 420, $this->y, 'UTF-8');
    			// $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
    			if($show_price > 0) $page->drawText(Mage::helper('sales')->__('Subtotal'), 525, $this->y, 'UTF-8');
    			
			
    			$this->y -=20;
    
    			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
    
    			/* Add body */
    			foreach ($invoice->getAllItems() as $item)
    			{
    				if ($item->getOrderItem()->getParentItem())
    				{
    					continue;
    				}
    
    				if ($this->y < 15)
    				{
    					$page = $this->newPage(array('table_header' => true));
    				}
    
    				/* Draw item */
    				$page = $this->_drawItem($item, $page, $order);
    				$this->y -=8;
    			}
    
    			/* Add totals */
    			if($show_price > 0) $page = $this->insertTotals($page, $invoice);
    
    			// bottom address label
    			$this->y =115;
    
    			/* Add table head */
    			$page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));	
    				
    			/* Add bottom store address */
    			$store_address = $this->insertAddress($page, 'bottom', $order->getStore());
    			$this->y -=20;
    			if ($invoice->getStoreId())
    			{
    				Mage::app()->getLocale()->revert();
    			}
    
    		}
        
        
        
        $this->_afterGetPdf();
		return $pdf;
	}

	/**
	 * Create new page and assign to PDF object
	 *
	 * @param array $settings
	 * @return Zend_Pdf_Page
	 */
	public function newPage(array $settings = array())
	{
		$page_size = $this->_getConfig('page_size', 'a4', false,'general');
		if($page_size == 'letter') 
		{
			$settings['page_size'] = Zend_Pdf_Page::SIZE_LETTER;
			$page_top = 770;$padded_right = 587;
		}
		else if($page_size == 'a4') 
		{
			$settings['page_size'] = Zend_Pdf_Page::SIZE_A4;
			
			// $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
			$page_top = 820;$padded_right = 570;
		}
		elseif($page_size == 'a5landscape') 
		{
			$settings['page_size'] = '596:421';
			
			// $page = $pdf->newPage('596:421');					
			$page_top = 395;$padded_right = 573;
		}

		$pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
		$page = $this->_getPdf()->newPage($pageSize);
		
		$this->_getPdf()->pages[] = $page;
		$this->y = ($page_top-20);

		return $page;
	}
    
    private function newPage2(){
		
		$page_size = $this->_getConfig('page_size', 'a4', false,'general');
		
		if($page_size == 'letter') 
		{
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
			$page_top = 770;$padded_right = 587;
		}
		elseif($page_size == 'a4') 
		{
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
			$page_top = 820;$padded_right = 570;
		}
		
		$this->_getPdf()->pages[]     = $page;
        $this->y                      = $this->_itemsY;  
        
        $fontsize_productlineDefault = 9;
        $fontsize_productline   = $this->_getConfig('pickpack_fontsizeproductline', $fontsize_productlineDefault, false);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, $fontsize_productline);       
        return $page;    
    }  
    
    
        

	private function _getConfig($field, $default = '', $add_default = true, $group = 'wonder', $store = null){
        $value = Mage::getStoreConfig('pickpack_options/'.$group.'/'.$field, $store);
        if(trim($value) == '')
		{
            return $default;
        }
		else
		{
            if((strpos($value, ',') !== false) && (strpos($default, ',') !== false))// && (strpos($value, "\n")))
			{
                $values = explode(",", $value);
                $defaults = explode(",", $default);
                if($add_default)
				{
                    $value = ($values[0] + $defaults[0]) . ',' . ($values[1] + $defaults[1]);
                }     
           		else
				{
					$value = '';
					$i = 0;
					foreach($defaults as $i => $v)					
                    {
						if($value != '') $value .= ',';
						if(isset($values[$i]) && $values[$i] != '') $value .= $values[$i];
						else $value .= $v;
						$i ++;
					}
					$value = ($value);
				}
            }
			else
			{
                $value = ($add_default) ? ($value + $default) : $value; 
            }            
            return $value;
        }
    }

	private function parseString($string, $font=null, $fontsize=null) 
   { 
      if (is_null($font)) 
         $font = $this->_font; 
      if (is_null($fontsize)) 
         $fontsize = $this->_fontsize; 
		//$string = preg_replace('/[^A-Za-z0-9 _\-\+\&\(\{\}\)]/','',$string);
      	// $drawingString = iconv('', 'UTF-16BE', $string); 

		// $drawingString = iconv('UTF8', 'UTF-16BE//TRANSLIT', $string);
		//$drawingString = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $string);
		//$drawingString = iconv('', 'UTF-16BE//TRANSLIT', $string);
		
		// $string = "ʿABBĀSĀBĀD";

		// echo iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string).'<br />';
		// 	// output: [nothing, and you get a notice]
		// 
		// 	echo iconv('UTF-8', 'ISO-8859-1//IGNORE', $string).'<br />';
		// 	// output: ABBSBD
		// 
		// 	echo iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $string).'<br />';
		// 	echo iconv('UTF-8', 'ISO-8859-1//IGNORE//TRANSLIT', $string).'<br />';
		
		// $drawingString = iconv('UTF-8', 'ISO-8859-1//IGNORE//TRANSLIT', $string);
		$drawingString = iconv('UTF-8', 'UTF-16BE//TRANSLIT//IGNORE', $string);
		// echo iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $string).'<br />';
		// 	exit;
      $characters = array(); 
      for ($i = 0; $i < strlen($drawingString); $i++) { 
         $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]); 
      } 
      $glyphs = $font->glyphNumbersForCharacters($characters); 
      $widths = $font->widthsForGlyphs($glyphs); 
      $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontsize; 
      return $stringWidth; 
    }
}