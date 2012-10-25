<?php
class NightingaleConant_SpecialItem_Model_Observer {
    
    protected $_orderId = 0;
    
    public function attachSpecialItem(Varien_Event_Observer $obs) {
        
        $order = $obs->getEvent()->getOrder();
        if($this->_orderId == 0) {
            $this->_orderId = $order->getId();
        } else {
            return;
        }
        //Mage::log(get_class_methods(get_class($order)));
        $orderAttributes = Mage::app()->getRequest()->getPost('amorderattr');
        $promoCode = $orderAttributes['promo_code'];
        $orderType = $orderAttributes['order_type_code'];
        $custGroupId = $order->getCustomerGroupId();
        
        Mage::log($promoCode."==".$orderType."==".$custGroupId);
        $sku = array();
        foreach($order->getAllItems() as $oitem) {
            $sku[] = $oitem->getSku();
        }
        //Mage::log($sku);
        
        $associateItems = Mage::getModel('specialitem/specialitem')->getCollection()
                ->addFieldToFilter('active', 1);
        Mage::log(count($associateItems));
        foreach($associateItems as $item) {
            $matched = true;
            $iSku = $item->getItemSku();
            $iPromoCode = $item->getPromoCode();
            $iCustomerGroup = $item->getCustomerGroup();
            $iOrderType = $item->getOrderType();
            
            if(!empty($iSku) && !empty($sku)) {
                $matched = in_array($iSku, $sku) ? true : false;
            }
            if($matched && (!empty($iPromoCode) && !empty($promoCode))) {
                $matched = ($iPromoCode == $promoCode) ? true : false;
            }
            if($matched && (is_int($iCustomerGroup) && is_int($custGroupId))) {
                $matched = ($iCustomerGroup == $custGroupId) ? true : false;
            }
            if($matched && (!empty($iOrderType) && !empty($orderType))) {
                $matched = ($iOrderType == $orderType) ? true : false;
            }
            
            if($matched) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getProductSku());
                $qty = 1;
                Mage::log($product->getSku());

                $rowTotal = $product->getPrice();
                $order_item = Mage::getModel('sales/order_item')
                        ->setStoreId($order->getStore()->getStoreId())
                        ->setQuoteItemId(NULL)
                        ->setQuoteParentItemId(NULL)
                        ->setProductId($product->getId())
                        ->setProductType($product->getTypeId())
                        ->setQtyBackordered(NULL)
                        ->setTotalQtyOrdered($qty)
                        ->setQtyOrdered($qty)
                        ->setName($product->getName())
                        ->setSku($product->getSku())
                        ->setPrice($product->getPrice())
                        ->setBasePrice($product->getPrice())
                        ->setOriginalPrice($product->getPrice())
                        ->setRowTotal($rowTotal)
                        ->setBaseRowTotal($rowTotal)
                        ->setOrder($order);
                $order_item->save();
            }            
        }
        
    }
    
    
}