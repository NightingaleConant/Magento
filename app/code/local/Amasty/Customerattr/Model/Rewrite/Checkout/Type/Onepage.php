<?php
class Amasty_Customerattr_Model_Rewrite_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    public function saveBilling($data, $customerAddressId)
    {
        if (isset($data['amcustomerattr']))
        {
            // checking unique attributes
            $checkUnique = array();
            $collection = Mage::getModel('eav/entity_attribute')->getCollection();
            $collection->addFieldToFilter('is_user_defined', 1);
            $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            foreach ($collection as $attribute)
            {
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
            if ($checkUnique)
            {
                foreach ($checkUnique as $attributeCode => $attributeLabel)
                {
                    $customerCollection = Mage::getResourceModel('customer/customer_collection');
                    $customerCollection->addAttributeToSelect($attributeCode);
                    if ($customerCollection->getSize() > 0)
                    {
                        foreach ($customerCollection as $customerWithAttribute)
                        {
                            if ($data['amcustomerattr'][$attributeCode] == $customerWithAttribute->getData($attributeCode))
                            {
                                $result = array(
                                    'error'     => 1,
                                    'message'   => Mage::helper('amcustomerattr')->__('Please specify different value for "%s" attribute. Customer with such value already exists.', $attributeLabel),
                                );
                                return $result;
                            }
                        }
                    }
                }
            }
            Mage::getSingleton('checkout/session')->setAmcustomerattr($data['amcustomerattr']);
        }
        return parent::saveBilling($data, $customerAddressId);
    }
}