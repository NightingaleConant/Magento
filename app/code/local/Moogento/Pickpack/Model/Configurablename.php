<?php

class Moogento_Pickpack_Model_Configurablename
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'simple', 'label'=>Mage::helper('pickpack')->__('Simple product name')),
			array('value' => 'configurable', 'label'=>Mage::helper('pickpack')->__('Configurable product name'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
