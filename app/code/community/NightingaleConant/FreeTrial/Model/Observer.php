<?php
/**
 * @author - Kalpesh Mehta
 * @desc - Change the status to "freetrial" after the shipment is created
 */

class NightingaleConant_FreeTrial_Model_Observer extends Varien_Object {
          
    public function changeOrderStatusAfterShip($observer) {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $order->setStatus("freetrial")->save();
    }    
    
}

?>