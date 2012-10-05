<?php
class NightingaleConant_Harvey_Adminhtml_ShipController extends Mage_Adminhtml_Controller_Action {
    
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function exportAction() {
        $e = Mage::getModel('harvey/export')->fromMagento();
        if($e === true) {
            echo 'Successfully exported data';
        } elseif($e === 'nofile') {
            echo 'File path is empty';
        } else {
            echo 'Failed to export';
        }
    }
    
    public function importAction() {
        $i = Mage::getModel('harvey/import')->toMagento();
        if($i === true) {
            echo 'Successfully imported data';
        } elseif($i !== false) {
            echo $i;
        } else {
            echo 'Failed to export';
        }
    }
}