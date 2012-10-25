<?php

class NightingaleConant_OrderPlacedBy_Block_Renderer_Shiptoname extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        return $row->getShipFirstname() . " " . $row->getShipLastname();
    }

}
