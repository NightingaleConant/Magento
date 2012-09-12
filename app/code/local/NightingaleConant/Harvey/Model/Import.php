<?php
class NightingaleConant_Harvey_Model_Import extends Mage_Core_Model_Abstract {
    
    protected $_status = 'printed'; //Printed status here...
    
    public function toMagento() {
        $path = Mage::getStoreConfig('harvey/configuration/import');
        $content = file_get_contents($path);
        $data = explode("\n",$content);
        
        if(empty($data)) {
            return 'File is empty';
        }
                
        $ship_details = $this->getShipDetails();
        $shippedOrders = 0;
                
        foreach($data as $v) {
            //$v1 = preg_split('/\s{2,}/', $v);
            $harvey_invoice = trim(substr($v, 0, 15));
            $order_id = trim(substr($v, 15, 20));
            $track_number = trim(substr($v, 35, 30));
            $cust_name = trim(substr($v, 65, 35));
            $addr1 = trim(substr($v, 100, 35));
            $addr2 = trim(substr($v, 135, 35));
            $city = trim(substr($v, 170, 30));
            $state = trim(substr($v, 200, 5));
            $zip = trim(substr($v, 205, 10));
            $flg = trim(substr($v, 215, 1));
            $box_number = trim(substr($v, 216, 4));
            $weight = trim(substr($v, 220, 10));
            $cost = trim(substr($v, 230, 8));
            $ship_date = trim(substr($v, 238, 8));
            $harvey_data = trim(substr($v, 246, 6));
            $service_method = trim(substr($v, 252, 3));
            $service_type = trim(substr($v, 255, 2));
            
            $track_number = ($track_number == '') ? 'Tracking Number Not Available' : $track_number;
            if($service_method != '' && $service_type != '')
                $ship_title = $ship_details[$service_method][$service_type];
            else
                $ship_title = "";
            $carrier_code = ($service_method == 'EPO') ? 'usps' : (($service_method == 'FEX') ? 'fedex' : 'custom' );
            
            /* Checking the values for testing...
              Mage::log($harvey_invoice."==".$order_id."==".$track_number."==".$cust_name."=="
                    .$addr1."==".$addr2."==".$city."==".$state."==".$zip."==".$flg."=="
                    .$box_number."==".$weight."==".$cost."==".$ship_date."==".$harvey_data
                    ."==".$service_method."==".$service_type,"==".$ship_title."==".$carrier_code);
                Mage::log('======================================');            
             */
            
            
            /**
             * Changing the flow of default Magento, which changes order status to 'complete' after shipment.
             * Below code will change the order status to 'Printed'
             */
            
            $order = Mage::getModel('sales/order')
                     ->loadByIncrementId($order_id);
            
            if($order->canShip()) {
            
                $convertOrder = new Mage_Sales_Model_Convert_Order();
                $shipment = $convertOrder->toShipment($order);

                $items = $order->getAllItems();
                $totalQty = 0;
                if (count($items)) {
                    foreach ($items as $_eachItem) {
                        $_eachShippedItem = $convertOrder->itemToShipmentItem($_eachItem);
                        $_eachShippedItem->setQty($_eachItem->getQtyOrdered());
                        $shipment->addItem($_eachShippedItem);
                        $totalQty += $_eachItem->getQtyOrdered();
                    }

                    $shipment->setTotalQty($totalQty);
                }

                $arrTracking = array(
                    'carrier_code' => $carrier_code,
                    'title' => $ship_title,
                    'number' => $track_number,
                );
                
                $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
                $shipment->addTrack($track);
                

                try {
                    $saveTransaction = Mage::getModel('core/resource_transaction')
                                        ->addObject($shipment)
                                        ->addObject($shipment->getOrder())
                                        ->save();

                    /**
                    //This will mark all the order items as shipped,
                    //which will eventually change the order to Complete state.
                    if (count($items)) {
                        foreach ($items as $_eachItem) {
                            $qtyOrdered = $_eachItem->getQtyOrdered();
                            $_eachItem->setQtyShipped($qtyOrdered);
                        }
                        
                    }*/
                    
                    //Updating status of order as "shipped"...
                    $shipment->getOrder()->setIsInProcess(true);
                    $shipment->getOrder()->setStatus($this->_status);
                    $shipment->getOrder()->save();
                    return true;
                } catch(Exception $e) {
                    Mage::log($e->__toString());
                    return $e->__toString();
                }
                
                $shippedOrders += 1;
            }
        }
        if($shippedOrders == 0) {
            return 'No orders found which can be shipped';
        }
        
    }
    
    private function getShipDetails() {
        
        return array(
            "EPO" => array(
                "12" => "USPS Media Mail",
                "02" => "USPS Priority Mail",
                "04" => "USPS 1st Class Mail",
                "08" => "USPS Air Parcel Post"                
            ),
            "FEX" => array(
                "80" => "FedEx Home Delivery",
                "02" => "FedEx 2 Day",
                "44" => "FedEx Ground",
                "68" => "FedEx International",
                "13" => "FedEx Standard Overnight",
                "01" => "FedEx Priority Overnight",
                "15" => "FedEx First Overnight",
                "08" => "FedEx Express Saver"
            ),
            "MSI" => array(
                "2" => "Canada MSI Mail"
            )
        );
    }
}

?>
