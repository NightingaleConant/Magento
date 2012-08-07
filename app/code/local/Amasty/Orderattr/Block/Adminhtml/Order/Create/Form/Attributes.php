<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Create_Form_Attributes extends Mage_Adminhtml_Block_Template
{
    protected $_entityTypeId;
    
    protected $_formElements = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amorderattr/create_attributes.phtml');
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
    }
    
    public function getFormElements()
    {
        if ($this->_formElements)
        {
            return $this->_formElements;
        }

        $collection = Mage::getModel('eav/entity_attribute')->getCollection();
        $collection->addFieldToFilter('checkout_step', array(2,3,4,5));
        $collection->addFieldToFilter('entity_type_id', $this->_entityTypeId);
        $collection->getSelect()->order('sorting_order');
        $attributes = $collection->load();
        
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('amorderattr', array());
        
        $attributeValues = null;
        /**
        * Reorder or order edit process. Should load values from old order.
        */
        if ( ($reorderId = Mage::getSingleton('adminhtml/session_quote')->getOrderId())  ||  ($reorderId = Mage::getSingleton('adminhtml/session_quote')->getReordered())  )
        {
            $attributeValues = Mage::getModel('amorderattr/attribute')->load($reorderId, 'order_id');
        }
        
        foreach ($attributes as $attribute)
        {
            $currentStore = Mage::getSingleton('adminhtml/session_quote')->getStore()->getId();
            $storeIds = explode(',', $attribute->getData('store_ids'));
            if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds))
            {
                continue;
            }
            
            if ($inputType = $attribute->getFrontend()->getInputType())
            {
                $fieldType      = $inputType;
                $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                // global name space, will merge from all steps
                $fieldName = 'amorderattr[' . $attribute->getAttributeCode(). ']';
                                    
                // default_value
                $attributeValue = '';
                if ($attribute->getData('default_value'))
                {
                    $attributeValue = $attribute->getData('default_value');
                }
                if ($attributeValues && $attributeValues->hasData($attribute->getAttributeCode()))
                {
                    $attributeValue = $attributeValues->getData($attribute->getAttributeCode());
                }
                
                // applying translations
                $attributeLabel = $attribute->getFrontendLabel();
                
                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType,
                    array(
                        'name'      => $fieldName,
                        'label'     => $attributeLabel,
                        'class'     => $attribute->getFrontend()->getClass(),
                        'required'  => $attribute->getIsRequired(),
                        'note'      => $attribute->getNote(),
                        'value'     => $attributeValue,
                    )
                )
                ->setEntityAttribute($attribute);
                
                if ($inputType == 'select' || $inputType == 'multiselect') {
                    
                    // getting values translations
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter($currentStore, false)
                        ->load();
                    foreach ($valuesCollection as $item) {
                        $values[$item->getId()] = $item->getValue();
                    }
                    
                    // applying translations
                    $options = $attribute->getSource()->getAllOptions(true, true);
                    foreach ($options as $i => $option)
                    {
                        if (isset($values[$option['value']]))
                        {
                            $options[$i]['label'] = $values[$option['value']];
                        }
                    }
                    
                    $element->setValues($options);
                } elseif ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
                    $element->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                }
            }
        }
        $this->_formElements = $form->getElements();
        return $this->_formElements;
    }
}