<?php
class Moogento_Pickpack_Model_Optionschoice
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No')),
            array('value' => 'inline', 'label'=>Mage::helper('pickpack')->__('Yes, inline with product SKU')),
            array('value' => 'newline', 'label'=>Mage::helper('pickpack')->__('Yes, new line for each, under product SKU')),
            array('value' => 'newsku', 'label'=>Mage::helper('pickpack')->__('Yes, count each option as a separate SKU')),
            array('value' => 'newskuparent', 'label'=>Mage::helper('pickpack')->__('Yes, count each option as a separate SKU, grouped under parent product')),
        );
    }

}
