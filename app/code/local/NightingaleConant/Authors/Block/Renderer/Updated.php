<?php

class NightingaleConant_Authors_Block_Renderer_Updated extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $record = $row->getData();        
        foreach ($record as $k => $v) {            
            if ($k == 'updated_by') {
                Mage::log($k .'=='. $v);
                $val = Mage::getModel('admin/user')->load($v)->getName();
                return $val;
            }
        }
        return " ";
    }

}
