<?php
class NightingaleConant_PromocodeLookup_Model_Lookup extends Mage_Core_Model_Abstract {
    
    public function _construct() 
    {
        parent::_construct();
        $this->_init('promocodelookup/lookup');
    }
    
    public function lookup($code = '', $name = '')
    {
        if($code == '' && $name == '') {
            return false;
        }
        $coll = Mage::getModel('promocodes/promocodes')->getCollection();
        if($code != '')
            $coll->addFieldToFilter('ckc_key_code', array('like' => '%'.$code.'%'));
        if($name != '')
            $coll->addFieldToFilter('ckc_key_code_name', array('like' => '%'.$name.'%'));
            
        //Mage::log($coll->getData());
        $data = '<table width="350px" style="border:1px solid black">
            <tr><td width="120px"><b>Promo Code</b></td>
            <td width="230px"><b>Description</b></td></tr>';
        foreach($coll->getData() as $v) {
            $data .= '<tr><td><a href="javascript:void(0);" onclick="javascript:opener.document.getElementById(\'promo_code\').value=\''.$v["ckc_key_code"].'\';window.close();">'
                    .$v['ckc_key_code'].'</td><td>'.$v['ckc_key_code_name'].'</td></tr>';
        }
        $data .= '</table>';
        return $data;
        
    }
        
}

?>
