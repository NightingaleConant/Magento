<?php
class Moogento_Pickpack_Model_Observer
{

    public function applyLimitToGrid(Varien_Event_Observer $observer)
    {
    	$block = $observer->getEvent()->getBlock();
    	if(($block instanceof Mage_Adminhtml_Block_Widget_Grid) && !($block  instanceof Mage_Adminhtml_Block_Dashboard_Grid))
    	$block->setDefaultLimit(100);

    }

}
