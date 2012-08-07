<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Fields extends Mage_Core_Block_Template
{
    protected $_entityTypeId;
    
    protected $_formElements = array();
    
    protected $_step;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amorderattr/fields.phtml');
        
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
    }
    
    public function setStep($step)
    {
        $this->_step = $step;
        return $this;
    }
    
    public function getStep()
    {
        return $this->_step;
    }
    
    public function getStepCode()
    {
        switch ($this->getStep())
        {
            case 'billing':
                return 2;
            break;
            case 'shipping':
                return 3;
            break;
            case 'shipping_method':
                return 4;
            break;
            case 'payment':
                return 5;
            break;
            default:
                return 0;
            break;
        }
    }
    
    public function getFormElements()
    {
        if ($this->_formElements)
        {
            return $this->_formElements;
        }
//        $collection = Mage::getModel('eav/entity_attribute')->getCollection();
        $collection = Mage::getModel('eav/entity_attribute')->getCollection();
        
        $collection->addFieldToFilter('is_visible_on_front', 1);
        $collection->addFieldToFilter('entity_type_id', $this->_entityTypeId);
        $collection->addFieldToFilter('checkout_step', $this->getStepCode());
        $collection->getSelect()->order('sorting_order');
        $attributes = $collection->load();
        
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('amorderattr', array());
        
        foreach ($attributes as $attribute)
        {
            $currentStore = Mage::app()->getStore()->getId();
            $storeIds = explode(',', $attribute->getData('store_ids'));
            if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds))
            {
                continue;
            }
            
            if ($inputType = $attribute->getFrontend()->getInputType())
            {
            	$afterElementHtml = '';
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
                
                
                // if enabled, we will pre-fill the attribut with the last used value. works for registered customer only
                if ($attribute->getSaveSelected() && Mage::getSingleton('customer/session')->isLoggedIn())
                {
                    $orderCollection = Mage::getModel('sales/order')->getCollection();
                    $orderCollection->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getId());
                    // 1.3 compatibility
                    $alias = Mage::helper('ambase')->isVersionLessThan(1,4) ? 'e' : 'main_table';
                    $orderCollection->getSelect()
                       ->join(
                            array('custom_attributes' => Mage::getModel('amorderattr/attribute')->getResource()->getTable('amorderattr/order_attribute')),
                            "$alias.entity_id = custom_attributes.order_id",
                            array($attribute->getAttributeCode())
                       );
                    $orderCollection->getSelect()->order('custom_attributes.order_id DESC');
                    $orderCollection->getSelect()->limit(1);
                    if ($orderCollection->getSize() > 0)
                    {
                        foreach ($orderCollection as $lastOrder)
                        {
                            $attributeValue = $lastOrder->getData($attribute->getAttributeCode());
                        }
                    }
                }
                
                // applying translations
                $translations = $attribute->getStoreLabels();
                if (isset($translations[Mage::app()->getStore()->getId()]))
                {
                    $attributeLabel = $translations[Mage::app()->getStore()->getId()];
                } else 
                {
                    $attributeLabel = $attribute->getFrontend()->getLabel();
                }
                
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
                
                $afterElementHtml .= '<div style="padding: 4px;"></div>';

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    
                	$values = array();
                    // getting values translations
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter(Mage::app()->getStore()->getId(), false)
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

                    if ($inputType == 'select' && $attribute->getParentDropdown())
                    {
                    	$parentAttribute = Mage::getModel('eav/entity_attribute')->load($attribute->getParentDropdown());
                    	$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
						                        ->setAttributeFilter($attribute->getId())
						                        ->load();
						$linkedRelationship = array();
						if ($valuesCollection->getSize() > 0)
						{
							foreach ($valuesCollection as $value)
							{
								foreach ($options as $option)
								{
									if ($option['value'] == $value->getOptionId())
									{
										$linkedRelationship[$option['value']] = $value->getParentOptionId();
									}
								}
							}
							$optionsJson = Zend_Json::encode($options);
							$relationsJson = Zend_Json::encode($linkedRelationship);
                    		$afterElementHtml .= '<script type="text/javascript">' . 
                    						      'Event.observe(window, "load", function(){ peditGrid' . $attribute->getId() . ' = new amLinkedFields("' . $parentAttribute->getAttributeCode() . '", "' . $attribute->getAttributeCode() . '", ' . $optionsJson . ', ' . $relationsJson . '); });' .
                    						      '</script>';
                    	    if ($attributeValue)
                    	    {
                    	    	// applying saved for future checkout value
                    	    	$afterElementHtml .= '<script type="text/javascript">' . 
                    						         'Event.observe(window, "load", function(){ 
                    						      	     $$("#' . $attribute->getAttributeCode() . ' option[value=' . $attributeValue . ']").each(function(elem){ elem.selected = true; }); 
                    	    			             });' .
                    						         '</script>';
                    	    }
						}
                    } else 
                    {
                    	$element->setValues($options);
                    }
                } elseif ($inputType == 'date' && 'time' != $attribute->getNote()) {
                    $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
                    $element->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                } elseif ($inputType == 'date' && 'time' == $attribute->getNote())
                {
                    $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
                    $element->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) . ' HH:mm');
                    $element->setTime(true);
                }
                
                $element->setAfterElementHtml($afterElementHtml);
            }
        }
        $this->_formElements = $form->getElements();
        return $this->_formElements;
    }
    
    protected function _toHtml()
    {
        if (!$this->getFormElements())
        {
            return '';
        }
        $html = parent::_toHtml();
        // including JS once
        if (!Mage::registry('amorderattr_js_added'))
        {
            $jsSrc = '<script type="text/javascript" src="' . Mage::getBaseUrl('js') . 'amasty/amorderattr/payment.js">' . '</script>';
            $html = $jsSrc . $html;
            Mage::register('amorderattr_js_added', true);
        }
        $html = str_replace('</label>', '</label><br />', $html);
        return $html;
    }
    
}
