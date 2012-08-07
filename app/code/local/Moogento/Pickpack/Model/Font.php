<?php

class Moogento_Pickpack_Model_Font
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'helvetica', 'label'=>Mage::helper('pickpack')->__('Helvetica')),
			array('value' => 'times', 'label'=>Mage::helper('pickpack')->__('Times')),
			array('value' => 'courier', 'label'=>Mage::helper('pickpack')->__('Courier'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
