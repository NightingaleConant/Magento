<?php

class Moogento_Pickpack_Model_Configurablenamecustom
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'simple', 'label'=>Mage::helper('pickpack')->__('Simple product name, even in configurable')),
			array('value' => 'configurable', 'label'=>Mage::helper('pickpack')->__('Configurable product name')),
			array('value' => 'custom', 'label'=>Mage::helper('pickpack')->__('Pull from custom attribute')),
		);
	}

}
