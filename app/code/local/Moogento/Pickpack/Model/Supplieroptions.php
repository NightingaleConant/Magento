<?php

class Moogento_Pickpack_Model_Supplieroptions
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'filter', 'label'=>Mage::helper('pickpack')->__('Hide products from other suppliers')),
			array('value' => 'grey', 'label'=>Mage::helper('pickpack')->__('Grey out products from other suppliers'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
