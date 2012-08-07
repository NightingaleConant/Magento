<?php

class Moogento_Pickpack_Model_Supplierfilter
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'pick', 'label'=>Mage::helper('pickpack')->__('Pick Lists only')),
			array('value' => 'pickpack', 'label'=>Mage::helper('pickpack')->__('All, Pick Lists and Packing Sheets'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
