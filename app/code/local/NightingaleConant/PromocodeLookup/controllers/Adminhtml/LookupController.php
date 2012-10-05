<?php
class NightingaleConant_PromocodeLookup_Adminhtml_LookupController extends Mage_Adminhtml_Controller_action {
    
    public function indexAction() 
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function lookupAction() 
    {
        $params = $this->getRequest()->getParams();
        $code = $params['code'];
        $name = $params['name'];
        if($code == '' && $name == '') {
            return false;
        }
        echo Mage::getModel('promocodelookup/lookup')->lookup($code, $name);
    }
    
}