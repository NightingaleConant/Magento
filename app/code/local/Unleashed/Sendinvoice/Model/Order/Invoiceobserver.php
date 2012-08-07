<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
class Unleashed_Sendinvoice_Model_Order_Invoiceobserver
{
    

   public function ping()
   {
      $customer_id = Mage::getStoreConfig('sendinvoice/mycustom_group/customer_id');
    $ch = curl_init();
   
    /*if(function_exists('curl_init'))
        echo "curl_init exists";
    else echo "curl_init doesnt exist";*/
// set URL and other appropriate options
    $url = "https://www71.unleashedsoftware.com/v2/Ping/Magento/".$customer_id;
    
    curl_setopt($ch, CURLOPT_URL, $url);
    
    //https://unleashedsoftware.com/v2/Ping/Magento/{id}
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    curl_setopt ($ch, CURLOPT_TIMEOUT, 78);
    

    $msg="";

// grab URL and pass it to the browser
        $msg = curl_exec($ch);
         
        
        if(curl_errno($ch)){
	 $msg = curl_error($ch);

	$str = "Ping failed:\nError:".$msg."         \n".$url;

	}else $str = "Ping was successful:\n".$msg."        \n".$url;

       
        
        Unleashed_Sendinvoice_Helper_Data::traceme($str);
        
// close cURL resource, and free up system resources
        curl_close($ch); 
       
       /*

echo "<br/>----------------------------------------------------------------------------<br/>";    
echo "If you can see the ping results above the doted line then it is pinging successfully"; exit;
exit;*/
   } 
    
    
    
}
