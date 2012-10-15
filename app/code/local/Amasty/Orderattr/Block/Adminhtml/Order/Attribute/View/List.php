<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Attribute_View_List extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amorderattr/list.phtml');
    }
    
    public function getEditUrl()
    {
    	$orderId = Mage::registry('current_order')->getId();
    	return $this->getUrl('amorderattr/adminhtml_order/edit', array('order_id' => $orderId));
    }
    
    public function getList()
    {
        $list = array();
        
        $collection = Mage::getModel('eav/entity_attribute')->getCollection();
        $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $collection->getSelect()->order('checkout_step');
        $collection->getSelect()->order('sorting_order');
        $attributes = $collection->load();
        
        $order = Mage::registry('current_order');
        
        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
        
	$orderholdValue = false;
	$attributeHold = Mage::getModel('eav/config')->getAttribute('customer','hold_code');
	$attributeOptions = $attributeHold->getSource()->getAllOptions();
	foreach($attributeOptions as $_attributeOption){
	    if($_attributeOption['value'] == $orderAttributes->getOrderHoldCode()){
		$orderholdValue = $_attributeOption['label'];
	    }
	}
	
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
			    if($attribute->getAttributeCode() == "order_hold_code"){
				if($option['label'] == $orderholdValue){
				    $value = $option['label'];
				    break;
				}
			    }
			    
                            if ($option['value'] == $orderAttributes->getData($attribute->getAttributeCode()))
                            {
                                $value = $option['label'];
                                break;
                            }
                        }
                        break;
                    case 'date':
                    	$value = $orderAttributes->getData($attribute->getAttributeCode());
                    	$format = Mage::app()->getLocale()->getDateTimeFormat(
	                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
	                    );
	                    if (!$value)
	                    {
	                        break;
	                    }
                    	if ('time' == $attribute->getNote())
                    	{
                    		$value = Mage::app()->getLocale()->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)->toString($format);
                    	} else 
                    	{
                    		$format = trim(str_replace(array('m', 'a', 'H', ':', 'h', 's'), '', $format));
                    		$value = Mage::app()->getLocale()->date($value, Varien_Date::DATE_INTERNAL_FORMAT, null, false)->toString($format);
                    	}
                    	break;
                    default:
                        $value = $orderAttributes->getData($attribute->getAttributeCode());
                        break;
                }
                $list[$attribute->getFrontendLabel()] = str_replace('$', '\$', $value);
            }
        }
        return $list;
    }
}