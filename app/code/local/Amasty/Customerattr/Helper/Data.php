<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function fields()
    {
        return Mage::app()->getLayout()->createBlock('amcustomerattr/customer_fields')->toHtml();
    }
    
    public function getAttributesHash()
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $collection->addFieldToFilter('is_user_defined', 1);
        if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Orderattr/active'))
        {
            $alias = 'main_table';
            if (false !== strpos($collection->getSelect()->__toString(), $alias))
            {
                $collection->addFieldToFilter('main_table.is_visible_on_front', 1);
            } else 
            {
                $collection->addFieldToFilter('e.is_visible_on_front', 1);
            }
        } else 
        {
            $collection->addFieldToFilter('is_visible_on_front', 1);
        }
        $attributes = $collection->load();
        $hash       = array();
        foreach ($attributes as $attribute)
        {
            $hash[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        return $hash;
    }
    
    public function getAttributeImageUrl($optionId)
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 
                                                    'amcustomerattr' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        if (file_exists($uploadDir . $optionId . '.jpg'))
        {
            return Mage::getBaseUrl('media') . '/' . 'amcustomerattr' . '/' . 'images' . '/' . $optionId . '.jpg';
        }
        return '';
    }
}