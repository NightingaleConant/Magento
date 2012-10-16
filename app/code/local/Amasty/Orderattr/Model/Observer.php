<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Model_Observer
{
    public function onSalesQuoteSaveAfter($observer)
    {
        $quote = $observer->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        if (Mage::app()->getRequest()->getPost('amorderattr'))
        {
            $session = Mage::getSingleton('checkout/type_onepage')->getCheckout();
            $orderAttributes = $session->getAmastyOrderAttributes();
            if (!$orderAttributes)
            {
                $orderAttributes = array();
            }
            $orderAttributes = array_merge($orderAttributes, Mage::app()->getRequest()->getPost('amorderattr'));
            $session->setAmastyOrderAttributes($orderAttributes);
        }
    }
    
    public function onCheckoutTypeOnepageSaveOrderAfter($observer)
    {
        $order = $observer->getOrder();
        $session = Mage::getSingleton('checkout/type_onepage')->getCheckout();
        $orderAttributes = $session->getAmastyOrderAttributes();
        if (is_array($orderAttributes) && !empty($orderAttributes))
        {
            $attributes = Mage::getModel('amorderattr/attribute');
            $attributes->addData($orderAttributes);
            $attributes->setData('order_id', $order->getId());
            
            /* check if the customer has a hold code and save it here */
            if(Mage::getSingleton('customer/session')->getCustomer()->getHoldCode()){
                $attributes->setData('order_hold_code', Mage::getSingleton('customer/session')->getCustomer()->getHoldCode());
                $order->hold()->save();
            }
            
            $this->_applyDefaultValues($order, $attributes);
            $attributes->save();
            $session->setAmastyOrderAttributes(array());
        }
    }
    
    // this will be used when creating/editing order in the backend
    public function onSalesOrderSaveAfter($observer)
    {
        if (false !== strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order') && 'save' == Mage::app()->getRequest()->getActionName() && !Mage::registry('amorderattr_saved'))
        {
            $order = $observer->getOrder();
            $orderAttributes = Mage::app()->getRequest()->getPost('amorderattr');
            if (is_array($orderAttributes) && !empty($orderAttributes))
            {
                $attributes = Mage::getModel('amorderattr/attribute');
                $attributes->addData($orderAttributes);
                $attributes->setData('order_id', $order->getId());
                $this->_applyDefaultValues($order, $attributes); // $attributes might be modified in that function
                $attributes->save();
                Mage::register('amorderattr_saved', true);
            }
        }
    }
    
    protected function _applyDefaultValues($order, $attributes)
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType('order')->getTypeId() );
        $collection->getSelect()
            ->where('main_table.is_user_defined = ?', 1)
            ->where('main_table.apply_default = ?', 1);
        if ($collection->getSize() > 0)
        {
            foreach ($collection as $attributeToApply)
            {
                if (!$attributes->getData($attributeToApply->getAttributeCode()) && $attributeToApply->getDefaultValue())
                {
                    $attributes->setData($attributeToApply->getAttributeCode(), $attributeToApply->getDefaultValue());
                }
            }
        }
    }
}
