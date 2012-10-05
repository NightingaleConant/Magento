<?php
/**
 * @author - Kalpesh Mehta
 * @desc - Payment method configuration
 */

class NightingaleConant_FreeTrial_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = "freetrial";
    
    protected $_canUseCheckout = false; 
    protected $_canUseInternal = true; 
    protected $_canCapture = true; 
    protected $_canCapturePartial = true; 
    
}