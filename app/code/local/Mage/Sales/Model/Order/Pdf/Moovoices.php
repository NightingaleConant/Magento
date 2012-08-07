<?php
/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Invoices extends Mage_Sales_Model_Order_Pdf_Aabstract
{    
    private $_nudgeY;
    private $_itemsY;    
       

	/***********************************************
        * Shifter Template - START
    ************************************************/
    public function getPdf2($order)
	{
        $this->_beforeGetPdf();
		$this->_initRenderer('invoices');

		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($style, 10);
       		  
           
		if ($order->getStoreId()) {
			Mage::app()->getLocale()->emulate($order->getStoreId());
		}
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
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
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->_setFontRegular($page, $fontsize_overall);
		}            

      
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
		$counter = 1;   
		$itemsCollection =  $order->getAllVisibleItems();
		$total_items     = count($itemsCollection);

		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontRegular($page, $fontsize_productline);          

		foreach ($itemsCollection as $item)
		{
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
				$size_id  = $options['info_buyRequest']['super_attribute'][$size_attribute_id]; #size
				$color_id = $options['info_buyRequest']['super_attribute'][$color_attribute_id]; #color     



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
					
					// print_r($options);
					// 					exit;				
				}
		
       			$page->drawText($qty, $qtyX, $this->y , 'UTF-8');
                   $page->drawText($sku, $skuX, $this->y , 'UTF-8');
                   $page->drawText($item->getName(), $productX, $this->y , 'UTF-8');
                   $page->drawText($color, $colorX, $this->y , 'UTF-8');
                   $page->drawText($size, $sizeX, $this->y , 'UTF-8');                    

				$price = $qty * $item->getPriceInclTax();
				
                   $page->drawText(number_format($price, 2, '.', ','), $priceX, $this->y , 'UTF-8');
                   $this->y -= 15;
                   
                   if(($counter % $cutoff_no) == 0 && $counter != $total_items){
                       $page = $this->newPage2();
                   }
                   
                   $counter++;
               }
		} 
	
       /***********************************************
       * Shifter Template - END
       ************************************************/
       $this->_afterGetPdf();
	return $pdf;
   }

   /***********************************************
    * Default Template - START
    ************************************************/

	public function getPdfDefault($order){
        $this->_beforeGetPdf();
		$this->_initRenderer('invoices');

		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($style, 10);

        //foreach ($invoices as $invoice)
		// {    		  

			if ($order->getStoreId()) 
			{
				Mage::app()->getLocale()->emulate($order->getStoreId());
			}
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
			$pdf->pages[] = $page;           


            /***********************************************************
            * CONFIGURATIONS
            ***********************************************************/

			// all y values must be positive, all x positive
			// bottom left is 0,0

            ## DEFAULT VALUES
            $columnYNudgeDefault        = 600;
            $barcodeXYDefault           = '468,800';
            $orderDateXYDefault         = '160,755';
            $orderIdXYDefault           = '40,755';
            $addressXYDefault           = '40,705';  
            $addressFooterXYDefault     = '50,160'; // * new
            $returnAddressFooterXYDefault = '320,140'; // * new

            $cutoff_noDefault           = 10;
            $fontsize_overallDefault    = 9;
            $fontsize_productlineDefault= 9;
            $fontsize_returnaddressDefault= 9;
            $fontsize_returnaddresstopDefault= 7;
            $fontsize_shipaddressDefault= 15;

            $skuXDefault                = 300;
            $qtyXDefault                = 40;
            $productXDefault            = 80;
            $colorXDefault              = 345;
            $sizeXDefault               = 430;
            $priceXDefault              = 505; 

			$colorAttributeNameDefault 	= 'color';
            $sizeAttributeNameDefault	= 'size';

			$showReturnLogoYNDefault	= 'Y';
			$returnLogoXYDefault		= '312,185';

			// hard-coded (not an option in config yet)
			//
			$showPriceYNDefault			= 'N';
			$showColorYNDefault			= 'N';
			$showSizeYNDefault			= 'N';
			$boldLastAddressLineYNDefault = 'Y';
			$fontsizeTitlesDefault		= 14;
			$fontColorGrey				= 0.6;
			$fontColorReturnAddressFooter = 0.2;
			$boxBkgColorDefault			= 0.7;
			//
			$showPriceYN				= $showPriceYNDefault;
			$showColorYN				= $showColorYNDefault;
			$showSizeYN					= $showSizeYNDefault;
			$boldLastAddressLineYN		= $boldLastAddressLineYNDefault;
 			$fontsize_returnaddresstop 	= $fontsize_returnaddresstopDefault;
			$fontsizeTitles 			= $fontsizeTitlesDefault;
			$boxBkgColor				= $boxBkgColorDefault;

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
            $addressFooterXY 		=  explode(",", $this->_getConfig('footer_address', $addressFooterXYDefault));

            $cutoff_no              = $this->_getConfig('pack_noofitems', $cutoff_noDefault, false);
            $fontsize_overall       = $this->_getConfig('pickpack_fontsizeoverall', $fontsize_overallDefault, false);
            $fontsize_productline   = $this->_getConfig('pickpack_fontsizeproductline', $fontsize_productlineDefault, false);
            $fontsize_returnaddress = $this->_getConfig('pickpack_returnfont', $fontsize_returnaddressDefault, false);
            $fontsize_shipaddress   = $this->_getConfig('pickpack_shipfont', $fontsize_shipaddressDefault, false);

            $skuX           		= $this->_getConfig('pack_sku', $skuXDefault);
            $qtyX           		= $this->_getConfig('pack_qty', $qtyXDefault);
            $productX       		= $this->_getConfig('pack_product', $productXDefault);
            $colorX         		= $this->_getConfig('pack_color', $colorXDefault);
            $sizeX          		= $this->_getConfig('pack_size', $sizeXDefault);
            $priceX         		= $this->_getConfig('pack_price', $priceXDefault);            

            $showReturnLogoYN       = $this->_getConfig('pickpack_returnlogo', $showReturnLogoYNDefault, 0, false);            
            $returnLogoXY	        = explode(",", $this->_getConfig('pickpack_nudgelogo', $returnLogoXYDefault));            
            $returnAddressFooterXY	= explode(",", $this->_getConfig('pickpack_returnaddress', $returnAddressFooterXYDefault));            



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
			$page->setFillColor(new Zend_Pdf_Color_GrayScale($boxBkgColor));			
			$page->setLineColor(new Zend_Pdf_Color_GrayScale($boxBkgColor));			
			$page->drawRectangle(20, ($orderIdXY[1]-5), 570, ($orderIdXY[1]+18));
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));			

			$this->_setFontBold($page,($fontsizeTitles+4));
            $page->drawText('/   '.Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), $orderDateXY[0], $orderDateXY[1], 'UTF-8');
			$page->drawText('#'.$order->getRealOrderId(), $orderIdXY[0], $orderIdXY[1], 'UTF-8');
			$this->_setFontRegular($page, $fontsize_overall);

			// header logo
   			$this->insertLogo($page, $order->getStore());

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
			$page->setLineColor(new Zend_Pdf_Color_GrayScale($boxBkgColorDefault));
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
				if((($addressXY[1]-$line_height) > 0) && $addressXY[0] > 0 && trim($item) != '~')
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
				if(isset($shippingAddressArray[$i]) && (($addressFooterXY[1]-$line_height) > 0) && ($addressFooterXY[0] > 0) && trim($shippingAddressArray[$i]) != '~')
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
			$page->setFillColor(new Zend_Pdf_Color_GrayScale($boxBkgColor));			
			$page->setLineColor(new Zend_Pdf_Color_GrayScale($boxBkgColorDefault));			
			$page->drawRectangle((20), ($columnYNudge-2), (570), ($columnYNudge+10));

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

					if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
					{
							$sku = $item->getProductOptionByCode('simple_sku');
					} 
					else 
					{
					         $sku = $item->getSku();
					}

                    $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();

        			$page->drawText($qty, ($qtyX+8), $this->y , 'UTF-8');
                    $page->drawText($sku, $skuX, $this->y , 'UTF-8');
					// $page->drawText($item->getSku(), $skuX, $this->y , 'UTF-8');
                    // $page->drawText($item->getSku(), $skuX, $this->y , 'UTF-8');
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
			// } 
            #exit;

        }    		
        /***********************************************
        * Default Template - END
        ************************************************/
        $this->_afterGetPdf();
		return $pdf;
    }












 /**
   getPickJim Template SEPARATED - START
    ************************************************/

public function getPickJim($orders=array())
{
	$this->_beforeGetPdf();
	$this->_initRenderer('invoices');

	$pdf = new Zend_Pdf();
	$this->_setPdf($pdf);
	$style = new Zend_Pdf_Style();
	$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
	$pdf->pages[] = $page;
  
	$this->y = 777;

	$skuX = 100;
	$qtyX = 50;
	$productX = 200;
	$fontsize_overall = 15;
	$fontsize_productline = 9;
    	
	$this->_setFontBold($style, 10);

	// page title
	$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
	$this->_setFontItalic($page,20);
	$page->drawText(Mage::helper('sales')->__('Order-separated Pick List'), 31, 800, 'UTF-8');

	$this->y = 777;

	foreach ($orders as $orderSingle) 
	{
		$order = Mage::getModel('sales/order')->load($orderSingle);

		$putOrderId= $order->getRealOrderId();
		$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));
		$page->drawRectangle(27, $this->y, 570, ($this->y - 20));

		// order #
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
		$this->_setFontBold($page,14);
		$page->drawText(Mage::helper('sales')->__('#') .$order->getRealOrderId(), 31, ($this->y - 15), 'UTF-8');

		$barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
		if($barcodes==1)
		{
			$barcodeString = $this->convertToBarcodeString($order->getRealOrderId());
			$page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
			$page->setFont(Zend_Pdf_Font::fontWithPath(dirname(__FILE__)  . '/' . 'Code128bWin.ttf'), 16);
			$page->drawText($barcodeString, 250, ($this->y - 20), 'CP1252');
		}

		$shmethod = Mage::getStoreConfig('pickpack_options/picks/pickpack_shipmethod');
		if($shmethod == 0)
		{
			$this->_setFontBold($page,12);
			$shippingMethod  = $order->getShippingDescription();
			$page->drawText($shippingMethod, 420, ($this->y - 15), 'UTF-8');
		}

		$this->y -= 30;

		$itemsCollection =  $order->getAllVisibleItems();//$order->getItemsCollection();
        $total_items     = count($itemsCollection);
        $this->_setFontRegular($page, 10); 
		
		foreach ($itemsCollection as $item)
		{
			if ($this->y<15) 
			{
				$page = $this->newPage();
			}

			/* Draw item */
			$nameyn = Mage::getStoreConfig('pickpack_options/messages/pickpack_name');
			$namenudge = Mage::getStoreConfig('pickpack_options/picks/pickpack_namenudge');
			
			if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
			{
				$sku = $item->getProductOptionByCode('simple_sku');
			} 
			else 
			{
				$sku = $item->getSku();
			}

            $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();

			$page->drawText($qty. ' x ', 35, $this->y , 'UTF-8');
            $page->drawText($sku, 60, $this->y , 'UTF-8');
            if($nameyn == 1) $page->drawText($item->getName(), (120+intval($namenudge)), $this->y , 'UTF-8');
			
			$this->y -= 10;
		}
		
	}

	$printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickprint');
	if($printdates!=1)
	{
		$this->_setFontBold($page);
		$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time()+(14*60*60))), 210, 18, 'UTF-8');
	}
	$this->_afterGetPdf();

	// if ($order->getStoreId()) {
	// 		            Mage::app()->getLocale()->revert();
	// 		        }
	return $pdf;	
	/**
	getPickJim Template - END
	*********************************************
	************************************************/

}






 /**
   getPickCOMBINED Template - START
    ************************************************/

public function getPickCombined($orders=array())
{
	$this->_beforeGetPdf();
	$this->_initRenderer('invoices');

	$pdf = new Zend_Pdf();
	$this->_setPdf($pdf);
	$style = new Zend_Pdf_Style();
	$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
	$pdf->pages[] = $page;
  
	$this->y = 777;

	$skuX = 100;
	$qtyX = 50;
	$productX = 200;
	$fontsize_overall = 15;
	$fontsize_productline = 9;
    $total_quantity = 0;
    $total_cost = 0;

	// page title
	$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
	$this->_setFontBold($style, 10);
	$this->_setFontItalic($page,20);
	$page->drawText(Mage::helper('sales')->__('Order-combined Pick List'), 31, 800, 'UTF-8');
	
	foreach ($orders as $orderSingle) 
	{
		$order = Mage::getModel('sales/order')->load($orderSingle);		
		
		// $putOrderId= $order->getRealOrderId();
		// $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
		// 	$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.92));
		// $page->drawRectangle(27, $this->y, 570, ($this->y - 20));

		// $this->y -= 30;

		$itemsCollection =  $order->getAllVisibleItems();//$order->getItemsCollection();
        // $total_items     = count($itemsCollection);
        $this->_setFontRegular($page, 10); 
		
		foreach ($itemsCollection as $item)
		{
			
			/* Draw item */
			$nameyn = Mage::getStoreConfig('pickpack_options/messages/pickpack_name');
			$namenudge = Mage::getStoreConfig('pickpack_options/picks/pickpack_namenudge');
			// 				
			if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) 
			{
				$sku = $item->getProductOptionByCode('simple_sku');
			} 
			else 
			{
				$sku = $item->getSku();
			}
			
			$qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();
			$total_quantity = $total_quantity + $qty;
			// cost
			// $ke = $item->getSku();
			// $quan=$quan+$item->_num;
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);								
			$cost=$amount+$qty*(is_object($product)?$product->getCost():0);
			$total_cost = $total_cost + $cost;

			$sku_master[$sku] = $sku;
			$sku_name[$sku] = $item->getName();
			if(isset($sku_qty[$sku])) $sku_qty[$sku] = ($sku_qty[$sku]+$qty);
			else $sku_qty[$sku] = $qty;
			// $page->drawText($qty. ' x ', 35, $this->y , 'UTF-8');
			// 		$page->drawText($sku, 60, $this->y , 'UTF-8');
			// 		if($nameyn == 1) $page->drawText($item->getName(), (120+intval($namenudge)), $this->y , 'UTF-8');
			// 				
			
		}
		
	}


	// $nitems = array();
	$sku_test = array();
	
	ksort($sku_master);
	
	foreach($sku_master as $key =>$sku)
	{
		
		if(!in_array($sku,$sku_test))
		{
			$sku_test[] = $sku;
			
			if ($this->y<15) 
			{
				$page = $this->newPage();
			}
		
			$page->drawText($sku_qty[$sku]. ' x ', 35, $this->y , 'UTF-8');
			$page->drawText($sku, 60, $this->y , 'UTF-8');
			if($nameyn == 1) $page->drawText($sku_name[$sku], (120+intval($namenudge)), $this->y , 'UTF-8');
		
			$this->y -= 10;
		}
	}

	$this->y -= 30;
	$this->_setFontItalic($page,12);

	$currency = Mage::getStoreConfig('pickpack_options/messages/pickpack_currency');
	$currency = Mage::app()->getLocale()->currency($currency)->getSymbol();
	$costyn = Mage::getStoreConfig('pickpack_options/messages/pickpack_cost');
	$countyn = Mage::getStoreConfig('pickpack_options/messages/pickpack_count');

	if($countyn==0)
	{
		$page->drawText('Total quantity : '.$total_quantity, 375, $this->y , 'UTF-8');
	}
	
	$this->y -= 20;
	
	if($costyn == 0)
	{
		$page->drawText('Total cost : '.$currency."  ".($total_cost), 375, $this->y , 'UTF-8');
	}
		
	$printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_packprint');
	if($printdates!=1)
	{
		$this->_setFontBold($page);
		$page->drawText(Mage::helper('sales')->__('Printed:').'   '.date('l jS F Y h:i:s A', Mage::getModel('core/date')->timestamp(time()+(14*60*60))), 210, 18, 'UTF-8');
	}
	$this->_afterGetPdf();

	// if ($order->getStoreId()) {
	// 		            Mage::app()->getLocale()->revert();
	// 		        }
	return $pdf;	
	/**
	getPickCOMBINED Template - END
	*********************************************
	************************************************/

}







	public function getPdfPicklist($order){
        $this->_beforeGetPdf();
		$this->_initRenderer('invoices');

		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($style, 10);
        /***********************************************
        * PickList Template - START
        ************************************************/ 
            
        //foreach ($invoices as $invoice)
		{    		  
           
			if ($order->getStoreId()) {
				Mage::app()->getLocale()->emulate($order->getStoreId());
			}
			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
			$pdf->pages[] = $page;           
			
                        
            /***********************************************************
            * CONFIGURATIONS
            ***********************************************************/
            
			// all y values must be positive, all x positive
			// bottom left is 0,0
			
            ## DEFAULT VALUES
            $columnYNudgeDefault        = 600;
            $barcodeXYDefault           = '468,800';
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
            $fontsize_overall       = $this->_getConfig('pickpack_fontsizeoverall', $fontsize_overallDefault, false);
            $fontsize_productline   = $this->_getConfig('pickpack_fontsizeproductline', $fontsize_productlineDefault, false);
            $fontsize_returnaddress = $this->_getConfig('pickpack_returnfont', $fontsize_returnaddressDefault, false);
            $fontsize_shipaddress   = $this->_getConfig('pickpack_shipfont', $fontsize_shipaddressDefault, false);
            
            $skuX           		= $this->_getConfig('pack_sku', $skuXDefault);
            $qtyX           		= $this->_getConfig('pack_qty', $qtyXDefault);
            $productX       		= $this->_getConfig('pack_product', $productXDefault);
            $colorX         		= $this->_getConfig('pack_color', $colorXDefault);
            $sizeX          		= $this->_getConfig('pack_size', $sizeXDefault);
            $priceX         		= $this->_getConfig('pack_price', $priceXDefault);            
                  
            $showReturnLogoYN       = $this->_getConfig('pickpack_returnlogo', $showReturnLogoYNDefault, 0, false);            
            $returnLogoXY	        = explode(",", $this->_getConfig('pickpack_nudgelogo', $returnLogoXYDefault));            
            $returnAddressFooterXY	= explode(",", $this->_getConfig('pickpack_returnaddress', $returnAddressFooterXYDefault));            

			

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
			$page->drawRectangle(20, ($orderIdXY[1]-5), 570, ($orderIdXY[1]+18));
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));			

			$this->_setFontBold($page,($fontsizeTitles+4));
            $page->drawText('/   '.Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), $orderDateXY[0], $orderDateXY[1], 'UTF-8');
			$page->drawText('#'.$order->getRealOrderId(), $orderIdXY[0], $orderIdXY[1], 'UTF-8');
			$this->_setFontRegular($page, $fontsize_overall);

			// header logo
   			$this->insertLogo($page, $order->getStore());
   
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
			$page->drawRectangle((20), ($columnYNudge-2), (570), ($columnYNudge+10));

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
                                     
        			$page->drawText($qty, ($qtyX+8), $this->y , 'UTF-8');
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
		$show_price = 0;
		$this->_beforeGetPdf();
		$this->_initRenderer('invoices');

		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($style, 10);
        
         
    		foreach ($invoices as $invoice)
    		{
    			if ($invoice->getStoreId()) {
    				Mage::app()->getLocale()->emulate($invoice->getStoreId());
    			}
    			$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
    			$pdf->pages[] = $page;
    
    			$order = $invoice->getOrder();
    
    			/* Add image */
    			$this->insertLogo($page, $order->getStore());
    
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
    			$page->drawRectangle(25, $this->y, 570, $this->y -20);
    
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
		/* Add new table head */
		$show_price = 0;
		$page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
		$this->_getPdf()->pages[] = $page;
		$this->y = 800;


		if (!empty($settings['table_header'])) {
			$this->_setFontRegular($page);
			$page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
			$page->setLineWidth(0.5);

			//                   ? xpos header rectangle  ? width            ? height header rectangle
			$page->drawRectangle(25, $this->y, 570, $this->y-15);

			$this->y -=10;

			$page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
			$page->drawText(Mage::helper('sales')->__('Product'), 35, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('SKU'), 255, $this->y, 'UTF-8');
			if($show_price > 0) $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('QTY'), 430, $this->y, 'UTF-8');
			//$page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
			if($show_price > 0) $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');

			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$this->y -=20;
		}

		return $page;
	}
    
    private function newPage2(){
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
		$this->_getPdf()->pages[]     = $page;
        $this->y                      = $this->_itemsY;  
        
        $fontsize_productlineDefault = 9;
        $fontsize_productline   = $this->_getConfig('pickpack_fontsizeproductline', $fontsize_productlineDefault, false);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, $fontsize_productline);       
        return $page;    
    }  
    
    
        
    private function _getConfig($field, $default = '', $add_default = true, $group = 'wonder'){
        $value = Mage::getStoreConfig('pickpack_options/'.$group.'/'.$field);
        if(trim($value) == ''){
            return $default;
        }else{
            if(strpos($value, ',') !== false && strpos($default, ',') !== false){
                $values = explode(",", $value);
                $defaults = explode(",", $default);
                if($add_default){
                    $value = ($values[0] + $defaults[0]) . ',' . ($values[1] + $defaults[1]);
                }                
            }else{
                $value = ($add_default) ? ($value + $default) : $value; 
            }            
            return $value;
        }
    }
}