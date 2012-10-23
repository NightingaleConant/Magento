<?php
class NightingaleConant_SalesTaxReport_Block_Renderer_Taxcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {        
        $region_id = $row->getRegionId();        
        if(!is_numeric($region_id)) return '';
        $region = Mage::getSingleton('directory/region')->load($region_id);
        $regionCode = $region->getCode();
        return $regionCode;
    }

}