<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Model_Sales_Order extends Mage_Sales_Model_Order
{
    /**
    * Gets the value of custom order attribute
    * 
    * @param string $attr attribute code
    */
    public function custom($attr) {
        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($this->getId(), 'order_id');
        $attribute = Mage::getModel('eav/entity_attribute')->load($attr, 'attribute_code');
        if ($attribute->getAttributeId()) {
            switch ($attribute->getFrontendInput())
            {
                case 'select':
                    $options = $attribute->getSource()->getAllOptions(true, true);
                    foreach ($options as $option)
                    {
                        if ($option['value'] == $orderAttributes->getData($attribute->getAttributeCode()))
                        {
                            $value = $option['label'];
                            break;
                        }
                    }
                    break;
                default:
                    $value = $orderAttributes->getData($attribute->getAttributeCode());
                    break;
            }
            return $value;
        }
    }
}