<?php

class Moogento_Pickpack_Model_Tickbox
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No')),
			array('value' => 'pick', 'label'=>Mage::helper('pickpack')->__('Yes, for Pick Lists only')),
			array('value' => 'pickpack', 'label'=>Mage::helper('pickpack')->__('Yes, for Pick Lists and Packing Sheets'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
