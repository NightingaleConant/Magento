<?php

class Moogento_Pickpack_Model_Fontsize
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 6, 'label'=>Mage::helper('pickpack')->__('6pt')),
			array('value' => 7, 'label'=>Mage::helper('pickpack')->__('7pt')),
			array('value' => 8, 'label'=>Mage::helper('pickpack')->__('8pt')),
			array('value' => 9, 'label'=>Mage::helper('pickpack')->__('9pt')),
			array('value' => 10, 'label'=>Mage::helper('pickpack')->__('10pt')),
			array('value' => 11, 'label'=>Mage::helper('pickpack')->__('11pt')),
			array('value' => 12, 'label'=>Mage::helper('pickpack')->__('12pt')),
			array('value' => 13, 'label'=>Mage::helper('pickpack')->__('13pt')),
			array('value' => 14, 'label'=>Mage::helper('pickpack')->__('14pt')),
			array('value' => 15, 'label'=>Mage::helper('pickpack')->__('15pt')),
			array('value' => 16, 'label'=>Mage::helper('pickpack')->__('16pt')),
			array('value' => 17, 'label'=>Mage::helper('pickpack')->__('17pt')),
			array('value' => 18, 'label'=>Mage::helper('pickpack')->__('18pt')),
			array('value' => 24, 'label'=>Mage::helper('pickpack')->__('24pt'))
		);
	}

}
