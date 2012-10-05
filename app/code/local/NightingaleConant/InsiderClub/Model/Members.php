<?php
class NightingaleConant_InsiderClub_Model_Members extends Mage_Core_Model_Abstract {
    
    public function getFreeProductHistory($custId) {
        
        if($custId == '' || $custId <= 0) {
            return 'Invalid Customer ID';
        }
        $order_type = 198; //ICR - Insiders Club
        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('customer_id', array('eq' => $custId))
            ->addFieldToSelect('entity_id');
        $orderCollection->getSelect()->joinLeft('amasty_amorderattr_order_attribute as amasty',
                'amasty.order_id = main_table.entity_id', array());
        $orderCollection->getSelect()->where('amasty.order_type_code = "'.$order_type.'"');
        //Mage::log($orderCollection->getSelect()->__toString());
        $orderIds = $orderCollection->getAllIds();
        
        $orderItems = Mage::getResourceModel('sales/order_item_collection')
            ->addFieldToSelect('item_id')->addFieldToSelect('price')
            ->addFieldToSelect('product_id')
            ->addFieldToFilter('order_id', array('in' => $orderIds));
        //echo $orderItems->getSelect()->__toString();
        
        $cnt = 0;
        foreach($orderItems as $item) {
            if($item->getPrice() == 0.00) {
                $cnt++;
            } else {
                $p = Mage::getModel('catalog/product')->load($item->getProductId());
                $pPrice = $p->getPrice();
                $priceMinus100 = $pPrice - 100;
                $iPrice = $item->getPrice();                
                $epsilon = 0.01; //two digits of precision.. because comparing two floats was never easy!

                if(($pPrice >= 100) && (abs($priceMinus100 - $iPrice) < $epsilon)) {
                    $cnt++;
                }
            }
        }
        
        return $cnt;
    }    
}