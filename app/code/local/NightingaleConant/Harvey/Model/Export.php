<?php
class NightingaleConant_Harvey_Model_Export extends Mage_Core_Model_Abstract {
    
    protected $_status = 'ready_to_print'; //Ready To Ship status here...
    
    public function fromMagento() {
        
        $file = Mage::getStoreConfig('harvey/configuration/export');
        if($file == '') {
            return 'nofile';            
        }
        
        $service_codes = $this->getServiceCodes();
        
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addFieldToSelect('customer_email')
                ->addFieldToSelect('entity_id','order_id')
                ->addFieldToSelect('shipping_description');
        $orders->getSelect()->joinLeft('sales_flat_order_address as oaddr', 
                'main_table.shipping_address_id = oaddr.entity_id', 
                array('firstname','lastname','company','customer_id', 'street',
                    'postcode','region_id','city','country_id','telephone'));        
        $orders->addFieldToFilter('status', $this->_status); //Ready To Ship
        
        //Mage::log($orders->getSelect()->__toString());

        $data = ""; $country_arr = array();
        foreach($orders as $order) {
            $cust_name = $order->getFirstname() ." ". $order->getLastname();
            $company = $order->getCompany() == null ? $cust_name : $order->getCompany();
            $service = $service_codes[$order->getShippingDescription()];
            if($country_arr[$order->getCountryId()]) {
                $country_name = $country_arr[$order->getCountryId()];
            } else {
                $country_name = $this->getCountryName($order->getCountryId());
                $country_arr[$order->getCountryId()] = $country_name;
            }
            
            
            $data .= str_pad($company, 35, " ", STR_PAD_RIGHT) . 
                str_pad($cust_name, 35, " ", STR_PAD_RIGHT) .
                str_pad($order->getOrderId(), 20, " ", STR_PAD_RIGHT) .
                str_pad($order->getStreet(), 35, " ", STR_PAD_RIGHT) .
                str_pad(" ", 35, " ", STR_PAD_RIGHT) .
                str_pad($order->getCity(), 30, " ", STR_PAD_RIGHT) .
                str_pad($order->getRegionId(), 5, " ", STR_PAD_RIGHT) .
                str_pad($order->getPostcode(), 10, " ", STR_PAD_RIGHT) .
                str_pad($country_name, 20, " ", STR_PAD_RIGHT) .
                str_pad($order->getTelephone(), 20, " ", STR_PAD_RIGHT) .
                str_pad($order->getCustomerEmail(), 60, " ", STR_PAD_RIGHT) .
                str_pad($service, 7, " ", STR_PAD_RIGHT) . "\n";
            
        }
        
        $handle = fopen($file,"w+");
        $write = fwrite($handle,$data);   
        fclose($handle);
        
        if($write)
            return true;
        else
            return false;
    }
    
    private function getServiceCodes() {
        
        return array(
            "USPS Media Mail" => "EPO@@12",
            "USPS Priority Mail" => "EPO@@02",
            "USPS 1st Class Mail" => "EPO@@04",
            "USPS Air Parcel Post" => "EPO@@08",
            "USPS Parcel Post" => "EPO@@08",
            "FedEx Home Delivery" => "FEX@@80",
            "FedEx 2 Day" => "FEX@@02",
            "FedEx Ground" => "FEX@@44",
            "FedEx International" => "FEX@@68",
            "FedEx Standard Overnight" => "FEX@@13",
            "FedEx Priority Overnight" => "FEX@@01",
            "FedEx First Overnight" => "FEX@@15",
            "FedEx Express Saver" => "FEX@@08",
            "Canada MSI Mail" => "MSI   2"
        );
    }
    
    private function getCountryName($code) {
        if($code != "")
            return Mage::app()->getLocale()->getCountryTranslation($code);
        return "";
    }
}

?>
