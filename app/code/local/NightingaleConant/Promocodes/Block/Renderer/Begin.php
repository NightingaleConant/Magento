<?php

class NightingaleConant_Promocodes_Block_Renderer_Begin extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $record = $row->getData();        
        foreach ($record as $k => $v) {
            if($k == 'ckc_begin_date')
                return date("d M Y", strtotime($v));
        }
        return " ";
    }

}
