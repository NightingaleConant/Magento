<?php

class NightingaleConant_Authors_Block_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        foreach ((array) $row->getData() as $k => $v) {
            if ($k == 'image') {
                $val = "<a href='' onclick='javascript:window.open(\"" . Mage::getBaseUrl('media') . $v . "\");'><img src='" . Mage::getBaseUrl('media') . $v . "' width='100' height='100' border='0' /></a>";
                return $val;
            }
        }
        return " ";
    }

}
