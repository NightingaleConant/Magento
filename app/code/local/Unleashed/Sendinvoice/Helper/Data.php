<?php

class Unleashed_Sendinvoice_Helper_Data extends Mage_Core_Helper_Abstract
{
    
        public static function traceme($str)
    {   //echo $str;
    
        $str.= " -------------CREATED ON >> ".now();
        $myFile = Mage::getBaseDir('media').DS."unleashed".DS."mytracefile.txt";
        
        //chmod($myFile, 0777);
        $fh = fopen($myFile, 'a') or die("can't open file");
        //chmod($myFile, 0755);
        $stringData = $str."\n\n";
        fwrite($fh, $stringData);

        fclose($fh);

    }

}