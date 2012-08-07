<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Helper_Pdf extends Mage_Core_Helper_Abstract
{
    public function addAttrbutes(&$page, $obj, $control)
    {
        /* checking settings */
        if ($control instanceof Mage_Sales_Model_Order_Pdf_Invoice && !Mage::getStoreConfig('amorderattr/pdf/invoice'))
        {
            return ;
        }
        if ($control instanceof Mage_Sales_Model_Order_Pdf_Shipment && !Mage::getStoreConfig('amorderattr/pdf/shipment'))
        {
            return ;
        }
        
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }
        
    	/* loading attributes */
        $attributes = Mage::getModel('eav/entity_attribute')->getCollection();
        $attributes->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $attributes->addFieldToFilter('include_pdf', 1);
        $attributes->getSelect()->order('checkout_step');
        $attributes->getSelect()->order('sorting_order');
        
        if (!$attributes->getSize())
        {
            return ;
        }
        
        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
        
        $list = array();
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
                case 'date':
                	$value = $orderAttributes->getData($attribute->getAttributeCode());
                	$format = Mage::app()->getLocale()->getDateTimeFormat(
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                    );
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
        
        if (empty($list))
        {
            return ;
        }
        
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        
        $page->drawRectangle(25, $control->y, 570, $control->y -15);
        $control->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->drawText($this->__('Order Attributes'), 35, $control->y, 'UTF-8');
        $control->y -= 15;
        
        foreach ($list as $label => $value)
        {
            $page->drawText($label . ': ', 35, $control->y, 'UTF-8');
            $page->drawText($value, 150, $control->y, 'UTF-8');
            $control->y -= 10;
        }
        
        $control->y -= 10;
        
    }
}