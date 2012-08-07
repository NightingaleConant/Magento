<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Rewrite_Customer extends Mage_Customer_Model_Customer
{
    protected $_customAttributes = array();
    
    protected function _beforeSave()
    {
        parent::_beforeSave();
        
        $checkUnique = array();
        
        /**
        * Will detect which attributes are dates, and check unique attributes
        */
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $collection->addFieldToFilter('is_user_defined', 1);
        $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
        
        $castDate = array();
        foreach ($collection as $attribute)
        {
            if ('datetime' == $attribute->getBackendType())
            {
                $castDate[] = $attribute->getAttributeCode();
            }
            if ($attribute->getIsUnique())
            {
                $translations = $attribute->getStoreLabels();
                if (isset($translations[Mage::app()->getStore()->getId()]))
                {
                    $attributeLabel = $translations[Mage::app()->getStore()->getId()];
                } else 
                {
                    $attributeLabel = $attribute->getFrontend()->getLabel();
                }
                $checkUnique[$attribute->getAttributeCode()] = $attributeLabel;
            }
        }

        /**
        * Adding customer attributes to self data array
        */
        $customerAttributes = Mage::app()->getRequest()->getPost('amcustomerattr');
        if (!$customerAttributes && ('checkout' == Mage::app()->getRequest()->getModuleName() || 'sgps' == Mage::app()->getRequest()->getModuleName()))
        {
            $customerAttributes = Mage::getSingleton('checkout/session')->getAmcustomerattr();
        }
        if ($customerAttributes)
        {
            Mage::getSingleton('customer/session')->setAmcustomerattr($customerAttributes);
            foreach ($customerAttributes as $attributeCode => $attributeValue)
            {
                if (in_array($attributeCode, $castDate))
                {
                    $attributeValue = date('Y-m-d', strtotime($attributeValue));
                }
                $this->setData($attributeCode, $attributeValue);
            }
        }
        
        if ($checkUnique)
        {
            foreach ($checkUnique as $attributeCode => $attributeLabel)
            {
                //skip empty values
                if (!$this->getData($attributeCode)) {
                    continue;
                }
                
                $customerCollection = Mage::getResourceModel('customer/customer_collection');
                $customerCollection->addAttributeToFilter($attributeCode, array('eq' => $this->getData($attributeCode)));
                $mainAlias = ( false !== strpos($customerCollection->getSelect()->__toString(), 'AS `e') ) ? 'e' : 'main_table';
                $customerCollection->getSelect()->where($mainAlias . '.entity_id != ?', $this->getId());
                if ($customerCollection->getSize() > 0)
                {
                    $e = new Mage_Customer_Exception(Mage::helper('amcustomerattr')->__('Please specify different value for "%s" attribute. Customer with such value already exists.', $attributeLabel));
                    if (method_exists($e, 'setMessage'))
                    {
                    	$e->setMessage(Mage::helper('amcustomerattr')->__('Please specify different value for "%s" attribute. Customer with such value already exists.', $attributeLabel));
                    }
                    throw $e;
                }
            }
        }
        return $this;
    }
    
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $collection->addFieldToFilter('is_user_defined', 1);
        $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
        foreach ($collection as $attribute)
        {
            if ('customer_activated' != $attribute->getAttributeCode() && 'unlock_customer' != $attribute->getAttributeCode())
            {
                $attributes[] = $attribute;
            }
        }
        return $attributes;
    }
    
    public function loadByEmail($customerEmail)
    {
		if ('forgotpasswordpost' == Mage::app()->getRequest()->getActionName())
        {
            return parent::loadByEmail($customerEmail);
        }
        if (!Mage::getStoreConfig('amcustomerattr/login/disable_email') || !Mage::getStoreConfig('amcustomerattr/login/login_field'))
        {
            parent::loadByEmail($customerEmail);
            if ($this->getId())
            {
                // customer found by e-mail, no need to load by attribute
                return $this;
            }
        }
        if (Mage::getStoreConfig('amcustomerattr/login/login_field'))
        {
            // will try to load by attribute
            $attribute = Mage::getModel('customer/attribute')->load(Mage::getStoreConfig('amcustomerattr/login/login_field'), 'attribute_code');
            if ($attribute->getId())
            {
                $this->_getResource()->loadByAttribute($this, $customerEmail, $attribute);
            }
        }
        return $this;
    }
    
    public function custom($attributeCode)
    {
        if ('group_name' == $attributeCode)
        {
            $groupName = '';
            // possibility to get customer group name
            if ($this->getGroupId())
            {
                $group = Mage::getModel('customer/group')->load($this->getGroupId());
                $groupName = $group->getCode();
            }
            return $groupName;
        }
        
        if (!$this->_customAttributes)
        {
            $customAttributes    = array();
            $attributeCollection = Mage::getModel('customer/attribute')->getCollection();
            $attributeCollection->addFieldToFilter('is_user_defined', 1);
            $attributeCollection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            foreach ($attributeCollection as $attribute)
            {
                if ($inputType = $attribute->getFrontend()->getInputType())
                {
                    switch ($inputType)
                    {
                        case 'date':
                        case 'text':
                        case 'textarea':
                            $customAttributes[$attribute->getAttributeCode()] = $this->getData($attribute->getAttributeCode());
                            break;
                        case 'select':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $customAttributes[$attribute->getAttributeCode()] = $options[$this->getData($attribute->getAttributeCode())];
                            break;
                        case 'multiselect':
                            $options = array();
                            $columnData = '';
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $value = explode(',', $this->getData($attribute->getAttributeCode()));
                            foreach ($options as $val => $label)
                            {
                                if (in_array($val, $value))
                                {
                                    $columnData .= $label . ', ';
                                }
                            }
                            if ($columnData)
                            {
                                $columnData = substr($columnData, 0, -2);
                            }
                            $customAttributes[$attribute->getAttributeCode()] = $columnData;
                            break;
                    }
                }
            }
            $this->_customAttributes = $customAttributes;
        }
        return (isset($this->_customAttributes[$attributeCode]) ? $this->_customAttributes[$attributeCode] : '');
    }
}