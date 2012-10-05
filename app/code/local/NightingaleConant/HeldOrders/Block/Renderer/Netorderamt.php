<?php

class NightingaleConant_HeldOrders_Block_Renderer_Netorderamt extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        return $row->getSubtotal() + $row->getShippingAmount();
    }

}
