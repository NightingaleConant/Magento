<?php
class Amasty_Orderattr_Helper_Core_Data extends Mage_Core_Helper_Data
{
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
    {
        if ('saveBilling' == Mage::app()->getRequest()->getActionName())
        {
            if ('shipping_method' == $valueToEncode['goto_section'])
            {
                // will check if we have required attributes on shipping info step
                $collection = Mage::getModel('eav/entity_attribute')->getCollection();
                $collection->addFieldToFilter('is_visible_on_front', 1);
                $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
                $collection->addFieldToFilter('checkout_step', 3);
                $collection->addFieldToFilter('is_required', 1);
                $collection->load();
                $forceShipping = false;
                if ($collection->getSize() > 0)
                {
                    foreach ($collection as $attribute)
                    {
                        $currentStore = Mage::app()->getStore()->getId();
                        $storeIds = explode(',', $attribute->getData('store_ids'));
                        if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds))
                        {
                            continue;
                        }
                        $forceShipping = true;
                    }
                }
                if ($forceShipping)
                {
                    $valueToEncode['goto_section'] = 'shipping';
                    unset($valueToEncode['update_section']);
                }
            }
        }
        return parent::jsonEncode($valueToEncode, $cycleCheck, $options);
    }
}