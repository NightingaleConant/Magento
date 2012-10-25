<?php
class NightingaleConant_SalesTaxReport_Block_Salestaxreport extends Mage_Core_Block_Template {
 
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
 
    public function getSalesTaxReport() {
        if (!$this->hasData('salestaxreport')) {
            $this->setData('salestaxreport', Mage::registry('salestaxreport'));
        }
        return $this->getData('salestaxreport');
    }
 
}