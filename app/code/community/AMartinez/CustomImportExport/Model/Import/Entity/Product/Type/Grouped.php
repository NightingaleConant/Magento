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
 * Import entity grouped product type model
 */
class AMartinez_CustomImportExport_Model_Import_Entity_Product_Type_Grouped
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Grouped
{
    /**
     * Initialize attributes parameters for all attributes' sets.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract
     */
    protected function _initAttributes()
    {
        // temporary storage for attributes' parameters to avoid double querying inside the loop
        $attributesCache = array();

        foreach (Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($this->_entityModel->getEntityTypeId()) as $attributeSet) {
            foreach (Mage::getResourceModel('catalog/product_attribute_collection')
                ->setAttributeSetFilter($attributeSet->getId()) as $attribute) {

                $attributeCode = $attribute->getAttributeCode();
                $attributeId   = $attribute->getId();

                if ($attribute->getIsVisible() || in_array($attributeCode, $this->_forcedAttributesCodes)) {
                    if (!isset($attributesCache[$attributeId])) {
                        $attributesCache[$attributeId] = array(
                            'id'               => $attributeId,
                            'code'             => $attributeCode,
                            'for_configurable' => $attribute->getIsConfigurable(),
                            'is_global'        => $attribute->getIsGlobal(),
                            'is_required'      => $attribute->getIsRequired(),
                            'frontend_label'   => $attribute->getFrontendLabel(),
                            'frontend_input'   => $attribute->getFrontendInput(),
                            'is_static'        => $attribute->isStatic(),
                            'apply_to'         => $attribute->getApplyTo(),
                            'type'             => Mage_ImportExport_Model_Import::getAttributeType($attribute),
                            'default_value'    => strlen($attribute->getDefaultValue())
                                                  ? $attribute->getDefaultValue() : null,
                            'options'          => $this->_entityModel
                                                      ->getAttributeOptions($attribute, $this->_indexValueAttributes)
                        );
                    }
                    $this->_addAttributeParams($attributeSet->getAttributeSetName(), $attributesCache[$attributeId]);
                }
            }
        }
        return $this;
    }
    
    /**
     * Prepare attributes values for save: remove non-existent, remove empty values, remove static.
     *
     * @param array $rowData
     * @return array
     */
    public function prepareAttributesForSave(array $rowData)
    {
        $resultAttrs = array();

        foreach ($this->_getProductAttributes($rowData) as $attrCode => $attrParams) {
            if (!$attrParams['is_static']) {
                if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                	switch ($attrParams['frontend_input'])
                	{
                		case 'multiselect':
                			$codes = array();
                			foreach (explode(",", $rowData[$attrCode]) as $code)
                			{
                				$codes[] = 	$attrParams['options'][strtolower($code)];
                			}
                			$resultAttrs[$attrCode] = implode(",", array_unique($codes));
                			break;
                		case 'select':
                			$resultAttrs[$attrCode] = $attrParams['options'][strtolower($rowData[$attrCode])];
                			break;
                		default:
                			$resultAttrs[$attrCode] = $rowData[$attrCode];
                	}
                } elseif (null !== $attrParams['default_value']) {
                    $resultAttrs[$attrCode] = $attrParams['default_value'];
                }
            }
        }
            	
        return $resultAttrs;
    }
}
