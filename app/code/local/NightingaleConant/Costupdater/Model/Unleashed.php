<?php
/**
 * @desc - used by cron to get costs daily from Unleashed and insert into Magento DB
 */
class NightingaleConant_Costupdater_Model_Unleashed extends Mage_Core_Model_Abstract
{
    protected $_endpoint;
    protected $_key = 'TORP+1eAdSyTAwbEkCCC07nq3TpY2+hav9t1/aU9wWeu/JG5V32U34tX8LVm7JhCrM4roiTrbUUCxkLyHpETQw==';
    protected $_id = 'cb139c99-c554-4b15-a8d9-9f22d1f13806';
    protected $_request;
    protected $_requestUrl;
    protected $_storeID = 1; 
    
    //cURL request to get products details from Unleashed..
    public function dailyCosts() {

        $this->_endpoint = 'Products';
        /**
         * If we request whole data at once, Unleashed server is not able to handle it
         * and throws error "500". This is a known issue at their end.
         * https://api.unleashedsoftware.com/KnownIssues (requires login)
         */
        $a = array('0','1','2','3','4','5','6','7','8','9',
            'a','b','c','d','e','f','g','h','i','j','k','l','m','n',
            'o','p','q','r','s','t','u','v','w','x','y','z');
        
        $arr = array(); $values = array();
        
        foreach ($a as $startCode) {
            $this->_request = 'productCode=463A-3';
            //$this->_request = ''; 

            $this->_requestUrl = '';
            if (!empty($this->_request)) $this->_requestUrl = '?'.$this->_request;

            set_time_limit(300);
            // must use true for the last parameter of hash_hmac()
            $method_signature = base64_encode(hash_hmac('sha256', $this->_request, $this->_key, true));
            $useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
            $url = "https://api.unleashedsoftware.com/".$this->_endpoint . $this->_requestUrl;

            $curl = curl_init();
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            //curl_setopt($curl,CURLOPT_HEADER,1);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                    "Accept: application/json", 
                    "api-auth-id: ". $this->_id, 
                    "api-auth-signature: $method_signature",
                    "Keep-Alive: 300",
                    "Connection: keep-alive"
                ));
            curl_setopt($curl, CURLOPT_TIMEOUT, 300);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_USERAGENT, $useragent); 

            $curl_result = curl_exec($curl);

            curl_close($curl);
            $curl_result = rtrim($curl_result, '1');
            $arr = json_decode($curl_result, true);

            foreach((array)$arr["Items"] as $v) {
                $cost = $v["AverageLandPrice"];
                $values[] = array(null, $this->_storeID, $v["ProductCode"], $cost, now());
            }
        }

        Mage::log('Dailycost CRON: trying to insert '.sizeof($values). ' products cost');
        if(sizeof($values) > 0)
            $this->_insertCost($values);
        
    }
    
    //DB operation...
    protected function _insertCost($cValues) {
        
        $res = Mage::getSingleton("core/resource");
        $write = $res->getConnection("core_write");
        $tableName = $res->getTableName('costupdater/dailycost');
        $cols = array('dailycost_id','store_id','sku','cost','created_at');
        
        try {
            $write->insertArray($tableName, $cols, $cValues);
            Mage::log('Dailycost CRON: costs inserted successfully......');
        }
        catch (Exception $e) {
            Mage::log('Dailycost CRON: cron failed!!!!!!');
            Mage::log($e->__toString());
        }
        
    }
    
}

?>