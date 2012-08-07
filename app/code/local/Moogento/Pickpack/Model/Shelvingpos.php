<?php

class Moogento_Pickpack_Model_Shelvingpos
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'col', 'label'=>Mage::helper('pickpack')->__('Put in separate column')),
			array('value' => 'sku', 'label'=>Mage::helper('pickpack')->__('Add on right of SKU'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
