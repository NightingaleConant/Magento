<?php

class Moogento_Pickpack_Model_Fontstyle
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'regular', 'label'=>Mage::helper('pickpack')->__('Regular')),
			array('value' => 'bold', 'label'=>Mage::helper('pickpack')->__('Bold')),
			array('value' => 'italic', 'label'=>Mage::helper('pickpack')->__('Italic')),
			array('value' => 'bolditalic', 'label'=>Mage::helper('pickpack')->__('Bold italic'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
