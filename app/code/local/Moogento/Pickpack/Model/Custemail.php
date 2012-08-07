<?php

class Moogento_Pickpack_Model_Custemail
{

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'yes', 'label'=>Mage::helper('pickpack')->__('Yes, order details + shipping label')),
			array('value' => 'yesdetails', 'label'=>Mage::helper('pickpack')->__('Yes, order details only')),
			array('value' => 'yeslabel', 'label'=>Mage::helper('pickpack')->__('Yes, shipping label only')),
			array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
			// array('value' => 'split', 'label'=>Mage::helper('pickpack')->__('Split PDFs by supplier')),
		);
	}

}
