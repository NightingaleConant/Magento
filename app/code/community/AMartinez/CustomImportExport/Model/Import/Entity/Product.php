<?php
/**
 * Magento
 *
 * @category   AMartinez
 * @package    AMartinez_CustomImportExport
 * @author     Antonio Martinez
 * @copyright  Copyright (c) 2011 Antonio Martinez (toniyecla [at] gmail [dot] com)
 * @license    http://opensource.org/licenses/osl-3.0 Open Software License (OSL 3.0)
 */

/**
 * Import entity product model
 */
class AMartinez_CustomImportExport_Model_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{
	/* Mage_ImportExport_Model_Import_Entity_Abstract */
	
	protected $_newAttrParams = array();
	
    /**
     * Check one attribute. Can be overridden in child.
     *
     * @param string $attrCode Attribute code
     * @param array $attrParams Attribute params
     * @param array $rowData Row data
     * @param int $rowNum
     * @return boolean
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData, $rowNum)
    {
        switch ($attrParams['type']) {
            case 'varchar':
                $val   = Mage::helper('core/string')->cleanString($rowData[$attrCode]);
                //// convert to utf-8
                //// $val = iconv('ISO-8859-1', 'UTF-8' . '//IGNORE//TRANSLIT', $val);
                $valid = Mage::helper('core/string')->strlen($val) < self::DB_MAX_VARCHAR_LENGTH;
                break;
            case 'decimal':
                $val   = trim($rowData[$attrCode]);
                $valid = (float)$val == $val;
                break;
            case 'select':
                foreach (explode(",", $rowData[$attrCode]) as $code)
                {
					$valid = isset($attrParams['options'][strtolower($code)]);
					if (!$valid) {
						if (!in_array($attrCode . ":" . $code, $this->_newAttrParams))
						{
							if ($this->parseAttributeOption($attrCode, $code))
							{
								$this->_newAttrParams[] = $attrCode . ":" . $code;
								echo ":::: " . Mage::helper('importexport')->__("New attribute option added: ") . $attrCode . " - " . $code . " (first ocurrence in line " . ($rowNum+1) . ") ::::\n";
								$this->addRowError(Mage::helper('importexport')->__("Attribute option added for '%s'"), $rowNum, $attrCode);
							}
						}
						
					}
        		}
        		return true;
                break;
            case 'int':
                $val   = trim($rowData[$attrCode]);
                $valid = (int)$val == $val;
                break;
            case 'datetime':
                $val   = trim($rowData[$attrCode]);
                $valid = strtotime($val)
                         || preg_match('/^\d{2}.\d{2}.\d{2,4}(?:\s+\d{1,2}.\d{1,2}(?:.\d{1,2})?)?$/', $val)
                         || $val == "1970-01-01 00:00:00" // php null date
                         || $val == "1899-12-31"; // firebird null date                
                break;
            case 'text':
                $val   = Mage::helper('core/string')->cleanString($rowData[$attrCode]);
                //// convert to utf-8
                //// $val = iconv('ISO-8859-1', 'UTF-8' . '//IGNORE//TRANSLIT', $val);
                $valid = Mage::helper('core/string')->strlen($val) < self::DB_MAX_TEXT_LENGTH;
                break;
            default:
                $valid = true;
                break;
        }
        if (!$valid) {
            $this->addRowError(Mage::helper('importexport')->__("Invalid value for '%s'"), $rowNum, $attrCode);
        }
        return (bool) $valid;
    }
    
    /* Mage_ImportExport_Model_Import_Entity_Product */
    
    protected $entity_type_id;
    protected $eav_entity_setup;
    
    protected $_particularAttributes = array(
        '_store', '_attribute_set', '_type', '_category', '_product_websites', '_tier_price_website',
        '_tier_price_customer_group', '_tier_price_qty', '_tier_price_price', '_links_related_sku',
        '_links_related_position', '_links_crosssell_sku', '_links_crosssell_position', '_links_upsell_sku',
        '_links_upsell_position', '_custom_option_store', '_custom_option_type', '_custom_option_title',
        '_custom_option_is_required', '_custom_option_price', '_custom_option_sku', '_custom_option_max_characters',
        '_custom_option_sort_order', '_custom_option_file_extension', '_custom_option_image_size_x',
        '_custom_option_image_size_y', '_custom_option_row_title', '_custom_option_row_price',
        '_custom_option_row_sku', '_custom_option_row_sort', '_media_image', '_media_attribute_id', '_media_label',
        '_media_position', '_media_is_disabled'
    );

	// , 'image', 'small_image', 'thumbnail'
    protected $_imagesArrayKeys = array(
        '_media_image', 'image', 'small_image', 'thumbnail'
    );

    /**
     * Save product media gallery.
     */
    protected function _saveMediaGallery(array $mediaGalleryData)
    {
        if (empty($mediaGalleryData)) {
            return $this;
        }

        static $mediaGalleryTableName = null;
        static $mediaValueTableName = null;
        static $productId = null;

        if (!$mediaGalleryTableName) {
            $mediaGalleryTableName = Mage::getModel('importexport/import_proxy_product_resource')
                    ->getTable('catalog/product_attribute_media_gallery');
        }

        if (!$mediaValueTableName) {
            $mediaValueTableName = Mage::getModel('importexport/import_proxy_product_resource')
                    ->getTable('catalog/product_attribute_media_gallery_value');
        }

        foreach ($mediaGalleryData as $productSku => $mediaGalleryRows) {
            $productId = $this->_newSku[$productSku]['entity_id'];
            $insertedGalleryImgs = array();

            if (Mage_ImportExport_Model_Import::BEHAVIOR_APPEND != $this->getBehavior()) {
                $this->_connection->delete(
                    $mediaGalleryTableName,
                    $this->_connection->quoteInto('entity_id IN (?)', $productId)
                );
            }

            foreach ($mediaGalleryRows as $insertValue) {

                if (!in_array($insertValue['value'], $insertedGalleryImgs)) {
                    $valueArr = array(
                        'attribute_id' => $insertValue['attribute_id'],
                        'entity_id'    => $productId,
                        'value'        => $insertValue['value']
                    );

                    $this->_connection
                            ->insertOnDuplicate($mediaGalleryTableName, $valueArr, array('entity_id'));

                    $insertedGalleryImgs[] = $insertValue['value'];
                }

                $newMediaValues = $this->_connection->fetchPairs($this->_connection->select()
                                        ->from($mediaGalleryTableName, array('value', 'value_id'))
                                        ->where('entity_id IN (?)', $productId)
                );

                if (array_key_exists($insertValue['value'], $newMediaValues)) {
                    $insertValue['value_id'] = $newMediaValues[$insertValue['value']];
                }

                $valueArr = array(
                    'value_id' => $insertValue['value_id'],
                    'store_id' => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
                    'label'    => $insertValue['label'],
                    'position' => $insertValue['position'],
                    'disabled' => $insertValue['disabled']
                );

                try {
                    $this->_connection
                            ->insertOnDuplicate($mediaValueTableName, $valueArr, array('value_id'));
                } catch (Exception $e) {
                    $this->_connection->delete(
                            $mediaGalleryTableName, $this->_connection->quoteInto('value_id IN (?)', $newMediaValues)
                    );
                }
            }
        }

        return $this;
    }
    
    /**
     * Gather and save information about product entities.
     */
    protected function _saveProducts()
    {
        /** @var $resource Mage_ImportExport_Model_Import_Proxy_Product_Resource */
        $resource       = Mage::getModel('importexport/import_proxy_product_resource');
        $priceIsGlobal  = Mage::helper('catalog')->isPriceGlobal();
        $strftimeFormat = Varien_Date::convertZendToStrftime(Varien_Date::DATETIME_INTERNAL_FORMAT, true, true);
        $productLimit   = null;
        $productsQty    = null;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = array();
            $entityRowsUp = array();
            $attributes   = array();
            $websites     = array();
            $categories   = array();
            $tierPrices   = array();
            $mediaGallery = array();
            $uploadedGalleryFiles = array();
            
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                $rowScope = $this->getRowScope($rowData);

                if (self::SCOPE_DEFAULT == $rowScope) {
                    $rowSku = $rowData[self::COL_SKU];

                    // 1. Entity phase
                    if (isset($this->_oldSku[$rowSku])) { // existing row
                        $entityRowsUp[] = array(
                            'updated_at' => now(),
                            'entity_id'  => $this->_oldSku[$rowSku]['entity_id']
                        );
                    } else { // new row
                        if (!$productLimit || $productsQty < $productLimit) {
                            $entityRowsIn[$rowSku] = array(
                                'entity_type_id'   => $this->_entityTypeId,
                                'attribute_set_id' => $this->_newSku[$rowSku]['attr_set_id'],
                                'type_id'          => $this->_newSku[$rowSku]['type_id'],
                                'sku'              => $rowSku,
                                'created_at'       => now(),
                                'updated_at'       => now()
                            );
                            $productsQty++;
                        } else {
                            $rowSku = null; // sign for child rows to be skipped
                            $this->_rowsToSkip[$rowNum] = true;
                            continue;
                        }
                    }
                } elseif (null === $rowSku) {
                    $this->_rowsToSkip[$rowNum] = true;
                    continue; // skip rows when SKU is NULL
                } elseif (self::SCOPE_STORE == $rowScope) { // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE]     = $this->_newSku[$rowSku]['type_id'];
                    $rowData['attribute_set_id'] = $this->_newSku[$rowSku]['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->_newSku[$rowSku]['attr_set_code'];
                }
                if (!empty($rowData['_product_websites'])) { // 2. Product-to-Website phase
                    $websites[$rowSku][$this->_websiteCodeToId[$rowData['_product_websites']]] = true;
                }
                if (!empty($rowData[self::COL_CATEGORY])) { // 3. Categories phase
                    $categories[$rowSku][$this->_categories[$rowData[self::COL_CATEGORY]]] = true;
                }
                if (!empty($rowData['_tier_price_website'])) { // 4. Tier prices phase
                    $tierPrices[$rowSku][] = array(
                        'all_groups'        => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL ?
                                               0 : $rowData['_tier_price_customer_group'],
                        'qty'               => $rowData['_tier_price_qty'],
                        'value'             => $rowData['_tier_price_price'],
                        'website_id'        => self::VALUE_ALL == $rowData['_tier_price_website'] || $priceIsGlobal ?
                                               0 : $this->_websiteCodeToId[$rowData['_tier_price_website']]
                    );
                }
                // Media gallery phase 
                foreach ($this->_imagesArrayKeys as $imageCol) {
                    if (!empty($rowData[$imageCol])) {
                        if (!array_key_exists($rowData[$imageCol], $uploadedGalleryFiles)) {
                        	// Mage::getConfig()->getOptions()->getMediaDir() . '/catalog/product' .
                            $uploadedGalleryFiles[$rowData[$imageCol]] = $rowData[$imageCol];
                        }
                        $rowData[$imageCol] = $uploadedGalleryFiles[$rowData[$imageCol]];
                    }
                }
                
                if (!empty($rowData['_media_image'])) {                	
                    $mediaGallery[$rowSku][] = array(
                        'attribute_id'      => $rowData['_media_attribute_id'],
                        'label'             => $rowData['_media_label'],
                        'position'          => $rowData['_media_position'],
                        'disabled'          => $rowData['_media_is_disabled'],
                        'value'             => $rowData['_media_image']
                    );
                }
                // 5. Attributes phase
                if (self::SCOPE_NULL == $rowScope) {
                    continue; // skip attribute processing for SCOPE_NULL rows
                }
                $rowStore = self::SCOPE_STORE == $rowScope ? $this->_storeCodeToId[$rowData[self::COL_STORE]] : 0;
                $rowData  = $this->_productTypeModels[$rowData[self::COL_TYPE]]->prepareAttributesForSave($rowData);
                $product  = Mage::getModel('importexport/import_proxy_product', $rowData);

                foreach ($rowData as $attrCode => $attrValue) {
                    $attribute = $resource->getAttribute($attrCode);
                    $attrId    = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds  = array(0);

                    if ('datetime' == $attribute->getBackendType()) {
                        $attrValue = gmstrftime($strftimeFormat, strtotime($attrValue));
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->_storeIdToWebsiteStoreIds[$rowStore];
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = array($rowStore);
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                    }
                    $attribute->setBackendModel($backModel); // restore 'backend_model' to avoid 'default' setting
                }
            }
            $this->_saveProductEntity($entityRowsIn, $entityRowsUp)
                ->_saveProductWebsites($websites)
                ->_saveProductCategories($categories)
                ->_saveProductTierPrices($tierPrices)
                ->_saveMediaGallery($mediaGallery)
                ->_saveProductAttributes($attributes);
        }
        return $this;
    }

    /**
     * Add attribute option
     *
     * @return true
     */
    function parseAttributeOption($code, $value)
    {
    	if (!$this->entity_type_id) {
    		$this->entity_type_id = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
    	}
    	
    	if (!$this->eav_entity_setup) {
        	$this->eav_entity_setup = new Mage_Eav_Model_Entity_Setup('core_setup');
    	}

        if ( !$id = $this->eav_entity_setup->getAttribute($this->entity_type_id, $code, 'attribute_id')) {
            return false;
        }
        
        // $attribute = Mage::getModel('catalog/product')->setStoreId(0)->getResource()->getAttribute($id);
        // $options = $this->getAttributeOptions($attribute);
                
        $new_option['attribute_id'] = $id;
        $new_option['value']['_custom_'.$value][0] = $value;        
        $this->eav_entity_setup->addAttributeOption($new_option);
        
        return true;
    }
    
    /**
     * Check product category validity
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isProductCategoryValid(array $rowData, $rowNum)
    {
        if (!empty($rowData[self::COL_CATEGORY]) && !isset($this->_categories[$rowData[self::COL_CATEGORY]])) {      
       		if (!empty($rowData['_custom_option_store']) && isset($this->_storeCodeToId[$rowData['_custom_option_store']])) {
                    $storeId = $this->_storeCodeToId[$rowData['_custom_option_store']];
            } else {
                    $storeId = $this->_storeCodeToId['default'];
            }
        	if ($this->_addCategory($rowData[self::COL_CATEGORY], $storeId))
        	{
        		echo ":::: " . Mage::helper('importexport')->__("New category created: ") . $rowData[self::COL_CATEGORY] . " (first ocurrence in line " . ($rowNum+1) . ") ::::\n";
        	}
        }
        return true;
    }

    /**
     * Add category
     *
     * @return true
     */
	function _addCategory($category, $storeId)
    {
        $store = Mage::app()->getStore($storeId);
        $rootCategoryId = $store->getGroup()->root_category_id;
        $rootCategoryPath = "1/" . $rootCategoryId;

		$relativePath = $rootCategoryPath;
		foreach (explode("/", $category) as $child)
		{
			$fullCategoryName = isset($fullCategoryName) ? ($fullCategoryName . "/" . $child) : ($child);	
			if (!isset($this->_categories[$fullCategoryName]))
			{
				$cat = Mage::getModel('catalog/category');
				$cat->setStoreId(0)
					->setName(trim($child))
					->setDisplayMode(trim($child))
					->setAttributeSetId($cat->getDefaultAttributeSetId())
					->setIsActive(1)
					->setPath($relativePath)
					->setIsAnchor(true)
					->save();
				 $this->_categories[$fullCategoryName] = $cat->getId();
			}
			$relativePath = $relativePath . "/" . $this->_categories[$fullCategoryName];
		}

		return true;
    }
}
