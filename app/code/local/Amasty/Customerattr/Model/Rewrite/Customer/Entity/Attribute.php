<?php
class Amasty_Customerattr_Model_Rewrite_Customer_Entity_Attribute extends Mage_Customer_Model_Entity_Attribute
{
    public function saveAttributeConfiguration($attributeId, $configuration)
    {
        $this->_getWriteAdapter()->update($this->getTable('customer/eav_attribute'), $configuration, 'attribute_id = "' . $attributeId . '"');
    }
}