<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Rewrite_Customer_Form extends Mage_Customer_Model_Form
{
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        
        // 1 entity type is customer attributes.
        if (1 != $this->getEntity()->getEntityTypeId())
        {
            return $attributes;
        }
        
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $collection->addFieldToFilter('is_user_defined', 1);
        $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
        foreach ($collection as $attribute)
        {
            // should not add attributes on the order create page in the backend
            if ('sales_order_create' != Mage::app()->getRequest()->getControllerName())
            {
                if ('customer_activated' != $attribute->getAttributeCode() && 'unlock_customer' != $attribute->getAttributeCode())
                {
                    $attributes[] = $attribute;
                }
            }
        }
        return $attributes;
    }
    
    protected function _isAttributeOmitted($attribute)
    {
        $res = parent::_isAttributeOmitted($attribute);
        if (!Mage::app()->getStore()->isAdmin() && $attribute->getIsUserDefined()){
            // will skip all user-defined (created with the extension) attributes, to avoid errors on checkout for registered customer
            $res = true;
        }
        return $res;
    }
}