<?php
/**
 * Product_import.php
 * CommerceThemes @ InterSEC Solutions LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.commercethemes.com/LICENSE-M1.txt
 *
 * @category   Product
 * @package    Productimport
 * @copyright  Copyright (c) 2003-2009 CommerceThemes @ InterSEC Solutions LLC. (http://www.commercethemes.com)
 * @license    http://www.commercethemes.com/LICENSE-M1.txt
 */ 

class Mage_Catalog_Model_Convert_Adapter_Productimport
extends Mage_Catalog_Model_Convert_Adapter_Product
{
	
	/**
	* Save product (import)
	* 
	* @param array $importData 
	* @throws Mage_Core_Exception
	* @return bool 
	*/
	public function saveRow( array $importData )
	{
		#$product = $this -> getProductModel();
		$product = $this->getProductModel()
            ->reset();
		#$product -> setData( array() );

		#if ( $stockItem = $product -> getStockItem() ) {
			#$stockItem -> setData( array() );
		#} 
		
		if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
            }
        }
        else {
            $store = $this->getStoreByCode($importData['store']);
        }
		
		if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
		}

		if (empty($importData['sku'])) {
				$message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'sku');
				Mage::throwException($message);
		}
		$product->setStoreId($store->getId());
		$productId = $product->getIdBySku($importData['sku']);

		$iscustomoptions = "false"; //sets currentcustomoptionstofalse
		$finalsuperattributepricing = "";
		$finalsuperattributetype = $importData['type'];
		if (isset($importData['super_attribute_pricing']) && $importData['super_attribute_pricing'] !="") {
    	$finalsuperattributepricing = $importData['super_attribute_pricing'];
		}
		$new = true; // fix for duplicating attributes error
		
		if ($productId) {
            $product->load($productId);
						$new = false; // fix for duplicating attributes error
        }
        else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();

            /**
             * Check product define type
             */
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
                Mage::throwException($message);
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);
            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'attribute_set');
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for new products not defined', $field);
                    Mage::throwException($message);
                }
            }
        }
		
		$this->setProductTypeInstance($product);
		
		// delete disabled products
		// note "Disabled text should be converted to handle multi-lanugage values aka age::helper('catalog')->__(''); type deal
		/*
		if ( $importData['status'] == 'Disabled' ) {
			$product = Mage :: getSingleton( 'catalog/product' ) -> load( $productId );
			$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'image' ) ) );
			$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'small_image' ) ) );
			$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'thumbnail' ) ) );
			$media_gallery = $product -> getData( 'media_gallery' );
			foreach ( $media_gallery['images'] as $image ) {
				$this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $image['file'] ) );
			} 
			$product -> delete();
			return true;
		} 
		*/
		
		$currentproducttype = $importData['type'];
		
		if ($importData['type'] == 'configurable') {
			
			$product->setCanSaveConfigurableAttributes(true);
			$configAttributeCodes = $this->userCSVDataAsArray($importData['config_attributes']);
			$usingAttributeIds = array();

			/***
			* Check the product's super attributes (see catalog_product_super_attribute table), and make a determination that way.
			**/
			$cspa  = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
			$attr_codes = array();
			if(isset($cspa) && !empty($cspa)){ //found attributes
				foreach($cspa as $cs_attr){
				//$attr_codes[$cs_attr['attribute_id']] = $cs_attr['attribute_code'];
					$attr_codes[] = $cs_attr['attribute_id'];
				}
			}


			foreach($configAttributeCodes as $attributeCode) {
				$attribute = $product->getResource()->getAttribute($attributeCode);
				if ($product->getTypeInstance()->canUseAttribute($attribute)) {
					//if (!in_array($attributeCode,$attr_codes)) { // fix for duplicating attributes error
					if ($new) { // fix for duplicating attributes error // <---------- this must be true to fill $usingAttributes
						$usingAttributeIds[] = $attribute->getAttributeId();
					}
				}
			}
			if (!empty($usingAttributeIds)) {
				$product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);
				$updateconfigurablearray = array();
				$insidearraycount=0;
				$finalarraytoimport = $product->getTypeInstance()->getConfigurableAttributesAsArray();
				$updateconfigurablearray = $product->getTypeInstance()->getConfigurableAttributesAsArray();
					
					foreach($updateconfigurablearray as $eacharrayvalue) {	
					 if($this->getBatchParams('configurable_use_default') != "") {			
					 	$finalarraytoimport[$insidearraycount]['use_default'] = $this->getBatchParams('configurable_use_default'); //added in 1.5.x 
						//<var name="configurable_use_default"><![CDATA[1]]></var>
					 }
					 $finalarraytoimport[$insidearraycount]['label'] = $eacharrayvalue['frontend_label'];
					 #$finalarraytoimport[$insidearraycount]['values'] = array( );
					 #$attribute = Mage::getModel('catalog/product_type_configurable_attribute')->setProductAttribute($eacharrayvalue['attribute_id']);
					 #$attribute->setStoreLabel($eacharrayvalue['frontend_label']);
					 #print_r($attribute->getStoreLabel());
					 $insidearraycount+=1;
					}
				#$attribute->save();
				#print_r($finalarraytoimport);
				#$product->setConfigurableAttributesData($product->getTypeInstance()->getConfigurableAttributesAsArray());
				$product->setConfigurableAttributesData($finalarraytoimport);
				$product->setCanSaveConfigurableAttributes(true);
				$product->setCanSaveCustomOptions(true);
			}
			if (isset($importData['associated'])) {
				$product->setConfigurableProductsData($this->skusToIds($importData['associated'], $product));
			}
		}
		//THIS IS FOR DOWNLOADABLE PRODUCTS
		if ($importData['type'] == 'downloadable' && $importData['downloadable_options'] != "") {
			if ($new) {
			    $downloadableitems = array();
			  	$filearrayforimport = array();
			  	$filenameforsamplearrayforimport = array();
				#$filenameforsamplearrayforimport = "";
				$downloadableitemsoptionscount=0;
				//THIS IS FOR DOWNLOADABLE OPTIONS
				$commadelimiteddata = explode('|',$importData['downloadable_options']);
				foreach ($commadelimiteddata as $data) {
					$configBundleOptionsCodes = $this->userCSVDataAsArray($data);
					
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_delete'] = 0;
					$downloadableitems['link'][$downloadableitemsoptionscount]['link_id'] = 0;
					$downloadableitems['link'][$downloadableitemsoptionscount]['title'] = $configBundleOptionsCodes[0];
					$downloadableitems['link'][$downloadableitemsoptionscount]['price'] = $configBundleOptionsCodes[1];
					$downloadableitems['link'][$downloadableitemsoptionscount]['number_of_downloads'] = $configBundleOptionsCodes[2];
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_shareable'] = 2;
					if(isset($configBundleOptionsCodes[5])) {
						#$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = '';
						if($configBundleOptionsCodes[3] == "file") {
						#$filenameforsamplearrayforimport = $configBundleOptionsCodes[5];
						$filenameforsamplearrayforimport[] = array('file'  => ''.$configBundleOptionsCodes[5].'' , 'name'  => ''.$configBundleOptionsCodes[0].'' , 'price'  => ''.$configBundleOptionsCodes[1].'');
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => ''.$configBundleOptionsCodes[5].'', 'type' => 'file', 'url'  => '');
						} else {
						
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => '[]', 'type' => 'url', 'url'  => ''.$configBundleOptionsCodes[5].'');
						}
					} else {
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = '';
					}
					$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = '';
					$downloadableitems['link'][$downloadableitemsoptionscount]['type'] = $configBundleOptionsCodes[3];
					#$downloadableitems['link'][$downloadableitemsoptionscount]['link_url'] = $configBundleOptionsCodes[4];
					if($configBundleOptionsCodes[3] == "file") {
						#$filearrayforimport = array('file'  => 'media/import/mypdf.pdf' , 'name'  => 'asdad.txt', 'size'  => '316', 'status'  => 'old');
						#$document_directory =  Mage :: getBaseDir( 'media' ) . DS . 'import' . DS;
						#echo "DIRECTORY: " . $document_directory;
						#$filearrayforimport = '[{"file": "/home/discou33/public_html/media/import/mypdf.pdf", "name": "mypdf.pdf", "status": "new"}]';
						#$filearrayforimport = '[{"file": "mypdf.pdf", "name": "quickstart.pdf", "size": 324075, "status": "new"}]';
						$filearrayforimport[] = array('file'  => ''.$configBundleOptionsCodes[4].'' , 'name'  => ''.$configBundleOptionsCodes[0].'' , 'price'  => ''.$configBundleOptionsCodes[1].'');
						
						if(isset($configBundleOptionsCodes[5])) {
							if($configBundleOptionsCodes[5] == 0) {
											$linkspurchasedstatus = 0;
											$linkspurchasedstatustext = false;
							} else {
											$linkspurchasedstatus = 1;
											$linkspurchasedstatustext = true;
							}
							$product->setLinksPurchasedSeparately($linkspurchasedstatus);
							$product->setLinksPurchasedSeparately($linkspurchasedstatustext);
						}
						
						
						#$product->setLinksPurchasedSeparately(0);
						#$product->setLinksPurchasedSeparately(false);
					
						#$files = Zend_Json::decode($filearrayforimport);
						#$files = "mypdf.pdf";
						
						#$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = $filearrayforimport;
					} else if($configBundleOptionsCodes[3] == "url") {
						$downloadableitems['link'][$downloadableitemsoptionscount]['link_url'] = $configBundleOptionsCodes[4];
					}
					$downloadableitems['link'][$downloadableitemsoptionscount]['sort_order'] = 0;
			    $product->setDownloadableData($downloadableitems);
					$downloadableitemsoptionscount+=1;
				}
				#print_r($downloadableitems);
			} else {
					//first delete all links then we update
				  $download_info=Mage::getModel('downloadable/product_type');
					$download_info->setProduct($product);
					if ($download_info->hasLinks()) {
						$_links=$download_info->getLinks();
						foreach ($_links as $_link) {
										$_link->delete();
						}
					}
					
					//begin update
					$downloadableitems = array();
			  			$filearrayforimport = array();
				$filenameforsamplearrayforimport = "";
				$downloadableitemsoptionscount=0;
				//THIS IS FOR DOWNLOADABLE OPTIONS
				$commadelimiteddata = explode('|',$importData['downloadable_options']);
				foreach ($commadelimiteddata as $data) {
					$configBundleOptionsCodes = $this->userCSVDataAsArray($data);
					
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_delete'] = 0;
					$downloadableitems['link'][$downloadableitemsoptionscount]['link_id'] = 0;
					$downloadableitems['link'][$downloadableitemsoptionscount]['title'] = $configBundleOptionsCodes[0];
					$downloadableitems['link'][$downloadableitemsoptionscount]['price'] = $configBundleOptionsCodes[1];
					$downloadableitems['link'][$downloadableitemsoptionscount]['number_of_downloads'] = $configBundleOptionsCodes[2];
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_shareable'] = 2;
					if(isset($configBundleOptionsCodes[5])) {
						#$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = '';
						if($configBundleOptionsCodes[3] == "file") {
						#$filenameforsamplearrayforimport = $configBundleOptionsCodes[5];
						$filenameforsamplearrayforimport[] = array('file'  => ''.$configBundleOptionsCodes[5].'' , 'name'  => ''.$configBundleOptionsCodes[0].'' , 'price'  => ''.$configBundleOptionsCodes[1].'');
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => ''.$configBundleOptionsCodes[5].'', 'type' => 'file', 'url'  => '');
						} else {
						
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => '[]', 'type' => 'url', 'url'  => ''.$configBundleOptionsCodes[5].'');
						}
					} else {
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = '';
					}
					$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = '';
					$downloadableitems['link'][$downloadableitemsoptionscount]['type'] = $configBundleOptionsCodes[3];
					#$downloadableitems['link'][$downloadableitemsoptionscount]['link_url'] = $configBundleOptionsCodes[4];
					if($configBundleOptionsCodes[3] == "file") {
						#$filearrayforimport = array('file'  => 'media/import/mypdf.pdf' , 'name'  => 'asdad.txt', 'size'  => '316', 'status'  => 'old');
						#$document_directory =  Mage :: getBaseDir( 'media' ) . DS . 'import' . DS;
						#echo "DIRECTORY: " . $document_directory;
						#$filearrayforimport = '[{"file": "/home/discou33/public_html/media/import/mypdf.pdf", "name": "mypdf.pdf", "status": "new"}]';
						#$filearrayforimport = '[{"file": "mypdf.pdf", "name": "quickstart.pdf", "size": 324075, "status": "new"}]';
						#echo "FILE: " . $configBundleOptionsCodes[4];
						$filearrayforimport[] = array('file'  => ''.$configBundleOptionsCodes[4].'' , 'name'  => ''.$configBundleOptionsCodes[0].'' , 'price'  => ''.$configBundleOptionsCodes[1].'');
						
						if(isset($configBundleOptionsCodes[5])) {
							if($configBundleOptionsCodes[5] == 0) {
											$linkspurchasedstatus = 0;
											$linkspurchasedstatustext = false;
							} else {
											$linkspurchasedstatus = 1;
											$linkspurchasedstatustext = true;
							}
							$product->setLinksPurchasedSeparately($linkspurchasedstatus);
							$product->setLinksPurchasedSeparately($linkspurchasedstatustext);
						}
						
						
						#$product->setLinksPurchasedSeparately(0);
						#$product->setLinksPurchasedSeparately(false);
					
						#$files = Zend_Json::decode($filearrayforimport);
						#$files = "mypdf.pdf";
						
						#$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = $filearrayforimport;
					} else if($configBundleOptionsCodes[3] == "url") {
						$downloadableitems['link'][$downloadableitemsoptionscount]['link_url'] = $configBundleOptionsCodes[4];
					}
					$downloadableitems['link'][$downloadableitemsoptionscount]['sort_order'] = 0;
			    $product->setDownloadableData($downloadableitems);
					$downloadableitemsoptionscount+=1;
				}
			
			}
		}
		//THIS IS FOR BUNDLE PRODUCTS
		if ($importData['type'] == 'bundle') {
			if ($new) {
				$optionscount=0;
				$items = array();
				//THIS IS FOR BUNDLE OPTIONS
				$commadelimiteddata = explode('|',$importData['bundle_options']);
				foreach ($commadelimiteddata as $data) {
					$configBundleOptionsCodes = $this->userCSVDataAsArray($data);
					$titlebundleselection = ucfirst(str_replace('_',' ',$configBundleOptionsCodes[0]));
					$items[$optionscount]['title'] = $titlebundleselection;
					$items[$optionscount]['type'] = $configBundleOptionsCodes[1];
					$items[$optionscount]['required'] = $configBundleOptionsCodes[2];
					$items[$optionscount]['position'] = $configBundleOptionsCodes[3];
					$items[$optionscount]['delete'] = 0;
					$optionscount+=1;
					
					
					if ($items) {
							$product->setBundleOptionsData($items);
					}
					$options_id = $product->getOptionId();
					$selections = array();
					$bundleConfigData = array();
					$optionscountselection=0;
					//THIS IS FOR BUNDLE SELECTIONS
					$commadelimiteddataselections = explode('|',$importData['bundle_selections']);
					foreach ($commadelimiteddataselections as $selection) {
						$configBundleSelectionCodes = $this->userCSVDataAsArray($selection);
						$selectionscount=0;
						foreach ($configBundleSelectionCodes as $selectionItem) {
							$bundleConfigData = explode(':',$selectionItem);
							$selections[$optionscountselection][$selectionscount]['option_id'] = $options_id;
							$selections[$optionscountselection][$selectionscount]['product_id'] = $product->getIdBySku($bundleConfigData[0]);
							$selections[$optionscountselection][$selectionscount]['selection_price_type'] = $bundleConfigData[1];
              $selections[$optionscountselection][$selectionscount]['selection_price_value'] = $bundleConfigData[2];
							$selections[$optionscountselection][$selectionscount]['is_default'] = $bundleConfigData[3];
							if(isset($bundleConfigData) && isset($bundleConfigData[4]) && $bundleConfigData[4] != '') {
							$selections[$optionscountselection][$selectionscount]['selection_qty'] = $bundleConfigData[4];
							$selections[$optionscountselection][$selectionscount]['selection_can_change_qty'] = $bundleConfigData[5];
							}
							if(isset($bundleConfigData) && isset($bundleConfigData[6]) && $bundleConfigData[6] != '') {
							$selections[$optionscountselection][$selectionscount]['position'] = $bundleConfigData[6];
							}
							$selections[$optionscountselection][$selectionscount]['delete'] = 0;
							$selectionscount+=1;
						}
						$optionscountselection+=1;
					}
					if ($selections) {
							$product->setBundleSelectionsData($selections);
					}
				
				}
				

        if ($product->getPriceType() == '0') {
            $product->setCanSaveCustomOptions(true);
            if ($customOptions = $product->getProductOptions()) {
                foreach ($customOptions as $key => $customOption) {
                    $customOptions[$key]['is_delete'] = 1;
                }
                $product->setProductOptions($customOptions);
            }
        }

        $product->setCanSaveBundleSelections();
			}
		}
		if ( isset( $importData['related'] ) ) {
			$linkIds = $this -> skusToIds( $importData['related'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setRelatedLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['upsell'] ) ) {
			$linkIds = $this -> skusToIds( $importData['upsell'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setUpSellLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['crosssell'] ) ) {
			$linkIds = $this -> skusToIds( $importData['crosssell'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setCrossSellLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['grouped'] ) ) {
			$linkIds = $this -> skusToIds( $importData['grouped'], $product );
			if ( !empty( $linkIds ) ) {
				$product -> setGroupedLinkData( $linkIds );
			} 
		} 

		if ( isset( $importData['category_ids'] ) ) {
			$product -> setCategoryIds( $importData['category_ids'] );
		} 


		if( isset($importData['tier_prices']) && !empty($importData['tier_prices']) ) {
			$this->_editTierPrices($product, $importData['tier_prices'], $store);
		}

		
		if ( isset( $importData['categories'] ) && $importData['categories'] !="" ) {
			if ( isset( $importData['store'] ) ) {
				$cat_store = $this -> _stores[$importData['store']];
			} else {
				$message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, required field "store" for new products not defined', $field );
				Mage :: throwException( $message );
			} 
			$categoryIds = $this -> _addCategories( $importData['categories'], $cat_store );
			if ( $categoryIds ) {
				$product -> setCategoryIds( $categoryIds );
			} 
		} 
		
		foreach ( $this -> _ignoreFields as $field ) {
			if ( isset( $importData[$field] ) ) {
				unset( $importData[$field] );
			} 
		} 
		
		if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
		}
		
		if ( isset( $importData['websites'] ) ) {
			$websiteIds = $product -> getWebsiteIds();
			if ( !is_array( $websiteIds ) ) {
				$websiteIds = array();
			} 
			$websiteCodes = explode( ',', $importData['websites'] );
			foreach ( $websiteCodes as $websiteCode ) {
				try {
					$website = Mage :: app() -> getWebsite( trim( $websiteCode ) );
					if ( !in_array( $website -> getId(), $websiteIds ) ) {
						$websiteIds[] = $website -> getId();
					} 
				} 
				catch ( Exception $e ) {
				} 
			} 
			$product -> setWebsiteIds( $websiteIds );
			unset( $websiteIds );
		} 
		
		$custom_options = array();

		foreach ( $importData as $field => $value ) {
			//SEEMS TO BE CONFLICTING ISSUES WITH THESE 2 CHOICES AND DOESNT SEEM TO REQUIRE THIS IN ALL THE TESTING SO LEAVING COMMENTED
			//if ( in_array( $field, $this -> _inventoryFields ) ) { 
				//continue;
			//} 
			/*
			if (in_array($field, $this->_inventorySimpleFields))
			{
				continue;
			}
			*/
			if ( in_array( $field, $this -> _imageFields ) ) {
				continue;
			} 
			
			$attribute = $this -> getAttribute( $field );
			if ( !$attribute ) {
			/* CUSTOM OPTION CODE */
if(strpos($field,':')!==FALSE && strlen($value)) {
   $values=explode('|',$value);
   if(count($values)>0) {
			$iscustomoptions = "true";
			
			foreach($values as $v) {
         $parts = explode(':',$v);
         $title = $parts[0];
			}
			//RANDOM ISSUE HERE SOMETIMES WITH TITLES OF LAST ITEM IN DROPDOWN SHOWING AS TITLE MIGHT NEED TO SEPERATE TITLE variables
      @list($title,$type,$is_required,$sort_order) = explode(':',$field);
      $title2 = ucfirst(str_replace('_',' ',$title));
      $custom_options[] = array(
         'is_delete'=>0,
         'title'=>$title2,
         'previous_group'=>'',
         'previous_type'=>'',
         'type'=>$type,
         'is_require'=>$is_required,
         'sort_order'=>$sort_order,
         'values'=>array()
      );
      foreach($values as $v) {
         $parts = explode(':',$v);
         $title = $parts[0];
         if(count($parts)>1) {
            $price_type = $parts[1];
         } else {
            $price_type = 'fixed';
         }
         if(count($parts)>2) {
            $price = $parts[2];
         } else {
            $price =0;
         }
         if(count($parts)>3) {
            $sku = $parts[3];
         } else {
            $sku='';
         }
         if(count($parts)>4) {
            $sort_order = $parts[4];
         } else {
            $sort_order = 0;
         }
         if(count($parts)>5) {
            $max_characters = $parts[5];
         } else {
            $max_characters = '';
         }
         if(count($parts)>6) {
            $file_extension = $parts[6];
         } else {
            $file_extension = '';
         }
         if(count($parts)>7) {
            $image_size_x = $parts[7];
         } else {
            $image_size_x = '';
         }
         if(count($parts)>8) {
            $image_size_y = $parts[8];
         } else {
            $image_size_y = '';
         }
         switch($type) {
            case 'file':
               /* TODO */
               $custom_options[count($custom_options) - 1]['price_type'] = $price_type;
               $custom_options[count($custom_options) - 1]['price'] = $price;
               $custom_options[count($custom_options) - 1]['sku'] = $sku;
               $custom_options[count($custom_options) - 1]['file_extension'] = $file_extension;
               $custom_options[count($custom_options) - 1]['image_size_x'] = $image_size_x;
               $custom_options[count($custom_options) - 1]['image_size_y'] = $image_size_y;
               break;
               
            case 'field':
               $custom_options[count($custom_options) - 1]['max_characters'] = $max_characters;
            case 'area':
               $custom_options[count($custom_options) - 1]['max_characters'] = $max_characters;
               /* NO BREAK */
               
            case 'date':
            case 'date_time':
            case 'time':
               $custom_options[count($custom_options) - 1]['price_type'] = $price_type;
               $custom_options[count($custom_options) - 1]['price'] = $price;
               $custom_options[count($custom_options) - 1]['sku'] = $sku;
               break;
                                          
            case 'drop_down':
            case 'radio':
            case 'checkbox':
            case 'multiple':
            default:
               $custom_options[count($custom_options) - 1]['values'][]=array(
                  'is_delete'=>0,
                  'title'=>$title,
                  'option_type_id'=>-1,
                  'price_type'=>$price_type,
                  'price'=>$price,
                  'sku'=>$sku,
                  'sort_order'=>$sort_order,
                  'max_characters'=>$max_characters,
               );
               break;
         }
      }
   }
}
/* END CUSTOM OPTION CODE */
				continue;
			} 

			$isArray = false;
			$setValue = $value;
			
			if ( $attribute -> getFrontendInput() == 'multiselect' ) {
				$value = explode( self :: MULTI_DELIMITER, $value );
				$isArray = true;
				$setValue = array();
			} 
			
			if ( $value && $attribute -> getBackendType() == 'decimal' ) {
				$setValue = $this -> getNumber( $value );
			} 
			
			if ( $attribute -> usesSource() ) {
				$options = $attribute -> getSource() -> getAllOptions( false );
				
				if ( $isArray ) {
					foreach ( $options as $item ) {
						if ( in_array( $item['label'], $value ) ) {
							$setValue[] = $item['value'];
						} 
					} 
				} 
				else {
					$setValue = null;
					foreach ( $options as $item ) {
					    #$item_label = (string)$item['label'];
							#$item_value = (string)$value;
							#echo "LABEL: " . $item_label . "<br>";
							#echo "VALUE: " . $item_value  . "<br>";
						  #echo "COMPARE: " . strcmp(strtolower($item_label), strtolower($item_value)) . "<br>";
						  #if (trim($item_label) == trim($item_value) || strcmp(strtolower($item_label), strtolower($item_value)) == 0) {
						if ( $item['label'] == $value ) {
							$setValue = $item['value'];
						} 
					} 
				} 
			} 
			
			$product -> setData( $field, $setValue );
		} 
		
		if ( !$product -> getVisibility() ) {
			$product -> setVisibility( Mage_Catalog_Model_Product_Visibility :: VISIBILITY_NOT_VISIBLE );
		} 
		
		$stockData = array();
		$inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
			? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
			: array(); 
			
		foreach ( $inventoryFields as $field ) {
			if ( isset( $importData[$field] ) ) {
				if ( in_array( $field, $this -> _toNumber ) ) {
					$stockData[$field] = $this -> getNumber( $importData[$field] );
				} 
				else {
					$stockData[$field] = $importData[$field];
				} 
			} 
		} 
		$product -> setStockData( $stockData );
		
		
		if($new || $this->getBatchParams('reimport_images') == "true") {  //starts CHECK FOR IF REIMPORTING IMAGES TO PRODUCTS IS TRUE
			//this is a check if we want to delete all images before import of images from csv
			if($this->getBatchParams('deleteall_andreimport_images') == "true") {
			$attributes = $product->getTypeInstance()->getSetAttributes();                     
						if (isset($attributes['media_gallery'])) {
								$gallery = $attributes['media_gallery'];
								//Get the images
								$galleryData = $product->getMediaGallery();                                                                                                                    
								foreach($galleryData['images'] as $image){
														//If image exists
										if ($gallery->getBackend()->getImage($product, $image['file'])) {
												$gallery->getBackend()->removeImage($product, $image['file']);
														if ( file_exists( $image['file'] ) ) {
															$ext = substr(strrchr($image['file'], '.'), 1);
															if( strlen( $ext ) == 4 ) {
																unlink (Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . $image['file']);
															}
														}
										}
								}                                                             
						}
			}		
		 if($importData["image"] != "" || $importData["small_image"] != "" || $importData["thumbnail"] != "" ) {		
			$mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();

			$arrayToMassAdd = array();
	
			foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
				if (isset($importData[$mediaAttributeCode])) {
					$file = $importData[$mediaAttributeCode];
					if(file_exists(Mage :: getBaseDir( 'media' ) . DS . 'import' . $file)){
						if (trim($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
							$arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
						}
					}
				}
			}
			
			if($this->getBatchParams('exclude_images') == "true") {
				#$product -> addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import/' . $file, $fields, false );
				$addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes($product, $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import', false);
			} else {
				#$product -> addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import/' . $file, $fields, false, false );
				$addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes($product, $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import', false, false);
			}
	
			foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
				$addedFile = '';
				if (isset($importData[$mediaAttributeCode . '_label'])) {
					$fileLabel = trim($importData[$mediaAttributeCode . '_label']);
					if (isset($importData[$mediaAttributeCode])) {
						$keyInAddedFile = array_search($importData[$mediaAttributeCode],
							$addedFilesCorrespondence['alreadyAddedFiles']);
						if ($keyInAddedFile !== false) {
							$addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
						}
					}
	
					if (!$addedFile) {
						$addedFile = $product->getData($mediaAttributeCode);
					}
					if ($fileLabel && $addedFile) {
						$mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
					}
				}
			}		
									
		} //end check on empty values
			
			if ( !empty( $importData['gallery'] ) ) {
				$galleryData = explode( ',', $importData["gallery"] );
				foreach( $galleryData as $gallery_img ) {
					try {
						if($this->getBatchParams('exclude_gallery_images') == "true") {
						  $product -> addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import/' . $gallery_img, null, false, true );
						} else {
						  $product -> addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import/' . $gallery_img, null, false, false );
						}
					} 
					catch ( Exception $e ) {
					} 
				} 
			} 
		
		
		  #} // this ends check if enabled
		} // this else is for check for if we can reimport products
		$product -> setIsMassupdate( true );
		$product -> setExcludeUrlRewrite( true );
		
		$product -> save();
		
		
		if ( isset( $importData['product_tags'] ) && $importData['product_tags'] !="" ) {
		
			#$configProductTags = $this->userCSVDataAsArray($importData['product_tags']);
			$configProductTags = explode(',', $importData['product_tags']);
			
			#foreach ($commadelimiteddata as $dataseperated) {
			foreach ($configProductTags as $tagName) {
				try {
				$commadelimiteddata = explode(':',$tagName);
				
				$tagName = $commadelimiteddata[1];
				$tagModel = Mage::getModel('tag/tag');
				$result = $tagModel->loadByName($tagName);
				#echo $result;
				#echo "PRODID: " . $product -> getIdBySku( $importData['sku'] ) . " Name: " . $tagName;
				$tagModel->setName($tagName)
									->setStoreId($importData['store'])
									->setStatus($tagModel->getApprovedStatus())
									->save();
									
				$tagRelationModel = Mage::getModel('tag/tag_relation');
				/*$tagRelationModel->loadByTagCustomer($product -> getIdBySku( $importData['sku'] ), $tagModel->getId(), '13194', Mage::app()->getStore()->getId());*/
				
				$tagRelationModel->setTagId($tagModel->getId())
						->setCustomerId(trim($commadelimiteddata[0]))
						->setProductId($product -> getIdBySku( $importData['sku'] ))
						->setStoreId($importData['store'])
						->setCreatedAt( now() )
						->setActive(1)
						->save();
				$tagModel->aggregate();
				}
				catch ( Exception $e ) {
				}		
			}
		}
		
		/* Remove existing custom options attached to the product */
foreach ($product->getOptions() as $o) {
   $o->getValueInstance()->deleteValue($o->getId());
   $o->deletePrices($o->getId());
   $o->deleteTitles($o->getId());
   $o->delete();
}

/* Add the custom options specified in the CSV import file */
if(count($custom_options)) {
   foreach($custom_options as $option) {
      try {
        $opt = Mage::getModel('catalog/product_option');
        $opt->setProduct($product);
        $opt->addOption($option);
        $opt->saveOptions();
      }
      catch (Exception $e) {}
   }
}

if($iscustomoptions == "true") {
######### CUSTOM QUERY FIX FOR DISAPPEARING OPTIONS ################# 
// fetch write database connection that is used in Mage_Core module 

		if($currentproducttype == "simple") {
		$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
		$fixOptions = Mage::getSingleton('core/resource')->getConnection('core_write'); 
		// now $write is an instance of Zend_Db_Adapter_Abstract 
		$fixOptions->query("UPDATE ".$prefix."catalog_product_entity SET has_options = 1 WHERE type_id = 'simple' AND entity_id IN (SELECT distinct(product_id) FROM ".$prefix."catalog_product_option)"); 
		
		} else if ($currentproducttype == "configurable") {
		$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
		$fixOptions = Mage::getSingleton('core/resource')->getConnection('core_write'); 
		// now $write is an instance of Zend_Db_Adapter_Abstract 
		$fixOptions->query("UPDATE ".$prefix."catalog_product_entity SET has_options = 1 WHERE type_id = 'configurable' AND entity_id IN (SELECT distinct(product_id) FROM ".$prefix."catalog_product_option)"); 
		}
}



/* DOWNLOADBLE PRODUCT FILE METHOD START */
		#print_r($filearrayforimport);
	if(isset($filearrayforimport)) {
	$filecounterinternall=1;
   foreach($filearrayforimport as $fileinfo) {
		$document_directory = Mage :: getBaseDir( 'media' ) . DS . 'import' . DS;
		$files = $fileinfo['file'];
		#echo "FILE: " . $fileinfo['file'];
		#echo "ID: " . $product->getId();
	  $resource = Mage::getSingleton('core/resource');
	  $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
	  $write = $resource->getConnection('core_write');
	  $read = $resource->getConnection('core_read');
	  $select_qry =$read->query("SHOW TABLE STATUS LIKE '".$prefix."downloadable_link' ");
	  $row = $select_qry->fetch();
	  $next_id = $row['Auto_increment'];
			
		$okvalueformodelID = $next_id - $filecounterinternall;
		#echo "next_id: " . $okvalueformodelID;
		$linkModel = Mage::getModel('downloadable/link')->load($okvalueformodelID);
													
		$link_file = 	$document_directory . $files;	
					
		$file = realpath($link_file);
		
        if (!$file || !file_exists($file)) {
            Mage::throwException(Mage::helper('catalog')->__('Link  file '.$file.' not exists'));
        }
        $pathinfo = pathinfo($file);
       

        $linkfile       = Varien_File_Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = Varien_File_Uploader::getDispretionPath($linkfile);
        $linkfile       = $dispretionPath . DS . $linkfile;

        $linkfile = $dispretionPath . DS
                  . Varien_File_Uploader::getNewFileName(Mage_Downloadable_Model_Link::getBasePath().DS.$linkfile);

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->setAllowCreateFolders(true);
        $distanationDirectory = dirname(Mage_Downloadable_Model_Link::getBasePath().DS.$linkfile);

        try {
            $ioAdapter->open(array(
                'path'=>$distanationDirectory
            ));

                $ioAdapter->cp($file, Mage_Downloadable_Model_Link::getBasePath().DS.$linkfile);
                $ioAdapter->chmod(Mage_Downloadable_Model_Link::getBasePath().DS.$linkfile, 0777);
        
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('catalog')->__('Failed to move file: %s', $e->getMessage()));
        }

        $linkfile = str_replace(DS, '/', $linkfile);
		
					#echo "SET: " . $linkfile;
					$linkModel->setLinkFile($linkfile);
					$linkModel->save();

					$intesdf = $next_id - $filecounterinternall;
					$write->query("UPDATE `".$prefix."downloadable_link_title` SET title = '".$fileinfo['name']."' WHERE link_id = '".$intesdf."'");
					$write->query("UPDATE `".$prefix."downloadable_link_price` SET price = '".$fileinfo['price']."' WHERE link_id = '".$intesdf."'");
					#$product->setLinksPurchasedSeparately(false);
					#$product->setLinksPurchasedSeparately(0);
			$filecounterinternall++;
		}
	}
	/* END DOWNLOADBLE METHOD */

	/* SAMPLE FILE DOWNLOADBLE PRODUCT SAMPLE FILE METHOD START */
	#print_r($filenameforsamplearrayforimport);
	if(isset($filenameforsamplearrayforimport)) {
	$filecounterinternall=1;
   foreach($filenameforsamplearrayforimport as $fileinfo) {
		$document_directory = Mage :: getBaseDir( 'media' ) . DS . 'import';
		$samplefiles = $fileinfo['file'];
		#print_r($filenameforsamplearrayforimport);
		#echo "ID: " . $fileinfo['name'] ."<br/>";
	  $resource = Mage::getSingleton('core/resource');
	  $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
	  $write = $resource->getConnection('core_write');
	  $read = $resource->getConnection('core_read');
	  $select_qry =$read->query("SHOW TABLE STATUS LIKE '".$prefix."downloadable_link' ");
	  $row = $select_qry->fetch();
	  $next_id = $row['Auto_increment'];
			
		$okvalueformodelID = $next_id - $filecounterinternall;
		#echo "next_id: " . $okvalueformodelID."<br/>";
		$linkSampleModel = Mage::getModel('downloadable/link')->load($okvalueformodelID);
		$link_sample_file = 	$document_directory . $samplefiles;
					
		$file = realpath($link_sample_file);
		
        if (!$file || !file_exists($file)) {
            Mage::throwException(Mage::helper('catalog')->__('Link sample file '.$file.' not exists'));
        }
        $pathinfo = pathinfo($file);
        $linksamplefile = Varien_File_Uploader::getCorrectFileName($pathinfo['basename']);
        $dispretionPath = Varien_File_Uploader::getDispretionPath($linksamplefile);
        $linksamplefile = $dispretionPath . DS . $linksamplefile;
        $linksamplefile = $dispretionPath . DS . Varien_File_Uploader::getNewFileName(Mage_Downloadable_Model_Link::getBaseSamplePath().DS.$linksamplefile);

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->setAllowCreateFolders(true);
        $distanationDirectory = dirname(Mage_Downloadable_Model_Link::getBaseSamplePath().DS.$linksamplefile);

        try {
            $ioAdapter->open(array(
                'path'=>$distanationDirectory
            ));

                $ioAdapter->cp($file, Mage_Downloadable_Model_Link::getBaseSamplePath().$linksamplefile);
                $ioAdapter->chmod(Mage_Downloadable_Model_Link::getBaseSamplePath().$linksamplefile, 0777);
        
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('catalog')->__('Failed to move sample file: %s', $e->getMessage()));
        }

          $linksamplefile = str_replace(DS, '/', $linksamplefile);
					$linkSampleModel->setSampleFile($linksamplefile);
					$linkSampleModel->save();

					#$intesdf = $next_id-1;
					$intesdf = $next_id - $filecounterinternall;
					$write->query("UPDATE `".$prefix."downloadable_link_title` SET title = '".$fileinfo['name']."' WHERE link_id = '".$intesdf."'");
					$write->query("UPDATE `".$prefix."downloadable_link_price` SET price = '".$fileinfo['price']."' WHERE link_id = '".$intesdf."'");
			$filecounterinternall++;
		}
	}
/* END SAMPLE FILE DOWNLOADBLE METHOD */	
/* START OF SUPER ATTRIBUTE PRICING */


if ($finalsuperattributetype == 'configurable') {
if ($finalsuperattributepricing != "") {

					$adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
					$superProduct = Mage :: getModel ( 'catalog/product' )-> load ( $product -> getId ()); 
					$superArray = $superProduct -> getTypeInstance ()-> getConfigurableAttributesAsArray (); 
					
					#print_r($superArray);
					
						$SuperAttributePricingData = array();
						$FinalSuperAttributeData = array();
						$SuperAttributePricingData = explode('|',$finalsuperattributepricing);
						
						$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
						foreach( $superArray AS $key => $val ) { 
								#$x = 0 ; 
								foreach( $val[ 'values' ] AS $keyValues => $valValues ) { 
						
									foreach($SuperAttributePricingData as $singleattributeData) {
										$FinalSuperAttributeData = explode(':',$singleattributeData);
										
										if($FinalSuperAttributeData[0] == $superArray[$key][ 'values' ][$keyValues][ 'label' ]) {
										$insertPrice='INSERT into '.$prefix.'catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value) VALUES
												 ("'.$superArray[$key][ 'values' ][$keyValues][ 'product_super_attribute_id' ].'", "'.$superArray[$key][ 'values' ][$keyValues][ 'value_index' ].'", "'.$FinalSuperAttributeData[2].'", "'.$FinalSuperAttributeData[1].'");';
										#echo "SQL2: " . $insertPrice;
										$adapter->query($insertPrice);
										}
									}
							 }
            }
}
}			
/* END OF SUPER ATTRIBUTE PRICING */

/* ADDED FIX FOR IMAGE LABELS */

	if(isset($imagelabeldataforimport)) {
	
			#echo "PROD ID: " . $product->getId() . "<br/>";
			#echo "LABELS: " . $imagelabeldataforimport . "<br/>";
			$resource = Mage::getSingleton('core/resource');
	   	$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$prefixlabels = Mage::getConfig()->getNode('global/resources/db/table_prefix');  
			$readlabels = $resource->getConnection('core_read');
			$writelabels = $resource->getConnection('core_write');
			$select_qry_labels =$readlabels->query("SELECT value_id FROM ".$prefixlabels."catalog_product_entity_media_gallery WHERE entity_id = '". $product->getId() ."'");
			$row_labels = $select_qry_labels->fetch();
			$value_id = $row_labels['value_id'];
			// now $write label to db
			$writelabels->query("UPDATE ".$prefix."catalog_product_entity_media_gallery_value SET label = '".$imagelabeldataforimport."' WHERE value_id = '".$value_id."'");  
			//this is for if you have flat product catalog enabled.. need to write values to both places
			#$writelabels->query("UPDATE ".$prefix."catalog_product_flat_1 SET image_label = '".$imagelabeldataforimport."' WHERE entity_id = '". $product->getId() ."'"); 
			#$writelabels->query("UPDATE ".$prefix."catalog_product_flat_1 SET image_label = '".$imagelabeldataforimport."' WHERE entity_id = '". $product->getId() ."'");
			#SELECT attribute_id FROM ".$prefixlabels."eav_attribute WHERE attribute_code = 'image_label';
			#$writelabels->query("UPDATE ".$prefix."catalog_product_entity_varchar SET value = '".$imagelabeldataforimport."' WHERE entity_id = '". $product->getId() ."' AND attribute_id = 101");  
				
	}
	if(isset($smallimagelabeldataforimport)) {
	
			#echo "PROD ID: " . $product->getId() . "<br/>";
			#echo "LABELS: " . $smallimagelabeldataforimport . "<br/>";
			$resource = Mage::getSingleton('core/resource');
	   	$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$prefixlabels = Mage::getConfig()->getNode('global/resources/db/table_prefix');  
			$readlabels = $resource->getConnection('core_read');
			$writelabels = $resource->getConnection('core_write');
			$select_qry_labels =$readlabels->query("SELECT value_id FROM ".$prefixlabels."catalog_product_entity_media_gallery WHERE entity_id = '". $product->getId() ."'");
			$row_labels = $select_qry_labels->fetch();
			$value_id = $row_labels['value_id']+1;
			// now $write label to db
			$writelabels->query("UPDATE ".$prefix."catalog_product_entity_media_gallery_value SET label = '".$smallimagelabeldataforimport."' WHERE value_id = '".$value_id."'"); 
				
	}
	if(isset($thumbnailimagelabeldataforimport)) {
	
			#echo "PROD ID: " . $product->getId() . "<br/>";
			#echo "LABELS: " . $smallimagelabeldataforimport . "<br/>";
			$resource = Mage::getSingleton('core/resource');
	   	$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
			$prefixlabels = Mage::getConfig()->getNode('global/resources/db/table_prefix');  
			$readlabels = $resource->getConnection('core_read');
			$writelabels = $resource->getConnection('core_write');
			$select_qry_labels =$readlabels->query("SELECT value_id FROM ".$prefixlabels."catalog_product_entity_media_gallery WHERE entity_id = '". $product->getId() ."'");
			$row_labels = $select_qry_labels->fetch();
			$value_id = $row_labels['value_id']+2;
			// now $write label to db
			$writelabels->query("UPDATE ".$prefix."catalog_product_entity_media_gallery_value SET label = '".$thumbnailimagelabeldataforimport."' WHERE value_id = '".$value_id."'"); 
				
	}

/* END FIX FOR IMAGE LABLES */
		return true;
	} 
	/**
	 * Edit tier prices
	 * 
	 * Uses a pipe-delimited string of qty:price to set tiers for the product row and appends.
	 * Removes if REMOVE is present.
	 * 
	 * @todo Prevent duplicate tiers (by qty) being set
	 * @internal Magento will save duplicate tiers; no enforcing unique tiers by qty, so we have to do this manually
	 * @param Mage_Catalog_Model_Product $product Current product row
	 * @param string $tier_prices_field Pipe-separated in the form of qty:price (e.g. 0=250=12.75|0=500=12.00)
	 */	
	private function _editTierPrices(&$product, $tier_prices_field = false, $store)
	{
		if (($tier_prices_field) && !empty($tier_prices_field)) {
            
            if(trim($tier_prices_field) == 'REMOVE'){
            
                $product->setTierPrice(array());
            
            } else {
                
                
                //get current product tier prices
                $existing_tps = $product->getTierPrice();
                
                $etp_lookup = array();
                //make a lookup array to prevent dup tiers by qty
                foreach($existing_tps as $key => $etp){
                    $etp_lookup[intval($etp['price_qty'])] = $key;
                }
                
                //parse incoming tier prices string
                $incoming_tierps = explode('|',$tier_prices_field);
								$tps_toAdd = array();  
								$tierpricecount=0;              
							foreach($incoming_tierps as $tier_str){
										//echo "t: " . $tier_str;
                    if (empty($tier_str)) continue;
                    
                    $tmp = array();
                    $tmp = explode('=',$tier_str);
                    
                    if ($tmp[1] == 0 && $tmp[2] == 0) continue;
										//echo ('adding tier');
                    //print_r($tmp);
                    $tps_toAdd[$tierpricecount] = array(
                                        'website_id' => 0, // !!!! this is hard-coded for now
										#'website_id' => $store->getWebsiteId(),
                                        'cust_group' => $tmp[0], // !!! so is this
                                        'price_qty' => $tmp[1],
                                        'price' => $tmp[2],
                                        'delete' => ''
                                    );
                                    
                    //drop any existing tier values by qty
                    if(isset($etp_lookup[intval($tmp[1])])){
                        unset($existing_tps[$etp_lookup[intval($tmp[1])]]);
                    }
                    $tierpricecount++;
                }

                //combine array
                $tps_toAdd =  array_merge($existing_tps, $tps_toAdd);
               
							 	//print_r($tps_toAdd);
                //save it
                $product->setTierPrice($tps_toAdd);
            }
            
        }
	}

	
	protected function userCSVDataAsArray( $data )
	{
		return explode( ',', str_replace( " ", "", $data ) );
	} 
	
	protected function skusToIds( $userData, $product )
	{
		$productIds = array();
		foreach ( $this -> userCSVDataAsArray( $userData ) as $oneSku ) {
			if ( ( $a_sku = ( int )$product -> getIdBySku( $oneSku ) ) > 0 ) {
				parse_str( "position=", $productIds[$a_sku] );
			} 
		} 
		return $productIds;
	} 
	
	 protected $_categoryCache = array();
   protected function _addCategories($categories, $store)
    {
		// $rootId = $store->getRootCategoryId();
		// $rootId = Mage::app()->getStore()->getRootCategoryId();
        //$rootId = 2; // our store's root category id
				if($this->getBatchParams('root_catalog_id') != "") {
					$rootId = $this->getBatchParams('root_catalog_id');
				} else {
				  $rootId = 2; 
				}
        if (!$rootId) {
            return array();
        }
        $rootPath = '1/'.$rootId;
        if (empty($this->_categoryCache[$store->getId()])) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store)
                ->addAttributeToSelect('name');
            $collection->getSelect()->where("path like '".$rootPath."/%'");

            foreach ($collection as $cat) {
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i=2, $l=sizeof($pathArr); $i<$l; $i++) {
					//if(!is_null($collection->getItemById($pathArr[$i]))) { }
                    $name = $collection->getItemById($pathArr[$i])->getName();
                    $namePath .= (empty($namePath) ? '' : '/').trim($name);
                }
                $cat->setNamePath($namePath);
            }
            
            $cache = array();
            foreach ($collection as $cat) {
                $cache[strtolower($cat->getNamePath())] = $cat;
                $cat->unsNamePath();
            }
            $this->_categoryCache[$store->getId()] = $cache;
        }
        $cache =& $this->_categoryCache[$store->getId()];
        
        $catIds = array();
		  //->setIsAnchor(1)
	      //Delimiter is ' , ' so people can use ', ' in multiple categorynames
        foreach (explode(' , ', $categories) as $categoryPathStr) {
            $categoryPathStr = preg_replace('#\s*/\s*#', '/', trim($categoryPathStr));
            if (!empty($cache[$categoryPathStr])) {
                $catIds[] = $cache[$categoryPathStr]->getId();
                continue;
            }
            $path = $rootPath;
            $namePath = '';
            foreach (explode('/', $categoryPathStr) as $catName) {
                $namePath .= (empty($namePath) ? '' : '/').strtolower($catName);
                if (empty($cache[$namePath])) {
                    $cat = Mage::getModel('catalog/category')
                        ->setStoreId($store->getId())
                        ->setPath($path)
                        ->setName($catName)
						->setIsActive(1)
                        ->save();
                    $cache[$namePath] = $cat;
                }
                $catId = $cache[$namePath]->getId();
                $path .= '/'.$catId;
            }
            if ($catId) {
                $catIds[] = $catId;
            }
        }
        return join(',', $catIds);
    }
	
	protected function _removeFile( $file )
	{
		if ( file_exists( $file ) ) {
		$ext = substr(strrchr($file, '.'), 1);
			if( strlen( $ext ) == 4 ) {
				if ( unlink( $file ) ) {
					return true;
				} 
			}
		} 
		return false;
	} 
}