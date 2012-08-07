<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Fields_Email extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amorderattr/list_email.phtml');
    }
    
    public function getList()
    {
        $list = array();
        
        $collection = Mage::getModel('eav/entity_attribute')->getCollection();
        $collection->addFieldToFilter('is_visible_on_front', 1);
        $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $collection->getSelect()->order('checkout_step');
        $attributes = $collection->load();
        
        $order = $this->getOrder();
        
        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
        
        if ($attributes->getSize())
        {
            foreach ($attributes as $attribute)
            {
                $currentStore = $order->getStoreId();
                $storeIds = explode(',', $attribute->getData('store_ids'));
                if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds))
                {
                    continue;
                }
                
                $value = '';
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
                
                $translations = $attribute->getStoreLabels();
                if (isset($translations[$order->getStoreId()]))
                {
                    $attributeLabel = $translations[$order->getStoreId()];
                } else 
                {
                    $attributeLabel = $attribute->getFrontend()->getLabel();
                }
                
                $list[$attributeLabel] = $value;
            }
        }
        
        return $list;
    }
    
    protected function _toHtml()
    {
        if (!Mage::getStoreConfig('sales_email/order/include'))
        {
            return '';
        }
        return parent::_toHtml();
    }
}
