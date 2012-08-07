<?php

class Moogento_Pickpack_Model_Capitaladdress
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => '1', 'label'=>Mage::helper('pickpack')->__('Yes, all addresses')),
			array('value' => 'usonly', 'label'=>Mage::helper('pickpack')->__('Yes, US addresses only')),
			array('value' => '0', 'label'=>Mage::helper('pickpack')->__('No'))
		);
	}

}
