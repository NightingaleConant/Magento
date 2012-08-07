<?php
class Amasty_Customerattr_Model_Rewrite_Customer_Resource_Attribute extends Mage_Customer_Model_Resource_Attribute
{
    public function saveAttributeConfiguration($attributeId, $configuration)
    {
        $this->_getWriteAdapter()->update($this->getTable('customer/eav_attribute'), $configuration, 'attribute_id = "' . $attributeId . '"');
    }
}